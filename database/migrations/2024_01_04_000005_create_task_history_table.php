<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            // 'status' | 'assigned_to' | 'priority' | otros campos auditados
            $table->string('field_changed', 50);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['task_id', 'field_changed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_history');
    }
};
