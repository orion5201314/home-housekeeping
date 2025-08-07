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

namespace app\adminapi\logic\setting;


use app\common\enum\DefaultEnum;
use app\common\enum\MapKeyEnum;
use app\common\logic\BaseLogic;
use app\common\model\MapKey;
use think\facade\Cache;

class MapKeyLogic extends BaseLogic
{
    /**
     * @notes 公共列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/11/5 下午1:56
     */
    public function commonLists()
    {
        $lists = (new MapKey())->order(['id'=>'desc'])->json(['error_info'],true)->select()->toArray();

        return $lists;
    }

    /**
     * @notes 新增key
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/11/5 下午2:05
     */
    public function add($params)
    {
        MapKey::create([
            'key' => $params['key'],
            'type' => $params['type']
        ]);

        return true;
    }

    /**
     * @notes 详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/11/5 下午2:07
     */
    public function detail($id)
    {
        $result = MapKey::where('id',$id)->json(['error_info'],true)->findOrEmpty()->toArray();

        return $result;
    }

    /**
     * @notes 编辑
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/11/5 下午2:20
     */
    public function edit($params)
    {
        $mapKey = MapKey::findOrEmpty($params['id']);
        $mapKey->key = $params['key'];
        $mapKey->type = $params['type'];
        $mapKey->status = $mapKey->status == MapKeyEnum::STATUS_ABNORMAL ? MapKeyEnum::STATUS_WAIT : $mapKey->status;
        $mapKey->save();

        //删除缓存
        Cache::delete('TENCENT_MAP_KEY');

        return true;
    }

    /**
     * @notes 删除
     * @param $id
     * @return bool
     * @author ljj
     * @date 2024/11/5 下午2:21
     */
    public function del($id)
    {
        return MapKey::destroy($id);
    }
}