<?php

namespace App\Domain\Intake;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

final class PublicFormToken
{
    public function issue(): string
    {
        return Crypt::encryptString(now()->getTimestamp().'|'.Str::random(48));
    }

    public function consume(string $token): bool
    {
        try {
            $parts = explode('|', Crypt::decryptString($token));
        } catch (DecryptException) {
            return false;
        }

        if (count($parts) !== 2 || ! ctype_digit($parts[0]) || strlen($parts[1]) !== 48) {
            return false;
        }
        $age = now()->getTimestamp() - (int) $parts[0];
        if ($age < config('intake.minimum_fill_seconds') || $age > config('intake.token_ttl_seconds')) {
            return false;
        }

        return Cache::add('intake-token:'.hash('sha256', $token), true, (int) config('intake.token_ttl_seconds'));
    }
}
