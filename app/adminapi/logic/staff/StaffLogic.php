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


use app\common\enum\AccountLogEnum;
use app\common\enum\StaffAccountLogEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\BaseLogic;
use app\common\logic\StaffAccountLogLogic;
use app\common\model\goods\Goods;
use app\common\model\staff\Staff;
use think\facade\Db;

class StaffLogic extends BaseLogic
{
    /**
     * @notes 添加师傅
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/2/10 3:52 下午
     */
    public function add($params)
    {
        $prevSn = Staff::whereNotNull('sn')->order('id', 'desc')->value('sn');
        Staff::create([
            'skill_id' => $params['skill_id'],
            'goods_id' => $params['goods_id'],
            'sn' => sequence_sn($prevSn,4),
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'sex' => $params['sex'],
            'age' => $params['age'],
            'identity_number' => $params['identity_number'],
            'education' => $params['education'] ?? null,
            'nation' => $params['nation'] ?? null,
            'identity_portrait_image' => $params['identity_portrait_image'],
            'identity_emblem_image' => $params['identity_emblem_image'],
            'portrait_image' => $params['portrait_image'],
            'work_image' => $params['work_image'],
            'credentials_image' => $params['credentials_image'],
            'province_id' => $params['province_id'],
            'city_id' => $params['city_id'],
            'district_id' => $params['district_id'],
            'address' => $params['address'] ?? '',
            'longitude' => $params['longitude'] ?? 0,
            'latitude' => $params['latitude'] ?? 0,
            'last_address_info' => json_encode([
                'province_id' => $params['province_id'],
                'city_id' => $params['city_id'],
                'district_id' => $params['district_id'],
                'address' => $params['address'] ?? '',
                'longitude' => $params['longitude'] ?? 0,
                'latitude' => $params['latitude'] ?? 0,
            ]),
            'status' => $params['status'],
            'work_status' => $params['work_status'],
            'sort' => $params['sort'],
            'is_staff' => 1,
        ]);

        return true;
    }

    /**
     * @notes 师傅详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/2/10 4:22 下午
     */
    public function detail($id)
    {
        $result = Staff::where(['id'=>$id])->findOrEmpty()->toArray();
        $result['goods'] = Goods::where(['id'=>$result['goods_id']])->field('id,name,image')->select()->toArray();

        return $result;
    }

    /**
     * @notes 编辑师傅
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/2/10 4:27 下午
     */
    public function edit($params)
    {
        Staff::update([
            'skill_id' => $params['skill_id'],
            'goods_id' => $params['goods_id'],
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'sex' => $params['sex'],
            'age' => $params['age'],
            'identity_number' => $params['identity_number'],
            'education' => $params['education'] ?? null,
            'nation' => $params['nation'] ?? null,
            'identity_portrait_image' => $params['identity_portrait_image'],
            'identity_emblem_image' => $params['identity_emblem_image'],
            'portrait_image' => $params['portrait_image'],
            'work_image' => $params['work_image'],
            'credentials_image' => $params['credentials_image'],
            'province_id' => $params['province_id'],
            'city_id' => $params['city_id'],
            'district_id' => $params['district_id'],
            'address' => $params['address'] ?? '',
            'longitude' => $params['longitude'] ?? 0,
            'latitude' => $params['latitude'] ?? 0,
            'last_address_info' => json_encode([
                'province_id' => $params['province_id'],
                'city_id' => $params['city_id'],
                'district_id' => $params['district_id'],
                'address' => $params['address'] ?? '',
                'longitude' => $params['longitude'] ?? 0,
                'latitude' => $params['latitude'] ?? 0,
            ]),
            'status' => $params['status'],
            'work_status' => $params['status'] == 0 ? 0 : $params['work_status'],
            'sort' => $params['sort'],
        ],['id'=>$params['id']]);

        return true;
    }

    /**
     * @notes 删除师傅
     * @param $id
     * @return bool
     * @author ljj
     * @date 2022/2/10 4:31 下午
     */
    public function del($id)
    {
        return Staff::destroy($id);
    }

    /**
     * @notes 调整金额
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/4 下午4:24
     */
    public function adjustAmount($params)
    {
        Db::startTrans();
        try {
            $staff = Staff::find($params['id']);

            if(StaffAccountLogEnum::INC == $params['adjust_action']){
                //增加
                $changeObject = StaffAccountLogEnum::DEPOSIT;
                $changeType = StaffAccountLogEnum::ADMIN_INC_DEPOSIT;
                if (StaffAccountLogEnum::DEPOSIT == $params['adjust_type']) {
                    //保证金
                    $staff->staff_deposit = $staff->staff_deposit + $params['adjust_num'];
                }
                if (StaffAccountLogEnum::EARNINGS == $params['adjust_type']) {
                    //佣金
                    $changeObject = StaffAccountLogEnum::EARNINGS;
                    $changeType = StaffAccountLogEnum::ADMIN_INC_EARNINGS;
                    $staff->staff_earnings = $staff->staff_earnings + $params['adjust_num'];
                }
                $staff->save();
                //流水日志
                StaffAccountLogLogic::add($staff->id,$changeObject,$changeType,StaffAccountLogEnum::INC,$params['adjust_num'],'',$params['remark'] ?? '');
            }else{
                //减少
                $changeObject = StaffAccountLogEnum::DEPOSIT;
                $changeType = StaffAccountLogEnum::ADMIN_DEC_DEPOSIT;
                if (StaffAccountLogEnum::DEPOSIT == $params['adjust_type']) {
                    //保证金
                    $staff->staff_deposit = $staff->staff_deposit - $params['adjust_num'];
                }
                if (StaffAccountLogEnum::EARNINGS == $params['adjust_type']) {
                    //佣金
                    $changeObject = StaffAccountLogEnum::EARNINGS;
                    $changeType = StaffAccountLogEnum::ADMIN_DEC_EARNINGS;
                    $staff->staff_earnings = $staff->staff_earnings - $params['adjust_num'];
                }
                $staff->save();
                //流水日志
                StaffAccountLogLogic::add($staff->id,$changeObject,$changeType,StaffAccountLogEnum::DEC,$params['adjust_num'],'',$params['remark'] ?? '');
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}