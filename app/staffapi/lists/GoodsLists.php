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


use app\common\enum\GoodsEnum;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsCategory;

class GoodsLists extends BaseStaffDataLists
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
        if (isset($this->params['category_id']) && $this->params['category_id'] != '') {
            //默认一级分类
            $categoryIds = GoodsCategory::where(['pid'=>$this->params['category_id']])
                ->column('id');
            Array_push($categoryIds,$this->params['category_id']);
            $goodsIds = Goods::where(['category_id' => $categoryIds])->column('id');
            $where[] = ['id', 'in', $goodsIds];
        }

        return $where;
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
        $lists = Goods::field('id,skill_id,name,image,min_price as price,label,sale_num + virtual_sale_num as sale_num')
            ->where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id'=>'desc'])
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
        return Goods::where($this->where())->count();
    }
}