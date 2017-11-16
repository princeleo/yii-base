<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\common\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\base\Widget;
use yii\data\Pagination;

/**
 * LinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * LinkPager works with a [[Pagination]] object which specifies the totally number
 * of pages and the current page number.
 *
 * Note that LinkPager only generates the necessary HTML markups. In order for it
 * to look like a real pager, you should provide some CSS styles for it.
 * With the default configuration, LinkPager should look good using Twitter Bootstrap CSS framework.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LinkPager extends Widget
{
    /**
     * @var Pagination the pagination object that this pager is associated with.
     * You must set this property in order to make LinkPager work.
     */
    public $pagination;
    /**
     * @var array HTML attributes for the pager container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'pages pull-left'];
    /**
     * @var array HTML attributes for the link in a pager container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $linkOptions = ['style'=>'margin:0;padding:0;background-color:transparent;'];
    /**
     * @var string the CSS class for the "first" page button.
     */
    public $firstPageCssClass = 'first';
    /**
     * @var string the CSS class for the "last" page button.
     */
    public $lastPageCssClass = 'last';
    /**
     * @var string the CSS class for the "previous" page button.
     */
    public $prevPageCssClass = 'prev';
    /**
     * @var string the CSS class for the "next" page button.
     */
    public $nextPageCssClass = 'next';
    /**
     * @var string the CSS class for the active (currently selected) page button.
     */
    public $activePageCssClass = 'active pg-current';
    /**
     * @var string the CSS class for the disabled page buttons.
     */
    public $disabledPageCssClass = 'disabled';
    /**
     * @var integer maximum number of page buttons that can be displayed. Defaults to 10.
     */
    public $maxButtonCount = 10;
    /**
     * @var string|boolean the label for the "next" page button. Note that this will NOT be HTML-encoded.
     * If this property is false, the "next" page button will not be displayed.
     */
    public $nextPageLabel = '<i class="fa fa-angle-right"></i>';
    /**
     * @var string|boolean the text label for the previous page button. Note that this will NOT be HTML-encoded.
     * If this property is false, the "previous" page button will not be displayed.
     */
    public $prevPageLabel = '<i class="fa fa-angle-left"></i>';
    /**
     * @var string|boolean the text label for the "first" page button. Note that this will NOT be HTML-encoded.
     * Default is false that means the "first" page button will not be displayed.
     */
    public $firstPageLabel = '首页';
    /**
     * @var string|boolean the text label for the "last" page button. Note that this will NOT be HTML-encoded.
     * Default is false that means the "last" page button will not be displayed.
     */
    public $lastPageLabel = '末页';
    /**
     * @var boolean whether to register link tags in the HTML header for prev, next, first and last page.
     * Defaults to `false` to avoid conflicts when multiple pagers are used on one page.
     * @see http://www.w3.org/TR/html401/struct/links.html#h-12.1.2
     * @see registerLinkTags()
     */
    public $registerLinkTags = false;
    /**
     * @var boolean Hide widget when only one page exist.
     */
    public $hideOnSinglePage = true;

    public $showPageLabel = true;
    public $showRadix = true;
    public $showRadixOption = 3;


    /**
     * Initializes the pager.
     */
    public function init()
    {
        if ($this->pagination === null) {
            throw new InvalidConfigException('The "pagination" property must be set.');
        }
    }

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run()
    {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }
        echo $this->renderPageButtons();
        echo $this->showPageButtons();
    }

    protected function showPageButtons()
    {
        $buttons = [];
        $pageCount = $this->pagination->getPageCount();
        $totalCount = $this->pagination->totalCount;
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        //show page
        //$currentUrl = Yii::$app->request->getPathInfo().'&'.$this->pagination->pageSizeParam.'=';
        if ($this->showPageLabel !== false) {
            $buttons[] = '<input value="" class="page-goto">页<button type="button" class="btn new-btn-medium new-btn-primary" onclick="location.href=location.href+(location.href.match(\'[?]\') ? \'&\' : \'?\')+\'page=\'+$(\'.page-goto\').val()">跳转</button>';
            $buttons[] = '<div class="pages-text pull-right">共'.$pageCount.'页/'.$totalCount.'条</div>';
        }
        return Html::tag('div', implode("\n", $buttons), ['class'=>'pages-skip pull-right']);
    }

    /**
     * Registers relational link tags in the html header for prev, next, first and last page.
     * These links are generated using [[\yii\data\Pagination::getLinks()]].
     * @see http://www.w3.org/TR/html401/struct/links.html#h-12.1.2
     */
    protected function registerLinkTags()
    {
        $view = $this->getView();
        foreach ($this->pagination->getLinks() as $rel => $href) {
            $view->registerLinkTag(['rel' => $rel, 'href' => $href], $rel);
        }
    }

    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        if ($this->firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            if(!$this->showRadix){
                $buttons[] = $this->renderPageButton($i + 1, $i, 'page-number', false, $i == $currentPage);
            }else{
                if($i == $beginPage || $i == $endPage || ($i > $currentPage-$this->showRadixOption && $i < $currentPage+$this->showRadixOption)){
                    $is_radix = true;
                    $buttons[] = $this->renderPageButton($i + 1, $i, 'page-number', false, $i == $currentPage);
                }elseif(!isset($is_radix) || $is_radix){
                    $is_radix = false;
                    $buttons[] = $this->renderRadix('...', 'page-number');
                }
            }
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        if ($this->lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }

    protected function renderRadix($label, $class)
    {
        $options = ['class' => $class === '' ? null : $class];
        Html::addCssClass($options, $this->disabledPageCssClass);
        return Html::tag('li', Html::tag('label', $label), $options);
    }

    /**
     * Renders a page button.
     * You may override this method to customize the generation of page buttons.
     * @param string $label the text label for the button
     * @param integer $page the page number
     * @param string $class the CSS class for the page button.
     * @param boolean $disabled whether this page button is disabled
     * @param boolean $active whether this page button is active
     * @return string the rendering result
     */
    protected function renderPageButton($label, $page, $class, $disabled, $active)
    {
        $options = ['class' => $class === '' ? null : $class];
        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);

            return Html::tag('li', Html::tag('label', $label), $options);
        }
        $linkOptions = $this->linkOptions;
        $linkOptions['data-page'] = $page;

        return Html::tag('li', Html::a($label, $this->pagination->createUrl($page), $linkOptions), $options);
    }

    /**
     * @return array the begin and end pages that need to be displayed.
     */
    protected function getPageRange()
    {
        $currentPage = $this->pagination->getPage();
        $pageCount = $this->pagination->getPageCount();

        $beginPage = max(0, $currentPage - (int) ($this->maxButtonCount / 2));
        if (($endPage = $beginPage + $this->maxButtonCount - 1) >= $pageCount) {
            $endPage = $pageCount - 1;
            $beginPage = max(0, $endPage - $this->maxButtonCount + 1);
        }

        return [$beginPage, $endPage];
    }
}
