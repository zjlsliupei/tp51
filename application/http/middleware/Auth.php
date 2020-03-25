<?php

namespace app\http\middleware;

use think\Request;

class Auth
{
    // 请添加要过滤的api接口
    private $noCheckApi = [];

    public function __construct()
    {
        // 加载不检测api接口
        $uncheckApiList = e_config('uncheck_api_list');
        if (!empty($uncheckApiList)) {
            $uncheckApiListArr = explode(',', $uncheckApiList);
            foreach ($uncheckApiListArr as $item) {
                $this->noCheckApi[] = strtolower($item);
            }
        }
    }

    public function handle(Request $request, \Closure $next)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $token = $request->param("token");
        $pathInfo = $request->pathinfo();
        $pathInfo = strtolower($pathInfo);
        if (!in_array($pathInfo, $this->noCheckApi)) {
            $result = get_info_by_token($token);
            if ($result === false) {
                return code([], 1000, 'token不合法');
            }
            $GLOBALS['user'] = json_decode($result, true);
        }
        return $next($request);
    }

}
