<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use BelongsToBusiness, HasFactory;

    public const TYPE_WHATSAPP = 'whatsapp';
    public const TYPE_INSTAGRAM = 'instagram';
    public const TYPE_STORE_WEBHOOK = 'store_webhook';
    public const TYPE_EMAIL = 'email';

    public const STATUS_CONNECTED = 'connected';
    public const STATUS_DISCONNECTED = 'disconnected';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'business_id',
        'type',
        'name',
        'status',
        'last_synced_at',
    ];

    protected $hidden = ['credentials_encrypted'];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'credentials_encrypted' => 'encrypted',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }
}
