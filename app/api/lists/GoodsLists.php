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

namespace app\api\lists;


use app\common\enum\GoodsEnum;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsCategory;

class GoodsLists extends BaseShopDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2022/2/17 5:18 下午
     */
    public function where(): array
    {
        $where[] = ['status','=',GoodsEnum::SHELVE];
        if (isset($this->params['keyword']) && $this->params['keyword'] != '') {
            $where[] = ['name','like', '%'.$this->params['keyword'].'%'];
        }
        if (isset($this->params['category_id']) && $this->params['category_id'] != '') {
            $goodsCategory = GoodsCategory::find($this->params['category_id']);
            $level = $goodsCategory['level'] ?? '';
            $categoryIds = [];
            switch ($level){
                case 1:
                    $categoryIds = GoodsCategory::where(['pid'=>$this->params['category_id']])
                        ->column('id');
                    Array_push($categoryIds,$this->params['category_id']);
                    break;
                case 2:
                    $categoryIds = [$this->params['category_id']];
                    break;
            }
            $goodsIds = Goods::where(['category_id' => $categoryIds])->column('id');
            $where[] = ['id', 'in', $goodsIds];
        }

        return $where;
    }

    /**
     * @notes 排序条件
     * @return string[]
     * @author ljj
     * @date 2024/9/25 下午6:16
     */
    public function sort(): array
    {
        $sort = ['sort'=>'asc','id'=>'desc'];
        if (isset($this->params['sort_type']) && $this->params['sort_type'] != '') {
            $value = empty($this->params['sort_value']) ? 'desc' : $this->params['sort_value'];
            switch ($this->params['sort_type']) {
                case 'price':
                    $sort = ['min_price'=>$value];
                    break;
                case 'sale':
                    $sort = ['sale_num'=>$value];
                    break;
            }
        }

        return $sort;
    }

    /**
     * @notes 服务列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/17 5:17 下午
     */
    public function lists(): array
    {
        $cityId = $this->params['city_id'] ?? 0;
        $lists = Goods::field('id,name,image,min_price as price,label,sale_num + virtual_sale_num as sale_num')
            ->where($this->where())
            ->where(function ($query) use($cityId) {
                if (!empty($cityId)) {
                    $query->whereRaw('open_city_id is null or JSON_CONTAINS(open_city_id, "['.$cityId.']", "$")');
                }
            })
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sort())
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            $list['price'] = trim(rtrim(sprintf("%.4f", $list['price'] ), '0'),'.');
        }

        return $lists;
    }

    /**
     * @notes 服务总数
     * @return int
     * @author ljj
     * @date 2022/2/17 5:17 下午
     */
    public function count(): int
    {
        $cityId = $this->params['city_id'] ?? 0;
        return Goods::where($this->where())
            ->where(function ($query) use($cityId) {
                if (!empty($cityId)) {
                    $query->whereRaw('open_city_id is null or JSON_CONTAINS(open_city_id, "['.$cityId.']", "$")');
                }
            })
            ->count();
    }
}