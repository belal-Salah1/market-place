<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meta_browser_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            // Null for the base snippet's PageView, which fires with no eventID.
            $table->string('event_id')->nullable();
            $table->timestamps();

            // No unique on event_id: a repeated id is a double fire we want to see.
            $table->index(['event_name', 'created_at']);
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_browser_events');
    }
};
