<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `service.service_address` — dato personal del cliente final (contrato §6).
 */
final readonly class AddressData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $street = null,
        public ?string $neighborhood = null,
        public ?string $municipality = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['street', 'neighborhood', 'municipality', 'state', 'postal_code'];

        return new self(
            street: isset($raw['street']) ? (string) $raw['street'] : null,
            neighborhood: isset($raw['neighborhood']) ? (string) $raw['neighborhood'] : null,
            municipality: isset($raw['municipality']) ? (string) $raw['municipality'] : null,
            state: isset($raw['state']) ? (string) $raw['state'] : null,
            postalCode: isset($raw['postal_code']) ? (string) $raw['postal_code'] : null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'street' => $this->street,
            'neighborhood' => $this->neighborhood,
            'municipality' => $this->municipality,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
        ], $this->extra);
    }
}
