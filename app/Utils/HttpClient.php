<?php 

namespace K_HttpClient;

//use  App\Services\Logger;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Session;

/* Version 0.9, 6th April 2003 - Simon Willison ( http://simon.incutio.com/ )
   Manual: http://scripts.incutio.com/httpclient/
*/
class HttpClient {
    // Request vars
    private $host;
    private $port;
    private $path;
    private $method;
    private $postdata = '';
    private $cookies = array();
    private $referer;
    private $accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
    private $accept_encoding = 'gzip';
    private $accept_language = 'en-us';
    private $user_agent = 'Incutio HttpClient v0.9';
    // Options
    private $timeout = 180;
    private $use_gzip = true;
    private $persist_cookies = true;  // If true, received cookies are placed in the $this->cookies array ready for the next request
    // Note: This currently ignores the cookie path (and time) completely. Time is not important,
    //       but path could possibly lead to security problems.
    private $persist_referers = true; // For each request, sends path of last request as referer
    private $debug = false;
    //@todo
    private $handle_redirects = true; // Auaomtically redirect if Location or URI header is found
    private $max_redirects = 50;//////////暂时修改
    private $headers_only = false;    // If true, stops receiving once headers have been read.
    // Basic authorization variables
    private $username;
    private $password;
    // Response vars
    private $status;
    private $headers = array();
    private $content = '';
    private $errormsg;
    // Tracker variables
    private $redirect_count = 0;
    private $cookie_host = '';
    private $debug_log;
    private $last_err_code;
    private $last_err_msg;
    /*private $special_path = array(
        "/v1/wx/get_user_by_mp_code",
        "/v1/activity_application_users",
        "/v1/wechat_pays/refund");*/
    private $uuid;
    private $admin_id = null;
    private $noPermissionCode = 6233; //无权限error_code
    private $subAccountDeleteCode = 6234;//子账号被删除err_code
    private $manager_label = null;//管理端权限label参数
    private $isDbRedirects = false;//是否后台重定向

    public function __construct($host = null, $port = null,$uuid = null,$admin_id =null)
    {
        if (empty($host)) {
            $this->host = env('CGI_HOST');
        } else {
            $this->host = $host;
        }

        if (empty($port)) {
            $this->port = env('CGI_PORT');
        } else {
            $this->port = $port;
        }

        //if(ENVIRONMENT == "production"){
        //    $this->host = "api.inner.grouplus.cn";
        //}else if(ENVIRONMENT == "testing"){
        //    $this->host = "test.api.inner.grouplus.cn";
        //}

        $this->uuid = $uuid;
        //$this->debug_log = Logger::factory ( Logger::DEBUG_LOG, 'grouplus' );

        if($admin_id){
            $this->admin_id = $admin_id;
        }
    }

    //获取错误信息
    public function get_last_err_msg(){
        return $this->last_err_msg;
    }

    function get($path, $data = false,$get_err=false) {
        //$this->path = $path;
        $this->method = 'GET';
        if($data){
            if(strpos($path,"?") == false){
                $path .= '?'.$this->buildQueryString($data);
            }else{
                $path .= '&'.$this->buildQueryString($data);
            }
        }
        //后台http重定向,直接请求重定向地址,不添加公共参数
        if($this->isDbRedirects){
            $this->path = $path;
            $this->isDbRedirects = false;
        }else{
            $this->path = $this->addPathParam($path);
        }
        return $this->send_request($get_err);
    }

    function post($path, $data,$get_err=false) {
        //  $this->debug_log->log("post路径：{$path},请求参数：".json_encode($data,JSON_UNESCAPED_UNICODE));
        //添加公共参数
        $this->path = $this->addPathParam($path);
        $this->method = 'POST';
        $this->postdata = $this->buildPostQueryString($data);
        return $this->send_request($get_err);
    }

    function delete($path,$data=false,$get_err=false){
        // $this->debug_log->log("delete路径：{$path},请求参数：".$this->JSON($data));
        //添加公共参数
        $this->path = $this->addPathParam($path);
        $this->method = 'DELETE';
        //$data["logid"] = $this->uuid;
        $this->postdata = $this->buildPostQueryString($data);
        return $this->send_request($get_err);
    }

    function put($path,$data,$get_err=false){
        // $this->debug_log->log("put路径：{$path},请求参数：".json_encode($data,JSON_UNESCAPED_UNICODE));
        //添加公共参数
        $this->path = $this->addPathParam($path);
        $this->method = 'PUT';
        //$data["logid"] = $this->uuid;
        $this->postdata = $this->buildPostQueryString($data);
        return $this->send_request($get_err);
    }

