<?php

namespace app\models\app;

use \app\models\BaseModel;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_app".
 *
 * @property integer $id
 * @property string $os
 * @property string $type
 * @property string $title
 * @property integer $state
 * @property integer $modified
 * @property integer $created
 * @property integer $push
 * @property integer $desc
 * @property string $op_name
 * @property string $channel
 * @property string $url
 */
class AppVersion extends BaseModel
{


    //渠道数据
    public static $channels = [
        'sctek'=>'官方渠道',
        'pos'=>'POS机市场渠道',
        'qh360'=>'360市场渠道',
        'tencent'=>'腾讯市场渠道',
        'sogou'=>'搜狗市场渠道',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%app_version}}';
    }

    public function rules()
    {
        return [
            [['os','channel','name','code','title','desc','url','necessary','type','state'],'required'],
            [['os','channel','name','title','desc','url'], 'string'],
            [['code','type','state','push'], 'integer'],
        ];
    }


    /**
     * 单个查询
     * @param $params
     * @return array|null|\yii\db\ActiveRecord
     */
    public function search($params)
    {
        $query = AppVersion::find();

        if(!empty($params['os'])){
            $query->andFilterWhere(['app_version.os'=>$params['os']]);
        }
        if(!empty($params['code'])){
            $query->andFilterWhere(['>=', 'app_version.code', $params['code']]);
        }
        if(!empty($params['type'])){
            $query->andFilterWhere(['app_version.type'=>$params['type']]);
        }
        if(!empty($params['channel'])){
            $query->andFilterWhere(['like', 'app_version.channel', $params['channel']]);
        }
        $query->andWhere(['push' => 1]);//已经发布
        $query->orderBy('code DESC');
        $rs = $query->one();
//        var_dump($query->createCommand()->getRawSql());
        return $rs;
    }

    /**
     * 多个查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function listSearch($params) {
        $query = AppVersion::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC,
                ]
            ],
        ]);
        $this->load(['AppVersion'=>$params]);
//        if (!($this->load(['AppVersion'=>$params]) && $this->validate())) {
//            var_dump($this->getErrors());exit;
//            return $dataProvider;
//        }
        $query->andFilterWhere(['os' => $this->os])
            ->andFilterWhere(['type' => $this->type])
            ->andFilterWhere(['push' => $this->push])
            ->andFilterWhere(['like', 'title', $this->title]);
        return $dataProvider;
    }

    /**
     * 格式化显示渠道数据,每4个一行显示
     * @param $dataChannels string 渠道数据库保存数据， [,1,2,3,4,]
     * @return string [全部], [官方渠道  POS机市场渠道  ],每种渠道2个空格区分,
     */
    public static function channelStr($dataChannels) {
        $channels = array_filter(explode(",", $dataChannels), function($val) {
            return !!$val && $val != -1;
        });
        $channelStr = '';
        $_channels = AppVersion::$channels;//所有渠道枚举
        if(count($channels) == count($_channels)) {
            $channelStr = '全部';
        }else {
            array_walk($channels, function($channel,$key) use (&$channelStr, $_channels) {
                if(isset($_channels[$channel])) {
                    $channelStr .= "<span style='margin-right: 25px;'>" . $_channels[$channel] . "</span>";
                } else {
                    $channelStr .= "<span style='margin-right: 25px;'>" . "未知渠道[{$channel}]" . "</span>";
                }
                if($key % 4 === 0) {
                    $channelStr .= "<br>";
                }
            });
        }
        return $channelStr;
    }
}
