<?php

declare(strict_types=1);

namespace App\Application;

use App\Models\ReceiptIntake;
use App\Models\SolarCase;
use App\Models\SolarCaseEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegisterReceiptCase
{
    public function __invoke(ReceiptIntake $intake, ?User $actor): void
    {
        DB::transaction(function () use ($intake, $actor): void {
            $locked = ReceiptIntake::query()->lockForUpdate()->findOrFail($intake->id);
            if ($locked->solar_case_id !== null) {
                $intake->setAttribute('solar_case_id', $locked->solar_case_id);

                return;
            }
            $case = SolarCase::create([
                'public_id' => (string) Str::uuid(),
                'client_id' => $locked->client_id,
                'responsible_user_id' => $actor?->is_active ? $actor->id : null,
            ]);
            $locked->update(['solar_case_id' => $case->id]);
            $intake->setAttribute('solar_case_id', $case->id);
            SolarCaseEvent::create([
                'solar_case_id' => $case->id,
                'created_by' => $actor?->id,
                'event_key' => 'receipt:'.$locked->id,
                'event' => 'receipt_received',
                'created_at' => now(),
            ]);
        });
    }
}
