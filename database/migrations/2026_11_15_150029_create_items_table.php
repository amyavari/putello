<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('household_id')->constrained();
            $table->foreignUuid('place_id')->nullable()->constrained();
            $table->string('name');
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
