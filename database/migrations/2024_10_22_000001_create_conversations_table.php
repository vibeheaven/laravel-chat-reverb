<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Grup adı (private için null olabilir)
            $table->text('description')->nullable(); // Grup açıklaması
            $table->string('image')->nullable(); // Grup resmi
            $table->enum('type', ['private', 'group'])->default('private'); // Sohbet tipi
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Grubu oluşturan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

