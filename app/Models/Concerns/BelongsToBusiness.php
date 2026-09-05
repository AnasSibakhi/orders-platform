<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new BelongsToBusinessScope);

        static::creating(function ($model) {
            if (! $model->business_id) {
                $current = app(CurrentBusiness::class);

                if ($current->isSet()) {
                    $model->business_id = $current->id();
                }
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
