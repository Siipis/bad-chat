<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOnlinesTable extends Migration
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

        DB::statement("ALTER TABLE $table MODIFY status ENUM('online', 'afk', 'brb', 'gaming')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = $this->prefix . 'onlines';

        DB::statement("ALTER TABLE $table MODIFY status ENUM('online', 'afk', 'brb')");
    }
}
