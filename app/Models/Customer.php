<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'phone_normalized',
        'email',
    ];

    public function channelIdentities(): HasMany
    {
        return $this->hasMany(CustomerChannelIdentity::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
