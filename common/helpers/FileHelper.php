<?php

namespace app\common\helpers;

class FileHelper {

    # 文件格式
    const TYPE_IMAGE = 1 ;
    const TYPE_VOICE = 2 ;
    const TYPE_MUSIC = 2 ;
    const TYPE_VIDEO = 4 ;
    const TYPE_DOC = 5 ;
    const TYPE_EXCEL = 6 ;
    const TYPE_NARROW = 7 ;

    // 文件类型
    public static $fileType = null;

    // 文件格式
    public static $fileFormat = null;

    public static $fileName = null;

    /**
     * 获取指定文件格式的文件类型
     * 如果$format有指定值，则必须是  图片、语音、音乐、视频 其中的一个
     * @return mixed
     */
    public static function getFileType($filePath,$fileName = '',$format = false){
        if( empty($fileName)){
            self::$fileName = $filePath;
        }else{
            self::$fileName = $fileName;
        }
        $fileStream = self::_getFileStream($filePath);
        if($fileStream === false) {
            return false;
        }

        switch($format){
            case self::TYPE_IMAGE :
                self::_getImageType($fileStream);
                break;
            case self::TYPE_VOICE :
                self::_getVoiceType($fileStream);
                break;
            case self::TYPE_NARROW :
                self::_getNarrowType($fileStream);
                break;

            case self::TYPE_DOC:
                self::_getDocumentType($fileStream);
                break;

            default:
                self::_getAllType($fileStream);
                break;
        }

        return self::$fileType;
    }

    /**
     * 获取全部格式的文件类型
     * @return mixed
     */
    private static function _getAllType($fileStream)
    {
        if(self::_getImageType($fileStream) || self::_getVoiceType($fileStream) || self::_getNarrowType($fileStream) || self::_getDocumentType($fileStream)){
            return self::$fileType;
        }
        return null;
    }


    /**
     * 获取图片格式的文件类型
     * @return mixed
     */
    private static function _getImageType($fileStream){
        self::$fileFormat = self::TYPE_IMAGE;
        $typeCode = intval($fileStream['chars1'].$fileStream['chars2']);
        switch ($typeCode)
        {
            case 255216:
                self::$fileType = 'jpg';
                break;
            case 7173:
                self::$fileType = 'gif';
                break;
            case 6677:
                self::$fileType = 'bmp';
                break;
            case 13780:
                self::$fileType = 'png';
                break;
        }
        if ($fileStream['chars1']=='-1' && $fileStream['chars2']=='-40' ) {
            self::$fileType = 'jpg';
        }
        if ($fileStream['chars1']=='-119' && $fileStream['chars2']=='80' ) {
            self::$fileType = 'png';
        }

        return self::$fileType;
    }

    /**
     * 获取文档文件类型
     * @param $fileStream
     */
    private static function _getDocumentType($fileStream)
    {
        self::$fileFormat = self::TYPE_DOC;
        $typeCode = intval($fileStream['chars1'].$fileStream['chars2']);

        switch($typeCode)
        {
            case 8075:
                self::$fileType = 'xlsx'; //或ppt
                break;

            case -48:
                self::$fileType = 'docx';
                break;

            case 3780:
                self::$fileType = 'pdf';
                break;
            //case
        }

        return self::$fileType;
    }

    /**
     * 获取语音格式的文件类型
     * @return mixed
     */
    private static function _getVoiceType($fileStream){
        //mp3/wma/wav
        self::$fileFormat = self::TYPE_VOICE;
        $typeCode = intval($fileStream['chars1'].$fileStream['chars2']);
        switch ($typeCode)
        {
            case 7368:
                self::$fileType = 'mp3';
                break;
            case 0 :
                self::$fileType = 'mp4';
                break;
            case 8273:
                self::$fileType = 'wav';
                break;
        }

        return self::$fileType;
    }

    /**
     * 获取压缩文件格式的文件类型
     * @return mixed
     */
    private static function _getNarrowType($fileStream){
        self::$fileFormat = self::TYPE_NARROW;
        $typeCode = intval($fileStream['chars1'].$fileStream['chars2']);
        switch ($typeCode)
        {
            case 8297:
                self::$fileType = 'rar';
                break;
            case 8075://cdn不支持zip格式
                //self::$fileType = 'zip';
                //break;
        }

        return self::$fileType;
    }

    /**
     * 根据文件路径拿到文件流
     * @return mixed
     */
    private static function _getFileStream($filePath){
        //本地图片
        if(file_exists($filePath)){
            $file = fopen($filePath, "rb");
            //只读2字节
            $bin = fread($file, 2);
            fclose($file);
            return @unpack("c2chars", $bin);
        }
        //网络图片
        if(strpos($filePath,'http://') !== false){
            $file = file_get_contents($filePath);
            //截取前2个字节
            $bin = substr($file,0,2);
            return @unpack("c2chars", $bin);
        }
        return false;
    }

}

