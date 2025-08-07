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

namespace app\staffapi\logic;

use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\StaffEnum;
use app\common\logic\BaseLogic;
use app\common\model\order\Order;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffApply;
use app\common\model\staff\StaffBusytime;
use app\common\model\staff\StaffImproveInfo;
use app\common\model\staff\StaffSkill;
use app\common\model\staff\StaffWithdrawAccount;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\staffapi\service\StaffTokenService;
use think\facade\Config;
use think\facade\Db;

class StaffLogic extends BaseLogic
{
    /**
     * @notes 师傅中心
     * @param $staffInfo
     * @return array
     * @author ljj
     * @date 2024/10/10 下午3:31
     */
    public function center($staffInfo)
    {
        $staff_id = $staffInfo['staff_id'] ?? 0;
        $staff = Staff::where(['id'=>$staff_id])
            ->field('id,sn,name,work_image as avatar,last_address_info,is_staff,work_status,staff_deposit,staff_earnings,goods_id,identity_portrait_image,identity_emblem_image,portrait_image,identity_number,sex,age,password,mobile')
            ->append(['sex_desc'])
            ->findOrEmpty()
            ->toArray();

        //申请入驻信息
        $staffApply = StaffApply::where(['staff_id'=>$staff_id])->order(['id'=>'desc'])->findOrEmpty()->toArray();
        $staff['apply_status'] = $staffApply['apply_status'] ?? -1;
        $staff['apply_remarks'] = $staffApply['remarks'] ?? '';

        $staff['avatar'] = empty($staff['avatar']) ? '' : FileService::getFileUrl($staff['avatar']);

        //判断是否设置密码
        $staff['has_password'] = empty($staff['password']) ? false : true;
        unset($staff['password']);

        return $staff;
    }

    /**
     * @notes 师傅信息
     * @param $staff_id
     * @return array
     * @author ljj
     * @date 2024/10/10 下午4:22
     */
    public static function info($staff_id)
    {
        $staff = Staff::where(['id'=>$staff_id])
            ->append(['skill_name','education_name','nation_name'])
            ->findOrEmpty()
            ->toArray();

        return $staff;
    }

    /**
     * @notes 设置用户信息
     * @param $params
     * @return bool
     * @author ljj
     * @date 2024/11/11 下午5:53
     */
    public static function setInfo($params): bool
    {
        Staff::update(['id' => $params['staff_id'], $params['field'] => $params['value']]);
        return true;
    }

