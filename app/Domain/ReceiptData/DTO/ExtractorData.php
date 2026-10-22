<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `document.extractor` — identificador y versión del lector que produjo el payload.
 */
final readonly class ExtractorData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $identifier = null,
        public ?string $version = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['identifier', 'version'];

        return new self(
            identifier: isset($raw['identifier']) ? (string) $raw['identifier'] : null,
            version: isset($raw['version']) ? (string) $raw['version'] : null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'identifier' => $this->identifier,
            'version' => $this->version,
        ], $this->extra);
    }
}
