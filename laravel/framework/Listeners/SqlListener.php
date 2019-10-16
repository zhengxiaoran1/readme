<?php 

namespace Framework\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SqlListener {
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param  =QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event) {

    	// logger($event->sql);

    	$sql = str_replace("?", "'%s'", $event->sql);

        $sql = vsprintf($sql, $event->bindings);

        $log = new Logger('sql');

        $log->pushHandler(
            new StreamHandler(
                storage_path('logs/sql.log'),
                Logger::INFO
            )
        );

        $log->addInfo($sql);

//        \Log::info($log);

        // 在这里编写业务逻辑
    }
}