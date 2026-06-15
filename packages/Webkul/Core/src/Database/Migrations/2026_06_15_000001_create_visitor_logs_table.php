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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('device_type', 30)->nullable()->comment('desktop, mobile, tablet');
            $table->string('referer', 2048)->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('session_id', 128)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('ip_address');
            $table->index('customer_id');
            $table->index('session_id');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
