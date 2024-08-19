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
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id'); // اضافه کردن ستون user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // اضافه کردن کلید خارجی
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // حذف کلید خارجی
            $table->dropColumn('user_id'); // حذف ستون user_id
        });
    }
};
