<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $itemName
     * @param int $requestedQuantity
     * @param int $availableQuantity
     */
    public function __construct(string $itemName, int $requestedQuantity, int $availableQuantity)
    {
        $message = sprintf(
            'Insufficient stock for item [%s]. Requested: %d, Available: %d.',
            $itemName,
            $requestedQuantity,
            $availableQuantity
        );

        parent::__construct($message);
    }
}