    function patch($path,$data=false,$get_err=false){
        //  $this->debug_log->log("patch路径：{$path},请求参数：".$this->JSON($data));
        //添加公共参数
        $this->path = $this->addPathParam($path);
        $this->method = 'PATCH';
//        if($data){
//            $data["logid"] = $this->uuid;
//        } else{
//            $data = array("logid"=>$this->uuid);
//        }
        $this->postdata = $this->buildPostQueryString($data);

        return $this->send_request($get_err);
    }

    public function setManagerLabel($label = null){
        $this->manager_label = $label;
    }

    //@todo
    function updateSession(){
        if(Session::has('session_group_user')){
            $user = Session::get('session_group_user');
            $url = "/v1/user_permissions/permission_functions";
            $this->setManagerLabel("FUNCTION_GET_FUNCTIONS");
            $param = array("admin_id"=>$user->admin_id);
            $rs = $this->get($url,$param);
            if($rs){
                $permissions = $rs->permissions;
                $user->permissions = $permissions;
                $user->functions_array = $this->functions_array($user);
                Session::forget('session_group_user');
                Session::put('session_group_user',$user);
                Session::save();
            }
        }
    }

    //@todo
    private function functions_array($user){
        $functions_array = array();

        foreach($user->permissions as $key_promission => $promission) {
            foreach($promission->functions as $key_function => $function) {
                $functions_array[$function->function_label] = $function;
            }
        }

        return $functions_array;
    }

