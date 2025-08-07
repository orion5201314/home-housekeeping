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


use app\common\logic\BaseLogic;
use app\common\model\goods\GoodsAdditional;

class GoodsAdditionalLogic extends BaseLogic
{
    /**
     * @notes 新增
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/8/22 上午11:16
     */
    public function add($params)
    {
        GoodsAdditional::create([
            'goods_id' => $params['goods_id'],
            'name' => $params['name'],
            'price' => $params['price'],
            'duration' => $params['duration'],
            'status' => $params['status'],
        ]);

        return true;
    }

    /**
     * @notes 详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/8/22 上午11:16
     */
    public function detail($id)
    {
        $result = GoodsAdditional::where(['id'=>$id])->findOrEmpty()->toArray();

        return $result;
    }

    /**
     * @notes 编辑
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/8/22 上午11:17
     */
    public function edit($params)
    {
        GoodsAdditional::update([
            'name' => $params['name'],
            'price' => $params['price'],
            'duration' => $params['duration'],
            'status' => $params['status'],
        ],['id'=>$params['id']]);

        return true;
    }

    /**
     * @notes 删除
     * @param $id
     * @return bool
     * @author ljj
     * @date 2024/8/22 上午11:17
     */
    public function del($id)
    {
        return GoodsAdditional::destroy($id);
    }

    /**
     * @notes 状态
     * @param $params
     * @return GoodsAdditional
     * @author ljj
     * @date 2024/8/22 上午11:17
     */
    public function status($params)
    {
        return GoodsAdditional::update(['status'=>$params['status']],['id'=>$params['id']]);
    }
}