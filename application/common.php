<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

if (!function_exists("code")) {
    /**
     * 统一json返回
     * @param array $data 二维数组数据
     * @param int $code 返回code码，0：成功，非0：失败
     * @param string $msg 错误信息
     * @return \think\response\Json
     */
    function code($data = [], $code = 0, $msg = 'ok')
    {
        $return = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        return json($return)->header([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
        ]);
    }
}

if (!function_exists("http_post")) {
    /**
     * post请求
     * @param string $url
     * @param array $queryParam
     * @param array $postParam
     * @param array $header
     * @return bool|string
     */
    function http_post($url, $queryParam = [], $postParam = [], $header = [])
    {
        return liupei\phptools\Http::post($url, $queryParam, $postParam, $header);
    }
}

if (!function_exists("http_get")) {
    /**
     * get请求
     * @param $url
     * @param array $queryParam
     * @param array $header
     * @return bool|string
     */
    function http_get($url, $queryParam = [], $header = [])
    {
        return liupei\phptools\Http::get($url, $queryParam, $header);
    }
}

if (!function_exists("get_info_by_token")) {
    /**
     * 获取token绑定的值
     * @param string $token
     * @return mixed
     */
    function get_info_by_token($token = '')
    {
        if ($token == e_config('dev_token')) {
            return e_config('dev_token_value');
        }
        return cache('token_'. $token);
    }
}

if (!function_exists("create_token")) {

    /**
     * 创建token并缓存关联信息
     * @param string $bindInfo 绑定的关联信息
     * @param int $expireSecond 缓存过期秒数
     * @return bool|mixed
     */
    function create_token($bindInfo = '', $expireSecond = 7200)
    {
        $id = md5(create_unique_id());
        if ($id === false) {
            return false;
        }
        cache('token_'. $id, $bindInfo, $expireSecond);
        return $id;
    }
}



if (!function_exists("create_unique_id")) {
    /**
     * 生成唯一id
     * @return false|string 成功返回17位长度的字符串，失败返回false
     */
    function create_unique_id()
    {
        $uniqid = uniqid();
        $uniqid = str_replace('.', '', $uniqid);
        $unString = base_convert($uniqid, 16, 36);
        // 补足17位
        return str_pad($unString, 17, rand(1,9999999));
    }
}

if (!function_exists("e_config")) {
    /**
     * 获取配置，根据环境变量获取相应环境下的配置
     * @param string $name 变量名
     * @return mixed
     */
    function e_config($name)
    {
        if (empty($GLOBALS['e_config'])) {
            $GLOBALS['e_config'] = \Noodlehaus\Config::load(__DIR__ . '/../conf/conf.ini');
        }
        $runmode = \think\facade\Env::get("runmode", 'prod');
        // $runmode = $GLOBALS['e_config']->get('runmode', 'prod');
        return $GLOBALS['e_config']->get("{$runmode}.{$name}", $GLOBALS['e_config']->get($name));
    }
}

if (!function_exists("user")) {
    /**
     * 获取用户信息
     * @param string|null $key 变量名
     * @return mixed
     */
    function user($key = null)
    {
        if (is_null($key)) {
            return $GLOBALS['user'];
        }
        return isset($GLOBALS['user'][$key]) ? $GLOBALS['user'][$key] : null;
    }
}


