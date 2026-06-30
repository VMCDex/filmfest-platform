<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrailerFilePathToFilmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('films', function (Blueprint $table) {
        $table->string('trailer_file_path')->nullable()->after('poster_path');
    });
}

public function down()
{
    Schema::table('films', function (Blueprint $table) {
        $table->dropColumn('trailer_file_path');
    });
}
}
