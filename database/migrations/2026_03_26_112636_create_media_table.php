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
        Schema::create('media', function (Blueprint $table) {
            $table->id(); // primaire sleutel van het mediarecord
            $table->morphs('mediable'); // maakt mediable_id en mediable_typevoor polymorfe koppeling
$table->string('disk')->default('public'); // storage disk waarop hetbestand staat
$table->string('file_name'); // naam van het bestand
$table->string('file_path'); // pad naar het bestand op disk
$table->string('mime_type')->nullable(); // mime type zoalsimage/jpeg
$table->unsignedBigInteger('file_size')->nullable(); // grootte vanhet bestand in bytes
$table->string('alt_text')->nullable(); // alternatieve tekst voorSEO en accessibility
$table->text('caption')->nullable(); // optioneel onderschrift bij deafbeelding
$table->unsignedInteger('sort_order')->default(0); // sorteervolgordevoor later uitbreidbare galerijen
$table->boolean('is_featured')->default(false); // aanduiding of ditde featured image is
$table->timestamps(); // created_at en updated_at
$table->softDeletes(); // soft deletes voor veiliger beheer
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
