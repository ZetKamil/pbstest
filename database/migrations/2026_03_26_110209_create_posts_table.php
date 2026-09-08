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
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // primaire sleutel
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // auteur van de post
$table->string('title'); // titel van de post
$table->string('slug')->unique(); // unieke slug voor URL's
$table->text('excerpt')->nullable(); // korte samenvatting, mag leeg zijn
$table->longText('body'); // volledige inhoud
$table->boolean('is_published')->default(false); // publicatiestatus
$table->timestamp('published_at')->nullable(); // publicatiedatum, mag leeg zijn
$table->timestamps(); // created_at en updated_at
$table->softDeletes(); // deleted_at zodat posts soft deleted kunnen worden
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
