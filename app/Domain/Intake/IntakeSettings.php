<?php

namespace App\Domain\Intake;

final class IntakeSettings
{
    public function available(): bool
    {
        return config('intake.enabled') && $this->notice()['version'] !== '' && $this->notice()['text'] !== '';
    }

    /** @return array{version: string, text: string, url: string} */
    public function notice(): array
    {
        return [
            'version' => trim((string) config('intake.privacy_version')),
            'text' => trim((string) config('intake.privacy_text')),
            'url' => $this->safeUrl((string) config('intake.privacy_url')),
        ];
    }

    public function frameAncestors(): string
    {
        $origins = [];
        foreach (config('intake.frame_ancestors', []) as $origin) {
            $origin = trim($origin);
            // Strict origins prevent directive injection and accidental wildcard grants.
            if (preg_match('~^https://(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,63}(?::[0-9]{1,5})?$~iD', $origin)) {
                $origins[] = $origin;
            }
        }

        return $origins === [] ? "'none'" : implode(' ', array_unique($origins));
    }

    private function safeUrl(string $url): string
    {
        return filter_var($url, FILTER_VALIDATE_URL) && str_starts_with($url, 'https://') ? $url : '';
    }
}
