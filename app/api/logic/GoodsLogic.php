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

namespace app\api\logic;


use app\common\enum\DefaultEnum;
use app\common\enum\GoodsCommentEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsCollect;
use app\common\model\goods\GoodsComment;
use app\common\model\goods\GoodsImage;
use app\common\model\goods\GoodsSkuValue;
use app\common\model\order\OrderTime;
use app\common\model\staff\Staff;
use app\common\service\ConfigService;

class GoodsLogic extends BaseLogic
{
    /**
     * @notes 服务详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/18 10:40 上午
     */
    public function detail($params)
    {
        //商品信息
        $goods = Goods::field('id,name,label,content,sku_type,min_price as price,min_line_price as line_price,sale_num + virtual_sale_num as sale_num,image')
            ->with(['sku','sku_name_list'])
            ->where(['id'=>$params['id']])
            ->findOrEmpty()
            ->toArray();

        //商品评价
        $commentTotalCount = GoodsComment::where(['goods_id'=>$params['id']])->count();
        $commentGoodCount = GoodsComment::where([['goods_id','=',$params['id']],['service_comment','>',3]])->count();
        $goods['goods_comment'] = [
            'lists' => GoodsComment::where(['goods_id'=>$params['id']])->append(['goods_comment_image','user'])->limit(2)->select()->toArray(),
            'num' => $commentTotalCount,
            'rate' => $commentTotalCount > 0 ? round(($commentGoodCount/$commentTotalCount)*100).'%' : '100%',
        ];

        //商品轮播图
        $goods['goods_image']['data'] = GoodsImage::field('id,goods_id,uri as image')->where(['goods_id'=>$params['id']])->select()->toArray();

        //是否收藏
        $collect = GoodsCollect::where(['user_id'=>$params['user_id'],'goods_id'=>$params['id']])->findOrEmpty();
        $goods['is_collect'] = !$collect->isEmpty() ? 1 : 0;

        //商品规格
        //处理多规格信息
        if ($goods['sku_type']) {
            foreach ($goods['sku_name_list'] as $key => $item) {
                $goods['sku_name_list'][$key]['value'] = GoodsSkuValue::where(['goods_id'=>$params['id'],'sku_name_id'=>$item['id']])->column('id,value');
            }
        } else {
            //处理单规格信息
            $goods['sku'][0]['sku_value_ids'][] = 1;
            $goods['sku_name_list'][] = [
                'id' => 0,
                'name' => '规格',
                'value' => [
                    [
                        'id' => 1,
                        'value' => '默认'
                    ]
                ]
            ];
        }

        return $goods;
    }

    /**
     * @notes 预约上门时间
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/11 2:32 下午
     */
    public function appointTime($params)
    {
        //计算服务时间
        $goods = Goods::alias('g')
            ->join('goods_sku gs','g.id = gs.goods_id')
            ->field('g.appoint_start_time,g.appoint_end_time,gs.duration')
            ->where(['gs.id'=>$params['sku_id']])
            ->findOrEmpty()
            ->toArray();
//        $duration = (int)($goods['duration'] * 60);
        $appointTime = [];
        //默认时间间隔为30分钟
        $duration = 30 * 60;
        for ($i = strtotime($goods['appoint_start_time'].':00'); $i <= strtotime($goods['appoint_end_time'].':00'); $i += $duration) {
            //剩余时间不足服务时间
            if (($i + $duration) > strtotime($goods['appoint_end_time'].':00')) {
                continue;
            }

            $appointTime[] = date("H:i",$i);
        }

        return [
            'advance_reservation_time' => ConfigService::get('transaction', 'advance_reservation_time',7),
            'appoint_time' => $appointTime,
        ];
    }

    /**
     * @notes 收藏服务
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/3/16 4:14 下午
     */
    public function collect($params)
    {
        if($params['is_collect']){
            $goods_collect = GoodsCollect::where(['goods_id'=>$params['id'],'user_id'=>$params['user_id']])->findOrEmpty();
            if(!$goods_collect->isEmpty()){
                return true;
            }

            $goods_collect->goods_id = $params['id'];
            $goods_collect->user_id  = $params['user_id'];
            $goods_collect->save();
        }else {
            GoodsCollect::where(['goods_id'=>$params['id'],'user_id'=>$params['user_id']])->delete();
        }

        return true;
    }
}