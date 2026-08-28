<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Makes every state-changing web request atomic at the database boundary.
 *
 * Nested DB::transaction() calls remain safe because Laravel uses the same
 * connection transaction level. A failed HTTP response is rolled back as well,
 * including controllers that convert an exception into a 4xx/5xx response.
 */
class DatabaseTransaction
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $connection = DB::connection();
        $startingLevel = $connection->transactionLevel();
        $connection->beginTransaction();

        try {
            $response = $next($request);

            if ($this->shouldRollBack($request, $response)) {
                $this->rollBackToLevel($connection, $startingLevel);
            } else {
                while ($connection->transactionLevel() > $startingLevel) {
                    $connection->commit();
                }
            }

            return $response;
        } catch (Throwable $exception) {
            $this->rollBackToLevel($connection, $startingLevel);
            throw $exception;
        }
    }

    private function rollBackToLevel($connection, int $startingLevel): void
    {
        while ($connection->transactionLevel() > $startingLevel) {
            $connection->rollBack();
        }
    }

    private function shouldRollBack(Request $request, Response $response): bool
    {
        if ($response->isServerError() || $response->isClientError()) {
            return true;
        }

        // A number of existing controllers intentionally convert caught
        // exceptions into redirects with a flashed error message. Treat those
        // as failed units of work too, so earlier writes are not committed.
        if ($request->hasSession()) {
            $newFlashKeys = (array) $request->session()->get('_flash.new', []);
            if (in_array('error', $newFlashKeys, true)) {
                return true;
            }
        }

        $contentType = (string) $response->headers->get('Content-Type');
        if (str_contains(strtolower($contentType), 'application/json')) {
            $payload = json_decode((string) $response->getContent(), true);

            if (is_array($payload) && (($payload['success'] ?? null) === false || array_key_exists('error', $payload))) {
                return true;
            }
        }

        return false;
    }
}
