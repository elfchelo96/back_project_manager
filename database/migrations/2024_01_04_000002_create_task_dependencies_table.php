<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('depends_on_task_id')->constrained('tasks')->onDelete('cascade');
            // blocks: task_id no puede cerrarse hasta que depends_on_task_id este cerrada
            // relates_to: relacion informativa sin restriccion de cierre
            $table->string('type', 20)->default('blocks');
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id'], 'task_dependencies_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
    }
};
