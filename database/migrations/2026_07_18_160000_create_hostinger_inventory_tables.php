<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostinger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('api_token');
            $table->string('status')->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hostinger_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostinger_account_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->string('username')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('vhost_type')->nullable();
            $table->string('root_directory')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('php_version')->nullable();
            $table->string('php_version_full')->nullable();
            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['hostinger_account_id', 'domain']);
        });

        Schema::create('hostinger_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostinger_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('external_id')->nullable();
            $table->string('domain');
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['hostinger_account_id', 'domain']);
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

    public function down(): void
    {
        Schema::dropIfExists('hostinger_subscriptions');
        Schema::dropIfExists('hostinger_domains');
        Schema::dropIfExists('hostinger_websites');
        Schema::dropIfExists('hostinger_accounts');
    }
};
