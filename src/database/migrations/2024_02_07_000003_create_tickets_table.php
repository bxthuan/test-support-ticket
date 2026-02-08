<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('status', 50)->default('Open')->index();
            $table->string('category', 50)->nullable()->index();
            $table->string('sentiment', 50)->nullable()->index();
            $table->text('suggested_reply')->nullable();
            $table->timestamp('ai_processed_at')->nullable()->index();
            $table->unsignedTinyInteger('ai_retry_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
