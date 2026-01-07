<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (\Illuminate\Validation\ValidationException $e) {
            activity('validation_error')
                ->causedBy(auth()->user())
                ->withProperties([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                    'url' => request()->fullUrl(),
                    'input' => request()->except(['password', 'password_confirmation']),
                    'ip' => request()->ip(),
                ])
                ->log($e->getMessage());
        });

        $this->reportable(function (Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return;
            }

            activity('server_error')
                ->causedBy(auth()->user())
                ->withProperties([
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => substr($e->getTraceAsString(), 0, 1000), // Limit trace size
                    'url' => request()->fullUrl(),
                    'ip' => request()->ip(),
                ])
                ->log($e->getMessage());
        });
    }
}
