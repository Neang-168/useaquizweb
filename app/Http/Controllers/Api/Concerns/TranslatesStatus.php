<?php

namespace App\Http\Controllers\Api\Concerns;

trait TranslatesStatus
{
    /**
     * Convert the frontend's "Active" / "Inactive" string into a boolean
     * for storage against a boolean status column.
     */
    protected function statusToBool(string $status): bool
    {
        return $status === 'Active';
    }

    /**
     * Convert a boolean status column back into the "Active" / "Inactive"
     * string the frontend expects.
     */
    protected function statusToLabel(bool $status): string
    {
        return $status ? 'Active' : 'Inactive';
    }
}
