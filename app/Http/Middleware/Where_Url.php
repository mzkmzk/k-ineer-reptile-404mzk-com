<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use App\Models\Creator_Site_Model;

class Where_Url
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
       // dump(12312);
        $where_array = json_decode( $request->get('where'));
        $site_data  = Creator_Site_Model::where('creator_user_id', $request->get('creator_user_id'))
                                ->first();
        $host = is_null($site_data) ? '没有任何域名匹配....' : $site_data->host;
        //$where_array = [];
        if ( is_null($where_array) ){
            $where_array = [];
        }
       
        //dump(gettype($where_array) );
        array_push($where_array, [
            "key" => "referer",
            "condition" => "REGEXP",
            "value" => $host
        ]);
        $request->merge(([
            'where' => json_encode($where_array)
        ]));
        
        

        return $next($request);
    }
}
