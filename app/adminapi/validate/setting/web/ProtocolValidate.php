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

namespace app\adminapi\validate\setting\web;


use app\common\model\Protocol;
use app\common\validate\BaseValidate;

class ProtocolValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'title.require' => '请输入标题',
        'content.require' => '请输入内容',
    ];

    public function sceneAdd()
    {
        return $this->only(['title','content']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','title','content']);
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    /**
     * @notes 校验删除
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/19 上午11:46
     */
    public function checkDel($value,$rule,$data)
    {
        $result = Protocol::where(['id'=>$value])->findOrEmpty()->toArray();
        if($result['is_system']){
            return '系统内置数据，无法删除';
        }
        return true;
    }
}