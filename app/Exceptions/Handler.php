<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response|\Symfony\Component\HttpFoundation\Response|ResponseFactory
    {
        if ($e instanceof AuthenticationException) {
            return parent::render($request, $e);
        }

        $status = $this->getHttpStatusCode($e);

        if ($request->is('admin*')) {
            if (view()->exists("errors.admin.{$status}")) {
                return response(
                    view("errors.admin.{$status}", ['exception' => $e, '__status' => $status]),
                    $status,
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            }

            if (view()->exists('errors.admin.minimal')) {
                return response(
                    view('errors.admin.minimal', ['exception' => $e, '__status' => $status, 'message' => $e->getMessage()]),
                    $status,
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            }
        }

        if (view()->exists("errors.{$status}")) {
            return response(
                view("errors.{$status}", ['exception' => $e, '__status' => $status]),
                $status,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }

        if (view()->exists('errors.minimal')) {
            return response(
                view('errors.minimal', ['exception' => $e, '__status' => $status, 'message' => $e->getMessage()]),
                $status,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }

        return parent::render($request, $e);
    }

    /**
     * Get HTTP status code from exception.
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        if (method_exists($exception, 'status')) {
            return $exception->status();
        }

        return 500;
    }
}
