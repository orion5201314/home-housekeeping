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

namespace app\adminapi\logic\goods;


use app\common\enum\DefaultEnum;
use app\common\enum\GoodsEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsImage;
use app\common\model\goods\GoodsSku;
use app\common\model\goods\GoodsSkuName;
use app\common\model\goods\GoodsSkuValue;
use app\common\model\staff\Staff;
use think\facade\Db;

class GoodsLogic extends BaseLogic
{
    /**
     * @notes 添加服务
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/2/9 3:28 下午
     */
    public function add($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $goods = $this->setGoodsBase($params);
            $this->setGoodsSku($goods,$params);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 查看服务详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/2/9 3:51 下午
     */
    public function detail($id)
    {
        $detail = Goods::with(['goods_image','sku','sku_name_list'])
            ->where(['id'=>$id])
            ->json(['open_city_id'],true)
            ->findOrEmpty()
            ->toArray();

        if (!empty($detail)) {
            //轮播图处理
            $detail['goods_image'] = array_column($detail['goods_image'], 'uri');
            //处理多规格信息
            if ($detail['sku_type']) {
                foreach ($detail['sku_name_list'] as $key => $item) {
                    $detail['sku_name_list'][$key]['value'] = GoodsSkuValue::where(['goods_id'=>$id,'sku_name_id'=>$item['id']])->column('id,value');
                }
            }
        }

        return $detail;
    }

    /**
     * @notes 编辑服务
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/2/9 4:06 下午
     */
    public function edit($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $goods = $this->setGoodsBase($params);
            $this->setGoodsSku($goods,$params);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除服务
     * @param $ids
     * @return bool|string
     * @author ljj
     * @date 2022/2/9 4:13 下午
     */
    public function del($ids)
    {
        Goods::destroy($ids);
        return true;
    }

    /**
     * @notes 修改服务状态
     * @param $params
     * @return Goods
     * @author ljj
     * @date 2022/2/9 4:54 下午
     */
    public function status($params)
    {
        return Goods::update(['status'=>$params['status']],['id'=>$params['ids']]);
    }


    /**
     * @notes 设置服务基础信息
     * @param $params
     * @return Goods|array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/8/28 下午3:30
     */
    public function setGoodsBase($params){

        $goods = new Goods();
        //售价数组
        $specPriceArray = array_column($params['sku'],'price');
        //划线价数组
        $specLineationPriceArray = array_column($params['sku'],'line_price');

        //编辑操作
        if(!empty($params['id'])){
            $goods = $goods::find($params['id']);
            //删除轮播图
            GoodsImage::where(['goods_id'=>$goods->id])->delete();
            //保存改变前的规格类型
            $goods->oldSkuType = $goods->sku_type;
        }

        //更新基础信息
        $goods->category_id = $params['category_id'];
        $goods->skill_id = implode(',',$params['skill_id']);
        $goods->open_city_id = empty($params['open_city_id']) ? [] : json_encode($params['open_city_id']);
//        $goods->type = $params['type'];
        $goods->name = $params['name'];
        $goods->remarks = $params['remarks'];
        $goods->label = $params['label'];
        $goods->image = $params['goods_image'][0];
        $goods->status = $params['status'];
        $goods->sort = $params['sort'] ?? DefaultEnum::SORT;
        $goods->content = $params['content'];
        $goods->sku_type = $params['sku_type'];
        $goods->min_price = min($specPriceArray);
        $goods->max_price = max($specPriceArray);
        $goods->min_line_price = min($specLineationPriceArray);
        $goods->max_line_price = max($specLineationPriceArray);
        $goods->virtual_sale_num = $params['virtual_sale_num'];
        $goods->appoint_start_time = $params['appoint_start_time'];
        $goods->appoint_end_time = $params['appoint_end_time'];
        $goods->earnings_ratio = $params['earnings_ratio'];
        $goods->save();

        //添加轮播图
        $goodsImage = $params['goods_image'];
        if ($goodsImage) {
            array_walk($goodsImage, function (&$image) use ($goods) {
                $image = ['uri' => $image,'goods_id'=>$goods->id];
            });
            (new GoodsImage())->saveAll($goodsImage);
        }

        return $goods;
    }

    /**
     * @notes 设置服务SKU
     * @param $goods
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/8/29 下午5:45
     */
    public function setGoodsSku($goods,$params)
    {
        if (GoodsEnum::SKU_TYPE_SINGLE == $params['sku_type']) {
            //单规格
            $sku = $params['sku'][0];

            $goodsSku = new GoodsSku();
            //编辑操作
            if(!empty($sku['id'])){
                $goodsSku = $goodsSku::find($sku['id']);
                if(GoodsEnum::SKU_TYPE_MULTIPLE == $goods['oldSkuType']){
                    //原先是多规格，删除多规格数据
                    GoodsSku::where(['goods_id'=>$goods->id])->delete();
                    GoodsSkuName::where(['goods_id'=>$goods->id])->delete();
                    GoodsSkuValue::where(['goods_id'=>$goods->id])->delete();
                }
            }
            $goodsSku->goods_id = $goods->id;
            $goodsSku->spec_value_ids = [];
            $goodsSku->sku_value_arr = ['默认'];
            $goodsSku->price = $sku['price'];
            $goodsSku->line_price = $sku['line_price'];
            $goodsSku->duration = $sku['duration'];
            $goodsSku->save();
        } else {
            //多规格

            //编辑操作
            if(!empty($params['id'])){
                //删除旧数据
                GoodsSku::where(['goods_id'=>$goods->id])->delete();
                GoodsSkuName::where(['goods_id'=>$goods->id])->delete();
                GoodsSkuValue::where(['goods_id'=>$goods->id])->delete();
            }

            foreach ($params['sku_name_list'] as &$sku_name) {
                $GoodsSkuName = GoodsSkuName::create([
                    'goods_id'  => $goods->id,
                    'name'      => $sku_name['name'],
                ]);
                $sku_name['id'] = $GoodsSkuName->id;
                foreach ($sku_name['value'] as &$sku_value) {
                    $GoodsSkuValue = GoodsSkuValue::create([
                        'goods_id'      => $goods->id,
                        'sku_name_id'   => $GoodsSkuName->id,
                        'value'         => $sku_value['value'],
                    ]);
                    $sku_value['id'] = $GoodsSkuValue->id;
                }
            }
            unset($sku_name);

            $skuData = [];//sku数据
            foreach ($params['sku'] as $sku) {
                //计算规格ids
                $skuValueIds = [];
                $skuValueArr = $sku['sku_value_arr'];
                $skuValueArr = array_map(function($value) {
                    return md5($value);
                }, $skuValueArr);
                foreach ($params['sku_name_list'] as $sku_name) {
                    foreach ($sku_name['value'] as $value) {
                        if (in_array(md5($value['value']), $skuValueArr)) {
                            $skuValueIds[] = $value['id'];
                        }
                    }
                }

                $skuData[] = [
                    'goods_id' => $goods->id,
                    "sku_value_ids" => $skuValueIds,
                    "sku_value_arr" => $sku['sku_value_arr'],
                    "price" => $sku['price'],
                    "line_price" => $sku['line_price'] ?? 0,
                    "duration" => $sku['duration'],
                ];

            }
            (new GoodsSku())->saveAll($skuData);
        }
    }
}