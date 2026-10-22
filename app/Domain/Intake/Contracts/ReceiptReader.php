<?php

namespace App\Domain\Intake\Contracts;

interface ReceiptReader
{
    public function identifier(): string;

    public function version(): string;

    /** Returns raw internal JSON from an explicitly selected synthetic fixture. */
    public function readFixture(string $fixtureIdentifier): string;
}
