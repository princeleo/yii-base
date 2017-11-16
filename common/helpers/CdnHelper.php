<?php

namespace app\common\helpers;

class CdnHelper {

    const CND_PRIVATE_REQUEST_URL = 'http://private_img.api.nexto2o.com';//隐私地址

    public static $cdnData = null ;

    /**
     * @param 上传图片到cdn
     * 内部调用
     */
    private static function _transfer($fileStream, $fileName, $fileType, $is_private = false)
    {
        //CDN接口有的文件名中带空格会上传失败
        $fileName = str_replace(" ", "", $fileName);
        $url = ($is_private ? self::CND_PRIVATE_REQUEST_URL : CND_REQUEST_URL) . '?file_name=' . $fileName . '&platform=3&file_type=' . $fileType;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileStream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:")); //头部要送出'Expect: '
        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPV4协议解析域名
        $sContent = curl_exec($ch);
        $aStatus = curl_getinfo($ch);
        curl_close($ch);
        if (intval($aStatus["http_code"]) == 200) {
            self::$cdnData = $sContent;
        }

        return self::$cdnData;
    }

    /**
     * 获取隐私图片
     * @param string $url
     * @param string $token
     */
    private static function _getImage($url = '', $token = '')
    {
        //CDN接口有的文件名中带空格会上传失败
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['token'=>$token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:")); //头部要送出'Expect: '
        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPV4协议解析域名
        $sContent = curl_exec($ch);
        $aStatus = curl_getinfo($ch);
        curl_close($ch);
        if (intval($aStatus["http_code"])==200) {
            self::$cdnData = $sContent;
        }

        return self::$cdnData;
    }

    /**
     * @param 上传文件
     * 内部调用
     */
    public static function uploadFile($filePath, $fileName, $fileType, $is_private = false)
    {
        return self::_transfer(file_get_contents($filePath), $fileName, $fileType, $is_private);
    }

    /**
     * @param 上传文件
     * 内部调用
     */
    public static function getImage($url, $token)
    {
        return self::_getImage($url, $token);
    }

    /**
     * @param 文件流上传文件
     * 内部调用
     */
    public static function uploadFileRaw($stream = null, $fileName = null, $fileType = null)
    {
        return self::_transfer($stream, $fileName, $fileType);
    }

}

