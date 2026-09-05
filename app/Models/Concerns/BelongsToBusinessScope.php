<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Automatically restricts every query on a tenant-scoped model to the
 * current business. This is the safety net that makes "a bug leaks
 * business A's data to business B" structurally hard instead of relying
 * on every single controller remembering to add ->where('business_id', ...).
 *
 * If no business is bound (e.g. a console command running across all
 * tenants), the scope simply doesn't apply — callers must be explicit
 * about that by using withoutGlobalScope() or running outside a web
 * request context, which makes the "no tenant filter" case visible in
 * the code rather than accidental.
 */
class BelongsToBusinessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $current = app(CurrentBusiness::class);

        if ($current->isSet()) {
            $builder->where($model->getTable().'.business_id', $current->id());
        }
    }
}
