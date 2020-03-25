<?php


namespace app\api\controller;

use liupei\dingtalk\Client;
use app\api\model\Department;
use app\api\model\Employee;
use think\Validate;

class Ding
{
    public function __construct()
    {
        //为什么要带缓存配置？每次请求接口都从钉钉拿access_token这样不好吧，配置缓存后后续请求可以从缓存拿……好了不能透露太多
        Client::config([
            'type' => 'corp', // corp:企业内部开发
            'app_key' => e_config("app_key"), // 钉钉微应用对应的app_key
            'app_secret' => e_config("app_secret"), // 钉钉微应用对应的app_secret
            'corp_id' => e_config("corp_id"), // 钉钉微应用对应的app_secret
            'agent_id' => e_config("agent_id"), // 钉钉微应用对应的app_secret
            'cache' => [
                'host'   => e_config("cache_host"),
                'port'   => e_config("cache_port"),
                'select' => e_config("cache_select"),
                'password' => e_config("cache_password"),
                'prefix' => e_config("cache_prefix"),
            ]
        ]);

    }

    /**
     * 钉钉登录
     * @return \think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $code = input('code');
        $client = Client::newClient();
        $response = $client->path("/user/getuserinfo")->withAccessToken(true)->queryParam(['code' => $code])->request();
        if (!$response->isSuccess()) {
            return ajax_return([], 1002, $response->getErrMsg());
        }
        $ee = new Employee();
        $info = Employee::where('userid', $response->getData('userid'))->find();
        // 不存在，保存用户到数据库
        if (empty($info)) {
            $em = new Employee();
            $info = $em->syncUser($response->getData('userid'));
            if (empty($info)) {
                return ajax_return([], 1002, '同步用户信息出错');
            }
        }

//        $userid = '090133404330725926';.
//        $loginInfo = $ee->getLoginInfo($userid);
        $loginInfo = $ee->getLoginInfo($response->getData('userid'));

        if (empty($loginInfo)) {
            return ajax_return([], 1002, '暂无权限登录,请联系管理员开通');
        }
        if ($loginInfo['is_start'] == 1) {
            return ajax_return([], 1002, '用户已经被禁用,请联系管理员开通');
        }

        $token = create_token(json_encode($loginInfo));
        // 获取spaceid
        $domain = 'wensi';
        $client = Client::newClient();
        $spaceId = $client->getFile()->getCustomSpace($domain);

        return ajax_return([
            'token' => $token,
            'info' => $loginInfo,
            'corp' => [
                'app_id' => e_config('app_key'),
                'corp_id' => e_config('corp_id'),
                'agent_id' => e_config('agent_id'),
                'space_id' => $spaceId
            ]
        ]);
    }

    /**
     * 同步企业用户和部门信息
     * @return \think\response\Json
     * @throws \ErrorException
     */
    public function syncUsers()
    {
        $corpId = e_config('corp_id');
        $department = new Department();
        $result = $department->syncDepartment($corpId);
        if ($result == false) {
            ajax_return([], 1002, $department->getError());
        }
        $employee = new Employee();
        $result2 = $employee->syncUsers($corpId);
        if ($result2 == false) {
            ajax_return([], 1002, $employee->getError());
        }
        return ajax_return();
    }

    /**
     * 获取js签名所需参数
     * @throws \ErrorException
     */
    public function getCorpSign()
    {
        $url = input('url');
        $client = Client::newClient();
        $config = $client->getSign()->getCorpSign($url);
        return ajax_return(['config' => $config]);
    }

    /**
     * 授权自定义空间权限
     * @return \think\response\Json
     * @throws \ErrorException
     */
    public function grantCustomSpace()
    {
        $data = [];
        $data['type'] = input('post.type', 'add');
        $data['fields'] = input('post.fields', '');
        $validate = new Validate([
            'type' => 'require|in:add,download',
        ]);
        if (!$validate->check($data)) {
            return ajax_return([], 1001, $validate->getError());
        };

        $domain = 'wensi';
        $client = Client::newClient();
        $success = $client->getFile()->grantCustomSpace(user('userid'),'/', $data['type'], $data['fields'], 'wensi');
        $spaceId = $client->getFile()->getCustomSpace($domain);
        if ($success) {
            return ajax_return(['space_id' => $spaceId]);
        } else {
            return ajax_return([], 1002, '失败');
        }
    }
}