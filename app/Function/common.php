<?php

/**
 * 统一处理sub添加租户分离
 */

use App\Models\SaasModel;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Redis;


/**
 * 创建cookieKey
 * @param $userId
 * @return string
 */
function createUserKey($userId)
{
    $time      = time();
    $strToSign = "," . $userId . ',' . $time;
    $secretKey = '()wd_passport@!@#!@';
    return base64_encode(hash_hmac("sha1", $strToSign, $secretKey));
}




/**
 * 生成密码
 * @param $value
 * @return mixed
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 */
function makePassword($value)
{
    return app('hash')->make($value);
}

/**
 * 校验密码
 * @param $value
 * @param $hasValue
 * @return mixed
 */
function checkPassword($value, $hasValue)
{
    return app('hash')->check($value, $hasValue);
}

/**
 * 生成图片验证码数据
 * @param int $expire
 * @return array
 */
function generatePicVCode($expire = 300)
{
    $phraseId = 'pic_v_code' . rand(1, 9999) . time();
    $value    = rand(1, 9999);
    $code     = str_pad($value, 4, "0", STR_PAD_LEFT);

    Redis::setex($phraseId, $expire, $code);
    return [$phraseId, $code];
}

/**
 * 校验图片验证码数据
 * @param $phraseId
 * @param $code
 * @return bool
 */
function checkPicVCode($phraseId, $code)
{
    if (empty($code)) return false;

    $cacheCode = Redis::get($phraseId);

    return $cacheCode == $code;
}

/**
 * 生成短信验证码
 * @param $type
 * @param $phone
 * @return string
 */
function generateSmsCode($type, $phone)
{
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    Redis::setex('send_tenant_msg_' . $type . '_' . $phone, 300, $code);

    return $code;
}

/**
 * 校验短信验证码
 * @param $type
 * @param $phone
 * @param $code
 * @return bool
 */
function checkSmsCode($type, $phone, $code)
{
    if (empty($phone)) return false;

    $redisKey = 'send_tenant_msg_' . $type . '_' . $phone;

    $cacheCode = Redis::get($redisKey);

    Redis::expire($redisKey, -1);

    return $cacheCode == $code;
}

/**
 * 远程请求
 * @param $uri
 * @param array $data
 * @param string $method
 * @param array $header
 * @param string $type
 * @return bool|\Psr\Http\Message\ResponseInterface
 */
function remoteRequest($uri, $data = [], $method = 'GET', $header = [], $type = null)
{
    try {
        $option = $type ?: 'form_params';
        if (strtoupper($method) == 'GET') {
            $option = 'query';
        }

        $client   = new GuzzleHttpClient();
        $response = $client->request($method, $uri, [
            $option   => $data,
            'headers' => $header,
        ]);

        if (200 != $response->getStatusCode()) {
            return false;
        }

        return $response;
    } catch (Throwable $e) {
        info('remoteRequestError:' . $e->getMessage());
        return false;
    }
}


if (!function_exists('filter')) {
    function filter($arr)
    {
        if ($arr === '' || $arr === null) {
            return false;
        }
        return true;
    }
}

/**
 * 根据身份证号获取年龄
 * @param $IDNumber
 * @return false|string
 */
function getAgeByIDnumber($IDNumber)
{
    if (!$IDNumber) {
        return 0;
    }
    # 1.从身份证中获取出生日期
    $id = $IDNumber;//身份证

    $birth_Date = strtotime(substr($id, 6, 8));//截取日期并转为时间戳
    # 2.格式化[出生日期]
    $Year  = date('Y', $birth_Date);//yyyy
    $Month = date('m', $birth_Date);//mm
    $Day   = date('d', $birth_Date);//dd

    # 3.格式化[当前日期]
    $current_Y = date('Y');//yyyy
    $current_M = date('m');//mm
    $current_D = date('d');//dd

    # 4.计算年龄()
    $age = $current_Y - $Year;//今年减去生日年
    if ($Month > $current_M || $Month == $current_M && $Day > $current_D) {//深层判断(日)
        $age--;//如果出生月大于当前月或出生月等于当前月但出生日大于当前日则减一岁
    }
    return $age;
}


/**
 * 参与大平台的考试系统配置
 */
