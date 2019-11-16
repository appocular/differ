<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class CreateDiffsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diffs', static function (Blueprint $table): void {
            $table->string('image_url');
            $table->string('baseline_url');
            $table->string('diff_url')->nullable();
            $table->boolean('different');
            $table->timestamps();

            $table->unique(['image_url', 'baseline_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diffs');
    }
}
