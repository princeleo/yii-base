<?php
/**
 * Created by PhpStorm.
 * User: lk2015
 * Date: 2017/1/3
 * Time: 16:21
 */

namespace app\common\widgets;

use yii\base\InvalidConfigException;
use yii\base\Widget;

class Html extends Widget
{
    public $html;
    /**
     * Initializes the pager.
     */
    public function init()
    {
        if ($this->html === null) {
            throw new InvalidConfigException('The "html" property must be set.');
        }
        if (!isset($this->html['view'])) {
            throw new InvalidConfigException('The "html" property must set view name.');
        }
    }

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run()
    {
        return $this->render($this->html['view'],['viewData' => $this->html]);
    }
}