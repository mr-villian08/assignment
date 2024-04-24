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
        Schema::create('bucket_suggestions', function (Blueprint $table) {
            $table->id();
            $table->integer("bucket_id");
            $table->integer("ball_id");
            $table->integer("quantity");
            $table->decimal("volume", 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bucket_suggestions');
    }
};
