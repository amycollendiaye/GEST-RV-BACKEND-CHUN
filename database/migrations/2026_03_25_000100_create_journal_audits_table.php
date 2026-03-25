<?php

use App\Enums\TypeAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('personel_hopital_id')->nullable();
            $table->enum('type_action', TypeAction::values())->index();
            $table->json('details');
            $table->string('adresse_ip', 45)->nullable()->index();
            $table->string('user_agent', 1024)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('personel_hopital_id')
                ->references('id')
                ->on('personel_hopitals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_audits');
    }
};
