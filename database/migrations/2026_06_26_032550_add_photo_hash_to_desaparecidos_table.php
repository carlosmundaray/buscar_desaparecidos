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
        Schema::table('desaparecidos', function (Blueprint $table) {
            $table->string('photo_hash', 64)->nullable()->index()->after('photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desaparecidos', function (Blueprint $table) {
            $table->dropColumn('photo_hash');
        });
    }
};
