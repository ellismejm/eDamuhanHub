
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weed_species', function (Blueprint $table) {
            $table->id();

            // Scientific identification
            $table->string('scientific_name', 255);
            $table->string('taxonomic_author', 255)->nullable();
            $table->string('eppo_code', 30)->nullable()->unique();

            // Classification and taxonomy
            $table->foreignId('classification_id')
                ->constrained('weed_classifications')
                ->restrictOnDelete();

            $table->foreignId('family_id')
                ->constrained('weed_families')
                ->restrictOnDelete();

            // Ecological information
            $table->foreignId('habitat_id')
                ->nullable()
                ->constrained('habitats')
                ->restrictOnDelete();

            $table->foreignId('life_cycle_id')
                ->nullable()
                ->constrained('life_cycles')
                ->restrictOnDelete();

            // Original dataset information
            $table->text('common_names_source')->nullable();
            $table->text('reproduction_description')->nullable();

            // Detailed species information
            $table->longText('characteristics')->nullable();
            $table->longText('impacts')->nullable();
            $table->longText('weakness')->nullable();

            // Preserve original imported data
            $table->json('source_payload')->nullable();

            // Record ownership and modification
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Frequently used search filters
            $table->index('scientific_name');
            $table->index('classification_id');
            $table->index('family_id');
            $table->index('habitat_id');
            $table->index('life_cycle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weed_species');
    }
};
