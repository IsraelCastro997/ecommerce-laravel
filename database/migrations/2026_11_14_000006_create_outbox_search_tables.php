<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $t) {
            $t->id(); $t->uuid('event_id')->unique(); $t->string('aggregate_type', 70);
            $t->unsignedBigInteger('aggregate_id'); $t->string('event_type', 100);
            $t->json('payload'); $t->unsignedInteger('attempts')->default(0);
            $t->timestamp('available_at')->nullable(); $t->timestamp('processed_at')->nullable();
            $t->timestamps(); $t->index(['processed_at', 'available_at', 'id'], 'outbox_pending_idx');
        });
        Schema::create('search_synonyms', function (Blueprint $t) {
            $t->id(); $t->string('term', 180); $t->string('canonical_term', 180);
            $t->string('locale', 12)->default('es-MX'); $t->timestamps();
            $t->index(['locale', 'term']);
        });
        Schema::create('integration_sync_logs', function (Blueprint $t) {
            $t->id(); $t->string('integration', 40); $t->string('status', 30);
            $t->unsignedInteger('records_processed')->default(0); $t->text('error_message')->nullable();
            $t->dateTime('started_at'); $t->dateTime('finished_at')->nullable();
            $t->index(['integration', 'started_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('integration_sync_logs'); Schema::dropIfExists('search_synonyms');
        Schema::dropIfExists('outbox_events');
    }
};
