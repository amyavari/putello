<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_id')->constrained();
            $table->foreignUuid('borrower_id')->constrained();
            $table->date('lent_at');
            $table->date('due_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }
};
