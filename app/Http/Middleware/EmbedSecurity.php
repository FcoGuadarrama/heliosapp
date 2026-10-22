<?php

namespace App\Http\Middleware;

use App\Domain\Intake\IntakeSettings;
use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class EmbedSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('embed/cfe')) {
            return $next($request);
        }

        try {
            $key = 'embed:'.hash('sha256', (string) $request->ip());
            $limit = $request->isMethod('POST') ? 5 : 30;
            $key .= ':'.$request->method();
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                $response = response('Demasiados intentos. Intenta de nuevo más tarde.', 429)
                    ->header('Retry-After', (string) RateLimiter::availableIn($key));
            } else {
                RateLimiter::hit($key, 60);
                $response = $next($request);
            }
        } catch (Throwable $exception) {
            app(ExceptionHandler::class)->report($exception);
            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $message = match ($status) {
                422 => 'Formulario vencido o no válido. Recarga e intenta de nuevo.',
                413 => 'El archivo supera el tamaño permitido.',
                503 => 'Recepción no configurada.',
                default => 'No fue posible recibir el formulario.',
            };
            $response = response($message, $status);
        }

        // Laravel may render exceptions inside the route pipeline before returning here.
        // Never let a public embed response expose debug traces, even with APP_DEBUG=true.
        if ($response->getStatusCode() >= 500 ||
            (($response instanceof \Illuminate\Http\Response || $response instanceof JsonResponse) && $response->exception !== null)) {
            $response = response('No fue posible recibir el formulario. Recarga e intenta de nuevo.', $response->getStatusCode());
        }

        $response->headers->set('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; form-action 'self'; base-uri 'none'; frame-ancestors ".app(IntakeSettings::class)->frameAncestors());
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
