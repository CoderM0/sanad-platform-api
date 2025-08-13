<?php

use App\Models\Doctor;
use App\Models\MdSession;
use App\Models\Patient;
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
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Patient::class);
            $table->foreignIdFor(Doctor::class);
            $table->foreignIdFor(MdSession::class);
            $table->string("card_number");
            $table->float("amount");
            $table->date("reservation_date");
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_records');
    }
};
