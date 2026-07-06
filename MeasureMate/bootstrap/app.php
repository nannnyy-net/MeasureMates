<?php

use App\Http\Middleware\SecureHeadersMiddleware;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecureHeadersMiddleware::class);
        
        // Exclude CSRF validation for JSON API endpoints
        $middleware->validateCsrfTokens(except: [
            '/convert',
            '/notes',
            '/notes/*',
            '/favorites',
            '/favorites/*',
            '/history',
            '/history/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first() ?: 'Please correct the submitted information.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($e->validator)
                ->with('error', 'Please correct the submitted information.');
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested record could not be found.',
                ], 404);
            }

            return response()->view('errors.404', ['message' => 'The requested record could not be found.'], 404);
        });

        $exceptions->render(function (NotFoundHttpException|FileNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The page you requested could not be found.',
                ], 404);
            }

            return response()->view('errors.404', ['message' => 'The page you requested could not be found.'], 404);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please wait a moment and try again.',
                ], 429);
            }

            return response()->view('errors.429', ['message' => 'Too many requests. Please wait a moment and try again.'], 429);
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            report($e);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'We could not complete that request because of a database issue. Please try again.',
                ], 500);
            }

            return response()->view('errors.500', ['message' => 'We could not complete that request because of a database issue. Please try again.'], 500);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof ValidationException || $e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException || $e instanceof FileNotFoundException || $e instanceof QueryException) {
                return null;
            }

            report($e);

            // Log the actual exception for debugging
            Log::error('Unhandled Exception', [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again.',
                ], 500);
            }

            return response()->view('errors.500', ['message' => 'Something went wrong. Please try again.'], 500);
        });
    })->create();
