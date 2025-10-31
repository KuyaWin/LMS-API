<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basket_items', function (Blueprint $table) {
            if (!Schema::hasColumn('basket_items', 'addons')) {
                $table->json('addons')->nullable()->after('special_instructions');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('basket_items', function (Blueprint $table) {
            if (Schema::hasColumn('basket_items', 'addons')) {
                $table->dropColumn('addons');
            }
        });
    }
};
