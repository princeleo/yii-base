<?php

namespace app\models\base;

use app\models\notice\TextLinks;
use app\models\user\AuthUser;
use yii\data\ActiveDataProvider;
use Yii;


class BaseMateriel extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_materiel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'type','file_name'], 'required'],
            [['app_id','file_name'], 'string'],
            [['file_name'], 'string', 'max' => 50],
            [['upload_name', 'illustration', 'link_url'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键ID（自增）',
            'app_id' => '所属平台',
            'file_name' => '文件名称',
            'illustration' => '物料说明',
            'link_url' => '链接地址',
            'type' => '物料地址类型',
            'created' => '创建日期',
            'modified' => '更新时间',
	    'upload_name' => '上传文档名称',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BaseMateriel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

//        if (!($this->load(['BaseMateriel' => $params]) && $this->validate())) {
//            return $dataProvider;
//        }

//        $query->andFilterWhere([
//            'id' => $this->id,
//            'file_name' => $this->file_name,
//            'illustration' => $this->illustration,
//            'link_url' => $this->link_url,
//            'modified' => $this->modified,
//            'created' => $this->created
//        ]);

        if(!empty($params['id'])){
            $query->andFilterWhere(['in', 'id', $params['id']]);
        }

        if(!empty($params['agent_scope'])){
            $query->andFilterWhere(['in', 'app_id', $params['agent_scope']]);
        }

        if(!empty($params['orderby'])){
            $query->addOrderBy($params['orderby']);
        }
        if(!empty($params['search']))
        {
            $query->andFilterWhere(['like', 'file_name', $params['search']]);
        }

        if(!empty($params['app_id']))
        {
            $query->andFilterWhere(["app_id" => $params['app_id']]);
        }
        if(!empty($params['type']))
        {
            $query->andFilterWhere(["type" => $params['type']]);
        }
        $query->andFilterWhere(['=', 'app_id', $this->app_id]);

        return $dataProvider;
    }

}
