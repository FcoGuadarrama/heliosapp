<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use Illuminate\Support\Facades\Validator;

final class ReadingAssessment
{
    public function __construct(private ReceiptImportValidator $validator) {}

    public function validate(string $json): ReceiptValidationResult
    {
        return $this->validator->validateDecoded($this->decode($json));
    }

    /** @return array<string, mixed> */
    public function decode(string $json): array
    {
        $data = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            throw new ReceiptReadingFailure('reader_invalid_shape');
        }
        $rules = [
            'document' => 'required|array', 'document.source_type' => 'required|in:pdf,image',
            'document.source_reference' => 'required|string', 'document.page_count' => 'required|integer:strict|between:1,6',
            'document.extracted_at' => 'required|date', 'document.extractor' => 'required|array',
            'document.extractor.identifier' => 'required|string', 'document.extractor.version' => 'required|string',
            'receipt.issue_date' => 'nullable|date_format:Y-m-d', 'receipt.due_date' => 'nullable|date_format:Y-m-d',
            'receipt' => 'sometimes|array', 'service' => 'sometimes|array',
            'current_period' => 'sometimes|array', 'consumption_history' => 'sometimes|array|max:36',
            'consumption_history.*' => 'array', 'extraction' => 'required|array',
            'extraction.status' => 'required|in:complete,partial,failed',
            'extraction.missing_fields' => 'sometimes|array', 'extraction.missing_fields.*' => 'string',
            'extraction.confidence' => 'sometimes|array', 'extraction.confidence.*' => 'numeric:strict|between:0,1',
            'extraction.warnings' => 'sometimes|array', 'extraction.warnings.*' => 'array',
            'extraction.warnings.*.code' => 'nullable|string', 'extraction.warnings.*.field' => 'nullable|string',
            'extraction.warnings.*.message' => 'nullable|string',
            'extraction.evidence' => 'sometimes|array', 'extraction.evidence.*' => 'array',
            'extraction.evidence.*.field' => 'required|string', 'extraction.evidence.*.page' => 'required|integer:strict|min:1',
            'extraction.evidence.*.text' => 'required|string', 'extraction.evidence.*.bbox' => 'sometimes|nullable|array|size:4',
            'extraction.evidence.*.bbox.*' => 'numeric:strict|between:0,1',
            'service.service_address' => 'sometimes|nullable|array',
            'current_period.consumption_by_tier' => 'sometimes|array', 'current_period.consumption_by_tier.*' => 'array',
            'current_period.concepts' => 'sometimes|array', 'current_period.concepts.*' => 'array',
        ];
        foreach (['current_period', 'consumption_history.*'] as $prefix) {
            foreach (['period_start', 'period_end'] as $key) {
                $rules[$prefix.'.'.$key] = 'nullable|date_format:Y-m-d';
            }
            foreach (['days', 'consumption_kwh', 'amount_mxn'] as $key) {
                $rules[$prefix.'.'.$key] = 'nullable|numeric:strict|between:-1000000000000,1000000000000';
            }
        }
        foreach (['receipt.receipt_id', 'service.service_number', 'service.tariff_code', 'service.tariff_description',
            'service.voltage_level', 'service.billing_cycle', 'service.holder_name', 'consumption_history.*.source',
            'service.service_address.street', 'service.service_address.neighborhood', 'service.service_address.municipality',
            'service.service_address.state', 'service.service_address.postal_code', 'current_period.consumption_by_tier.*.tier',
            'current_period.concepts.*.code', 'current_period.concepts.*.description'] as $field) {
            $rules[$field] = 'nullable|string';
        }
        foreach (['receipt.total_amount_mxn', 'service.phases', 'service.contracted_demand_kw', 'current_period.demand_kw',
            'current_period.consumption_by_tier.*.kwh', 'current_period.consumption_by_tier.*.unit_price_mxn',
            'current_period.consumption_by_tier.*.amount_mxn', 'current_period.concepts.*.amount_mxn'] as $field) {
            $rules[$field] = 'nullable|numeric:strict|between:-1000000000000,1000000000000';
        }
        if (Validator::make($data, $rules)->fails()) {
            throw new ReceiptReadingFailure('reader_invalid_shape');
        }

        return $data;
    }
}
