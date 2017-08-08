<?php
namespace App\Listeners;

use Log;
use DateTime;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Handle the event.
     *
     * @param  Events  $event
     * @return void
     */
    //public function handle($sql, $params)
    public function handle($event)
    {
        //error_log(env('APP_ENV', 'production'));
            //foreach ($event->bindings as $index => $param) {
            //    if ($param instanceof DateTime) {
            //        $params[$index] = $param->format('Y-m-d H:i:s');
            //    }
            //}

            //$sql = str_replace("?", "'%s'", $sql);
            //array_unshift($params, $sql);

             //error_log(call_user_func_array('sprintf', $params));
        try{
            //error_log(json_encode($event->sql));
             //error_log(json_encode($event->bindings));
             $sql = str_replace("?", "'%s'", $event->sql);
             
             foreach ($event->bindings as $key => $value) {
                 if(!is_string($value)) {
                    error_log('use json_encode');
                    $event->bindings[$key] = json_encode($value);
                 }
             }
            $log = vsprintf($sql, $event->bindings);

            error_log($log);
        }catch(\Exception $e) {

           // error_log('error in sql '  . json_encode($e));
        }

    }
}