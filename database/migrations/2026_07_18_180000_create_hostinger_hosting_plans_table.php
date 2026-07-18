<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostinger_hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostinger_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('order_id');
            $table->string('subscription_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['hostinger_account_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostinger_hosting_plans');
    }
};
