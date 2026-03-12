<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('carts') && !Schema::hasColumn('carts', 'quantity')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->integer('quantity')->default(1);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'quantity')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
