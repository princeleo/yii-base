<?php
/**
 * 统一文件处理，包括上传，创建等
 */

namespace app\common\services;

use app\common\helpers\CdnHelper;
use Yii;
use app\common\errors\BaseError;
use app\common\helpers\FileHelper;
use app\common\exceptions\ParamsException;



class FileService
{
    const TYPE_AVATAR = 'avatar';
    const TYPE_OTHER = 'other';
    const TYPE_AGENT = 'agent';
    const TYPE_SHOP = 'shop';

    public static function businessType()
    {
        return [
            self::TYPE_AVATAR,
            self::TYPE_OTHER,
            self::TYPE_AGENT,
            self::TYPE_SHOP
        ];
    }

    public function uploadMore($params,$type = FileHelper::TYPE_IMAGE)
    {
        if(empty($params) || !is_array($params))
        {
            return BaseError::PARAMETER_ERR;
        }

        FileHelper::getFileType($params['filePath'],$params['fileName'],$type);
        if(empty(FileHelper::$fileType))
        {
            return BaseError::NOT_FILE_TYPE;
        }
        $path = Yii::$app->params['uploadPath'];
        $business = !empty($params['business']) && in_array($params['business'],self::businessType()) ? $params['business'].'/' : self::TYPE_OTHER.'/';

        $upload_path =  $business. date('Ymd') . '/';
        $this->mkdirs( $path.$upload_path);
        $params['fullName'] =  $path. $upload_path.$params['fileName'];

        //保存文件
        if(move_uploaded_file($params['tmp_name'],$params['fullName']))
        {
            return Yii::$app->params['visitPath'].$upload_path.$params['fileName'];
        }
        else
        {
            return BaseError::UPLOAD_FILE_ERR;
        }
    }

    public function upload($params,$type = FileHelper::TYPE_IMAGE){
        if(empty($params) || !is_array($params)){
            throw new ParamsException(BaseError::PARAMETER_ERR,$params);
        }

        FileHelper::getFileType($params['filePath'],$params['fileName'],$type);
        if(empty(FileHelper::$fileType)){
            throw new ParamsException(BaseError::NOT_FILE_TYPE,$params);
        }
        $path = Yii::$app->params['uploadPath'];
        $business = !empty($params['business']) && in_array($params['business'],self::businessType()) ? $params['business'].'/' : self::TYPE_OTHER.'/';

        $upload_path =  $business. date('Ymd') . '/';
        $this->mkdirs( $path.$upload_path);
        $params['fullName'] =  $path. $upload_path.$params['fileName'];

        //保存文件
        if(move_uploaded_file($params['tmp_name'],$params['fullName'])){
            return Yii::$app->params['visitPath'].$upload_path.$params['fileName'];
        }else{
            throw new ParamsException(BaseError::UPLOAD_FILE_ERR,$params);
        }
    }


    /**
     * 上传到CDN
     * @param $params
     * @param $type
     * @return string
     * @throws \app\common\exceptions\ParamsException
     */
    public function uploadCdnFile($params,$type = FileHelper::TYPE_IMAGE)
    {
        if(empty($params) || !is_array($params)){
            throw new ParamsException(BaseError::PARAMETER_ERR,$params);
        }

        FileHelper::getFileType($params['filePath'],$params['fileName'],$type);
        if(empty(FileHelper::$fileType)){
            throw new ParamsException(BaseError::NOT_ALLOW_FILE_TYPE,$params);
        }
        $path = Yii::$app->params['uploadPath'];
        $business = !empty($params['business']) && in_array($params['business'],self::businessType()) ? $params['business'].'/' : self::TYPE_OTHER.'/';

        $upload_path =   $path. $business. date('Ymd') . '/';
        //$this->mkdirs($upload_path);
        $params['fullName'] = $upload_path.$params['fileName'];

        //保存文件
        $cdn_up = CdnHelper::uploadFile($params['tmp_name'],$params['fileName'],FileHelper::$fileType);
        $cdn_up_arr = json_decode($cdn_up, true);
        if(! $cdn_up || ! $cdn_up_arr || ! isset($cdn_up_arr['url'])){
            throw new ParamsException(BaseError::UPLOAD_FILE_ERR,array_merge($params,['cdn_up'=>$cdn_up]),!empty($cdn_up_arr['errmsg']) ? $cdn_up_arr['errmsg'] : '',true);
        }else{
            return substr($cdn_up_arr['url'], -1) == '.' ? $cdn_up_arr['url'] . '.'.FileHelper::$fileType : $cdn_up_arr['url'];
        }
    }



    /**
     * 重新输入图片源-jgp
     * @param $file
     * @return string
     */
    public function createImageJpg($file) {

        $img = substr($file, strrpos($file, '/')+1);
        $name = substr($img, 0, strrpos($img, '.'));

        $max_width = 200;
        $max_height = 200;

        list($width, $height, $image_type) = getimagesize($file);

        switch ($image_type)
        {
            case 1: $src = imagecreatefromgif($file); break;
            case 2: $src = imagecreatefromjpeg($file);  break;
            case 3: $src = imagecreatefrompng($file); break;
            default: return '';  break;
        }

        $x_ratio = $max_width / $width;
        $y_ratio = $max_height / $height;

        if( ($width <= $max_width) && ($height <= $max_height) ){
            $tn_width = $width;
            $tn_height = $height;
        }elseif (($x_ratio * $height) < $max_height){
            $tn_height = ceil($x_ratio * $height);
            $tn_width = $max_width;
        }else{
            $tn_width = ceil($y_ratio * $width);
            $tn_height = $max_height;
        }

        $tmp = imagecreatetruecolor($tn_width,$tn_height);

        if(($image_type == 1) OR ($image_type==3))
        {
            imagealphablending($tmp, false);
            imagesavealpha($tmp,true);
            $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
            imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
        }
        imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

        $upload_path =  FileService::TYPE_SHOP.'/'. date('Ymd') . '/';
        $path = Yii::$app->params['uploadPath'].$upload_path.$name.'.jpg';
        imagejpeg($tmp, $path, 100);
        imagedestroy($tmp);
        return Yii::$app->params['visitPath'].$upload_path.$name.'.jpg';
    }

    /**
     * 创建目录
     *
     * @param     $dir
     * @param int $mode
     */
    public function mkdirs($dir, $mode = 0777)
    {
        if (!file_exists($dir)) {
            $this->mkdirs(dirname($dir), $mode);
            mkdir($dir, $mode);
        }
    }

}