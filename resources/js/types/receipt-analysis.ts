export interface ReceiptPeriod {
    period_start: string | null;
    period_end: string | null;
    consumption_kwh: number | null;
    days: number | null;
    amount_mxn: number | null;
}

export interface ReceiptConcept {
    code: string | null;
    description: string | null;
    amount_mxn: number | null;
}

export interface ReceiptReviewForm {
    tariff_code: string;
    consumption_kwh: number | null;
    period_start: string;
    period_end: string;
    phases: number | null;
    days: number | null;
    state_code: string;
    history: ReceiptPeriod[];
    concepts: ReceiptConcept[];
    period_amount_mxn: number | null;
    total_amount_mxn: number | null;
    revision: number;
    reason: string;
    confirmed: boolean;
}

export interface ReceiptAnalysisData {
    calculation_id: number | null;
    quote_draft: EquipmentQuoteDraft | null;
    processing: string;
    reason: string | null;
    can_process: boolean;
    revision: number;
    has_reading: boolean;
    upload_count: number;
    can_replace: boolean;
    source_verified: boolean;
    result: {
        requiredKwp: number;
        minimumInverterW: number;
        dailyConsumptionKwh: number;
        consumptionSource: string;
        moduleCount: number | null;
    } | null;
    hsp: { annual: number; stateName: string; catalogVersion: string } | null;
    validation: {
        errors: { code: string; message: string; field: string | null }[];
        warnings: { code: string; message: string; field: string | null }[];
        extraction_warnings: {
            code: string;
            message: string;
            field: string | null;
        }[];
    };
    form: Omit<ReceiptReviewForm, 'revision' | 'reason' | 'confirmed'> | null;
}

export interface EquipmentQuoteDraft {
    id: number;
    version: number;
    created_at: string;
    snapshot: {
        subtotal_cents: number;
        vat_cents: number | null;
        total_cents: number | null;
        lines: {
            product_id: string;
            model: string;
            quantity: number;
            line_total_cents: number;
            availability: 'unknown' | 'available' | 'insufficient';
        }[];
    };
}

export interface StateOption {
    code: string;
    name: string;
}
