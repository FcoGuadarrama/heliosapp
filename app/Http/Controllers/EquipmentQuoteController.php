<?php

namespace App\Http\Controllers;

use App\Application\CreateEquipmentQuoteDraft;
use App\Infrastructure\Syscom\SyscomCatalog;
use App\Infrastructure\Syscom\SyscomUnavailable;
use App\Models\ReceiptIntake;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class EquipmentQuoteController extends Controller
{
    use AuthorizesRequests;

    public function search(Request $request, ReceiptIntake $intake, SyscomCatalog $catalog): JsonResponse
    {
        $this->authorize('process', $intake);
        $data = $request->validate(['query' => 'required|string|min:2|max:120', 'page' => 'sometimes|integer|between:1,1000']);
        try {
            $result = $catalog->search($data['query'], (int) ($data['page'] ?? 1));
        } catch (SyscomUnavailable $failure) {
            return response()->json(['message' => $this->message($failure)], 503)->header('Cache-Control', 'no-store, private');
        }
        $products = array_map(fn (array $product): array => [
            'id' => (string) $product['producto_id'], 'model' => (string) $product['modelo'],
            'title' => (string) $product['titulo'], 'list_price' => $product['precios']['precio_lista'] ?? null,
            'stock' => $product['total_existencia'] ?? null,
        ], $result['productos'] ?? []);

        return response()->json(['products' => $products, 'page' => $result['pagina'] ?? 1, 'pages' => $result['paginas'] ?? 1])
            ->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, ReceiptIntake $intake, CreateEquipmentQuoteDraft $create): RedirectResponse
    {
        $this->authorize('process', $intake);
        $data = $request->validate(['calculation_id' => 'required|integer|min:1', 'submission_id' => 'required|uuid',
            'confirmed' => 'required|accepted', 'lines' => 'required|array|min:1|max:20', 'lines.*' => 'array',
            'lines.*.product_id' => ['required', 'string', 'regex:/^[1-9]\d{0,11}$/', 'distinct'],
            'lines.*.quantity' => 'required|integer|between:1,1000']);
        try {
            $create($intake, $request->user(), (int) $data['calculation_id'], $data['submission_id'],
                array_map(fn (array $line): array => ['product_id' => $line['product_id'], 'quantity' => (int) $line['quantity']], array_values($data['lines'])));
        } catch (SyscomUnavailable $failure) {
            throw ValidationException::withMessages(['quote' => $this->message($failure)]);
        }

        return back()->with('success', 'Borrador guardado con precios de lista consultados en Syscom.');
    }

    private function message(SyscomUnavailable $failure): string
    {
        return match ($failure->reason) {
            'rate_limited' => 'Syscom limitó temporalmente las consultas. Intenta más tarde.',
            'invalid_search' => 'Busca con hasta 10 palabras.',
            default => 'No se pudo consultar Syscom. Conserva la selección e intenta de nuevo.',
        };
    }
}
