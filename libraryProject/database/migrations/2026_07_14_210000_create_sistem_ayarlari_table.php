<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistem_ayarlari', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path', 500)->nullable();
            $table->string('kurum_adi', 250)->nullable();
            $table->string('web_sitesi', 250)->nullable();
            $table->string('is_telefonu', 30)->nullable();
            $table->string('cep_telefonu', 30)->nullable();
            $table->string('eposta', 250)->nullable();
            $table->string('il', 100)->nullable();
            $table->string('ilce', 100)->nullable();
            $table->text('adres')->nullable();
            $table->text('izinli_ip_adresleri')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sistem_ayarlari');
    }
};
