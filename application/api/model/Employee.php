<?php


namespace app\api\model;

use think\Model;
use liupei\dingtalk\Client;
use think\model\concern\SoftDelete;
use think\Db;
class Employee extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 同步钉钉用户
     * @param string $userid 钉钉userid
     * @return Employee|bool
     * @throws \ErrorException
     */
    public function syncUser($userid)
    {
        $client = Client::newClient();
        $responseUserInfo = $client->path("/user/get")->withAccessToken(true)->queryParam(['userid' => $userid])->request();
        if (!$responseUserInfo->isSuccess()) {
            return false;
        }
        $new = Employee::create([
            'userid' => $responseUserInfo->getData('userid'),
            'nickname' => $responseUserInfo->getData('name'),
            'avatar' => $responseUserInfo->getData('avatar'),
            'department' => implode(',', $responseUserInfo->getData('department')),
            'is_admin' => $responseUserInfo->getData('isAdmin'),
            'extend' => json_encode($responseUserInfo->getData()),
        ], true, true);
        return $new;
    }

    /**
     * 同步企业用户
     * @param string $corpId
     * @return bool|int|string
     * @throws \ErrorException
     */
    public function syncUsers($corpId)
    {
        $client = Client::newClient();
        $department = new Department();
        $depts = $department->select();
        if (empty($depts)) {
            $this->error = '部门不存在';
            return false;
        }
        $users = [];
        foreach ($depts as $dept) {
            // 获取部门下用户信息
            $userResponse = $client->path("/user/listbypage")->withAccessToken(true)->queryParam([
                'department_id' => $dept->department_id,
                'offset' => 0,
                'size' => 100
            ])->request();
            if ($userResponse->isSuccess()) {
                $userlist = $userResponse->getData('userlist');
                foreach ($userlist as $item) {
                    $users[] = [
                        "userid" => $item['userid'],
                        "nickname" => $item['name'],
                        "avatar" => $item['avatar'],
                        "department" => implode(',', $item['department']),
                        "is_admin" => $item['isAdmin'],
                        "extend" => json_encode($item),
                    ];
                }
            }
        }
        return $this->insertAll($users, true);
    }
    
    
    public function getEmployeeNameById($userid)
    {
        $where['userid'] = $userid;
        $rs = Db::name('employee')->field('userid,nickname,avatar')->where($where)->find();
        if(empty($rs)){
            return [];
        }else{
            return $rs;
        }
    }

    /**
     * 获取登录信息
     * @param string $userId 钉钉userid
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLoginInfo($userId)
    {
        $info = $this
            ->alias('a')
            ->join('s_employee_ext b', 'a.userid=b.userid')
            ->where('a.userid', $userId)
            ->field('a.*,b.part,b.is_start,b.area')
            ->find();
        return $info;
    }
}