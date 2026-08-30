<?php

namespace App\Exceptions;

use RuntimeException;

/** Thrown when a booking is attempted against a provider who isn't live. */
class ProviderNotBookableException extends RuntimeException
{
    public function __construct(string $message = 'This provider is not currently taking bookings.')
    {
        parent::__construct($message);
    }
}
