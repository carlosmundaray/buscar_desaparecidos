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
        Schema::create('desaparecidos', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->nullable()->index();
            $table->string('code')->nullable();
            $table->string('full_name')->index();
            $table->string('alias')->nullable();
            $table->string('cedula')->nullable()->index();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->string('last_seen_location')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('relationship')->nullable();
            $table->string('status')->default('missing')->index(); // 'missing' or 'found'
            $table->dateTime('found_at')->nullable();
            $table->string('found_location')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desaparecidos');
    }
};
