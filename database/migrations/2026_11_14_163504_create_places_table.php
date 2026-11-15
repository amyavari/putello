<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('household_id')->constrained();
            $table->foreignUuid('parent_id')->nullable()->constrained('places');
            $table->string('name');
            $table->timestamps();
        });
    }
};
