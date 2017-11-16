<?php

namespace app\models\base;

use app\models\notice\TextLinks;
use app\models\user\AuthUser;
use yii\data\ActiveDataProvider;
use Yii;

/**
 * This is the model class for table "base_notice".
 *
 * @property integer $id
 * @property integer $type
 * @property string $title
 * @property string $content
 * @property integer $status
 * @property integer $clicks
 * @property integer $deleted
 * @property integer $user_id
 * @property integer $created
 * @property integer $modified
 * @property integer $recommend
 */
class BaseNotice extends \app\models\BaseModel
{
    const TYPE_SYS_NOTICE = 1;
    const TYPE_BS_TEMP = 2;
    const TYPE_BS_KN = 3;
    const TYPE_CASE_SHARE = 4;
    const TYPE_HELP_CENTER = 5;
    const STATUS_PUBLISH = 1;
    const STATUS_NOT_PUBLISH = -1;
    const DELETED_DEFAULT = 1;
    const DELETED_DISABLE = 2;
    const DELETED_TRUE = 3;

    public static function getType($type = 1)
    {
        if($type == 1){
            return [
                self::TYPE_SYS_NOTICE => '系统公告',
                self::TYPE_BS_TEMP => '业务模板',
                self::TYPE_CASE_SHARE => '案例分享',
                self::TYPE_BS_KN => '业务知识',
                self::TYPE_HELP_CENTER => '帮助中心'
            ];
        }elseif($type == 2){
            return [
                self::TYPE_BS_TEMP => '业务模板',
                self::TYPE_CASE_SHARE => '案例分享',
                self::TYPE_BS_KN => '业务知识',
                self::TYPE_HELP_CENTER => '帮助中心'
            ];
        }

    }

    /**
     * 状态集合
     * @return array
     */
    public static  function getStatus(){
        return [
            self::STATUS_PUBLISH =>'发布',
            self::STATUS_NOT_PUBLISH =>'未发布'
        ];
    }

    public static function getDeteled()
    {
        return [
            self::DELETED_DEFAULT => '正常',
            self::DELETED_DISABLE => '禁用',
            self::DELETED_TRUE => '删除'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_notice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'status', 'clicks', 'deleted', 'user_id', 'created', 'modified', 'recommend','published'], 'integer'],
            [['title', 'content'], 'required','on' => 'create'],
            [['content','range'], 'string'],
            [['title'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键ID（自增）',
            'type' => '类型ID',
            'title' => '文档标题',
            'content' => '文档内容',
            'status' => '状态:1发布;2未发布',
            'clicks' => '点击次数',
            'deleted' => '动作:1正常，2禁用，3删除',
            'user_id' => '用户id',
            'created' => '创建日期',
            'modified' => '更新时间',
            'recommend' => '状态:1推荐;2未推荐',
            'published' => '发布时间'
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchNotice($params)
    {
        $query = BaseNotice::find()->with('links');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['BaseNotice' => $params]) && $this->validate())) {
            return $dataProvider;
        }
        if(!empty($params['agent_scope']))
        {
            $query->orFilterWhere(['or like', 'range', 'all']);
            $scope = explode(',',$params['agent_scope']);

            foreach($scope as $v)
            {
                $query->orFilterWhere(['like', 'range', $v]);
            }
        }

        if(!empty($params['range'])){
            if($params['range'] != 'all'){
                $query->orFilterWhere(['or like', 'range', 'all']);
                $scope = explode(',',$params['range']);

                foreach($scope as $v)
                {
                    $query->orFilterWhere(['like', 'range', $v]);
                }
            }
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => self::TYPE_SYS_NOTICE,
            'app_id' => $this->app_id,
            'status' => $this->status,
            'modified' => $this->modified,
            'created' => $this->created,
            'recommend' => $this->recommend,
            'user_id' => $this->user_id
        ]);



        if(!empty($params['orderby'])){
            $query->addOrderBy($params['orderby']);
        }
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['=', 'deleted', self::DELETED_DEFAULT]);

        if (!empty($params['published_start'])) {
            $query->andFilterWhere(['>=', BaseNotice::tableName().'.published', strtotime($params['published_start']) ? strtotime($params['published_start']) : $params['published_start']]);
        }
        if (!empty($params['published_end'])) {
            $query->andFilterWhere(['<=', BaseNotice::tableName().'.published', strtotime($params['published_end']) ? strtotime($params['published_end']) : $params['published_end']]);
        }
//        return $query->createCommand()->getRawSql();
        return $dataProvider;
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchKnowledge($params)
    {
        $query = BaseNotice::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);
        if (!($this->load(['BaseNotice' => $params]) && $this->validate())) {
            return $dataProvider;
        }

        if(!empty($params['agent_scope']))
        {
            $query->orFilterWhere(['or like', 'range', 'all']);
            $scope = explode(',',$params['agent_scope']);

            foreach($scope as $v)
            {
                $query->orFilterWhere(['like', 'range', $v]);
            }

        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'app_id' => $this->app_id,
            'status' => $this->status,
            'modified' => $this->modified,
            'created' => $this->created,
            'recommend' => $this->recommend,
            'user_id' => $this->user_id
        ]);

        if(!empty($params['orderby'])){
            $query->addOrderBy($params['orderby']);
        }
        $query->andFilterWhere(['<>','type', self::TYPE_SYS_NOTICE])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['=', 'deleted', self::DELETED_DEFAULT]);
        if (!empty($params['published_start'])) {
            $query->andFilterWhere(['>=', BaseNotice::tableName().'.published', strtotime($params['published_start']) ? strtotime($params['published_start']) : $params['published_start']]);
        }
        if (!empty($params['published_end'])) {
            $query->andFilterWhere(['<=', BaseNotice::tableName().'.published', strtotime($params['published_end']) ? strtotime($params['published_end']) : $params['published_end']]);
        }
        if(!empty($params['range']))
        {
            $query->andFilterWhere(['=', 'range', 'all']);
            $query->orFilterWhere(['like', 'range', $params['range']]);
        }

//return $query->createCommand()->getRawSql();
        return $dataProvider;
    }


    public function findOneWithUser($id)
    {
        $query = $this->find()->where(['id' => $id,'status' => self::STATUS_PUBLISH,'deleted'=>self::DELETED_DEFAULT])->with('user')->with('links')->one();
        $info = $this->recordToArray($query);
        unset($info['user']['password'],$info['user']['auth_key']);

        $model = $this->findOne($id);
        $model->load(['BaseNotice'=>['clicks'=>$model->clicks+1]]);
        $model->save();
        return $info;
    }

    public function getLinks()
    {
        return $this->hasMany(TextLinks::className(),['notice_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(AuthUser::className(), ['uid' => 'user_id']);
    }

    public function findOneNotice($id)
    {
        $query = self::find()->where(['id' => $id])->with('links')->with('user')->one();
        return $this->recordToArray($query);
    }
}
