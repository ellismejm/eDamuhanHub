
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species_local_names', function (Blueprint $table) {
            $table->id();

            $table->foreignId('weed_species_id')
                ->constrained('weed_species')
                ->cascadeOnDelete();

            $table->foreignId('local_name_id')
                ->constrained('local_names')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['weed_species_id', 'local_name_id'],
                'species_local_names_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_local_names');
    }
};
