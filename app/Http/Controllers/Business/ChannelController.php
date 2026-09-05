<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Channel;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(Business $business): View
    {
        $channels = $business->channels()->latest()->get();

        return view('channels.index', [
            'business' => $business,
            'channels' => $channels,
            'types' => [
                Channel::TYPE_WHATSAPP => 'WhatsApp',
                Channel::TYPE_INSTAGRAM => 'Instagram',
                Channel::TYPE_STORE_WEBHOOK => 'Store Webhook',
                Channel::TYPE_EMAIL => 'Email',
            ],
        ]);
    }

    /**
     * Phase 2 scope: create the channel record only. Real OAuth/API
     * connection flows (WhatsApp Cloud API, Instagram Graph API) are
     * wired up in Phase 4/5 — this just reserves the slot so the rest of
     * the schema (orders.channel_id, webhook routing) has something to
     * point at while those integrations are being built.
     */
    public function store(Business $business, Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:whatsapp,instagram,store_webhook,email'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $channel = $business->channels()->create([
            ...$validated,
            'status' => Channel::STATUS_DISCONNECTED,
        ]);

        $auditLog->log('channel.created', Channel::class, $channel->id, ['type' => $channel->type]);

        return back()->with('status', 'تمت إضافة القناة. الربط الفعلي بها متاح في مرحلة لاحقة.');
    }
}
