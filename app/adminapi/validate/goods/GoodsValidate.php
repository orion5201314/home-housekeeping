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


use app\common\enum\GoodsEnum;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsCategory;
use app\common\model\goods\GoodsSku;
use app\common\model\OpenCity;
use app\common\model\staff\StaffSkill;
use app\common\validate\BaseValidate;
use think\facade\Validate;

class GoodsValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'ids' => 'require|array',
        'category_id' => 'require|checkCategory',
        'skill_id' => 'require|array|checkSkill',
        'open_city_id' => 'checkOpenCity',
        'type' => 'require|in:1',
        'name' => 'require|max:64',
        'goods_image' => 'require|array|max:10',
        'status' => 'require|in:0,1',
        'sort' => 'max:5',
        'sku_type' => 'require|in:'.GoodsEnum::SKU_TYPE_SINGLE.','.GoodsEnum::SKU_TYPE_MULTIPLE,
        'sku_name_list' => 'requireIf:sku_type,'.GoodsEnum::SKU_TYPE_MULTIPLE.'|array',
        'sku' => 'require|array|checkSkuList',
        'appoint_start_time' => 'require|number|egt:0',
        'appoint_end_time' => 'require|number|elt:24',
        'earnings_ratio' => 'require|float|egt:0',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'category_id.require' => '请选择服务分类',
        'skill_id.require' => '请选择服务技能',
        'skill_id.array' => '服务技能错误',
        'type.require' => '请选择服务类型',
        'type.in' => '服务类型值错误',
        'name.require' => '请输入服务名称',
        'name.max' => '服务名称已超过限制字数',
        'goods_image.require' => '请上传轮播图',
        'goods_image.array' => '轮播图格式不正确',
        'goods_image.max' => '轮播图数量不能大于10张',
        'status.require' => '请选择服务状态',
        'status.in' => '服务状态值错误',
        'sort.max' => '排序值过大',
        'sku_type.require' => '请选择规格类型',
        'sku_type.in' => '规格类型值错误',
        'sku_name_list.requireIf' => '请输入规格项',
        'sku_name_list.array' => '规格项错误',
        'sku.require' => '服务规格数据缺失',
        'sku.array' => '服务规格数据错误',
        'appoint_start_time.require' => '请输入预约开始时间',
        'appoint_start_time.number' => '预约开始时间值错误',
        'appoint_start_time.egt' => '预约开始时间必须大于等于0',
        'appoint_end_time.require' => '请输入预约结束时间',
        'appoint_end_time.number' => '预约结束时间值错误',
        'appoint_end_time.egt' => '预约开始时间必须小于等于24',
        'earnings_ratio.require' => '请输入服务佣金',
        'earnings_ratio.float' => '服务佣金值错误',
        'earnings_ratio.egt' => '服务佣金必须大于等于0',
        'ids.require' => '请选择服务',
        'ids.array' => '参数格式错误',
    ];

    public function sceneAdd()
    {
        return $this->only(['category_id','skill_id','open_city_id','type','name','goods_image','status','sort','sku_type','sku_name_list','sku','appoint_start_time','appoint_end_time','earnings_ratio']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','category_id','skill_id','open_city_id','type','name','goods_image','status','sort','sku_type','sku_name_list','sku','appoint_start_time','appoint_end_time','earnings_ratio']);
    }

    public function sceneDel()
    {
        return $this->only(['ids']);
    }

    public function sceneStatus()
    {
        return $this->only(['ids','status']);
    }


    /**
     * @notes 检验服务ID
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/9 12:02 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Goods::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '服务不存在';
        }

        return true;
    }

    /**
     * @notes 检验服务分类id
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/9 12:06 下午
     */
    public function checkCategory($value,$rule,$data)
    {
        $result = GoodsCategory::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '服务分类不存在，请刷新重选';
        }
        return true;
    }

    /**
     * @notes 校验服务技能
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/27 下午2:28
     */
    public function checkSkill($value,$rule,$data)
    {
        foreach ($data['skill_id'] as $skill_id) {
            $result = StaffSkill::where(['id'=>$skill_id])->findOrEmpty();
            if ($result->isEmpty()) {
                return '服务技能不存在，请刷新重选';
            }
        }

        return true;
    }

    /**
     * @notes 校验开通城市
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/27 下午2:32
     */
    public function checkOpenCity($value,$rule,$data)
    {
        if (empty($data['open_city_id'])) {
            return true;
        }
        foreach ($data['open_city_id'] as $open_city_id) {
            $result = OpenCity::where(['city_id'=>$data['open_city_id']])->findOrEmpty();
            if ($result->isEmpty()) {
                return '部分城市暂未开通，请重新选择';
            }
        }

        return true;
    }

    /**
     * @notes 校验规格数据
     * @param $skuList
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/27 下午3:02
     */
    function checkSkuList($skuList, $rule, $data)
    {
        // 单规格
        if ($data['sku_type'] == GoodsEnum::SKU_TYPE_SINGLE) {

            $validate = Validate::rule([
                'price|服务价格' => 'require|float|egt:0',
                'line_price|划线价格' => 'float|egt:0',
                'duration|服务时长' => 'require|number',
            ]);
            if (!$validate->check($skuList[0])) {
                return $validate->getError();
            }
            // 修改
            if (!empty($sku['id'])) {
                if (!(new GoodsSku)->where(['id'=>$sku['id'],'goods_id'=>$data['id']])->value('id')) {
                    return '规格列表错误001';
                }
            }

            return true;
        }

        // 多规格
        $skuNameList        = array_values($data['sku_name_list']);
        $skuNameArr         = [];
        $skuNameValueArr    = [];
        $skuMd5List         = [];

        if (count($skuNameList) > 3) {
            return "规格项不能超过3个";
        }

        foreach (array_values($skuNameList) as $key => $skuNameInfo) {

            $validate = Validate::rule([
                'name|规格项名称' => 'require|max:30',
                'value|规格值' => 'require|array',
            ]);
            if (!$validate->check($skuNameInfo)) {
                return $validate->getError();
            }
            if (in_array($skuNameInfo['name'], $skuNameArr)) {
                return "规格项名称 {$skuNameInfo['name']} 重复";
            }

            $skuNameArr[] = $skuNameInfo['name'];
            $tempMd5Arr = [];

            foreach (array_values($skuNameInfo['value']) as $ko => $value) {
                if (!Validate::must($value['value'])) {
                    return "规格值不能为空";
                }
                if (mb_strlen($value['value']) > 20) {
                    return "规格值长度不能超过20";
                }
                if (in_array($value['value'], $skuNameValueArr)) {
                    return "规格值 {$value['value']} 重复";
                }
                $skuNameValueArr[] = $value['value'];
                $tempMd5Arr[] = md5($value['value']);
            }
            $skuMd5List[] = $tempMd5Arr;
        }

        $skuCartesian = array_cartesian($skuMd5List);

        if (count($skuCartesian) != count($skuList)) {
            return '规格明细 规格值组合错误';
        }

        foreach (array_values($skuList) as $key => $sku) {

            $validate = Validate::rule([
                'sku_value_arr|服务价格' => 'require',
                'price|服务价格' => 'require|float|egt:0',
                'line_price|划线价格' => 'float|egt:0',
                'duration|服务时长' => 'require|number',
            ]);
            if (!$validate->check($sku)) {
                return $validate->getError();
            }

            // 每一行规格的值 与规格名称长度一致
            if (count($sku['sku_value_arr']) != count($skuNameList)) {
                return "规格明细第" . ($key + 1) . "行错误003";
            }

            // md5 规格值检测
            $sku_value_md5_arr = array_map(function($value){
                return md5($value);
            }, $sku['sku_value_arr']);
            $searchKey = array_search($sku_value_md5_arr, $skuCartesian);
            if ($searchKey === false) {
                return "规格明细第" . ($key + 1) . "行 无法匹配规格值";
            }
            unset($skuCartesian[$searchKey]);

            // 修改
            if (!empty($sku['id'])) {
                if (!(new GoodsSku)->where(['id'=>$sku['id'],'goods_id'=>$data['id']])->value('id')) {
                    return "规格明细第" . ($key + 1) . "行错误";
                }
            }
        }

        return true;
    }
}