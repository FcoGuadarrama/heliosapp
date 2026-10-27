<?php

declare(strict_types=1);

namespace App\Infrastructure\Syscom;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class SyscomCatalog
{
    private const BASE_URL = 'https://developers.syscom.mx/api/v1';

    private ?string $token = null;

    /** @return array<string, mixed> */
    public function search(string $query, int $page = 1): array
    {
        if (mb_strlen(trim($query)) < 2 || mb_strlen($query) > 120 || str_word_count($query) > 10 || $page < 1 || $page > 1000) {
            throw new SyscomUnavailable('invalid_search');
        }

        $result = $this->get('/productos', ['busqueda' => $query, 'pagina' => $page, 'limit' => 20,
            'moneda' => 'mxn', 'iva' => 'false', 'inventarios' => 'true', 'recursos' => 'true']);
        if (! is_array($result['productos'] ?? null)) {
            throw new SyscomUnavailable('invalid_product_response');
        }
        foreach ($result['productos'] as $product) {
            $this->validateProduct($product);
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function product(string $id): array
    {
        return $this->products([$id])[0];
    }

    /** @param list<string> $ids
     * @return list<array<string, mixed>>
     */
    public function products(array $ids): array
    {
        if ($ids === [] || count($ids) > 20 || count(array_unique($ids)) !== count($ids)) {
            throw new SyscomUnavailable('invalid_product');
        }
        foreach ($ids as $id) {
            if (! preg_match('/^[1-9]\d{0,11}$/', $id)) {
                throw new SyscomUnavailable('invalid_product');
            }
        }
        $result = $this->get('/productos/'.implode(',', $ids), ['moneda' => 'mxn', 'iva' => 'false', 'inventarios' => 'true']);
        $products = array_is_list($result) ? $result : [$result];
        $indexed = [];
        foreach ($products as $product) {
            $this->validateProduct($product);
            $indexed[(string) $product['producto_id']] = $product;
        }
        if (count($indexed) !== count($ids) || array_diff($ids, array_keys($indexed)) !== []) {
            throw new SyscomUnavailable('product_not_found');
        }

        return array_map(fn (string $id): array => $indexed[$id], $ids);
    }

    private function validateProduct(mixed $product): void
    {
        if (! is_array($product) || ! isset($product['producto_id'], $product['modelo'], $product['titulo'])
            || ! is_scalar($product['producto_id']) || ! preg_match('/^[1-9]\d{0,11}$/', (string) $product['producto_id'])
            || ! is_string($product['modelo']) || ! is_string($product['titulo'])
            || (isset($product['precios']) && ! is_array($product['precios']))) {
            throw new SyscomUnavailable('invalid_product_response');
        }

    }

    private function token(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }
        $id = (string) config('services.syscom.client_id');
        $secret = (string) config('services.syscom.client_secret');
        if ($id === '' || $secret === '') {
            throw new SyscomUnavailable('credentials_missing');
        }
        try {
            $response = Http::asForm()->acceptJson()->connectTimeout(5)->timeout(20)->withoutRedirecting()
                ->post(self::BASE_URL.'/oauth/token', ['grant_type' => 'client_credentials', 'client_id' => $id, 'client_secret' => $secret]);
        } catch (ConnectionException) {
            throw new SyscomUnavailable('connection_failed');
        }
        if (! $response->successful()) {
            throw new SyscomUnavailable($response->status() === 429 ? 'rate_limited' : 'authentication_failed');
        }
        $token = $response->json('access_token');
        if (! is_string($token) || $token === '' || strlen($token) > 16384 || $response->json('token_type') !== 'Bearer') {
            throw new SyscomUnavailable('invalid_token_response');
        }

        return $this->token = $token;
    }

    /** @param array<string, int|string> $parameters
     * @return array<array-key, mixed>
     */
    private function get(string $path, array $parameters, bool $refresh = true): array
    {
        try {
            $response = Http::acceptJson()->withToken($this->token())->connectTimeout(5)->timeout(20)->withoutRedirecting()
                ->get(self::BASE_URL.$path, $parameters);
        } catch (ConnectionException) {
            throw new SyscomUnavailable('connection_failed');
        }
        if ($response->status() === 401 && $refresh) {
            $this->token = null;

            return $this->get($path, $parameters, false);
        }
        if (! $response->successful()) {
            throw new SyscomUnavailable(match ($response->status()) {
                401, 403 => 'access_denied', 404 => 'product_not_found', 429 => 'rate_limited', default => 'service_failed',
            });
        }
        $data = $response->json();
        if (! is_array($data)) {
            throw new SyscomUnavailable('invalid_product_response');
        }

        return $data;
    }
}
