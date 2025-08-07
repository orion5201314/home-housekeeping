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


use app\common\enum\notice\NoticeEnum;
use app\common\enum\StaffEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\Goods;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffApply;
use think\facade\Db;

class StaffApplyLogic extends BaseLogic
{
    /**
     * @notes 详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/9/4 下午5:49
     */
    public function detail($id)
    {
        $result = (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->field('s.*,sa.apply_status,sa.remarks as verify_remarks')
            ->where(['sa.id'=>$id])
            ->group('sa.staff_id')
            ->append(['apply_status_desc'])
            ->findOrEmpty()
            ->toArray();

        $apply = StaffApply::withoutField(['id','staff_id','apply_status','remarks','create_time','update_time','delete_time'])->findOrEmpty($id)->toArray();
        foreach ($apply as $key=>$item) {
            if ($item != '' || $item != null) {
                $result[$key] = $item;
            }
        }

        $result['goods'] = Goods::where(['id'=>$result['goods_id']])->field('id,name,image')->select()->toArray();

        return $result;
    }

    /**
     * @notes 审核
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/4 下午6:03
     */
    public function apply($params)
    {
        Db::startTrans();
        try {
            $staffApply = StaffApply::findOrEmpty($params['id']);
            $staffApply->apply_status = $params['apply_status'];
            $staffApply->remarks = $params['remarks'] ?? '';
            $staffApply->save();

            $noticeSceneId = NoticeEnum::APPLY_FAIL_NOTICE_STAFF;

            //审核成功
            if(StaffEnum::APPLY_STATUS_SUCCESS == $params['apply_status']){
                $prevSn = Staff::whereNotNull('sn')->order(['id'=>'desc'])->value('sn');
                Staff::update([
                    'id'=>$staffApply->staff_id,
                    'sn' => sequence_sn($prevSn,4),
                    'is_staff'=>1,
                    'skill_id'=>$staffApply->skill_id,
                    'goods_id'=>$staffApply->goods_id,
                    'name'=>$staffApply->name,
                    'sex'=>$staffApply->sex,
                    'age'=>$staffApply->age,
                    'identity_number'=>$staffApply->identity_number,
                    'education'=>$staffApply->education ?? null,
                    'nation'=>$staffApply->nation ?? null,
                    'identity_portrait_image'=>$staffApply->identity_portrait_image,
                    'identity_emblem_image'=>$staffApply->identity_emblem_image,
                    'portrait_image'=>$staffApply->portrait_image,
                    'work_image'=>$staffApply->work_image,
                    'credentials_image' => $staffApply->credentials_image,
                    'province_id' => $staffApply->province_id,
                    'city_id' => $staffApply->city_id,
                    'district_id' => $staffApply->district_id,
                    'address' => $staffApply->address,
                    'longitude' => $staffApply->longitude,
                    'latitude' => $staffApply->latitude,
                    'last_address_info' => json_encode([
                        'province_id' => $staffApply->province_id,
                        'city_id' => $staffApply->city_id,
                        'district_id' => $staffApply->district_id,
                        'address' => $staffApply->address,
                        'longitude' => $staffApply->longitude,
                        'latitude' => $staffApply->latitude,
                    ]),
                ]);

                $noticeSceneId = NoticeEnum::APPLY_SUCCESS_NOTICE_STAFF;
            }

            // 入驻审核通知 - 通知师傅
            event('Notice', [
                'scene_id' =>  $noticeSceneId,
                'params' => [
                    'staff_id' => $staffApply->staff_id,
                ]
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}