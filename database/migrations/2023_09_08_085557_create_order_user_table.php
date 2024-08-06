<?php

declare(strict_types=1);
use App\Enum\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_user', static function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Order::class);
            $table->foreignIdFor(User::class);
            $table->string('status')->default(OrderStatus::UNAVAILABLE->getLabel());
            $table->timestamps();
            $table->unique(['order_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_user');
    }
};
