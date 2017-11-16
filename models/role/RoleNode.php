<?php

namespace app\models\role;
use app\common\helpers\BaseHelper;
use Yii;
use app\models\BaseModel;
use app\common\errors\BaseError;

class RoleNode extends BaseModel
{
    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return '{{%auth_role_node}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'rid','app_id','pid','pname','ptitle','controller','methods'], 'required','on' => 'add'],
            [['rid','pid','modified','created'], 'integer'],
            [['app_id','controller','methods'],'string'],
            [['pname'], 'string','max' => 20],
            [['ptitle'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }


    /**
     * 更新权限
     */
    public function updateRoleNode($rid,$appId,$nodeTree)
    {
        if(!is_array($nodeTree) || empty($rid)) return false;
        $this::deleteAll('rid=:rid ',['rid'=>$rid]);
        $sqlData = array();
        foreach($nodeTree as $key =>$node){
            $keys = explode('||',$key);

            $data = array(
                'rid' => $rid,
                'app_id' => '"'.$keys[4].'"',
                'pid' => $keys[0],
                'pname' => '"'.$keys[1].'"',
                'ptitle' => '"'.$keys[2].'"',
                'controller' => '"'.$keys[3].'"',
                'methods' => '"'.implode(',',$node).'"',
                'created' => time(),
                'modified' => time()
            );
            /*if(!$this->load(['RoleNode'=>$data]) || !$this->save()){
                return false;
            }*/
            $sqlData[] = '('.implode(',',$data).')';
        }

        if(empty($sqlData)) return true;

        $sql = "INSERT INTO ".self::tableName()." (`rid`,`app_id`,`pid`,`pname`,`ptitle`,`controller`,`methods`,`created`,`modified`) VALUES ".implode(',',$sqlData);
        if(!Yii::$app->db->createCommand($sql)->query()){
            return false;
        }

        return true;
    }
}