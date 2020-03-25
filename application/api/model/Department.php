<?php


namespace app\api\model;

use think\Model;
use liupei\dingtalk\Client;
use think\model\concern\SoftDelete;
use think\Db;
class Department extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 同步企业部门
     * @param string $corpId
     * @return bool|\think\Collection
     * @throws \ErrorException
     */
    public function syncDepartment($corpId)
    {
        $client = Client::newClient();
        // 获取根部门信息
        $root = $client->path("/department/get")->withAccessToken(true)->queryParam(['id' => 1])->request();
        if (!$root->isSuccess()) {
            $this->error = $root->getErrMsg();
            return false;
        }
        $data = [];
        $data[] = [
            'department_id' => $root->getData('id'),
            'name' => $root->getData('name'),
            'parent_id' => 0,
        ];
        // 获取子部门信息
        $response = $client->path("/department/list")->withAccessToken(true)->queryParam(['fetch_child' => true])->request();
        if ($response->isSuccess()) {
            $departments = $response->getData('department');
            foreach ($departments as $item) {
                $data[] = [
                    'department_id' => $item['id'],
                    'name' => $item['name'],
                    'parent_id' => $item['parentid'],
                ];
            }
        }
        return $this->insertAll($data, true);
    }
    
    public function getDeptNameById($dept_id)
    {
        $where['department_id'] = $dept_id;
        $rs = Db::name('department')->field('department_id,name')->where($where)->find();
        return $rs;
    }
    
    public function getDeptInfo($dept_ids)
    {
        $where[] = ['department_id','in',$dept_ids];
        $rs = Db::name('department')->where($where)->column('department_id,name,parent_id','department_id');
        return $rs;
    }
}