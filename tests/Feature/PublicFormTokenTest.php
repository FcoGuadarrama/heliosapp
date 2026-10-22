<?php

namespace Tests\Feature;

use App\Domain\Intake\PublicFormToken;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PublicFormTokenTest extends TestCase
{
    public function test_equivalent_ciphertext_serialization_cannot_replay_a_consumed_token(): void
    {
        $tokens = app(PublicFormToken::class);
        $token = $tokens->issue();
        $payload = json_decode(base64_decode($token), true, 512, JSON_THROW_ON_ERROR);
        $equivalentToken = base64_encode(json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $this->assertNotSame($token, $equivalentToken);
        $this->assertSame(Crypt::decryptString($token), Crypt::decryptString($equivalentToken));
        $this->travel((int) config('intake.minimum_fill_seconds'))->seconds();

        $this->assertTrue($tokens->consume($token));
        $this->assertFalse($tokens->consume($equivalentToken));
    }
}
