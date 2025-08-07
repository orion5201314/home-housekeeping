<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------

namespace app\common\command;


use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\model\order\Order;
use app\common\service\ConfigService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class OrderAbnormalNotice extends Command
{

    protected function configure()
    {
        $this->setName('order_abnormal_notice')
            ->setDescription('订单异常通知');
    }

    protected function execute(Input $input, Output $output)
    {
        //当前时间
        $currentTime = time();

        $orders = Order::where(['order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE, OrderEnum::ORDER_STATUS_SERVICE]])
            ->whereRaw("appoint_time_end+86400 < $currentTime")
            ->field('id')
            ->select()
            ->toArray();

        if (empty($orders)) {
            return true;
        }

        Db::startTrans();
        try{
            foreach ($orders as $order) {
                // 订单异常通知平台
                $mobile = ConfigService::get('website', 'web_contact_mobile');
                if (!empty($mobile)) {
                    event('Notice', [
                        'scene_id' =>  NoticeEnum::ORDER_ABNORMAL_NOTICE_PLATFORM,
                        'params' => [
                            'mobile' => $mobile,
                            'order_id' => $order['id']
                        ]
                    ]);
                }
            }

            Db::commit();
        } catch(\Exception $e) {
            Db::rollback();
            Log::write('订单异常通知失败,失败原因:' . $e->getMessage());
        }
    }

}