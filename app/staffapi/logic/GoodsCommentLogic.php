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


use app\common\enum\OrderEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\GoodsComment;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;

class GoodsCommentLogic extends BaseLogic
{
    /**
     * @notes 服务评价分类
     * @param $staffId
     * @return array
     * @author ljj
     * @date 2024/10/15 下午4:13
     */
    public function commentCategory($staffId)
    {
        $orderIds = Order::where(['staff_id'=>$staffId,'order_status'=>OrderEnum::ORDER_STATUS_FINISH])->column('id');
        $orderGoodsIds = OrderGoods::where(['order_id'=>$orderIds,'is_comment'=>1])->column('id');
        $where[] = ['order_goods_id','in',$orderGoodsIds];

        return [
            [
                'type'    => 0,
                'name'  => '全部',
                'count' => GoodsComment::where($where)->count(),
            ],
            [
                'type'    => 1,
                'name'  => '有图',
                'count' => GoodsComment::alias('gc')
                    ->join('goods_comment_image gci', 'gc.id = gci.comment_id')
                    ->where($where)
                    ->group('gc.id')
                    ->count(),
            ],
            [
                'type'    => 2,
                'name'  => '好评',
                'count' => GoodsComment::where($where)
                    ->where('service_comment','>',3)
                    ->count(),
            ],
            [
                'type'    => 3,
                'name'  => '中差评',
                'count' => GoodsComment::where($where)
                    ->where('service_comment','<=',3)
                    ->count(),
            ]
        ];
    }
}