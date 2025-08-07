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

namespace app\staffapi\lists;


use app\common\enum\OrderEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\goods\GoodsComment;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;

class GoodsCommentLists extends BaseStaffDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/10/15 下午3:46
     */
    public function where()
    {
        $orderIds = Order::where(['staff_id'=>$this->staffId,'order_status'=>OrderEnum::ORDER_STATUS_FINISH])->column('id');
        $orderGoodsIds = OrderGoods::where(['order_id'=>$orderIds,'is_comment'=>1])->column('id');
        $where[] = ['gc.order_goods_id','in',$orderGoodsIds];
        switch ($this->params['type'] ?? 0){
            case 1://有图
                $where[]= ['gci.uri','not null',''];
                break;
            case 2://好评
                $where[]= ['gc.service_comment','>',3];
                break;
            case 3://中差评
                $where[]= ['gc.service_comment','<=',3];
                break;
            default:
                break;
        }
        return $where;
    }

    /**
     * @notes 评价列表
     * @return array
     * @author ljj
     * @date 2024/10/15 下午3:47
     */
    public function lists(): array
    {
        $lists = GoodsComment::alias('gc')
            ->leftjoin('goods_comment_image gci', 'gc.id = gci.comment_id')
            ->with(['goods_comment_image','user'])
            ->field('gc.id,gc.goods_id,gc.order_goods_id,gc.user_id,gc.service_comment,gc.comment,gc.reply,gc.create_time')
            ->append(['comment_level','goods_sku_desc'])
            ->where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('gc.id','desc')
            ->group('gc.id')
            ->select()
            ->toArray();


        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/10/15 下午3:54
     */
    public function count(): int
    {
        return GoodsComment::alias('gc')
            ->leftjoin('goods_comment_image gci', 'gc.id = gci.comment_id')
            ->group('gc.id')
            ->where($this->where())
            ->count();
    }
}