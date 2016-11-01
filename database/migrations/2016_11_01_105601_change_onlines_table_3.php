<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOnlinesTable3 extends Migration
{
    private $prefix;

    public function __construct()
    {
        $this->prefix = DB::getTablePrefix();
    }


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('onlines', function($table) {
            $table->dropColumn('status');
        });

        Schema::table('onlines', function($table) {
            $table->string('status', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = $this->prefix . 'onlines';

        DB::statement("ALTER TABLE $table MODIFY status ENUM('online', 'afk', 'brb', 'gaming', 'working')");
    }
}
