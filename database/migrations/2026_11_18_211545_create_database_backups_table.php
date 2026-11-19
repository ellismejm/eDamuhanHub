
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();

            $table->string('filename', 255);
            $table->string('file_path', 500)->unique();

            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('checksum', 128)->nullable();

            $table->string('backup_type', 50)->default('manual');
            $table->string('status', 30)->default('pending');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('backup_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
