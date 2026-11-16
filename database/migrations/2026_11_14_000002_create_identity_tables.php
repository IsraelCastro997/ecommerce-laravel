<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Requires Laravel's default users table to exist first.
        // Spatie Laravel Permission provides role and permission tables.
        Schema::create('social_accounts', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('provider', 32); $t->string('provider_user_id', 255);
            $t->string('provider_email', 255)->nullable(); $t->timestamps();
            $t->unique(['provider', 'provider_user_id']); $t->index(['user_id', 'provider']);
        });
        Schema::create('customer_profiles', function (Blueprint $t) {
            $t->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $t->string('display_name', 120)->nullable(); $t->string('phone', 30)->nullable();
            $t->string('preferred_currency', 3)->default('MXN'); $t->timestamps();
        });
        Schema::create('customer_addresses', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('label', 60)->nullable(); $t->string('recipient_name', 160);
            $t->string('line1', 255); $t->string('line2', 255)->nullable();
            $t->string('city', 120); $t->string('region', 120); $t->string('postal_code', 20);
            $t->char('country_code', 2)->default('MX'); $t->boolean('is_default')->default(false);
            $t->timestamps(); $t->index(['user_id', 'is_default']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses'); Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('social_accounts');
    }
};
