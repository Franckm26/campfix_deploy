<?php

namespace App\Services;

use Throwable;

class MailFailureDiagnostic
{
    /** Never return raw exception messages: SMTP errors can contain credentials. */
    public static function describe(Throwable $exception): string
    {
        $messages = '';
        for ($cause = $exception; $cause !== null; $cause = $cause->getPrevious()) {
            $messages .= ' '.strtolower($cause->getMessage());
        }

        if (str_contains($messages, 'authenticate') || str_contains($messages, 'authentication') || str_contains($messages, '535')) {
            return 'SMTP_AUTH: Mail server authentication failed. Check the matching SMTP login and key in this deployment.';
        }
        if (str_contains($messages, 'quota') || str_contains($messages, 'credit') || str_contains($messages, 'rate limit')) {
            return 'PROVIDER_LIMIT: The mail provider reported a quota or rate limit.';
        }
        if (str_contains($messages, 'view [') || str_contains($messages, 'view:') || str_contains($messages, 'undefined variable') || str_contains($messages, 'no hint path')) {
            return 'MAIL_TEMPLATE: The email template could not render. Check deployed mail views and compiled-view configuration.';
        }
        if (str_contains($messages, 'timed out') || str_contains($messages, 'timeout')) {
            return 'MAIL_TIMEOUT: Sending timed out. Check provider logs before retrying; acceptance is uncertain.';
        }
        if (str_contains($messages, 'connection') || str_contains($messages, 'getaddrinfo') || str_contains($messages, 'certificate')) {
            return 'SMTP_CONNECTION: Mail server connection or TLS failed. Check SMTP host, port, and encryption.';
        }
        if (str_contains($messages, 'expected response code')) {
            return 'SMTP_REJECTED: The mail server returned an unexpected SMTP response. Check sender verification and provider logs.';
        }

        return 'MAIL_ERROR: The test failed for an unclassified reason. Exception type: '.get_class($exception);
    }
}
