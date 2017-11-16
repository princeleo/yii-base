<?php

namespace app\models\notice;

use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class TextLinks extends BaseModel
{
    const URL_TYPE_DEFAULT = 1;
    const URL_TYPE_FILE = 2;

    const MODEL_TYPE_NOTICE = 1;
    const MODEL_TYPE_KNOWLEDGE = 2;
    const MODEL_TYPE_MESSAGE = 3;

    public static function getUrlType()
    {
        return [
            self::URL_TYPE_DEFAULT => '链接',
            self::URL_TYPE_FILE => '附件'
        ];
    }

    /**链接类型
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%text_links}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['name', 'link_url'], 'string'],
            [['notice_id', 'model_type','url_type'], 'integer'],
        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->where(['is_deleted' => 1]);
        if (isset($params['name']) && $params['name']) {
            $query->andFilterWhere(['like', 'name', $params['name']]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }

    /**
     * 获取附件数据
     *
     * @return array $data
     */
    public static function GetTypeLists($params)
    {
        if(empty($params['action'])|| $params['action']=='add')
        {
         return   [
                'lists' => array(['id'=>'','name'=>'','link_url'=>''])
            ];
        }


        $query = self::find()->select('id,name,link_url')->orderBy('id');
        if (isset($params['notice_id']) && $params['notice_id']) {
            $query->where(['notice_id'=>$params['notice_id']]);
        }
        if (isset($params['model_type']) && $params['model_type']) {
            $query->andWhere(['model_type'=>$params['model_type']]);
        }
        if (isset($params['url_type']) && $params['url_type']) {
            $query->andWhere(['url_type'=>$params['url_type']]);
        }

        // 返回一个Post实例的数组
        $posts = $query;
        $result = [
            'lists' => $query->asArray()->all()
        ];
        return $result;
    }


    /**根据公告ID回去数据
     * @param $id
     * @return null|static
     */
    protected function findModel($id)
    {
        if (($model = self::findOne($id)) !== null) {
            return $model;
        }
    }


    /**  附件链接动作列表
     * @param $model_id 模块ID
     * @param int $model_type 1,公告2知识3通知
     * @return mixed
     */
    public static function getModelList($model_id, $model_type = 1, $page_count = 1)
    {
        $query = self::find()->select(Modellink::tableName() . '.id,name, link_url')
        ->InnerJoin(Modellink::tableName(), Modellink::tableName() . '.link_id=' . Modellink::tableName() . '.id');
        if (!empty($model_type)) {
            $query->where(['model_type' => $model_type]);
        }
        if (!empty($model_id)) {
            $query->andFilterWhere(['model_id' => $model_id]);
        }
        if (!empty($page_count)) {
            $query->andFilterWhere(['page_count' => $page_count]);
        }

        return $query->asArray()->all();
    }

}
