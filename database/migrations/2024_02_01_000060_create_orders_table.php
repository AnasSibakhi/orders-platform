<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new'); // new|processing|paid|shipped|completed|cancelled
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->string('external_order_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Hot paths: dashboard filters by status, and channel timelines.
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'channel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
