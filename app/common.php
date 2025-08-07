<?php
// 应用公共文件
use app\common\model\user\User;
use think\helper\Str;

/**
 * @notes 生成密码加密密钥
 * @param string $plaintext
 * @param string $salt
 * @return string
 * @author 段誉
 * @date 2021/12/28 18:24
 */
function create_password(string $plaintext, string $salt) : string
{
    return md5($salt . md5($plaintext . $salt));
}


/**
 * @notes 随机生成token值
 * @param string $extra
 * @return string
 * @author 段誉
 * @date 2021/12/28 18:24
 */
function create_token(string $extra = '') : string
{
    $salt = env('project.unique_identification', 'likeshop');
    $encryptSalt = md5( $salt . uniqid());
    return md5($salt . $extra . time() . $encryptSalt);
}


/**
 * @notes 截取某字符字符串
 * @param $str
 * @param string $symbol
 * @return string
 * @author 段誉
 * @date 2021/12/28 18:24
 */
function substr_symbol_behind($str, $symbol = '.') : string
{
    $result = strripos($str, $symbol);
    if ($result === false) {
        return $str;
    }
    return substr($str, $result + 1);
}


/**
 * @notes 对比php版本
 * @param string $version
 * @return bool
 * @author 段誉
 * @date 2021/12/28 18:27
 */
function comparePHP(string $version) : bool
{
    return version_compare(PHP_VERSION, $version) >= 0 ? true : false;
}


/**
 * @notes 检查文件是否可写
 * @param string $dir
 * @return bool
 * @author 段誉
 * @date 2021/12/28 18:27
 */
function checkDirWrite(string $dir = '') : bool
{
    $route = root_path() . '/' . $dir;
    return is_writable($route);
}


/**
 * 多级线性结构排序
 * 转换前：
 * [{"id":1,"pid":0,"name":"a"},{"id":2,"pid":0,"name":"b"},{"id":3,"pid":1,"name":"c"},
 * {"id":4,"pid":2,"name":"d"},{"id":5,"pid":4,"name":"e"},{"id":6,"pid":5,"name":"f"},
 * {"id":7,"pid":3,"name":"g"}]
 * 转换后：
 * [{"id":1,"pid":0,"name":"a","level":1},{"id":3,"pid":1,"name":"c","level":2},{"id":7,"pid":3,"name":"g","level":3},
 * {"id":2,"pid":0,"name":"b","level":1},{"id":4,"pid":2,"name":"d","level":2},{"id":5,"pid":4,"name":"e","level":3},
 * {"id":6,"pid":5,"name":"f","level":4}]
 * @param array $data 线性结构数组
 * @param string $symbol 名称前面加符号
 * @param string $name 名称
 * @param string $id_name 数组id名
 * @param string $parent_id_name 数组祖先id名
 * @param int $level 此值请勿给参数
 * @param int $parent_id 此值请勿给参数
 * @return array
 */
function linear_to_tree($data, $sub_key_name = 'sub', $id_name = 'id', $parent_id_name = 'pid', $parent_id = 0)
{
    $tree = [];
    foreach ($data as $row) {
        if ($row[$parent_id_name] == $parent_id) {
            $temp = $row;
            $child = linear_to_tree($data, $sub_key_name, $id_name, $parent_id_name, $row[$id_name]);
            if($child){
                $temp[$sub_key_name] = $child;
            }
            $tree[] = $temp;
        }
    }
    return $tree;
}


/**
 * @notes 生成编号
 * @param $table
 * @param $field
 * @param string $prefix
 * @param int $rand_suffix_length
 * @param array $pool
 * @return string
 * @author ljj
 * @date 2022/2/15 3:47 下午
 */
