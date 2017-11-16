<?php


/**
 * 输出调试函数
 *
 * @param array $args
 */
function pr($args = array()) {
    $escape_html = true;
    $bg_color = '#EEEEE0';
    $txt_color = '#000000';
    $args = func_get_args();

    foreach($args as $arr){
        echo sprintf('<pre style="background-color: %s; color: %s;">', $bg_color, $txt_color);
        if($arr) {
            if($escape_html){
                echo htmlspecialchars( print_r($arr, true) );
            }else{
                print_r($arr);
            }
        }else {
            var_dump($arr);
        }
        echo '</pre>';
    }
}


/**
 * 统一加密方法
 * @param $data
 * @param $key
 * @return string
 */
function encrypt($data, $key)
{
    $bit = 192;
    set_include_path(dirname(__DIR__) . '/common/vendor/crypt');
    require_once 'Crypt/AES.php';
    $aes = new Crypt_AES();
    $aes->setKeyLength($bit);
    $aes->setKey($key);
    $data = is_array($data) ? http_build_query($data) : $data;
    $value = $aes->encrypt($data);

    return strtr(base64_encode($value), '+/=', '-_.');
}

/**
 * 统一解密方法
 * @param $data
 * @param $key
 * @return bool|int|string
 */
function decrypt($data, $key)
{
    $bit = 192;
    set_include_path(dirname(__DIR__) . '/common/vendor/crypt');
    require_once 'Crypt/AES.php';
    $aes = new Crypt_AES();
    $aes->setKeyLength($bit);
    $aes->setKey($key);
    $value = $aes->decrypt(base64_decode(strtr($data, '-_.', '+/=')));

    if(strstr($value,'=') !== false){
        parse_str($value,$value);
    }
    return $value;
}

/**
 * 可以统计中文字符串长度的函数
 * @param $str 要计算长度的字符串,一个中文算一个字符
 * @return int
 */
function absLength($str)
{
    if (empty($str)) {
        return 0;
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($str, 'utf-8');
    } else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}

/**
 * utf-8编码下截取中文字符串,参数可以参照substr函数
 * @param $str 要进行截取的字符串
 * @param int $start 要进行截取的开始位置，负数为反向截取
 * @param $end 要进行截取的长度
 * @return bool|string
 */
function utf8Substr($str, $start = 0, $end)
{
    if (empty($str)) {
        return false;
    }
    if (function_exists('mb_substr')) {
        if (func_num_args() >= 3) {
            $end = func_get_arg(2);
            return mb_substr($str, $start, $end, 'utf-8');
        } else {
            mb_internal_encoding("UTF-8");
            return mb_substr($str, $start);
        }

    } else {
        $null = "";
        preg_match_all("/./u", $str, $ar);
        if (func_num_args() >= 3) {
            $end = func_get_arg(2);
            return join($null, array_slice($ar[0], $start, $end));
        } else {
            return join($null, array_slice($ar[0], $start));
        }
    }
}