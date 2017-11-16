<?php

namespace app\models\department;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use app\common\helpers\BaseHelper;

class Department extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '正常',
            self::STATUS_DISABLE => '禁用'
        ];
    }

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return '{{%base_department}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'name'], 'required','on' => 'create'],
            [[ 'pid', 'status', 'modified','created'], 'integer'],
            [['name'], 'string', 'max' => 50]
        ];
    }


    /**
     * @param $did
     */
    public function getDepartmentTree($did)
    {
        $row = BaseHelper::recordToArray($this->findOne($did));
        if(!empty($row['pid'])){
            $row['parent'] = BaseHelper::recordToArray($this->findOne($row['pid']));
        }

        return empty($row) ? [] : $row;
    }


    /**
     * 获取数据的结构树
     * @param $arr
     */
    public function getDepartmentAllTree($arr)
    {
        if(empty($arr) || !is_array($arr)) return [];

        $parents = $childs = array();
        foreach($arr as $val){
            if($val['pid'] == 0){
                $parents[$val['id']] = $val;
            }else{
                $childs[] = $val;
            }
        }

        foreach($parents as &$parent){
            $parent['childs'] = array();
            foreach($childs as $child){
                if($parent['id'] == $child['pid']){
                    $parent['childs'][$child['id']] = $child;
                }
            }
        }
        return $parents;
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
        $query = Department::find();

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'pid' => $this->pid,
            'status' => $this->status,
            'modified' => $this->modified,
            'created' => $this->created,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }



    public function findDepartment()
    {
        $query = $this->find()->where(['status' => self::STATUS_DEFAULT])->all();
        return $this->getDepartmentAllTree($this->recordListToArray($query));
    }
}