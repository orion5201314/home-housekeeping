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

namespace app\adminapi\validate\setting;


use app\common\model\OpenCity;
use app\common\validate\BaseValidate;

class OpenCityValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'province_id' => 'require',
        'city_id' => 'require|checkCityId',
        'sort' => 'number|max:5',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'province_id.require' => '请选择省',
        'city_id.require' => '请选择市',
        'sort.number' => '排序值错误',
        'sort.max' => '排序值过大',
    ];

    public function sceneAdd()
    {
        return $this->only(['province_id','city_id','sort']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','province_id','city_id','sort']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验开通城市
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/22 下午4:16
     */
    public function checkCityId($value,$rule,$data)
    {
        $where[] = ['city_id','=',$value];
        if (isset($data['id'])) {
            $where[] = ['id','<>',$data['id']];
        }
        $result = OpenCity::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '该城市已开通';
        }
        return true;
    }
}