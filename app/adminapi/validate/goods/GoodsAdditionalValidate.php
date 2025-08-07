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

namespace app\adminapi\validate\goods;


use app\common\validate\BaseValidate;

class GoodsAdditionalValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'goods_id' => 'require',
        'name' => 'require',
        'price' => 'require|float|egt:0',
        'duration' => 'require|number',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'goods_id.require' => '参数缺失',
        'name.require' => '请输入附加项目名称',
        'price.require' => '请输入价格',
        'price.float' => '价格值错误',
        'price.egt' => '价格必须大于等于0',
        'duration.require' => '请输入时长',
        'duration.number' => '时长值错误',
        'status.require' => '请选择状态',
        'status.in' => '状态值错误',
    ];

    public function sceneAdd()
    {
        return $this->only(['goods_id','name','price','duration','status']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','price','duration','status']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    public function sceneStatus()
    {
        return $this->only(['id','status']);
    }
}