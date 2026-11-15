<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('household_id')->constrained();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->string('type');
            $table->foreignUuid('item_id')->nullable()->constrained();
            $table->foreignUuid('place_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_name');
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
};
