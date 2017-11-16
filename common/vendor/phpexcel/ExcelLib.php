<?php
/**
 * Created by PhpStorm.
 * User: lk2015
 * Date: 2016/12/29
 * Time: 13:56
 */
namespace app\common\vendor\phpexcel;

use yii\base\Exception;

require_once('PHPExcel.php');
require_once('PHPExcel/Autoloader.php');
require_once('PHPExcel/Reader/Excel5.php');

class ExcelLib {

    const HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
    const VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
    const HORIZONTAL_LEFT = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;

    /**
     * 表头
     *
     * @var array
     */
    protected $head = array();

    /**
     * 数据
     *
     * @var array
     */
    protected $data = array();

    /**
     * 表头样式
     *
     * @var array
     */
    protected $headStyle = array(
        'font' => array('bold'=>true),
        'alignment' => array('horizontal'=>\PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
    );

    /**
     * 文件类型
     *
     * @var string
     */
    protected $type = 'xlsx';

    /**
     * PHPExcel对象
     *
     * @var PHPExcel
     */
    protected $phpExcel;

    /**
     * 是否为多sheet文件
     * @var bool
     */
    private $isMultiSheet = false;

    /**
     * sheet个数
     * @var int
     */
    protected $sheetNum = 1;

    /**
     * sheet设置
     * @var array
     */
    private $sheetConf = [];

    /**
     * 汇总
     * @var array
     */
    private $summary = [];

    /**
     * 数据列表样式
     * @var array
     */
    private $dataStyle = [];

    /**
     * 当前行数
     * @var int
     */
    private $line_num = 0;

    /**
     * 列序列
     */
    protected static $column_serial = array(
        'A','B','C','D','E','F','G', 'H','I','J',
        'K','L','M','N', 'O','P','Q','R','S','T',
        'U', 'V','W','X','Y','Z','AA','AB','AC','AD',
        'AE', 'AF','AG','AH','AI','AJ','AK','AL','AM','AN',
        'AO', 'AP','AQ','AR','AS','AT','AU','AV','AW','AX',
    );

    private $_head = null;
    private $_headStyle = null;
    private $_data = null;
    private $_dataStyle = null;
    private $_sheetConf = null;
    /**
     * 财务结算报表，去除前面的点
     */
    public $point = false;

    /**
     * 将excel加载至数组中
     *
     * @param $filename
     * @return array
     * @throws PHPExcel_Reader_Exception
     */
    public static function loadToArray($filename)
    {
        $reader = \PHPExcel_IOFactory::createReader('Excel2007');

        $phpExcel = $reader->load($filename);

        return $phpExcel->getActiveSheet()->toArray();
    }

    /**
     * ExcelLib constructor.
     * @param array $head
     * @param int $sheetNum sheet数
     */
    public function __construct(array $head, $sheetNum=1)
    {
        $this->sheetNum = intval($sheetNum);
        if ($this->sheetNum >1) {
            $this->isMultiSheet = true;
        } else {
            if(!isset($head['head'])) {
                $head= [['head' => $head]];
            }
        }

        foreach ($head as $sheetIndex => $conf) {
            if (isset($conf['sheetConf'])) {
                $this->addSheetConf($conf['sheetConf'], $sheetIndex);
            }
            if (isset($conf['head'])) {
                $this->addHead($conf['head'], $sheetIndex);
            }
        }

        $this->phpExcel = new \PHPExcel();
        $this->defaultConfig();
    }

    /**
     * 设置当前活动sheet
     * @param int $index
     */
    public function setActiveSheetIndex($index=0)
    {
        $this->phpExcel->setactivesheetindex($index);
    }

    /**设置当前活动格式
     * @param string $index
     */
    public function setActiveSheetFormat($index='A1')
    {
        $this->phpExcel->getActiveSheet()->getStyle($index)->getNumberFormat()
            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    }

    /**
     * 创建sheet
     */
    public function createSheet()
    {
        $this->phpExcel->createSheet();
    }

    /**
     * 直接输出到浏览器（下载）
     *
     * @param $filename
     */
    public function download($filename)
    {
        $this->process();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');
        header ('Pragma: public');

        $write = $this->createWriter();
        $write->save('php://output');
    }

    /**
     * 直接写入文件
     *
     * @param $filename
     */
    public function save($filename)
    {
        $this->process();

        $write = $this->createWriter();
        $write->save($filename);
    }

    /**
     * 设置表头样式
     * @param array $style
     * @param int $sheetIndex
     */
    public function setHeadStyle($style, $sheetIndex=0)
    {
        $this->headStyle[$sheetIndex] = isset($this->headStyle[$sheetIndex]) ? array_merge($this->headStyle[$sheetIndex], $style) : $style;
    }

    /**
     * 设置头部
     * @param array $head
     * @param int $sheetIndex
     */
    public function addHead($head=[], $sheetIndex=0)
    {
        $this->head[$sheetIndex] = isset($this->head[$sheetIndex]) ? array_merge($this->head[$sheetIndex], $head) : $head;
    }

    /**
     * 设置sheet信息
     * @param array $sheetConf
     * @param int $sheetIndex
     */
    public function addSheetConf($sheetConf=[], $sheetIndex=0)
    {
        $this->sheetConf[$sheetIndex] = isset($this->sheetConf[$sheetIndex]) ? array_merge($this->sheetConf[$sheetIndex], $sheetConf) : $sheetConf;
    }

    /**
     * 设置sheet信息
     * @param array $summary
     * @param int $sheetIndex
     */
    public function addSummary($summary, $sheetIndex=0)
    {
        $this->summary[$sheetIndex][] =  $summary;
    }

    /**
     * 设置sheet信息
     */
    public function setSheetConf()
    {
        if (isset($this->_sheetConf['sheetTitle'])) {
            $this->phpExcel->getActiveSheet()->setTitle($this->_sheetConf['sheetTitle']);
        }
    }

    /**
     * 设置数据样式
     * @param array $style
     * @param int $sheetIndex
     */
    public function setDataStyle($style=[], $sheetIndex=0)
    {
        $this->dataStyle[$sheetIndex] = isset($this->dataStyle[$sheetIndex]) ? array_merge($this->dataStyle[$sheetIndex]) : $style;
    }

    /**
     * 设置文件类型
     *
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param array $line
     * @param int $sheetIndex
     * @throws Exception
     */
    public function add(array $line, $sheetIndex=0)
    {
        if (isset($this->head[$sheetIndex]) && count($line) != count($this->head[$sheetIndex])) {
            throw new Exception("line does't match head");
        }
        $this->data[$sheetIndex][] = $line;
    }

    /**
     * 处理数据
     */
    protected function process()
    {
        for ($i=0; $i < $this->sheetNum; $i++) {
            $this->phpExcel->createSheet();
            $this->phpExcel->setActiveSheetIndex($i);
            $this->line_num = 1;

            $this->_sheetConf = isset($this->sheetConf[$i]) ? $this->sheetConf[$i] : [];
            $this->setSheetConf();

            $this->_head = isset($this->head[$i]) ? $this->head[$i] : [];
            $this->_headStyle = isset($this->headStyle[$i]) ? $this->headStyle[$i] : [];
            $this->processHead();

            $this->_data = isset($this->data[$i]) ? $this->data[$i] : [];
            $this->_dataStyle = isset($this->dataStyle[$i]) ? $this->dataStyle[$i] : [];
            $this->processData();

            if (!empty($this->summary[$i])) {
                foreach ($this->summary[$i] as $item) {
                    $this->line_num += 3;
                    $this->_data = $item;
                    $this->processData();
                }
            }
        }
        $this->phpExcel->setActiveSheetIndex(0);
    }


    /**
     * 处理表头
     */
    protected function processHead()
    {
        if (!empty($this->_head) && is_array($this->_head)) {
            $actSheet = $this->phpExcel->getActiveSheet();

            $last_column = self::$column_serial[count($this->_head) - 1];
            $this->applyHeadFormStyle("A1:{$last_column}1", $this->_headStyle);

            foreach ($this->_head as $serial => $head) {
                $style = isset($head['style']) ? $head['style'] : [];

                $column = self::$column_serial[$serial];
                $this->applyColumnStyle($column, $style);

                $cell = "{$column}1";
                $this->applyHeadFormStyle($cell, $style);

                if (is_array($head)) {
                    $value = isset($head['value']) ? $head['value'] : '';
                } else {
                    $value = $head;
                }
                if (is_scalar($value) && null !== $value) {
                    $actSheet->setCellValue($cell, $value);
                }
            }
            $this->line_num += 1;
        }
    }

    /**
     * 列设置
     * @param $column
     * @param array $style
     */
    private function applyColumnStyle($column, $style=[])
    {
        $actSheet = $this->phpExcel->getActiveSheet();
        $style = array_merge($this->headStyle, $style);
        if (isset($style['width'])) {
            $actSheet->getColumnDimension($column)->setWidth($style['width']);
        } else {
            $actSheet->getColumnDimension($column)->setAutoSize();
        }
    }

    /**
     * 头部样式设置
     * @param $cell
     * @param $style
     */
    private function applyHeadFormStyle($cell, $style)
    {
        $actSheet = $this->phpExcel->getActiveSheet();
        $default = [
            'vertical' => self::VERTICAL_CENTER,
            'horizontal' => self::HORIZONTAL_CENTER,
            'borders' => array(
                'bottom'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'top'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'left'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'right'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
        ];
        $dataStyle = array_merge($default, $this->_headStyle);
        $dataStyle = array_merge($dataStyle, $style);
        $actSheet->getStyle($cell)->applyFromArray($dataStyle);
        $actSheet->getDefaultRowDimension()->setRowHeight(15);
    }

    private function applyDataFormStyle($cell, $style=[])
    {
        $actSheet = $this->phpExcel->getActiveSheet();

        $default = [
            'vertical' => self::VERTICAL_CENTER,
            'horizontal' => self::HORIZONTAL_CENTER,
            'borders' => array(
                'bottom'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'top'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'left'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'right'     => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
        ];
        $dataStyle = array_merge($default, $this->_headStyle);
        $dataStyle = array_merge($dataStyle, $this->_dataStyle);
        $dataStyle = array_merge($dataStyle, $style);
        if (!empty($dataStyle)) {
            $actSheet->getStyle($cell)->applyFromArray($dataStyle);
            if (isset($dataStyle['vertical'])) {
                $actSheet->getStyle($cell)->getAlignment()->setVertical($dataStyle['vertical']);
            }
            if (isset($dataStyle['horizontal'])) {
                $actSheet->getStyle($cell)->getAlignment()->setHorizontal($dataStyle['horizontal']);
            }
        }
        $actSheet->getDefaultRowDimension()->setRowHeight(15);
    }

    /**
     * 导出行
     * @param $line
     */
    private function _processLine($line)
    {
        $actSheet = $this->phpExcel->getActiveSheet();

        $line_num = $this->line_num;
        $maxStep = 1;
        foreach ($line as $serial => $data) {//每列
            $column = self::$column_serial[$serial];
            if (is_array($data)) {
                $i = 0;

                foreach ($data as $key=> $item) {//合并单元格
                    $cell = $column.($line_num+$i);
                    if (is_array($item)) {
                        $value = isset($item['value']) ? $item['value'] : null;
                    } else {
                        $value = $item;
                    }
                    $rowStep = empty($item['rows']) ? 1 : intval($item['rows']);
                    $i += $rowStep;

                    if ($rowStep > 1) {
                        $columnEnd = $column.($line_num+$i-1);
                        $this->applyDataFormStyle("{$cell}:{$columnEnd}", isset($data['style'])?$data['style']:[]);
                        $actSheet->mergeCells("{$cell}:{$columnEnd}");
                    } else {
                        $columnStep = empty($item['columns']) ? 1 : intval($item['columns']);
                        if ($columnStep > 1) {
                            $columnEnd = self::$column_serial[$serial+$columnStep-1].$line_num;
                            $this->applyDataFormStyle("{$cell}:{$columnEnd}", isset($data['style'])?$data['style']:[]);
                            $actSheet->mergeCells("{$cell}:{$columnEnd}");
                        } else {
                            $this->applyDataFormStyle($cell, isset($data['style'])?$data['style']:[]);
                        }
                    }
                    if (is_scalar($value) && null !== $value) {
                        $actSheet->setCellValue($cell, $this->str_filter($value));
                    }
                }

                if ($maxStep < $i) {
                    $maxStep = $i;
                }
            } else {
                $value = $data;
                $cell = "{$column}{$line_num}";
                $this->applyDataFormStyle($cell, []);
                if (is_scalar($value) && null !== $value) {
                    $actSheet->setCellValue($cell, $this->str_filter($value));
                }
            }
        }
        $this->line_num += $maxStep;
    }

    /**
     * @return bool
     */
    protected function processData()
    {
        if (empty($this->_data)) {
            return true;
        }
        foreach ($this->_data as $line) { //每行
            $this->_processLine($line);
        }
    }

    /**
     * 默认设置
     */
    protected function defaultConfig()
    {
        $actSheet = $this->phpExcel->setActiveSheetIndex(0);
        $actSheet->getDefaultRowDimension()->setRowHeight(20);
    }

    /**
     * 创建抄写员
     *
     * @return PHPExcel_Writer_IWriter
     */
    protected function createWriter()
    {
        switch ($this->type) {
            case 'xls':
                $writer = \PHPExcel_IOFactory::createWriter($this->phpExcel, 'Excel5');
                break;
            case 'xlsx':
            default:
                $writer = \PHPExcel_IOFactory::createWriter($this->phpExcel, 'Excel2007');
        }
        return $writer;
    }

    /**
     * 特殊字符串过滤
     *
     * @param $value
     *
     * @return string
     */
    protected function str_filter($value)
    {
        if ((is_numeric($value) && strlen($value) > 9) ||
            preg_match('/^\s*=/', $value) ||
            preg_match('/^=͟͟͞/', $value)) {
            if($this->point == true){
                return "{$value}";
            }else{
                return "`{$value}";
            }
        }
        return $value;
    }
}