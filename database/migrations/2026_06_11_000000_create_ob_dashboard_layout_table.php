<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_dashboard_layout', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('P_ID');
            $table->string('widget_key', 50);
            $table->tinyInteger('col')->default(1);
            $table->smallInteger('position')->default(0);
            $table->tinyInteger('visible')->default(1);
            $table->unique(['P_ID', 'widget_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_dashboard_layout');
    }
};
