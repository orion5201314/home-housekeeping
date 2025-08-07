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
use app\common\model\staff\Staff;

class StaffGoodsLists extends BaseShopDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/10/24 下午5:18
     */
    public function where(): array
    {
        $where[] = ['status','=',GoodsEnum::SHELVE];
        $staff = Staff::where(['id'=>$this->params['staff_id']])->field('goods_id')->findOrEmpty()->toArray();
        $where[] = ['id','in',$staff['goods_id'] ?? []];

        return $where;
    }

    /**
     * @notes 服务列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/24 下午5:19
     */
    public function lists(): array
    {
        $lists = Goods::field('id,name,image,min_price as price,label,sale_num + virtual_sale_num as sale_num')
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
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/10/24 下午5:20
     */
    public function count(): int
    {
        return Goods::where($this->where())->count();
    }
}