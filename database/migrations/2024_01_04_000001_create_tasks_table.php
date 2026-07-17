<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->onDelete('set null');
            $table->foreignId('status_id')->constrained('task_statuses')->onDelete('restrict');
            $table->foreignId('priority_id')->constrained('task_priorities')->onDelete('restrict');

            $table->foreignId('author_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Soporte de tareas padre / subtareas
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade');

            $table->string('subject');
            $table->text('description')->nullable();

            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('spent_hours', 8, 2)->default(0);
            $table->unsignedTinyInteger('done_ratio')->default(0); // 0-100

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status_id']);
            $table->index('assigned_to');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
