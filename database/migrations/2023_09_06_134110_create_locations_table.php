<?php

declare(strict_types=1);
use App\Models\District;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', static function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_physical')->default(true);
            $table->foreignIdFor(District::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
