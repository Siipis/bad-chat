<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOnlinesTable2 extends Migration
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
        $table = $this->prefix . 'onlines';

        DB::statement("ALTER TABLE $table MODIFY status ENUM('online', 'afk', 'brb', 'gaming', 'working')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = $this->prefix . 'onlines';

        DB::statement("ALTER TABLE $table MODIFY status ENUM('online', 'afk', 'brb', 'gaming')");
    }
}
