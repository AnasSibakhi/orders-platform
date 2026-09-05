<?php

namespace App\Support\Tenancy;

use App\Models\Business;

/**
 * Bound as a singleton in the container per-request by IdentifyBusiness
 * middleware. Every tenant-scoped model reads from this to filter/stamp
 * business_id, so there is exactly one place that decides "which business
 * are we operating as" for the whole request lifecycle.
 */
class CurrentBusiness
{
    protected ?Business $business = null;

    public function set(Business $business): void
    {
        $this->business = $business;
    }

    public function get(): ?Business
    {
        return $this->business;
    }

    public function id(): ?int
    {
        return $this->business?->id;
    }

    public function isSet(): bool
    {
        return $this->business !== null;
    }
}
