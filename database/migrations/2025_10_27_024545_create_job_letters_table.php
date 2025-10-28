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
        Schema::create('job_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('employee_name');
            $table->string('old_position')->nullable();
            $table->string('new_position');
            $table->date('effective_date');
            $table->longText('letter_html'); // Generated letter HTML
            $table->enum('status', ['draft', 'sent', 'acknowledged'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_letters');
    }
};
