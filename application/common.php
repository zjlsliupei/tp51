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


