<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Intake\IntakeSettings;
use App\Models\ReceiptIntake;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class StoreSecondReceipt
{
    public function __invoke(ReceiptIntake $original, UploadedFile $file, User $actor): ReceiptIntake
    {
        $path = null;
        $disk = Storage::disk('receipts');
        try {
            return DB::transaction(function () use ($original, $file, $actor, $disk, &$path): ReceiptIntake {
                $first = ReceiptIntake::findOrFail($original->id);
                $case = SolarCase::query()->lockForUpdate()->findOrFail($first->solar_case_id);
                if (! $actor->is_active || ! $actor->can('process', $case)) {
                    throw new AuthorizationException;
                }
                $first = ReceiptIntake::query()->lockForUpdate()->findOrFail($first->id);
                if ($case->intakes()->min('id') !== $first->id) {
                    throw ValidationException::withMessages(['receipt' => 'La segunda carga debe sustituir el recibo original.']);
                }
                Validator::make(['file' => $file], ['file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:'.config('intake.max_file_kb')])->validate();
                $hash = hash_file('sha256', $file->getRealPath());
                $second = $case->intakes()->where('id', '!=', $first->id)->first();
                if ($second !== null && $second->sha256 === $hash) {
                    return $second;
                }
                if ($case->intakes()->count() !== 1 || $first->status !== 'unreadable') {
                    throw ValidationException::withMessages(['receipt' => 'Solo se admite una segunda carga tras un recibo ilegible.']);
                }
                $path = $disk->putFile('intakes', $file);
                if ($path === false) {
                    throw new RuntimeException('No se pudo guardar el segundo archivo privado.');
                }
                $notice = app(IntakeSettings::class)->notice();
                $saved = ReceiptIntake::create([
                    'solar_case_id' => $case->id, 'client_id' => $case->client_id, 'created_by' => $actor->id,
                    'source' => 'crm', 'file_path' => $path, 'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(), 'sha256' => $hash,
                    'privacy_version' => $notice['version'], 'privacy_text' => $notice['text'],
                    'privacy_url' => $notice['url'], 'consented_at' => now(), 'status' => 'stored',
                ]);
                SolarCaseEvent::create(['solar_case_id' => $case->id, 'created_by' => $actor->id,
                    'event_key' => 'receipt-replacement:'.$case->id, 'event' => 'receipt_replaced', 'created_at' => now()]);

                return $saved;
            });
        } catch (Throwable $exception) {
            if (is_string($path)) {
                $disk->delete($path);
            }
            throw $exception;
        }
    }
}