if (!function_exists('joinedExamCityInfo')) {
    function examCityInfo()
    {
        $info = env('EXAM_CITY');
        $arr  = explode(",", $info);

        $formatData = [];
        foreach ($arr as $city) {
            $cityInfo = explode('_', $city);
            if (count($cityInfo) < 2) {
                continue;
            }
            $formatData[$cityInfo[0]] = [
                'code'            => $cityInfo[0],
                'agent'           => $cityInfo[1],
                'start_time'      => $cityInfo[2] ?? '',
                'enroll_end_time' => $cityInfo[3] ?? '',
                'link'            => url('/oauth/exam/' . $cityInfo[0]),
            ];
        }
        return $formatData;
    }
}

if (!function_exists('idcard_encry')) {
    function idcard_encry($idcard)
    {
        $len = strlen($idcard);

        if ($len == 15) {
            return substr_replace($idcard, "****", 8, 4);
        }

        if ($len == 18) {
            return substr_replace($idcard, "****", 10, 4);
        }

        return "";
    }
}

if (!function_exists('mobile_encry')) {
    function mobile_encry($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }
}

if (!function_exists('idNumber')) {
    function idNumber($ID)
    {
        return $ID ? substr_replace($ID, '********', 6, 8) : '';
    }
}

if (!function_exists('competitionEnvironment')) {
    function competitionEnvironment($value = '')
    {
        $user = request()->user();
        if (!$user) {
            return false;
        }

        $redisKey = 'competition_id_' . $user->getUserKey();

        // 清理数据
        if (is_null($value)) {
            return Redis::expire($redisKey, -1000);
        }

        // 获取数据
        if ($value == '') {
            return Redis::get($redisKey);
        }

        // 设置全局ID
        return Redis::setex($redisKey, config('const.USER_KEY_EXPIRE_TIME'), $value);
    }

}

if (!function_exists('uuid')) {
    function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        return $prefix . $chars;
    }
}

if (!function_exists('numberToStr')) {
    /**
     * 数字转换汉字
     * @param $num
     * @return string
     */
    function numberToStr($num)
    {
        if (empty($num)) {
            return $num;
        }
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('', '十', '百', '千', '万', '亿', '十', '百', '千');

        $num_str   = (string)$num;
        $count     = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num  = null; //临时数字
        $chiStr    = '';//拼接结果

        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr   = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
            $temp_num = $num_str[1];
            $chiStr   .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        } else if ($count > 2) {
            $index = 0;
            for ($i = $count - 1; $i >= 0; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag) {
                        $chiStr    = $chiNum[$temp_num] . $chiStr;
                        $last_flag = true;
                    }
                } else {
                    $chiStr    = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;
                    $zero_flag = false;
                    $last_flag = false;
                }
                $index++;
            }
        } else {
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }
}


//二维数组排序
if (!function_exists('arrSort')) {
    function arrSort($arr, $key, $order = 'desc')
    {
        for ($i = 0; $i < count($arr) - 1; $i++) {
            for ($j = 0; $j < count($arr) - 1 - $i; $j++) {
                if ($order == 'desc') {
                    if ($arr[$j][$key] < $arr[$j + 1][$key]) {
                        $tep         = $arr[$j];
                        $arr[$j]     = $arr[$j + 1];
                        $arr[$j + 1] = $tep;
                    }
                } else {
                    if ($arr[$j][$key] > $arr[$j + 1][$key]) {
                        $tep         = $arr[$j];
                        $arr[$j]     = $arr[$j + 1];
                        $arr[$j + 1] = $tep;
                    }
                }
            }
        }
        return $arr;
    }
}

/**
 * 获取初始密码
 */
if (!function_exists('getDefaultPassword')) {
    function getDefaultPassword()
    {
        return 'Wd111111';
    }
}

/**
 * http转https
 */
if (!function_exists('httpToHttps')) {
    function httpToHttps($url)
    {
        #检测url是否为空
        if (empty($url)) {
            return "";
        }

        $pathInfo = parse_url($url);

        #检测字符串中是否存在oss && 检测字符串第一次出现的字符索引下标位置
        if (isset($pathInfo['host']) && (strpos($pathInfo['host'], 'oss') !== false) && (strpos($url, 'http://') === 0)) {
            return 'https://'.substr($url, strlen('http://'));
        }

        return $url;
    }
}
