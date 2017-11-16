<?php


namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\common\exceptions\SqlException;

class BaseModel extends ActiveRecord
{
    const COL_STATUS_ENABLE = 1; //正常
    const COL_STATUS_DISABLE = -2; //禁用
    const COL_STATUS_DELETE = -1; //删除
    protected $ActiveDataProvider;

    /**
     * @param $code
     * @param array $data
     * @param string $msg
     * @throws \app\common\exceptions\SqlException
     */
    protected function setException($code,$data = array(),$msg = '')
    {
        throw new SqlException($code,$data,$msg);
    }

    /**
     * 统一处理更新时间
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            $attr = $this->getAttributes();
            if(array_key_exists('created',$attr) && array_key_exists('modified',$attr) && $this->isNewRecord){
                $this->modified = time();
                $this->created = time();
            }elseif(!$this->isNewRecord && array_key_exists('modified',$attr)){
                $this->modified = time();
            }
        }

        return true;
    }


    /**
     * 返回最后返回sql
     * @return string
     */
    public  function getLastSql()
    {
        return !empty(self::$command) && is_object(self::$command) ? self::$command->getRawSql() : '';
    }


    /**
     * 把activeRecord转成数组。
     * @param $activeRecord
     * @return array
     */
    public  function recordToArray($activeRecord)
    {
        $dataArr = [];
        if ($activeRecord instanceof \yii\db\ActiveRecord) {
            $dataArr = $activeRecord->toArray();
            self::finalToArr($activeRecord, $dataArr);
        } else {
            if (!is_array($activeRecord)) {
                return $activeRecord;
            }
            return self::recordListToArray($activeRecord);
        }
        return $dataArr;
    }


    /**
     * 列表转换
     * @param $activeRecordList
     */
    public  function recordListToArray($activeRecordList)
    {
        if (!is_array($activeRecordList)) {
            return $activeRecordList;
        }
        foreach ($activeRecordList as $key => $val) {
            if ($val instanceof \yii\db\ActiveRecord) {
                $activeRecordList[$key] = self::recordToArray($val);
            } else {
                $activeRecordList[$key] = self::recordListToArray($val);
            }
        }
        return $activeRecordList;
    }

    /**
     * 实体转数组
     * @param $record
     * @param $ret
     */
    public  function finalToArr($record, &$ret)
    {
        if ($record->getRelatedRecords()) {
            foreach ($record->getRelatedRecords() as $key => $val) {
                if ($val instanceof \yii\db\ActiveRecord) {
                    $ret[$key] = $val->toArray();
                    self::finalToArr($val, $ret[$key]);
                } else if (count($val) > 0) {
                    $ret[$key] = self::recordListToArray($val);
                } else {
                    $ret[$key] = [];
                }
            }
        } else {
            $ret = $record->toArray();
        }
    }




}