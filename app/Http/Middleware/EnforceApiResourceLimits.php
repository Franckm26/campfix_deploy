<?php

namespace App\Http\Middleware;

use App\Services\ApiSecurityResponse;
use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApiResourceLimits
{
    protected int $defaultPerPage = 15;

    protected int $maxPerPage = 100;

    protected int $maxPage = 1000;

    protected int $maxStringLength = 500;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $perPage = $request->query('per_page', $request->query('limit'));
        $page = $request->query('page');

        if ($perPage !== null) {
            if (! $this->isPositiveInteger($perPage)) {
                return $this->reject($request, 'Invalid pagination parameter.', 'invalid_pagination_parameter');
            }

            $boundedPerPage = min((int) $perPage, $this->maxPerPage);
            $request->query->set('per_page', $boundedPerPage);
            $request->query->set('limit', $boundedPerPage);
        } else {
            $request->query->set('per_page', $this->defaultPerPage);
        }

        if ($page !== null) {
            if (! $this->isPositiveInteger($page)) {
                return $this->reject($request, 'Invalid page parameter.', 'invalid_pagination_parameter');
            }

            $request->query->set('page', min((int) $page, $this->maxPage));
        }

        foreach ($request->query() as $key => $value) {
            if ($this->exceedsStringLength($value)) {
                return $this->reject(
                    $request,
                    'One or more query parameters exceed allowed limits.',
                    'query_parameter_too_large',
                    ['parameter' => (string) $key]
                );
            }
        }

        return $next($request);
    }

    protected function isPositiveInteger(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    protected function exceedsStringLength(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->exceedsStringLength($item)) {
                    return true;
                }
            }

            return false;
        }

        if (! is_scalar($value)) {
            return false;
        }

        return mb_strlen((string) $value) > $this->maxStringLength;
    }

    protected function reject(Request $request, string $message, string $code, array $details = []): Response
    {
        SecurityLogger::logValidationFailure([
            'user_id' => optional($request->user())->id,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'errors' => [$code => $details ?: $message],
            'input' => $request->query(),
        ]);

        return ApiSecurityResponse::error(
            $message,
            422,
            $code,
            $details,
            array_filter([
                'X-Request-ID' => (string) $request->attributes->get('request_id', ''),
                'X-API-Inventory' => (string) $request->attributes->get('api_inventory_group', 'general'),
            ], fn ($value) => $value !== '')
        );
    }
}