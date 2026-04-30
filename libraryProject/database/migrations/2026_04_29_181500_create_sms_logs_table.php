<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gsm', 20);
            $table->text('message');
            $table->boolean('is_success')->default(false);
            $table->unsignedInteger('http_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->string('source', 100)->nullable(); // ornek: katalog_bagis, odunc, uye_otp
            $table->unsignedBigInteger('created_user')->nullable();
            $table->timestamps();

            $table->index('gsm');
            $table->index('is_success');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
