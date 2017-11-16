<?php

namespace app\common;

use yii\base\Model;
use yii\base\InvalidParamException;

class BaseForm extends Model
{
    public $rules = [];
    private $attrFields = [];

    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }


    public function beforeValidate()
    {
        return true;
    }


    public function afterValidate()
    {
        return true;
    }

    public function rules()
    {
        return $this->rules;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name,$value)
    {
        $this->$name = $value;
    }

    /**
     * Returns the list of attribute names.
     * By default, this method returns all public non-static properties of the class.
     * You may override this method to change the default behavior.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return $this->attrFields;
    }

    public function setAttr($name)
    {
        $this->attrFields[] = $name;
    }
}