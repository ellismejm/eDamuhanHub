
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('weed_species_id')
                ->constrained('weed_species')
                ->cascadeOnDelete();

            $table->string('image_path', 500)->unique();
            $table->string('original_filename', 255);
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();

            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['weed_species_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_images');
    }
};
