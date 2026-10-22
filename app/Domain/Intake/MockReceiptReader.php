<?php

namespace App\Domain\Intake;

use App\Domain\Intake\Contracts\ReceiptReader;
use InvalidArgumentException;
use RuntimeException;

final class MockReceiptReader implements ReceiptReader
{
    public const FIXTURES = ['complete', 'partial', 'incoherent', 'malformed'];

    public function identifier(): string
    {
        return 'mock-fixture';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function readFixture(string $fixtureIdentifier): string
    {
        if (! in_array($fixtureIdentifier, self::FIXTURES, true)) {
            throw new InvalidArgumentException('Unknown synthetic fixture.');
        }
        $extension = $fixtureIdentifier === 'malformed' ? '.txt' : '.json';
        $json = file_get_contents(resource_path('fixtures/receipts/'.$fixtureIdentifier.$extension));
        if ($json === false) {
            throw new RuntimeException('Synthetic fixture unavailable.');
        }

        return $json;
    }
}
