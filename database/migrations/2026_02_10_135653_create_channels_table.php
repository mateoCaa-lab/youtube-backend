<?php
//Channels representa un canal de youtube, cada canal representa a un usuario, tiene nombre, slug y metadata
use Illuminate\Database\Migrations\Migration; //Estructura de la migracion
use Illuminate\Database\Schema\Blueprint; // definir columnas
use Illuminate\Support\Facades\Schema; // crear/modificar las tablas

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('banner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
