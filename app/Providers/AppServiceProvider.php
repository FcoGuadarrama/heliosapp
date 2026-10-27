<?php

namespace App\Providers;

use App\Application\ReadAndSizeReceipt;
use App\Domain\Hsp\ClimatologySource;
use App\Domain\Hsp\HspProvider;
use App\Domain\Intake\Contracts\ReceiptReader;
use App\Domain\Intake\MockReceiptReader;
use App\Domain\Processing\ReceiptProcessor;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Infrastructure\Hsp\DatabaseHspProvider;
use App\Infrastructure\Hsp\NasaClimatologyClient;
use App\Infrastructure\Receipts\OllamaReceiptReader;
use App\Infrastructure\Receipts\SourceReceiptReader;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ReceiptReader::class, MockReceiptReader::class);
        $this->app->bind(FileReceiptReader::class, fn ($app): FileReceiptReader => match (config('receipt_reader.driver')) {
            'source' => $app->make(SourceReceiptReader::class),
            'ollama' => $app->make(OllamaReceiptReader::class),
            default => throw new \LogicException('Invalid receipt reader driver.'),
        });
        $this->app->bind(ReceiptProcessor::class, ReadAndSizeReceipt::class);
        $this->app->bind(ClimatologySource::class, NasaClimatologyClient::class);
        $this->app->bind(HspProvider::class, DatabaseHspProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
