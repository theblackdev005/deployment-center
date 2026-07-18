<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hostinger_subscriptions');

        Schema::table('hostinger_websites', function (Blueprint $table) {
            $table->dropColumn(['php_version', 'php_version_full']);
        });

        Schema::create('hostinger_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostinger_account_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->nullable();
            $table->string('type');
            $table->string('severity')->default('warning');
            $table->string('title');
            $table->text('message');
            $table->string('status')->default('open');
            $table->string('fingerprint')->unique();
            $table->timestamp('detected_at');
            $table->timestamp('last_detected_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostinger_alerts');

        Schema::table('hostinger_websites', function (Blueprint $table) {
            $table->string('php_version')->nullable();
            $table->string('php_version_full')->nullable();
        });

        Schema::create('hostinger_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostinger_account_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('status')->nullable();
            $table->boolean('is_auto_renewed')->default(false);
            $table->unsignedInteger('billing_period')->nullable();
            $table->string('billing_period_unit')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->unsignedBigInteger('total_price')->nullable();
            $table->unsignedBigInteger('renewal_price')->nullable();
            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['hostinger_account_id', 'external_id']);
        });
    }
};
