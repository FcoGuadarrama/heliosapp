<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `service` — datos del servicio eléctrico y, en `holder_name` /
 * `service_address`, datos personales del cliente final (contrato §6).
 */
final readonly class ServiceData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $serviceNumber = null,
        public ?string $tariffCode = null,
        public ?string $tariffDescription = null,
        public ?string $voltageLevel = null,
        public int|float|null $phases = null,
        public int|float|null $contractedDemandKw = null,
        public ?string $billingCycle = null,
        public ?string $holderName = null,
        public AddressData $serviceAddress = new AddressData,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = [
            'service_number', 'tariff_code', 'tariff_description', 'voltage_level',
            'phases', 'contracted_demand_kw', 'billing_cycle', 'holder_name', 'service_address',
        ];

        return new self(
            serviceNumber: isset($raw['service_number']) ? (string) $raw['service_number'] : null,
            tariffCode: isset($raw['tariff_code']) ? (string) $raw['tariff_code'] : null,
            tariffDescription: isset($raw['tariff_description']) ? (string) $raw['tariff_description'] : null,
            voltageLevel: isset($raw['voltage_level']) ? (string) $raw['voltage_level'] : null,
            phases: $raw['phases'] ?? null,
            contractedDemandKw: $raw['contracted_demand_kw'] ?? null,
            billingCycle: isset($raw['billing_cycle']) ? (string) $raw['billing_cycle'] : null,
            holderName: isset($raw['holder_name']) ? (string) $raw['holder_name'] : null,
            serviceAddress: AddressData::fromArray(is_array($raw['service_address'] ?? null) ? $raw['service_address'] : null),
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'service_number' => $this->serviceNumber,
            'tariff_code' => $this->tariffCode,
            'tariff_description' => $this->tariffDescription,
            'voltage_level' => $this->voltageLevel,
            'phases' => $this->phases,
            'contracted_demand_kw' => $this->contractedDemandKw,
            'billing_cycle' => $this->billingCycle,
            'holder_name' => $this->holderName,
            'service_address' => $this->serviceAddress->toArray(),
        ], $this->extra);
    }
}
