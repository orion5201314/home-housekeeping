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

namespace app\adminapi\logic\staff;


use app\common\enum\StaffEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\Goods;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffImproveInfo;
use think\facade\Db;

class StaffImproveInfoLogic extends BaseLogic
{
    /**
     * @notes 详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/9/5 下午1:57
     */
    public function detail($id)
    {
        $result = (new StaffImproveInfo())->alias('sii')
            ->join('staff s','s.id = sii.staff_id')
            ->field('sii.*,s.name,s.mobile,s.sex,s.age,s.identity_number,s.identity_portrait_image,s.identity_emblem_image,s.portrait_image')
            ->where(['sii.id'=>$id])
            ->findOrEmpty()
            ->toArray();
        $result['goods'] = Goods::where(['id'=>$result['goods_id']])->field('id,name,image')->select()->toArray();

        $oldData = Staff::where(['id'=>$result['staff_id']])->findOrEmpty()->toArray();
        $result['work_image'] = $oldData['work_image'];
        foreach ($result as $key => $value) {
            if (!isset($oldData[$key])) {
                continue;
            }
            if ($value != $oldData[$key]) {
                $result[$key.'_changed'] = true;
            }
        }

        return $result;
    }

    /**
     * @notes 审核
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/5 下午2:01
     */
    public function verify($params)
    {
        Db::startTrans();
        try {
            $staffImproveInfo = StaffImproveInfo::findOrEmpty($params['id']);
            $staffImproveInfo->verify_status = $params['verify_status'];
            $staffImproveInfo->verify_remarks = $params['verify_remarks'] ?? '';
            $staffImproveInfo->save();

            //审核通过
            if(StaffEnum::VERIFY_STATUS_SUCCESS == $params['verify_status']){
                Staff::update([
                    'id' => $staffImproveInfo['staff_id'],
                    'skill_id' => $staffImproveInfo['skill_id'],
                    'goods_id' => $staffImproveInfo['goods_id'],
                    'education' => $staffImproveInfo['education'] ?? null,
                    'nation' => $staffImproveInfo['nation'] ?? null,
//                    'work_image' => $staffImproveInfo['work_image'],
                    'credentials_image' => $staffImproveInfo['credentials_image'],
                    'province_id' => $staffImproveInfo['province_id'],
                    'city_id' => $staffImproveInfo['city_id'],
                    'district_id' => $staffImproveInfo['district_id'],
                    'address' => $staffImproveInfo['address'],
                    'longitude' => $staffImproveInfo['longitude'],
                    'latitude' => $staffImproveInfo['latitude']
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}