<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnunActivePublished extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('coupon')->after('store')->nullable();
            $table->enum('active',['yes','no'])->nullable()->after('coupon')->default('yes');
            $table->enum('published',['yes','no'])->nullable()->after('active')->default('no');
            $table->enum('review',['yes','no'])->nullable()->after('published')->default('no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['active','published','coupon','review']);
        });
    }
}
