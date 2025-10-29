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
        Schema::create('ttlock_callback_history', function (Blueprint $table) {
            $table->id();

            // Basic callback information
            $table->string('lock_id')->index();
            $table->string('lock_mac')->index();
            $table->string('admin')->nullable(); // Admin email who received the callback
            $table->integer('notify_type')->nullable(); // notifyType from callback

            // Record information from the records array
            $table->json('records')->nullable(); // Full records array as JSON
            $table->integer('record_type_from_lock')->nullable(); // recordTypeFromLock from individual record
            $table->integer('record_type')->nullable(); // recordType from individual record
            $table->integer('success')->nullable(); // success status from record
            $table->string('username')->nullable(); // username from record
            $table->string('keyboard_pwd')->nullable(); // keyboardPwd from record
            $table->bigInteger('lock_date')->nullable(); // lockDate timestamp from record
            $table->bigInteger('server_date')->nullable(); // serverDate timestamp from record
            $table->integer('electric_quantity')->nullable(); // electricQuantity (battery level)

            // Processed information
            $table->string('event_type')->nullable(); // Processed event type (lock_operation, battery_low, etc.)
            $table->text('message')->nullable(); // Human readable message
            $table->json('raw_data')->nullable(); // Raw callback data
            $table->string('request_id')->nullable(); // Request ID for tracking

            // Status and processing
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_notes')->nullable();

            // Indexes for better performance
            $table->index(['lock_id', 'created_at']);
            $table->index(['record_type', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index('processed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttlock_callback_history');
    }
};
