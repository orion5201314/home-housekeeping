<?php
namespace app\common\logic;
use app\common\model\Region;

/**
 * 城市逻辑类
 * Class CityLogic
 * @package app\common\logic
 */
class CityLogic extends BaseLogic
{

    /**
     * @notes 获取经纬度附近的城市
     * @param $longitude
     * @param $latitude
     * @return array
     * @author cjhao
     * @date 2024/9/3 23:41
     */
    public static function getNearbyCity($longitude,$latitude)
    {
        if(empty($longitude) || empty($latitude)){
            $field = 'oc.id,oc.city_id,r.name,0 as distance,r.db09_lng as longitude,r.db09_lat as latitude';
        } else {
            //用st_distance_sphere函数计算两点记录，单位米，这里换成千米
            $field = 'oc.id,oc.city_id,r.name,round(st_distance_sphere(point('.$longitude.','.$latitude.'),
            point(r.gcj02_lng, r.gcj02_lat))/1000,2) as distance,'.$longitude.' as longitude,'.$latitude.' as latitude';
        }

        $cityLists = (new Region())->alias('r')
            ->join('open_city oc', 'oc.city_id = r.id')
            ->where('r.level','=',2)
            ->whereNull('oc.delete_time')
            ->field($field)
            ->append(['distance_desc'])
            ->order(['distance'=>'asc','id'=>'asc'])
            ->select()
            ->toArray();

        return $cityLists;

    }
}