    /**
     * @notes 忘记密码
     * @param array $params
     * @return bool
     * @author ljj
     * @date 2024/10/10 下午4:38
     */
    public static function forgetPassword(array $params)
    {
        try {
            $staff = Staff::where(['mobile' => $params['mobile']])->findOrEmpty();

            // 新密码密码
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);

            //更新信息
            $staff->password = $password;
            $staff->save();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 修改密码
     * @param array $params
     * @return bool
     * @author ljj
     * @date 2024/10/10 下午4:38
     */
    public static function changePassword(array $params)
    {
        try {
            $staff = Staff::where(['id' => $params['staff_id']])->findOrEmpty();

            // 新密码密码
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);

            //更新信息
            $staff->password = $password;
            $staff->save();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 更新最后一次定位地址
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/10/10 下午5:50
     */
    public static function updateLastAddress($params)
    {
        $lastAddressInfo = [];
        if (!empty($params['province_id']) && !empty($params['city_id']) && !empty($params['district_id']) && !empty($params['longitude']) && !empty($params['latitude'])) {
            $lastAddressInfo = [
                'province_id' => $params['province_id'],
                'city_id' => $params['city_id'],
                'district_id' => $params['district_id'],
                'address' => $params['address'] ?? '',
                'longitude' => $params['longitude'],
                'latitude' => $params['latitude'],
            ];
        }
        Staff::update([
            'last_address_info' => json_encode($lastAddressInfo),
        ],['id'=>$params['staff_id']]);
        return true;
    }

    /**
     * @notes 切换工作状态
     * @param $params
     * @return true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/11 下午1:58
     */
    public static function changeWorkStatus($staffId)
    {
        $staff = Staff::find($staffId);
        $staff->work_status = !$staff->work_status;
        $staff->save();

        return true;
    }

    /**
     * @notes 技能列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/11 下午4:35
     */
    public static function skillLists()
    {
        $lists = (new StaffSkill())->field('id,name')
            ->where(['status'=>1])
            ->order('id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 申请入驻
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/10/12 下午2:41
     */
    public function apply($params)
    {
        Db::startTrans();
        try {
            //创建申请记录
            StaffApply::create([
                'staff_id' => $params['staff_id'],
                'skill_id' => $params['skill_id'],
                'goods_id' => $params['goods_id'],
                'name' => $params['name'],
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
            ]);

            // 入驻申请通知平台
            $mobile = ConfigService::get('website', 'web_contact_mobile');
            if (!empty($mobile)) {
                event('Notice', [
                    'scene_id' =>  NoticeEnum::STAFF_APPLY_NOTICE_PLATFORM,
                    'params' => [
                        'mobile' => $mobile,
                        'staff_id' => $params['staff_id']
                    ]
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 申请详情
     * @param $staff_id
     * @return array
     * @author ljj
     * @date 2024/11/11 下午3:44
     */
    public function applyDetail($staff_id)
    {
        $staff = Staff::where(['id'=>$staff_id])
            ->append(['skill_name','education_name','nation_name'])
            ->findOrEmpty()
            ->toArray();

        $apply = StaffApply::withoutField(['id','staff_id','apply_status','remarks','create_time','update_time','delete_time'])
            ->where(['staff_id'=>$staff_id])
            ->append(['skill_name','education_name','nation_name'])
            ->order(['id'=>'desc'])
            ->findOrEmpty()
            ->toArray();
        foreach ($apply as $key=>$item) {
            if ($item) {
                $staff[$key] = $item;
            }
        }

        return $staff;
    }

    /**
     * @notes 服务时间
     * @param $staffId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/14 下午12:11
     */
    public function serviceTime($staffId)
    {
        //获取可提前预约天数
        $advanceReservationTime = ConfigService::get('transaction', 'advance_reservation_time',7);
        //处理天数日期数据
        $day = [];
        $weekArray = array("日", "一", "二", "三", "四", "五", "六");
        for ($i = 0;$i < $advanceReservationTime;$i++) {
            if ($i == 0) {
                $desc = '今天';
            } elseif ($i == 1) {
                $desc = '明天';
            } elseif ($i == 2) {
                $desc = '后天';
            } else {
                $desc = '周'.$weekArray[date("w",strtotime("+$i day"))];
            }
            $day[] = [
                'date' => date('m-d',strtotime("+$i day")),
                'desc' => $desc,
            ];
        }

        $todayTime = strtotime(date("Y-m-d"));//今天零时时间戳
        $lastdayTime = strtotime(date("Y-m-d 23:59:59",strtotime("+".($advanceReservationTime - 1)." day")));//后天23:59:59时间戳

        //获取师傅已接未完成订单
        $staffOrder = Order::field('appoint_time_start,appoint_time_end')
            ->where(['staff_id'=>$staffId,'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
            ->whereRaw('appoint_time_start between '.$todayTime.' and '.$lastdayTime.' or appoint_time_end between '.$todayTime.' and '.$lastdayTime.' or appoint_time_start <= '.$todayTime.' and appoint_time_end >= '.$lastdayTime)
            ->select()
            ->toArray();
        //计算订单预约时间点
        $orderAppointTime = [];
        foreach ($staffOrder as $order) {
            //订单预约时间拆分为30分时间段
            for ($i = $order['appoint_time_start']; $i < $order['appoint_time_end']; $i += (30 * 60)) {
                $timeH = (int)date("H",$i);
                $timeI = (int)date("i",$i);
                if ($timeI < 30) {
                    $timeI = '00';
                } else {
                    $timeI = '30';
                }
                $timeH = str_pad($timeH,2,'0',STR_PAD_LEFT);
                $time = $timeH.':'.$timeI;

                $orderAppointTime[strtotime(date("Y-m-d",$i))][] = $time;
            }
        }

        //获取师傅忙时
        $staffBusyTime = StaffBusytime::field('date,time')
            ->where(['staff_id'=>$staffId])
            ->whereTime('date', 'between', [$todayTime, $lastdayTime])
            ->json(['time'],true)
            ->select()
            ->toArray();
        $staffBusyTimeArr = [];
        foreach ($staffBusyTime as $item) {
            $staffBusyTimeArr[strtotime(date("Y-m-d",$item['date']))] = $item['time'];
        }

        $serviceTime = [];
        foreach ($day as $item) {
            //默认半小时间隔
            for ($i = strtotime('00:00'); $i <= strtotime('23:30'); $i += (30 * 60)) {
                //今天小于未来一小时时间点不显示
                if ($item['desc'] === '今天' && $i < time() + (60 * 60)) {
                    continue;
                }

                $status = 0;//时间点状态：0-可预约，1-已预约，2-不可预约
                $dayTime = strtotime(date('Y')."-".$item['date']);
                //判断时间点状态
                if (isset($staffBusyTimeArr[$dayTime]) && in_array(date("H:i",$i),$staffBusyTimeArr[$dayTime])) {
                    $status = 2;
                }
                if (isset($orderAppointTime[$dayTime]) && in_array(date("H:i",$i),$orderAppointTime[$dayTime])) {
                    $status = 1;
                }

                $serviceTime[$item['date']][] = [
                    'time' => date("H:i",$i),
                    'status' => $status
                ];
            }
        }

        //日期数据重新排序排序
        $daySort = array_column($day,'date');
        array_multisort($daySort,SORT_ASC,$day);

        return [
            'day' => $day,
            'service_time' => $serviceTime,
        ];
    }

    /**
     * @notes 设置忙时
     * @param $params
     * @author ljj
     * @date 2024/10/14 下午3:02
     */
    public function setBusytime($params)
    {
        $dayTime = strtotime(date('Y')."-".$params['day']);
        if (empty($params['busytime'])) {
            StaffBusytime::where(['date'=>$dayTime])->delete();
        } else {
            $staffBusytime = StaffBusytime::where(['date'=>$dayTime])->findOrEmpty();
            if ($staffBusytime->isEmpty()) {
                StaffBusytime::create([
                    'staff_id' => $params['staff_id'],
                    'date' => $dayTime,
                    'time' => json_encode($params['busytime']),
                ]);
            } else {
                $staffBusytime->time = json_encode($params['busytime']);
                $staffBusytime->save();
            }
        }

        return true;
    }

    /**
     * @notes 获取完善资料信息
     * @param $staff_id
     * @return array
     * @author ljj
     * @date 2024/10/15 上午11:52
     */
    public static function getImproveInfo($staff_id)
    {
        $StaffImproveInfo = StaffImproveInfo::where(['staff_id'=>$staff_id])
            ->order(['id'=>'desc'])
            ->append(['skill_name','education_name','nation_name','province','city','district'])
            ->findOrEmpty()
            ->toArray();

        if (empty($StaffImproveInfo) || $StaffImproveInfo['verify_status'] == StaffEnum::VERIFY_STATUS_SUCCESS) {
            $Staff = Staff::where(['id'=>$staff_id])
                ->append(['skill_name','education_name','nation_name','province','city','district'])
                ->findOrEmpty()
                ->toArray();

            $Staff['verify_status'] = empty($StaffImproveInfo) ? StaffEnum::VERIFY_STATUS_SUCCESS : $StaffImproveInfo['verify_status'];
        }

        return empty($StaffImproveInfo) ? $Staff : $StaffImproveInfo;
    }

    /**
     * @notes 校验完善资料
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/10/15 下午2:08
     */
    public function setImproveInfo($params)
    {
        StaffImproveInfo::create([
            'staff_id' => $params['staff_id'],
            'skill_id' => $params['skill_id'],
            'goods_id' => $params['goods_id'],
            'education' => $params['education'] ?? null,
            'nation' => $params['nation'] ?? null,
//            'work_image' => $params['work_image'],
            'credentials_image' => $params['credentials_image'],
            'province_id' => $params['province_id'],
            'city_id' => $params['city_id'],
            'district_id' => $params['district_id'],
            'address' => $params['address'] ?? '',
            'longitude' => $params['longitude'] ?? 0,
            'latitude' => $params['latitude'] ?? 0,
        ]);

        return true;
    }

    /**
     * @notes 获取提现账户
     * @param $staffId
     * @return array
     * @author ljj
     * @date 2024/10/16 下午5:00
     */
    public function getWithdrawAccount($staffId)
    {
        $result = StaffWithdrawAccount::where(['staff_id'=>$staffId])->findOrEmpty()->toArray();

        return $result;
    }

    /**
     * @notes 设置提现账户
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/10/16 下午5:08
     */
    public function setWithdrawAccount($params)
    {
        $staffWithdrawAccount = StaffWithdrawAccount::where(['staff_id'=>$params['staff_id']])->findOrEmpty();
        if (!$staffWithdrawAccount->isEmpty()) {
            $staffWithdrawAccount->save([
                'wechat_name' => $params['wechat_name'] ?? null,
                'wechat_mobile' => $params['wechat_mobile'] ?? null,
                'alipay_name' => $params['alipay_name'] ?? null,
                'alipay_account' => $params['alipay_account'] ?? null,
                'bank_holder_name' => $params['bank_holder_name'] ?? null,
                'bank_opening' => $params['bank_opening'] ?? null,
                'bank_number' => $params['bank_number'] ?? null,
            ]);
        } else {
            StaffWithdrawAccount::create([
                'staff_id' => $params['staff_id'],
                'wechat_name' => $params['wechat_name'] ?? null,
                'wechat_mobile' => $params['wechat_mobile'] ?? null,
                'alipay_name' => $params['alipay_name'] ?? null,
                'alipay_account' => $params['alipay_account'] ?? null,
                'bank_holder_name' => $params['bank_holder_name'] ?? null,
                'bank_opening' => $params['bank_opening'] ?? null,
                'bank_number' => $params['bank_number'] ?? null,
            ]);
        }

        return true;
    }
}