<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('larena_public_link_lookup', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash_ref', 96)->unique();
            $table->string('link_identity_ref')->nullable();
            $table->string('logical_file_id')->nullable();
            $table->string('access_scope_ref')->nullable();
            $table->string('audit_event_ref');
            $table->string('status', 32);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('preview_metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index('revoked_at');
            $table->index('access_scope_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('larena_public_link_lookup');
    }
};
