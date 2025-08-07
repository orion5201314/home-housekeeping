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


use app\common\enum\OrderEnum;
use app\common\model\goods\GoodsComment;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;
use app\common\service\ConfigService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class OrderComment extends Command
{

    protected function configure()
    {
        $this->setName('order_comment')
            ->setDescription('订单自动好评');
    }

    protected function execute(Input $input, Output $output)
    {
        //当前时间
        $currentTime = time();
        //自动好评时间（单位：天）
        $autoHighopinionTimes = ConfigService::get('transaction', 'auto_highopinion_times',1) * 86400;
        //自动好评内容
        $autoHighopinionContent = ConfigService::get('transaction', 'auto_highopinion_content','此用户未填写评价内容');

        $orders = Order::alias('o')
            ->join('order_goods og', 'o.id = og.order_id')
            ->where(['o.order_status'=>OrderEnum::ORDER_STATUS_FINISH,'og.is_comment'=>0])
            ->whereRaw("o.finish_time+$autoHighopinionTimes < $currentTime")
            ->field('o.id,og.id as order_goods_id,og.goods_id,o.user_id')
            ->select()
            ->toArray();

        if (empty($orders)) {
            return true;
        }

        Db::startTrans();
        try{
            foreach ($orders as $order) {
                //更新订单商品状态
                OrderGoods::update(['is_comment' => 1], ['id' => $order['order_goods_id']]);

                //添加评价数据
                GoodsComment::create([
                    'goods_id' => $order['goods_id'],
                    'user_id' => $order['user_id'],
                    'order_goods_id' => $order['order_goods_id'],
                    'service_comment' => 5,
                    'comment' => $autoHighopinionContent,
                ]);
            }

            Db::commit();
        } catch(\Exception $e) {
            Db::rollback();
            Log::write('订单自动好评失败,失败原因:' . $e->getMessage());
        }
    }

}