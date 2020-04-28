<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncomeCategoriesToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('income_categories', 'categories');

        Schema::table('categories', function (Blueprint $table) {
            $table->enum('type', [
                'other',
                'income',
                'expense'
            ])->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('type');

            Schema::rename('categories', 'income_categories');
        });
    }
}
