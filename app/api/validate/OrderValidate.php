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

namespace app\api\validate;


use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\model\goods\GoodsAdditional;
use app\common\model\order\Order;
use app\common\model\staff\StaffBusytime;
use app\common\validate\BaseValidate;

class OrderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'from' => 'require',
        'scene' => 'require',
        'difference_price' => 'require|float|gt:0',
        'additional_info' => 'require|array',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'from.require' => '参数缺失',
        'scene.require' => '参数缺失',
        'difference_price.require' => '请输入差价金额',
        'difference_price.float' => '差价金额值错误',
        'difference_price.gt' => '差价金额必须大于零',
        'additional_info.require' => '请选择需要加的项目',
        'additional_info.array' => '项目数据错误',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneCancel()
    {
        return $this->only(['id'])
            ->append('id','checkCancel');
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function scenePayWay()
    {
        return $this->only(['from','scene']);
    }

    public function sceneDifferencePrice()
    {
        return $this->only(['id','difference_price']);
    }

    public function sceneAdditional()
    {
        return $this->only(['id','additional_info'])
            ->append('additional_info','checkAdditional');
    }


    /**
     * @notes 检验订单id
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/28 10:12 上午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }

    /**
     * @notes 检验订单能否取消
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/28 11:28 上午
     */
    public function checkCancel($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_WAIT_PAY && ($result['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE || $result['order_sub_status'] != OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE)) {
            return '该订单无法取消';
        }
        return true;
    }

    /**
     * @notes 检验订单能否删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/28 11:48 上午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_CLOSE) {
            return '该订单无法删除';
        }
        return true;
    }

    /**
     * @notes 校验项目数据
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/9 上午11:53
     */
    public function checkAdditional($value,$rule,$data)
    {
        //获取当前订单信息
        $currentOrder = Order::where(['id'=>$data['id']])->field('staff_id,order_status,order_sub_status,appoint_time_start,appoint_time_end')->findOrEmpty()->toArray();
        if (
            $currentOrder['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE &&
            !in_array($currentOrder['order_sub_status'],[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]) &&
            $currentOrder['order_status'] != OrderEnum::ORDER_STATUS_SERVICE) {
            return '订单状态错误';
        }

        $additional = $value;
        foreach ($additional as $key=>$item) {
            if (!isset($item['id']) || !isset($item['num'])) {
                return '项目参数缺失';
            }
            if (empty($item['num'])) {
                unset($additional[$key]);
            }
        }
        if (empty($additional)) {
            return '请选择需要加的项目';
        }
        //判断师傅是否有时间接受加项
        $additionalIds = array_column($additional,'id');
        $numArr = array_column($additional,'num','id');
        $additionalInfo = GoodsAdditional::where(['id'=>$additionalIds])->select()->toArray();
        $additionalTime = 0;//加项增加时间/秒
        foreach ($additionalInfo as $key=>$item) {
            if (empty($numArr[$item['id']])) {
                unset($additionalInfo[$key]);
                continue;
            }
            $additionalTime += $item['duration'] * $numArr[$item['id']] * 60;
        }
        if ($additionalTime > 0) {
            //获取下一个订单信息
            $nextOrder = Order::where(['staff_id'=>$currentOrder['staff_id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE]])
                ->where('appoint_time_start','>',$currentOrder['appoint_time_end'])
                ->whereDay('appoint_time_start')
                ->field('appoint_time_start,appoint_time_end')
                ->findOrEmpty()
                ->toArray();
            if (!empty($nextOrder)) {
                //判断两个订单之间时候有足够时间加项
                if (($nextOrder['appoint_time_start'] - $currentOrder['appoint_time_end']) < $additionalTime) {
                    return '师傅服务时间不满足加项时间';
                }
            }

            //获取师傅忙时时间
            $staffBusyTime = StaffBusytime::field('date,time')
                ->where(['staff_id'=>$currentOrder['staff_id']])
                ->whereDay('date', date('Y-m-d',$currentOrder['appoint_time_start']))
                ->json(['time'],true)
                ->findOrEmpty()
                ->toArray();
            if (!empty($staffBusyTime) && !empty($staffBusyTime['time'])) {
                //默认半小时间隔
                for ($i = $currentOrder['appoint_time_end']; $i < $currentOrder['appoint_time_end'] + $additionalTime; $i += (30 * 60)) {
                    //判断是否在师傅忙时时间内
                    if (in_array(date("H:i",$i),$staffBusyTime['time'])) {
                        return '师傅服务时间不满足加项时间';
                    }
                }
            }
        }

        return true;
    }
}