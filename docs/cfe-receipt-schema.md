# Contrato: JSON de recibo CFE — v1.0

> Este es el **contrato de entrada de datos energéticos de Helios**. Lo producen por igual
> un archivo de prueba, el `MockReceiptReader` y, más adelante, el `OllamaReceiptReader`.
> Quien implemente un lector nuevo debe emitir exactamente esta forma.

## 1. Principios

1. **No todos los recibos CFE traen los mismos campos.** Casi todo es opcional. Lo que no
   se pudo leer se declara en `extraction.missing_fields`, no se rellena con ceros ni
   con estimaciones.
2. **Un JSON válido no significa un recibo correcto.** La validación estructural y la
   validación de coherencia son dos pasos distintos, y ambos son obligatorios.
3. **Toda cifra puede venir acompañada de evidencia** (`extraction.evidence`) que apunte
   al documento origen, para que un humano pueda verificarla.
4. **Nada se descarta en silencio.** Campos desconocidos se conservan y se reportan.

## 2. Estructura

```jsonc
{
  "schema_version": "1.0",                    // requerido

  "document": {                                // origen del dato
    "source_type": "pdf|image|json|manual",   // requerido
    "source_reference": "receipts/2026/ab12.pdf", // ruta o id externo; null si no aplica
    "page_count": 2,                           // opcional
    "extracted_at": "2026-10-21T10:00:00-06:00", // requerido (ISO 8601 con offset)
    "extractor": { "identifier": "mock", "version": "1.0.0" } // requerido
  },

  "receipt": {
    "receipt_id": "0000123456789",            // folio del recibo; opcional
    "issue_date": "2026-09-15",               // opcional
    "due_date": "2026-09-30",                 // opcional
    "total_amount_mxn": 1234.56               // opcional
  },

  "service": {
    "service_number": "123456789012",         // número de servicio / RPU; opcional
    "tariff_code": "1C",                      // opcional pero crítico
    "tariff_description": "Doméstica 1C",     // opcional
    "voltage_level": "baja_tension|media_tension|alta_tension|null",
    "phases": 1,                               // 1 | 2 | 3 | null
    "contracted_demand_kw": null,              // sólo tarifas con demanda
    "billing_cycle": "monthly|bimonthly|null",
    "holder_name": "...",                      // dato personal: ver §6
    "service_address": {
      "street": null, "neighborhood": null, "municipality": null,
      "state": null, "postal_code": null
    }
  },

  "current_period": {                          // periodo facturado del recibo
    "period_start": "2026-07-15",             // opcional pero crítico
    "period_end": "2026-09-14",               // opcional pero crítico
    "days": 61,                                // opcional; se contrasta con las fechas
    "consumption_kwh": 520,                    // opcional pero crítico
    "consumption_by_tier": [                   // escalones o periodos horarios
      { "tier": "basico", "kwh": 300, "unit_price_mxn": null, "amount_mxn": null }
    ],
    "demand_kw": null,
    "amount_mxn": 1234.56,
    "concepts": [
      { "code": "energia", "description": "Energía", "amount_mxn": 900.00 }
    ]
  },

  "consumption_history": [                     // historial impreso en el recibo
    {
      "period_start": "2026-05-15", "period_end": "2026-07-14",
      "days": 61, "consumption_kwh": 480, "amount_mxn": 1100.00,
      "source": "receipt_chart|receipt_table|other"
    }
  ],

  "extraction": {
    "status": "complete|partial|failed",      // requerido; lo declara el lector
    "confidence": { "service.tariff_code": 0.95 },  // 0..1 por campo; opcional
    "missing_fields": ["service.contracted_demand_kw"],
    "warnings": [
      { "code": "HISTORY_GAP", "field": "consumption_history", "message": "..." }
    ],
    "evidence": [
      { "field": "current_period.consumption_kwh", "page": 1,
        "bbox": [0.12, 0.44, 0.31, 0.49], "text": "520 kWh" }
    ]
  }
}
```

`schema_version`, `document.source_type`, `document.extracted_at`, `document.extractor`
y `extraction.status` son los **únicos campos estructuralmente obligatorios**.

## 3. Campos críticos

Sin estos, no puede producirse un diagnóstico energético. Su ausencia **no** es un error
de esquema: lleva el import a `needs_review`.

| Campo | Por qué |
|---|---|
| `current_period.consumption_kwh` | Base de todo el dimensionamiento |
| `current_period.period_start` / `period_end` | Normalizan el consumo a kWh/día |
| `service.tariff_code` | Determina el esquema tarifario y de compensación |

## 4. Reglas de validación de coherencia

Se ejecutan **después** de validar el esquema. Cada una produce `error` (rechaza el
import) o `warning` (lo marca para revisión supervisada).

| Código | Nivel | Regla |
|---|---|---|
| `MALFORMED_JSON` | error | El cuerpo no es JSON válido |
| `UNSUPPORTED_SCHEMA_VERSION` | error | `schema_version` no está soportada |
| `NEGATIVE_CONSUMPTION` | error | Algún `consumption_kwh` < 0 |
| `NEGATIVE_AMOUNT` | error | Algún `amount_mxn` < 0 |
| `INVALID_PERIOD` | error | `period_end` <= `period_start` |
| `FUTURE_PERIOD` | error | `period_end` posterior a hoy |
| `IMPOSSIBLE_PERIOD_LENGTH` | error | Duración fuera del rango 20–75 días |
| `PERIOD_DAYS_MISMATCH` | warning | `days` difiere de `period_end - period_start` en más de 2 días |
| `TIER_SUM_MISMATCH` | warning | Σ `consumption_by_tier[].kwh` difiere de `consumption_kwh` en más de 1% |
| `CONCEPT_SUM_MISMATCH` | warning | Σ `concepts[].amount_mxn` difiere de `amount_mxn` en más de 1% |
| `TOTAL_MISMATCH` | warning | `receipt.total_amount_mxn` difiere de `current_period.amount_mxn` en más de 1% |
| `OVERLAPPING_HISTORY` | warning | Dos periodos del historial se traslapan |
| `HISTORY_GAP` | warning | Hueco mayor a 10 días entre periodos consecutivos |
| `CONSUMPTION_OUTLIER` | warning | Un periodo se desvía más de 3σ del resto del historial |
| `UNKNOWN_TARIFF` | warning | `tariff_code` no está en el catálogo de tarifas conocidas |
| `MISSING_CRITICAL_FIELD` | warning | Falta alguno de los campos de §3 |
| `UNKNOWN_FIELD` | warning | Campo no contemplado por el esquema (se conserva íntegro) |
| `LOW_CONFIDENCE` | warning | Algún `confidence` de un campo crítico es menor a 0.70 |

**La tolerancia del 1% y los umbrales de esta tabla son decisiones de ingeniería de
software, no reglas de CFE.** Son configurables y no afectan ningún cálculo energético.

## 5. Estado resultante del import

| Estado | Condición |
|---|---|
| `rejected` | Hay al menos un `error` |
| `needs_review` | Sin errores, pero hay `warning` o falta un campo crítico |
| `validated` | Sin errores ni advertencias |
| `accepted` | Un usuario revisó y confirmó un import en `needs_review` |

Sólo un import en `validated` o `accepted` puede alimentar un diagnóstico energético.

## 6. Datos personales

`service.holder_name` y `service.service_address` son datos personales del cliente final.
No se envían a servicios de terceros, no se registran en logs de aplicación y el acceso
queda sujeto a permisos. Cuando se integre un lector externo, esta decisión se revisa
antes, no después.
