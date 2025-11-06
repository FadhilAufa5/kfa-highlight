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
        Schema::table('pdf_uploads', function (Blueprint $table) {
            $table->string('conversion_status')->default('pending')->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('pdf_uploads', function (Blueprint $table) {
            $table->dropColumn('conversion_status');
        });
    }
};
