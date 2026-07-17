<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('level')->default(1);
            $table->string('color', 7)->default('#6b7280');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_priorities');
    }
};
