<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('video_file_path')->nullable()->after('teacher_file_path');
            $table->string('video_url')->nullable()->after('video_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['video_file_path', 'video_url']);
        });
    }
};