function generate_sn($table, $field, $prefix = '', $rand_suffix_length = 4, $pool = []) : string
{
    $suffix = '';
    for ($i = 0; $i < $rand_suffix_length; $i++) {
        if (empty($pool)) {
            $suffix .= rand(0, 9);
        } else {
            $suffix .= $pool[array_rand($pool)];
        }
    }
    $sn = $prefix . date('YmdHis') . $suffix;
    if ($table->where($field, $sn)->find()) {
        return generate_sn($table, $field, $prefix, $rand_suffix_length, $pool);
    }
    return $sn;
}


/**
 * @notes 生成用户编码
 * @param string $prefix
 * @param int $length
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * @author ljj
 * @date 2022/2/17 11:25 上午
 */
function create_user_sn($prefix = '', $length = 8)
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if (User::where(['sn' => $sn])->find()) {
        return create_user_sn($prefix, $length);
    }
    return $sn;
}


/**
 * @notes 随机生成邀请码
 * @param $length
 * @return string
 * @author Tab
 * @date 2021/7/26 11:17
 */
function generate_code($length = 6)
{
    // 去除字母IO数字012
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ3456789';
    // 随机起始索引
    $start = mt_rand(0, strlen($letters) - $length);
    // 打乱字符串
    $shuffleStr = str_shuffle($letters);
    // 截取字符
    $randomStr = substr($shuffleStr, $start, $length);
    // 判断是否已被使用
    $user = User::where('code', $randomStr)->findOrEmpty();
    if($user->isEmpty()) {
        return $randomStr;
    }
    generate_code($length);
}



/**
 * @notes 自定义长度纯数字随机编码
 * @param $table
 * @param string $field
 * @param int $length
 * @param string $prefix
 * @return string
 * @author ljj
 * @date 2021/8/26 2:57 下午
 */
function create_number_sn($table, $field = 'sn', $length = 8, $prefix = '')
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if ($table->where($field, $sn)->find()) {
        return create_number_sn($table, $field, $length, $prefix);
    }
    return $sn;
}


/**
 * User: 意象信息科技 lr
 * Desc: 下载文件
 * @param $url 文件url
 * @param $save_dir 保存目录
 * @param $file_name 文件名
 * @return string
 */
function download_file($url, $save_dir, $file_name)
{
    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0775, true);
    }
    $file_src = $save_dir . $file_name;
    file_exists($file_src) && unlink($file_src);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    $resource = fopen($file_src, 'a');
    fwrite($resource, $file);
    fclose($resource);
    if (filesize($file_src) == 0) {
        unlink($file_src);
        return '';
    }
    return $file_src;
}


/**
 * @notes 删除目标目录
 * @param $path
 * @param $delDir
 * @return bool|void
 * @author 段誉
 * @date 2022/4/8 16:30
 */
function del_target_dir($path, $delDir)
{
    //没找到，不处理
    if (!file_exists($path)) {
        return false;
    }

    //打开目录句柄
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir("$path/$item")) {
                    del_target_dir("$path/$item", $delDir);
                } else {
                    unlink("$path/$item");
                }
            }
        }
        closedir($handle);
        if ($delDir) {
            return rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}


/**
 * @notes uri小写
 * @param $data
 * @return array|string[]
 * @author 段誉
 * @date 2022/7/19 14:50
 */
function lower_uri($data)
{
    if (!is_array($data)) {
        $data = [$data];
    }
    return array_map(function ($item) {
        return strtolower(Str::camel($item));
    }, $data);
}


/**
 * @notes 本地版本
 * @return mixed
 * @author 段誉
 * @date 2021/8/14 15:33
 */
