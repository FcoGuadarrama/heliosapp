<?php

namespace Tests\Feature;

use App\Infrastructure\Syscom\SyscomCatalog;
use App\Infrastructure\Syscom\SyscomUnavailable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyscomCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.syscom.client_id' => 'synthetic-id', 'services.syscom.client_secret' => 'synthetic-secret']);
    }

    public function test_search_requests_pesos_without_tax_and_keeps_list_price_separate(): void
    {
        Http::fake(['*/oauth/token' => Http::response(['access_token' => 'synthetic-token', 'token_type' => 'Bearer']),
            '*/productos*' => Http::response(['productos' => [['producto_id' => '123', 'modelo' => 'TEST', 'titulo' => 'Synthetic',
                'precios' => ['precio_lista' => '2000.0000', 'precio_descuento' => '1000.0000'], 'total_existencia' => null]]])]);
        $result = (new SyscomCatalog)->search('panel solar');
        $this->assertSame('2000.0000', $result['productos'][0]['precios']['precio_lista']);
        $this->assertNull($result['productos'][0]['total_existencia']);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET' && $request['moneda'] === 'mxn'
            && $request['iva'] === 'false' && $request->hasHeader('Authorization', 'Bearer synthetic-token'));
        Http::assertSentCount(2);
    }

    public function test_revoked_token_is_refreshed_once_without_unbounded_retry(): void
    {
        Http::fake(['*/oauth/token' => Http::sequence()->push(['access_token' => 'old', 'token_type' => 'Bearer'])
            ->push(['access_token' => 'new', 'token_type' => 'Bearer']),
            '*/productos*' => Http::sequence()->push([], 401)->push([], 401)]);
        try {
            (new SyscomCatalog)->search('panel solar');
            $this->fail('Expected denied access.');
        } catch (SyscomUnavailable $failure) {
            $this->assertSame('access_denied', $failure->reason);
            Http::assertSentCount(4);
        }
    }

    public function test_authentication_errors_do_not_expose_provider_body_or_credentials(): void
    {
        Http::fake(['*' => Http::response('synthetic-secret private response', 403)]);
        try {
            (new SyscomCatalog)->search('panel solar');
            $this->fail('Expected denied authentication.');
        } catch (SyscomUnavailable $failure) {
            $this->assertSame('authentication_failed', $failure->reason);
            $this->assertStringNotContainsString('synthetic-secret', $failure->getMessage());
        }
    }

    public function test_missing_credentials_and_invalid_ids_do_not_send_requests(): void
    {
        config(['services.syscom.client_secret' => '']);
        try {
            (new SyscomCatalog)->search('panel solar');
            $this->fail('Expected missing credentials.');
        } catch (SyscomUnavailable $failure) {
            $this->assertSame('credentials_missing', $failure->reason);
        }
        try {
            (new SyscomCatalog)->product('../pedidos');
            $this->fail('Expected invalid product.');
        } catch (SyscomUnavailable $failure) {
            $this->assertSame('invalid_product', $failure->reason);
        }
        Http::assertNothingSent();
    }
}
