<?php

namespace App\Application;

use App\Domain\Intake\IntakeSettings;
use App\Models\Client;
use App\Models\ReceiptIntake;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class StoreReceiptIntake
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data, UploadedFile $file, ?Client $client, ?User $actor): ReceiptIntake
    {
        $disk = Storage::disk('receipts');
        $path = $disk->putFile('intakes', $file);
        if ($path === false) {
            throw new RuntimeException('No se pudo guardar el archivo privado.');
        }

        try {
            return DB::transaction(function () use ($data, $file, $path, $client, $actor): ReceiptIntake {
                // Public callers never supply a client. No matching on email/phone.
                $client ??= Client::create([
                    'type' => null,
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'created_by' => $actor?->id,
                ]);
                $notice = app(IntakeSettings::class)->notice();

                $intake = ReceiptIntake::create([
                    'client_id' => $client->id,
                    'created_by' => $actor?->id,
                    'source' => $actor === null ? 'embed' : 'crm',
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'privacy_version' => $notice['version'],
                    'privacy_text' => $notice['text'],
                    'privacy_url' => $notice['url'],
                    'consented_at' => now(),
                    'status' => 'stored',
                ]);
                app(RegisterReceiptCase::class)($intake, $actor);

                return $intake;
            });
        } catch (Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        }
    }
}