function local_version()
{
    if(!file_exists('./upgrade/')) {
        // 若文件夹不存在，先创建文件夹
        mkdir('./upgrade/', 0777, true);
    }
    if(!file_exists('./upgrade/version.json')) {
        // 获取本地版本号
        $version = config('project.version');
        $data = ['version' => $version];
        $src = './upgrade/version.json';
        // 新建文件
        file_put_contents($src, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    $json_string = file_get_contents('./upgrade/version.json');
    // 用参数true把JSON字符串强制转成PHP数组
    $data = json_decode($json_string, true);
    return $data;
}


/**
 * @notes 解压压缩包
 * @param $file
 * @param $save_dir
 * @return bool
 * @author 段誉
 * @date 2021/8/14 15:27
 */
function unzip($file, $save_dir)
{
    if (!file_exists($file)) {
        return false;
    }
    $zip = new \ZipArchive();
    if ($zip->open($file) !== TRUE) {//中文文件名要使用ANSI编码的文件格式
        return false;
    }
    $zip->extractTo($save_dir);
    $zip->close();
    return true;
}


/**
 * @notes 遍历指定目录下的文件(目标目录,排除文件)
 * @param $dir //目标文件
 * @param string $exclude_file //要排除的文件
 * @param string $target_suffix //指定后缀
 * @return array|false
 * @author 段誉
 * @date 2021/8/14 14:44
 */
function get_scandir($dir, $exclude_file = '', $target_suffix = '')
{
    if (!file_exists($dir) || empty(trim($dir))) {
        return [];
    }

    $files = scandir($dir);
    $res = [];
    foreach ($files as $item) {
        if ($item == "." || $item == ".." || $item == $exclude_file) {
            continue;
        }
        if (!empty($target_suffix)) {
            if (get_extension($item) == $target_suffix) {
                $res[] = $item;
            }
        } else {
            $res[] = $item;
        }
    }

    if (empty($item)) {
        return false;
    }
    return $res;
}


/**
 * @notes 获取文件扩展名
 * @param $file
 * @return array|string|string[]
 * @author 段誉
 * @date 2021/8/14 15:24
 */
function get_extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}


/**
 * 组合,多用于计算商品属性的组合情况
传入：
$arr = [
[ '13寸', '15寸' ],
[ '320G', '500G' ],
];
得到：
$arr = [
[ '13寸', '320G' ],
[ '13寸', '500G' ],
[ '15寸', '320G' ],
[ '15寸', '500G' ],
];
 * @return array
 */
function array_cartesian($arr)
{
    $result = [];
    foreach ($arr as $value) {
        $tempArr = [];
        if ($result) {
            foreach ($result as $v) {
                foreach ($value as $vo) {
                    $vTemp      = $v;
                    $vTemp[]    = $vo;
                    $tempArr[]  = $vTemp;
                }
            }
        } else {
            foreach ($value as $vo) {
                $tempArr[] = [$vo];
            }
        }
        $result = $tempArr;
    }
    return $result;
}


/**
 * @notes 创建顺序编号
 * 根据长度往左边补0
 * 例：001，002，003
 * 当前长度大于等于 $num 时，不在补0，直接加 1
 * @param $prevSn //上一个编号
 * @param $len //编号长度
 */
function sequence_sn($prevSn,$len)
{
    $str = (string)$prevSn;
    $sn = (string)((int)ltrim($str,'0') + 1);
    $sn_len = strlen($sn);
    if ($sn_len < $len) {
        $sn = str_pad($sn,$len,'0',STR_PAD_LEFT);
    }

    return $sn;
}


/**
 * @notes 获取两点之间的距离
 * @param $lng1 //坐标点1-经度
 * @param $lat1 //坐标点1-纬度
 * @param $lng2 //坐标点2-经度
 * @param $lat2 //坐标点2-纬度
 * @param $unit //距离单位 1:米 2:公里
 * @param $decimal //小数位数
 * @return float
 * @date 2024/9/14 上午10:36
 */
function getDistance($lng1, $lat1, $lng2, $lat2, $unit = 2, $decimal = 2)
{
    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI           = 3.1415926535898;

    $radLat1 = $lat1 * $PI / 180.0;
    $radLat2 = $lat2 * $PI / 180.0;

    $radLng1 = $lng1 * $PI / 180.0;
    $radLng2 = $lng2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if ($unit === 2) {
        $distance /= 1000;
    }

    return round($distance, $decimal);
}