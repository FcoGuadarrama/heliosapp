<?php

declare(strict_types=1);

namespace App\Domain\Quotes;

use Illuminate\Validation\ValidationException;

final class ListPriceDraft
{
    /** @param list<array{product: array<string, mixed>, quantity: int}> $items
     * @return array<string, mixed>
     */
    public function calculate(array $items, ?string $vatRate): array
    {
        $lines = [];
        $subtotal = 0;
        foreach ($items as $item) {
            $product = $item['product'];
            $price = $product['precios']['precio_lista'] ?? null;
            $cents = $this->cents($price);
            if ($cents === null || $cents <= 0 || $item['quantity'] < 1 || $item['quantity'] > 1000) {
                throw ValidationException::withMessages(['quote' => 'Un producto no tiene precio de lista válido o su cantidad es incorrecta.']);
            }
            $stock = $product['total_existencia'] ?? null;
            $stock = is_int($stock) && $stock >= 0 ? $stock : null;
            $lineTotal = $cents * $item['quantity'];
            $subtotal += $lineTotal;
            $lines[] = ['product_id' => (string) $product['producto_id'], 'model' => (string) $product['modelo'],
                'title' => (string) $product['titulo'], 'quantity' => $item['quantity'],
                'price_field' => 'precio_lista', 'source_unit_price' => $price, 'unit_price_cents' => $cents,
                'line_total_cents' => $lineTotal, 'stock' => $stock,
                'availability' => $stock === null ? 'unknown' : ($stock >= $item['quantity'] ? 'available' : 'insufficient')];
        }
        $basisPoints = $vatRate === null ? null : $this->cents($vatRate);
        if ($vatRate !== null && ($basisPoints === null || $basisPoints > 10000)) {
            throw ValidationException::withMessages(['quote' => 'La tasa de IVA configurada no es válida.']);
        }
        $tax = $basisPoints === null ? null : intdiv($subtotal * $basisPoints + 5000, 10000);

        return ['currency' => 'MXN', 'tax_included_in_source' => false, 'price_basis' => 'syscom_precio_lista',
            'rounding' => 'unit_half_up_2dp_then_tax_on_subtotal_half_up', 'lines' => $lines,
            'subtotal_cents' => $subtotal, 'vat_basis_points' => $basisPoints, 'vat_cents' => $tax,
            'total_cents' => $tax === null ? null : $subtotal + $tax,
            'installation_included' => false, 'compatibility_status' => 'pending',
            'queried_at' => now()->toIso8601String()];
    }

    private function cents(mixed $value): ?int
    {
        if (! is_string($value) || ! preg_match('/^(\d{1,7})(?:\.(\d{1,8}))?$/D', $value, $match)) {
            return null;
        }
        $fraction = str_pad($match[2] ?? '', 3, '0');

        return (int) $match[1] * 100 + (int) substr($fraction, 0, 2) + ((int) $fraction[2] >= 5 ? 1 : 0);
    }
}
