<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\adminapi\logic\setting;


use app\common\logic\BaseLogic;
use app\common\model\OpenCity;
use app\common\model\Region;

class OpenCityLogic extends BaseLogic
{
    /**
     * @notes 新增
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/8/22 下午4:17
     */
    public function add($params)
    {
        OpenCity::create([
            'province_id' => $params['province_id'],
            'city_id' => $params['city_id'],
            'sort' => $params['sort'] ?? 0,
        ]);

        return true;
    }

    /**
     * @notes 详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/8/22 下午4:18
     */
    public function detail($id)
    {
        $result = OpenCity::where(['id'=>$id])->findOrEmpty()->toArray();

        return $result;
    }

    /**
     * @notes 编辑
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/8/22 下午4:18
     */
    public function edit($params)
    {
        OpenCity::update([
            'province_id' => $params['province_id'],
            'city_id' => $params['city_id'],
            'sort' => $params['sort'] ?? 0,
        ],['id'=>$params['id']]);

        return true;
    }

    /**
     * @notes 删除
     * @param $id
     * @return bool
     * @author ljj
     * @date 2024/8/22 下午4:18
     */
    public function del($id)
    {
        return OpenCity::destroy($id);
    }

    /**
     * @notes 通用列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/8/22 下午5:48
     */
    public function commonLists()
    {
        $lists = (new OpenCity())->field('province_id,city_id')
            ->append(['city_name','province_name'])
            ->order(['sort'=>'desc','id'=>'desc'])
            ->select()
            ->toArray();
        $data = [];
        foreach ($lists as $list) {
            if (!isset($data[$list['province_id']])) {
                $data[$list['province_id']] = [
                    'value' => $list['province_id'],
                    'pid' => 100000,
                    'label' => $list['province_name'],
                    'children' => []
                ];
            }
            $data[$list['province_id']]['children'][] = [
                'value' => $list['city_id'],
                'pid' => $list['province_id'],
                'label' => $list['city_name'],
//                        'children' => Region::where(['parent_id'=>$list['city_id']])->field('id as value,parent_id as pid,name as label')->select()->toArray()
            ];
        }
        $data = array_values($data);

        return $data;
    }

    /**
     * @notes 通用完整列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/16 上午10:26
     */
    public function commonIntactLists()
    {
        $openCity = (new OpenCity())->field('province_id,city_id')->select()->toArray();
        $provinceId = array_column($openCity, 'province_id');
        $cityId = array_column($openCity, 'city_id');
        $districtId = Region::where(['parent_id'=>$cityId])->column('id');
        $ids = array_merge($provinceId, $cityId, $districtId);

        $lists = Region::where(['id'=>$ids])->field('id,id as value,parent_id,level,name as label')->select()->toArray();
        $lists = linear_to_tree($lists,'children','id','parent_id',100000);

        return $lists;
    }
}