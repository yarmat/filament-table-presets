<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('filament_table_presets', function (Blueprint $table) {
            $table->id();
            $table->string('resource_class');

            // If you want to use polymorphic relationships, uncomment the following line:
            // $table->morphs('owner');
            // $table->index(['owner_id', 'owner_type', 'resource_class']);
            // and comment the following line
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->index(['owner_id', 'resource_class']);

            $table->string('name');
            $table->text('description')->nullable();
            $table->json('columns')->nullable();
            $table->json('filters')->nullable();
            $table->string('sort')->nullable();
            $table->string('search')->nullable();
            $table->boolean('public')->default(false);

            $table->string('panel')->index();

            $table->timestamps();
        });

        Schema::create('filament_table_preset_user', function (Blueprint $table) {
            $table->foreignId('preset_id')->constrained('filament_table_presets')->cascadeOnDelete();

            // If you want to use polymorphic relationships, uncomment the following line:
            // $table->morphs('user');
            // $table->unique(['preset_id', 'user_type', 'user_id']);
            // and comment the following line
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

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
