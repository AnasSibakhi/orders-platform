<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_channel_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('external_id'); // e.g. WhatsApp wa_id, Instagram-scoped user id
            $table->timestamps();

            $table->unique(['channel_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_channel_identities');
    }
};
