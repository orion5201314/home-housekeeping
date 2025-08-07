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

namespace app\common\model\goods;


use app\common\enum\GoodsEnum;
use app\common\model\BaseModel;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;
use app\common\model\staff\StaffSkill;
use think\model\concern\SoftDelete;

class Goods extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    /**
     * @notes 关联服务轮播图
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2022/2/9 3:37 下午
     */
    public function goodsImage()
    {
        return $this->hasMany(GoodsImage::class,'goods_id','id');
    }

    /**
     * @notes 关联服务评价模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2022/2/17 6:15 下午
     */
    public function goodsComment()
    {
        return $this->hasMany(GoodsComment::class,'goods_id','id')->append(['goods_comment_image','user']);
    }

    /**
     * @notes 关联服务SKU模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2024/8/29 上午10:12
     */
    public function sku()
    {
        return $this->hasMany(GoodsSku::class,'goods_id','id');
    }

    /**
     * @notes 关联服务SKU名称模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2024/8/29 上午11:28
     */
    public function skuNameList()
    {
        return $this->hasMany(GoodsSkuName::class,'goods_id','id');
    }

    /**
     * @notes 关联服务SKU值模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2024/8/29 上午11:28
     */
    public function skuValue()
    {
        return $this->hasMany(GoodsSkuValue::class,'goods_id','id');
    }


    /**
     * @notes 获取分类名称
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ljj
     * @date 2022/2/9 11:15 上午
     */
    public function getCategoryDescAttr($value,$data)
    {
        $category_arr = (new GoodsCategory())->column('name,pid','id');
        $category_name = '未知';
        $category_first = $category_arr[$data['category_id']] ?? [];
        if ($category_first) {
            $category_name = $category_first['name'];
            $category_second = $category_arr[$category_first['pid']] ?? [];
            if ($category_second) {
                $category_name = $category_second['name'].'/'.$category_name;
                $category_third = $category_arr[$category_second['pid']] ?? [];
                if ($category_third) {
                    $category_name = $category_third['name'].'/'.$category_name;
                }
            }
        }

        return $category_name;
    }

    /**
     * @notes 获取状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/2/9 11:22 上午
     */
    public function getStatusDescAttr($value,$data)
    {
        return GoodsEnum::getShowDesc($data['status']);
    }


    /**
     * @notes 分类搜索器
     * @param $query
     * @param $value
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/17 5:11 下午
     */
    public function searchCategoryIdAttr($query, $value, $data)
    {
        if ($value) {
            $goodsCategory = GoodsCategory::find($value);
            $level = $goodsCategory['level'] ?? '';
            $categoryIds = [];
            switch ($level){
                case 1:
                    $categoryIds = GoodsCategory::where(['pid'=>$value])
                        ->column('id');
                    Array_push($categoryIds,$value);
                    break;
                case 2:
                    $categoryIds = [$value];
                    break;
            }
            $goodsIds = Goods::where(['category_id' => $categoryIds])->column('id');
            $query->where('id', 'in', $goodsIds);
        }
    }

    /**
     * @notes 关键词搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author ljj
     * @date 2022/2/17 5:16 下午
     */
    public function searchKeywordAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('name', 'like', '%'.$value.'%');
        }
    }

    /**
     * @notes 处理技能ID
     * @param $value
     * @param $data
     * @return false|string[]
     * @author ljj
     * @date 2024/8/29 上午10:08
     */
    public function getSkillIdAttr($value,$data)
    {
        $skillId = explode(',',$data['skill_id']);
        array_walk($skillId, function (&$value) {
            $value = (int)$value;
        });
        return $skillId;
    }

    /**
     * @notes 获取标签
     * @param $value
     * @param $data
     * @return false|string[]
     * @author ljj
     * @date 2024/9/24 下午4:39
     */
    public function getLabelAttr($value,$data)
    {
        return empty($data['label']) ? [] : explode('|',$data['label']);
    }

    /**
     * @notes 下单次数
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2024/10/28 下午2:50
     */
    public function getOrderNumAttr($value,$data)
    {
        $orderNum = OrderGoods::where(['goods_id'=>$data['id']])->count();
        return $orderNum + $data['virtual_sale_num'];
    }

    /**
     * @notes 最小服务时长
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2024/10/28 下午2:50
     */
    public function getMinDurationAttr($value,$data)
    {
        return GoodsSku::where(['goods_id'=>$data['id']])->min('duration');
    }
}