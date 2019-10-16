<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminUserTable extends Migration
{
    /**
     * 默认值
     */
    const DEFAULT_VALUE_STRING = '';
    const DEFAULT_VALUE_INT = 0;
    const DEFAULT_VALUE_TINYINT_TRUE = 10;
    const DEFAULT_VALUE_TINYINT_FALSE = 0;

    /**
     * @author Sojo
     * @var string 数据库表名
     */
    private $tableName = 'admin_user';
    private $tableComment = '后台管理系统用户表';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 32)->unique()->default(self::DEFAULT_VALUE_STRING)->comment('用户名');
            $table->string('nickname', 32)->default(self::DEFAULT_VALUE_STRING)->comment('昵称');
            $table->string('email', 64)->default(self::DEFAULT_VALUE_STRING)->comment('Email');
            $table->string('telephone', 16)->default(self::DEFAULT_VALUE_STRING)->comment('电话');
            $table->char('mobile', 11)->default(self::DEFAULT_VALUE_STRING)->comment('手机号');
            $table->string('password')->default(self::DEFAULT_VALUE_STRING)->comment('密码');
            $table->unsignedTinyInteger('status')->default(self::DEFAULT_VALUE_TINYINT_TRUE)->comment('状态，0：禁止；10：正常');
            $table->rememberToken()->comment('记住密码功能的验证令牌');
            $table->unsignedInteger('created_at')->default(self::DEFAULT_VALUE_INT)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(self::DEFAULT_VALUE_INT)->comment('更新时间');
        });

        DB::statement("ALTER TABLE `{$this->tableName}` comment '{$this->tableComment}'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
