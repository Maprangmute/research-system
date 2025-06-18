<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::create('project_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('project_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('project_name_th')->nullable(); // เพิ่มตรงนี้
        // หรือจะเพิ่ม EN ด้วยก็ได้
        // $table->string('project_name_en')->nullable();
        $table->string('action');
        $table->json('changes')->nullable();
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_logs');
    }
};
