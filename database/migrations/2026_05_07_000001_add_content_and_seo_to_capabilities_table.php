<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capabilities', function (Blueprint $table) {
            $table->json('content')->nullable()->after('details');
            $table->string('seo_title')->nullable()->after('content');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->text('seo_keywords')->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('capabilities', function (Blueprint $table) {
            $table->dropColumn(['content', 'seo_title', 'seo_description', 'seo_keywords']);
        });
    }
};

