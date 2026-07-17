<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('hours', 6, 2);
            $table->text('comments')->nullable();
            $table->date('spent_on');
            $table->timestamps();

            $table->index(['task_id', 'user_id']);
            $table->index('spent_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
