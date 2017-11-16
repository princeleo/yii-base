<?php

namespace app\models\shop;

use app\common\errors\BaseError;
use app\common\exceptions\ApiException;
use app\common\exceptions\ParamsException;
use app\common\services\FileService;
use app\common\vendor\api\SpeedPosApi;
use Yii;
use yii\data\ActiveDataProvider;
use CURLFile;

/**
 * This is the model class for table "consume_order".
 *
 * @property string $id
 * @property integer $consume_date
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $shop_id
 * @property integer $exposure
 * @property integer $click
 * @property integer $ctr
 * @property integer $cpm
 * @property integer $cost
 * @property string $src
 * @property integer $created
 * @property integer $modified
 * @property integer $cpc
 */
class CustomerUploadUrl extends \app\models\BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_upload_url';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['local_url','speedpos_url'], 'string'],
        ];
    }

    public function findModel($url)
    {
        $url = str_replace(':81','',$url);
        return CustomerUploadUrl::find()->where(['local_url' => $url])->one();
    }


    /**  图片地址转进件资料的
     * @param $data
     * @return array|string
     */
    public function localToSpeedPos($data){
        $newUrl = array();
        $urlModel = new CustomerUploadUrl();
        $array = explode(',',$data);

        if(!empty($array)){
            foreach($array as $key =>$value){

                //图片库查找
                $uploadModel = $urlModel->findModel($value);
                if(!empty($uploadModel)){
                    if(empty($uploadModel->speedpos_url)){
                        $localUrl = $this->replaceSelfUrl($uploadModel->local_url);
                        $speed_pos_url = $this->speedPosUpload($localUrl,$uploadModel->type);
                        $uploadModel->speedpos_url = $speed_pos_url;
                        if (!$uploadModel->save()) {
                            Yii::error($uploadModel->errors,"localToSpeedPos");
                            throw new ApiException(BaseError::SAVE_ERROR);
                        }
                        $newUrl[] = $speed_pos_url;
                    }else{
                        $newUrl[] = $uploadModel->speedpos_url;
                    }
                }else{//再入库
                    $localUrl = $this->replaceSelfUrl($value);
                    $speed_pos_url = $this->speedPosUpload($localUrl,SpeedPosApi::PIC_ID_CARD);
                    $urlModel->speedpos_url = $speed_pos_url;
                    $urlModel->local_url = $value;
                    if (!$urlModel->save()) {
                        Yii::error($urlModel->errors,"localToSpeedPos");
                        throw new ApiException(BaseError::SAVE_ERROR);
                    }
                    $newUrl[] = $speed_pos_url;
                }
            }
        }
        $newUrl = implode(',',$newUrl);
        return $newUrl;
    }

    public function replaceSelfUrl($url){
        $str = str_replace(Yii::$app->params['visitUrl'],"",$url);
        return $str;
    }

    public function speedPosUpload($url,$type){
        $thirdApi = new SpeedPosApi();
        switch ($type) {
            case SpeedPosApi::PIC_ID_CARD:
                $searchParams['pictype'] = $thirdApi->getPic()[SpeedPosApi::PIC_ID_CARD];
                break;
            case SpeedPosApi::PIC_BUSINESS:
                $searchParams['pictype'] = $thirdApi->getPic()[SpeedPosApi::PIC_BUSINESS];
                break;
            case SpeedPosApi::PIC_BANK_CARD:
                $searchParams['pictype'] = $thirdApi->getPic()[SpeedPosApi::PIC_BANK_CARD];
                break;
            default:
                break;
        }
        $speed_pos_url = "";
        $speed_pic = rtrim(Yii::$app->params['uploadPath'],'upload/').$url;
        $name = substr($speed_pic, strrpos($speed_pic, '/')+1);
        $image_type=mime_content_type($speed_pic);   //获取png的mime类型

        //如果不是JPG，重新输出JPG图片
        if ($image_type != "image/jpeg") {
            $url = (new FileService())->createImageJpg($speed_pic);
            $speed_pic = rtrim(Yii::$app->params['uploadPath'],'upload/').$url;
            $name = substr($speed_pic, strrpos($speed_pic, '/')+1);
            $image_type=mime_content_type($speed_pic);   //获取png的mime类型
        }

        $searchParams['_file'] = new CURLFile($speed_pic,$image_type,$name);
        $thirdApi = new SpeedPosApi();
        $result = $thirdApi->requestApi('upload', $searchParams, false);
        Yii::error($result,"CustomerUpload_SpeedPosApi");
        if (!empty($result)) {
            if ($result['error'] == 0 && isset($result['data']['url'])) {
                $speed_pos_url = $result['data']['url'];
            }
        }
        return $speed_pos_url;
    }
}