    /**
     * 1)因为网络等原因调用后台接口失败，返回false;
     * 2)后台返回不符合json规范，返回false;
     * 3)后台返回的json数据中不包含err_code字段，返回false;
     * 4)后台返回符合规范情况下。方法参数 get_err 值为true,err_code不为0也返回信息；否则返回false
     */
    private function send_request($get_err=false){
        try{
            if($this->doRequest()){
                $rs = json_decode($this->content);
                return $rs ;
                if(json_last_error()==JSON_ERROR_NONE){//如果是合法的json数据
                    if(isset($rs->err_code)){
                        if($rs->err_code == 0){
                            return $rs;
                        }else if($rs->err_code == $this->noPermissionCode){//无权限
                            //更新session
                            $this->updateSession();
                            //如果是ajax操作
                            if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
                                $this->last_err_msg = "你的权限被更改,本帐号没有该功能的操作权限";
                                return false;
                            } else {//链接请求跳转到403
                                abort(403);
                            }
                        }else if($rs->err_code == $this->subAccountDeleteCode){//子账号被删除
                            Session::flush();
                            //如果是ajax操作
                            if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
                                $this->last_err_msg = "本账号已被管理员删除";
                                return false;
                            } else {//链接请求跳转到401
                                abort(401);
                            }
                        }else{
                            $this->last_err_msg = isset($rs->err_msg)?$rs->err_msg:"";
                            //err_code不为0也返回值
                            if($get_err){
                                return $rs;
                            }
                            return false;
                        }
                    }else{
                        $this->last_err_msg = "缺少err_code返回";
                        return $rs;
                        //return false;
                    }
                }else{
                    $this->last_err_msg = "后台接口返回信息不合json规范";
                    return false;
                }
            } else{
                // $this->last_err_msg = "后台接口调用失败";
                return false;//发送请求出错
            }
        }catch (Exception $e){
            $this->debug_log->log($e->getMessage());
            $this->debug_log->log($e->getTraceAsString());
            return false;
        }
    }

    //给path添加公共参数
    function addPathParam($path) {
        //添加logid
        if(strpos($path,"?") == false){
            $path .= '?logid='.$this->uuid;
        }else{
            $path .= '&logid='.$this->uuid;
        }
        //添加admin_id &&strpos($path,"admin_id") === false
        if($this->admin_id){
            $path .= "&admin_id=".$this->admin_id;
        }
        //权限参数
        /*
        switch (HOSTNAME) {
            case ADMIN_HOSTNAME://管理段
                if($this->manager_label){
                    $path .= "&function_label=".$this->manager_label;
                }
                break;
            case OPERATION_HOSTNAME://运营端
                $path .= "&function_label=FUNCTION_LABEL_OP_PERSPECTIVE";
                break;
            case USER_HOSTNAME2:
            case USER_HOSTNAME://用户端
                $uri = $_SERVER["REQUEST_URI"];
                if(strripos($uri,"/manager/")===0 || stripos($uri,"request=manager") !== false){
                    if($this->manager_label){
                        $path .= "&function_label=".$this->manager_label;
                    }
                }else{
                    $path .= "&function_label=FUNCTION_LABEL_USER_PERSPECTIVE";
                }
                break;
            default:
                //@todo
                $path .= "&function_label=FUNCTION_LABEL_USER_PERSPECTIVE";
               // exit('80 boy,The application environment is not set correctly.');
        }*/
        //重置manager_label
        $this->manager_label = null;
        return $path;
    }

    //构造post请求的参数
    function buildPostQueryString($data) {
        $querystring = '';
        if (is_array($data)) {
            $querystring = json_encode($data);
        }else{
            $querystring = $data;
        }
        return $querystring;
    }

    function buildQueryString($data) {
        $querystring = '';
        if (is_array($data)) {
            // Change data in to postable data
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $querystring .= urlencode($key).'='.urlencode($val2).'&';
                    }
                } else {
                    $querystring .= urlencode($key).'='.urlencode($val).'&';
                }
            }
            $querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
        } else {
            $querystring = $data;
        }
        return $querystring;
    }

    function doRequest() {
        //$this->debug_log->log("path：{$this->path} || host: {$this->host} || port：{$this->port}");
        //$this->debug_log->log("postdata:".$this->postdata);
        // Performs the actual HTTP request, returning true or false depending on outcome
        if (!$fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
            // Set error message
            switch($errno) {
                case -3:
                    $this->errormsg = 'Socket creation failed (-3)';
                case -4:
                    $this->errormsg = 'DNS lookup failure (-4)';
                case -5:
                    $this->errormsg = 'Connection refused or timed out (-5)';
                default:
                    $this->errormsg = 'Connection failed ('.$errno.')';
                    $this->errormsg .= ' '.$errstr;
                    $this->debug($this->errormsg);
            }
            $this->last_err_msg = "无法连接后台接口服务器，host:".$this->host.",port:".$this->port;
            return false;
        }


        socket_set_timeout($fp, $this->timeout);
        $request = $this->buildRequest();
        $this->debug('Request', $request);
        //error_log($request);
        fwrite($fp, $request);
        // Reset all the variables that should not persist between requests
        $this->headers = array();
        $this->content = '';
        $this->errormsg = '';
        // Set a couple of flags
        $inHeaders = true;
        $atStart = true;
        // Now start reading back the response
        //dump(fgets($fp, 40960000));
        while (!feof($fp)) {
            $line = fgets($fp, 409600);
            error_log($line);
            if ($atStart) {
                // Deal with first line of returned data
                $atStart = false;
                if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
                    $this->errormsg = "Status code line invalid: ".htmlentities($line);
                    //$this->debug_log->log($this->errormsg);
                    $this->last_err_msg = $this->errormsg;
                    return false;
                }
                $http_version = $m[1]; // not used
                $this->status = $m[2];
                $status_string = $m[3]; // not used
                $this->debug(trim($line));
                continue;
            }
            if ($inHeaders) {
                if (trim($line) == '') {
                    $inHeaders = false;
                    $this->debug('Received Headers', $this->headers);
                    if ($this->headers_only) {
                        break; // Skip the rest of the input
                    }
                    continue;
                }
                if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
                    // Skip to the next header
                    continue;
                }
                $key = strtolower(trim($m[1]));
                $val = trim($m[2]);
                // Deal with the possibility of multiple headers of same name
                if (isset($this->headers[$key])) {
                    if (is_array($this->headers[$key])) {
                        $this->headers[$key][] = $val;
                    } else {
                        $this->headers[$key] = array($this->headers[$key], $val);
                    }
                } else {
                    $this->headers[$key] = $val;
                }
                continue;
            }
            // We're not in the headers, so append the line to the contents
            $this->content .= $line;
        }
        fclose($fp);
        // If data is compressed, uncompress it
        if (isset($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
            $this->debug('Content is gzip encoded, unzipping it');
            $this->content = substr($this->content, 10); // See http://www.php.net/manual/en/function.gzencode.php
            $this->content = gzinflate($this->content);
        }
        // If $persist_cookies, deal with any cookies
        if ($this->persist_cookies && isset($this->headers['set-cookie']) && $this->host == $this->cookie_host) {
            $cookies = $this->headers['set-cookie'];
            if (!is_array($cookies)) {
                $cookies = array($cookies);
            }
            foreach ($cookies as $cookie) {
                if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
                    $this->cookies[$m[1]] = $m[2];
                }
            }
            // Record domain of cookies for security reasons
            $this->cookie_host = $this->host;
        }
        // If $persist_referers, set the referer ready for the next request
        if ($this->persist_referers) {
            $this->debug('Persisting referer: '.$this->getRequestURL());
            $this->referer = $this->getRequestURL();
        }
        // Finally, if handle_redirects and a redirect is sent, do that
        if ($this->handle_redirects) {
            if (++$this->redirect_count >= $this->max_redirects) {
                $this->errormsg = 'Number of redirects exceeded maximum ('.$this->max_redirects.')';
                //$this->debug_log->log($this->errormsg);
                $this->last_err_msg = $this->errormsg;
                $this->redirect_count = 0;
                return false;
            }
            $location = isset($this->headers['location']) ? $this->headers['location'] : '';
            $uri = isset($this->headers['uri']) ? $this->headers['uri'] : '';
            if ($location || $uri) {
                //$url = parse_url($location.$uri);
                $url = $location.$uri;
                // This will FAIL if redirect is to a different site
                $this->isDbRedirects = true;
                return $this->get($url);
            }
        }
       // $this->debug_log->log("返回信息：".$this->content);
        return true;
    }

    function buildRequest() {
        $headers = array();
        $headers[] = "{$this->method} {$this->path} HTTP/1.1"; // Using 1.1 leads to all manner of problems, such as "chunked" encoding
        $headers[] = "Host: {$this->host}";
        //$headers[] = "User-Agent: {$this->user_agent}";
        //$headers[] = "Accept: {$this->accept}";
        if ($this->use_gzip) {
        //    $headers[] = "Accept-encoding: {$this->accept_encoding}";
        }
        //$headers[] = "Accept-language: {$this->accept_language}";
        if ($this->referer) {
            $headers[] = "Referer: {$this->referer}";
        }
        // Cookies
        if ($this->cookies) {
            $cookie = 'Cookie: ';
            foreach ($this->cookies as $key => $value) {
                $cookie .= "$key=$value; ";
            }
            $headers[] = $cookie;
        }
        // Basic authentication
        if ($this->username && $this->password) {
            $headers[] = 'Authorization: BASIC '.base64_encode($this->username.':'.$this->password);
        }
        // If this is a POST, set the content type and length
        if ($this->postdata) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        //    $headers[] = 'Content-Length: '.strlen($this->postdata);
        }
        $request = implode("\r\n", $headers)."\r\n\r\n".$this->postdata;
        error_log($request);
        return $request;
    }

    function getStatus() {
        return $this->status;
    }
    function getContent() {
        return $this->content;
    }
    function getHeaders() {
        return $this->headers;
    }

    function getHeader($header) {
        $header = strtolower($header);
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        } else {
            return false;
        }
    }

    function getError() {
        return $this->errormsg;
    }

    function getCookies() {
        return $this->cookies;
    }

    function getRequestURL() {
        $url = 'https://'.$this->host;
        //$url = $this->host;
        if ($this->port != 80) {
            $url .= ':'.$this->port;
        }
        $url .= $this->path;
        return $url;
    }
    // Setter methods
    function setUserAgent($string) {
        $this->user_agent = $string;
    }
    function setAuthorization($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    function setCookies($array) {
        $this->cookies = $array;
    }
    // Option setting methods
    function useGzip($boolean) {
        $this->use_gzip = $boolean;
    }
    function setPersistCookies($boolean) {
        $this->persist_cookies = $boolean;
    }
    function setPersistReferers($boolean) {
        $this->persist_referers = $boolean;
    }
    function setHandleRedirects($boolean) {
        $this->handle_redirects = $boolean;
    }
    function setMaxRedirects($num) {
        $this->max_redirects = $num;
    }
    function setHeadersOnly($boolean) {
        $this->headers_only = $boolean;
    }
    function setDebug($boolean) {
        $this->debug = $boolean;
    }
    // "Quick" static methods
    function quickGet($url) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?'.$bits['query'];
        }
        $client = new HttpClient($host, $port);
        if (!$client->get($path)) {
            return false;
        } else {
            return $client->getContent();
        }
    }
    function quickPost($url, $data) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        $client = new HttpClient($host, $port);
        if (!$client->post($path, $data)) {
            return false;
        } else {
            return $client->getContent();
        }
    }
    //调试
    function debug($msg, $object = false) {
        if ($this->debug) {
            print '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>HttpClient Debug:</strong> '.$msg;
            if ($object) {
                ob_start();
                print_r($object);
                $content = htmlentities(ob_get_contents());
                ob_end_clean();
                print '<pre>'.$content.'</pre>';
            }
            print '</div>';
        }
    }


    /**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        $recursive_counter++;
        if ($recursive_counter > 10) {
            die('数组深度太深');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else if(is_object($value)){

            } else{
                $array[$key] = json_encode($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    private function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
}

