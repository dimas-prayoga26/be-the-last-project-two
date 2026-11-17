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
        Schema::table('designers', function (Blueprint $table) {
            $table->json('headline')->nullable()->after('title');
            $table->json('statement')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designers', function (Blueprint $table) {
            $table->dropColumn(['headline', 'statement']);
        });
    }
};
