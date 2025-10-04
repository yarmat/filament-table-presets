<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filament_table_presets', function (Blueprint $table) {
            $table->id();
            $table->string('resource_class');

            // Полиморфная связь с пользователем
            $table->morphs('user');

            $table->string('name');
            $table->text('description')->nullable();
            $table->json('columns')->nullable();
            $table->json('filters')->nullable();
            $table->string('sort')->nullable();
            $table->string('search')->nullable();
            $table->boolean('public')->default(false);

            $table->string('panel')->index();

            $table->timestamps();

            $table->index(['user_type', 'user_id', 'resource_class']);
        });

        Schema::create('filament_table_preset_user', function (Blueprint $table) {
            $table->foreignId('preset_id')->constrained('filament_table_presets')->cascadeOnDelete();

            // Полиморфная связь в пивоте
            $table->morphs('user');
            $table->unique(['preset_id', 'user_type', 'user_id']);

            $table->unsignedInteger('sort')->default(0);
            $table->boolean('default')->default(false);
            $table->boolean('visible')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_table_preset_user');
        Schema::dropIfExists('filament_table_presets');
    }
};
