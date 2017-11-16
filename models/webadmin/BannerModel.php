<?php
namespace app\models\webadmin;

use Yii;
use app\models\webadmin\FileModel;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

/**
 * This is the model class for table "banner".
 *
 * @property integer $id
 * @property integer $file_id
 * @property string $title
 * @property integer $region
 * @property string $jump_url
 * @property integer $sort
 * @property integer $platform_id
 * @property integer $deleted
 * @property integer $created
 * @property integer $modified
 */
class BannerModel extends PublicModel
{
    // 内容状态
    const DISPLAY   = 1;  // 显示
    const HIDE      = 2;  // 隐藏
    const DELETED   = 3;  // 删除
    public static $ContentStatus = [
        self::DISPLAY   => "显示",
        self::HIDE      => "隐藏",
        self::DELETED   => "删除",
    ];

    // 微客多
    // 手机端官网基础配置
    const PHONE_WEBSITE_INDEX               = 1;  // 手机官网   首页
    const PHONE_WEBSITE_SOLUTION            = 2;  // 手机官网   解决方案
    const PHONE_WEBSITE_PRODUCT             = 3;  // 手机官网   产品中心
    const PHONE_WEBSITE_CASE                = 4;  // 手机官网   商户案例
    const PHONE_WEBSITE_CHANNEL             = 5;  // 手机官网   渠道招商
    const PHONE_WEBSITE_PRODUCT_MOVEMENT    = 6;  // 手机官网   产品动态
    const PHONE_WEBSITE_ABOUT_US            = 7;  // 手机官网   关于我们

    public static $WebAdminNavigation = [
        self::PHONE_WEBSITE_INDEX => "首页",
//        self::PHONE_WEBSITE_SOLUTION => "解决方案",
//        self::PHONE_WEBSITE_PRODUCT => "产品中心",
//        self::PHONE_WEBSITE_CASE => "商户案例",
//        self::PHONE_WEBSITE_CHANNEL => "渠道招商",
//        self::PHONE_WEBSITE_PRODUCT_MOVEMENT => "产品动态",
//        self::PHONE_WEBSITE_ABOUT_US => "关于我们",
    ];

    //  大象官网
    const ELEPHANT_WEBSITE_INDEX        = 1;  // 大象官网 首页
    const ELEPHANT_BUSINESS             = 2;  // 大象官网 大象商家
    const ELEPHANT_SERVICE_PROVIDER     = 3;  // 大象官网 大象服务商
    const ELEPHANT_PARTNER              = 4;  // 大象官网 大象伙伴
    const ELEPHANT_MERCHANT_CASE        = 5;  // 大象官网 商家案例
    const ELEPHANT_INFORMATION_CENTER   = 6;  // 大象官网 咨询中心
    const ELEPHANT_CHANNEL_COOPERATION  = 7;  // 大象官网 渠道合作
    const ELEPHANT_ABOUT_US             = 8;  // 大象官网 关于我们
    const ELEPHANT_PHONE_WEBSITE_INDEX  = 9;  // 大象手机官网 首页
    public static $ElephantSiteNavigation = [
        self::ELEPHANT_WEBSITE_INDEX        => "首页",
        self::ELEPHANT_BUSINESS             => "大象商家",
        self::ELEPHANT_SERVICE_PROVIDER     => "大象服务商",
        self::ELEPHANT_PARTNER              => "大象伙伴",
        self::ELEPHANT_MERCHANT_CASE        => "商家案例",
        self::ELEPHANT_INFORMATION_CENTER   => "咨询中心",
        self::ELEPHANT_CHANNEL_COOPERATION  => "渠道合作",
        self::ELEPHANT_ABOUT_US             => "关于我们",
        self::ELEPHANT_PHONE_WEBSITE_INDEX  => "手机官网 首页",
    ];

    const SCTEK_WEBSITE_INDEX           = 1;  // 盛灿官网 首页
    const SCTEK_PAY                     = 2;  // 盛灿官网 支付金融
    const SCTEK_WISDOM_ESTATES          = 3;  // 盛灿官网 智慧地产
    const SCTEK_ADVERTISING             = 4;  // 盛灿官网 盛灿广告
    const SCTEK_ELEPHANT_ORDER          = 5;  // 盛灿官网 大象点餐
    const SCTEK_WKD                     = 6;  // 盛灿官网 微客多
    const SCTEK_WISDOM_DINING_ROOM      = 7;  // 盛灿官网 微客多智慧餐厅
    const SCTEK_CODE                    = 8;  // 盛灿官网 微客多一品一码
    const SCTEK_WISDOM_CITY             = 9;  // 盛灿官网 智慧城市
    const SCTEK_WISDOM_TRAFFIC          = 10;  // 盛灿官网 智慧交通
    const SCTEK_WISDOM_TRADING_AREA     = 11;  // 盛灿官网 智慧商圈
    const SCTEK_MERCHANT_CASE           = 12;  // 盛灿官网 商户案例
    const SCTEK_NEWS                    = 13;  // 盛灿官网 新闻中心
    const SCTEK_ABOUT_US                = 14;  // 盛灿官网 公司概况
    const SCTEK_ABOUT_COLLEGE           = 15;  // 盛灿官网 盛灿学院
    const SCTEK_ABOUT_HUMANITY          = 16;  // 盛灿官网 人文盛灿
    const SCTEK_ABOUT_JOIN              = 17;  // 盛灿官网 加入盛灿
    public static $SctekSiteNavigation = [
        self::SCTEK_WEBSITE_INDEX       => "首页",
        self::SCTEK_PAY                 => "支付金融",
        self::SCTEK_WISDOM_ESTATES      => "智慧地产",
        self::SCTEK_ADVERTISING         => "盛灿广告",
        self::SCTEK_ELEPHANT_ORDER      => "大象点餐",
        self::SCTEK_WKD                 => "微客多",
        self::SCTEK_WISDOM_DINING_ROOM  => "微客多智慧餐厅",
        self::SCTEK_CODE                => "微客多一品一码",
        self::SCTEK_WISDOM_CITY         => "智慧城市",
        self::SCTEK_WISDOM_TRAFFIC      => "智慧交通",
        self::SCTEK_WISDOM_TRADING_AREA => "智慧商圈",
        self::SCTEK_MERCHANT_CASE       => "商家案例",
        self::SCTEK_NEWS                => "新闻中心",
        self::SCTEK_ABOUT_US            => "公司概况",
        self::SCTEK_ABOUT_COLLEGE       => "盛灿学院",
        self::SCTEK_ABOUT_HUMANITY      => "人文盛灿",
        self::SCTEK_ABOUT_JOIN          => "加入我们"
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['region', 'platform_id'], 'required'],
            [['sort', 'file_id', 'platform_id', 'region', 'deleted'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['file_id', 'title', 'jump_url'], "safe"]
        ];
    }

    /**
     * 搜索列表
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $query = self::find();

        if (!empty($params['select'])) {
            $query->select($params['select']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        if (!empty($params['file_id'])) {
            $query->andFilterWhere(["file_id" => $params['file_id']]);
        }

        if (!empty($params['deleted'])) {
            $query->andFilterWhere(["=", "deleted", $params["deleted"]]);
        } else {
            $query->andFilterWhere(["deleted" => [self::DISPLAY, self::HIDE]]);
        }

        if (!empty($params['region'])) {
            $query->andFilterWhere(["=", "region", $params['region']]);
        }

        if (!empty($params['platform_id'])) {
            $query->andFilterWhere(["=", "platform_id", $params['platform_id']]);
        }

        if (!empty($params['jump_url'])) {
            $query->andFilterWhere(["like", "jump_url", $params["jump_url"]]);
        }

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['array'])) {
            $query->asArray();
        }

        if (!empty($params['limit'])) {
            $data = $this->page($query, $params['limit']);
        } else {
            $data = $query->all();
        }

        return $data;
    }


    /**
     * 批量修改
     * @param $data
     * @return array
     */
    public function batch_care($data)
    {
        $error = $ins = [];
        if (isset($data['ins'])) {
            $arr = $this->cheImg($data['ins'], ['region', 'file_id', 'title'], 1);
            foreach ($arr as $k => $v) {
                if (!isset($v['msg'])) {
                    $v['created'] = isset($v['created']) ? $v['created'] : time();
                    $v['modified'] = isset($v['modified']) ? $v['modified'] : time();
                    $v['deleted'] = isset($v['deleted']) ? $v['deleted'] : 2;
                    $ins[] = $v;
                } else {
                    $error['errorIns'][] = ['code' => 10008, 'msg' => "数据 ".$k."：".$v['msg']];
                }
            }
            if (!empty($ins)) {
                $keys = array_keys($ins[0]);
                try {
                    $this->batchInsert($this->tableName(), $keys, $ins);
                    $error['Ins'] = ['code' => 0, 'msg' => '添加成功'];
                } catch (Exception $e) {
                    $error['Ins'] = ['code' => 10004, 'msg' => '添加数据失败'];
                }
            }
        }
        if (isset($data['up'])) {
            $arr = $this->cheImg($data['up'], ['region', 'file_id', 'title', 'id'], 1);
            // 更新操作
            foreach ($arr as $k => $v) {
                if (!isset($v['msg'])) {
                    $v['modified'] = isset($v['modified']) ? $v['modified'] : time();
                    try {
                        $this->updateAll($v, ['id' => $v['id']]);
                        $error['Up'][] = ['code' => 0, 'msg' => "数据 $k 更新成功"];
                    } catch (Exception $e) {
                        $error['errorUp'][] = ['code' => 10004, 'msg' => "数据 $k 更新数据失败"];
                    }
                } else {
                    $error['errorUp'][] = ['code' => 10008, 'msg' => "数据 ".$k."：".$v['msg']];
                }
            }
        }
        return $error;
    }


    /**
     * 验证图片参数
     * @param $images   图片组
     * @param $rule 规则组
     * @param $type 1、必须
     * @return array
     */
    private function cheImg($images, $rule, $type)
    {
        foreach ($images as $k => $v) {
            if ($type == 1) {
                $code = $this->Must($v, $rule);
                if ($code['code'] == 10008) {
                    $images[$k]['msg'] = $code['msg'];
                }
            }
        }
        return $images;
    }


    /**
     * 验证图片参数
     * @param $params   需要验证的数组
     * @param $rule
     * @return array
     */
    private function Must($params, $rule = array())
    {
        foreach ($params as $k => $v) {
            if (in_array($k, $rule)) {
                if (empty($v)) {
                    if ($k == 'file_id') {
                        $msg = '图片';
                    }
                    if ($k == 'title') {
                        $msg = '文字说明';
                    }
                    if ($k == 'jump_url') {
                        $msg = '跳转链接';
                    }
                    if ($k == 'region') {
                        $msg = '区域';
                    }
                    return ['code' => 10008, 'msg' => "缺少 $msg"];
                }
            }
        }
        return ['code' => 0, 'msg' => "验证通过!"];
    }

    /**
     * 获取图片
     */
    public function getFile()
    {
        return $this->hasOne(FileModel::className(),['file_id'=>'file_id']);
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
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $params = $params[self::formName()];

        $query->andFilterWhere([
            'id' => $this->id,
            'file_id' => $this->file_id,
            'title' => $this->title,
            'region' => $this->region,
            'jump_url' => $this->jump_url,
            'sort' => $this->sort,
            'platform_id' => $this->platform_id,
            'created' => $this->created,
            'modified' => $this->modified,
            'deleted' => self::DISPLAY,
        ]);

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        return $dataProvider;
    }

    public static $RegionCode = [
        "SCTEK_WEBSITE_INDEX" => self::SCTEK_WEBSITE_INDEX,
        "SCTEK_PAY" => self::SCTEK_PAY,
        "SCTEK_WISDOM_ESTATES" => self::SCTEK_WISDOM_ESTATES,
        "SCTEK_ADVERTISING" => self::SCTEK_ADVERTISING,
        "SCTEK_ELEPHANT_ORDER" => self::SCTEK_ELEPHANT_ORDER,
        "SCTEK_WKD" => self::SCTEK_WKD,
        "SCTEK_WISDOM_DINING_ROOM" => self::SCTEK_WISDOM_DINING_ROOM,
        "SCTEK_CODE" => self::SCTEK_CODE,
        "SCTEK_WISDOM_CITY" => self::SCTEK_WISDOM_CITY,
        "SCTEK_WISDOM_TRAFFIC" => self::SCTEK_WISDOM_TRAFFIC,
        "SCTEK_WISDOM_TRADING_AREA" => self::SCTEK_WISDOM_TRADING_AREA,
        "SCTEK_MERCHANT_CASE" => self::SCTEK_MERCHANT_CASE,
        "SCTEK_NEWS" => self::SCTEK_NEWS,
        "SCTEK_ABOUT_US" => self::SCTEK_ABOUT_US,
        "SCTEK_ABOUT_COLLEGE"        => self::SCTEK_ABOUT_COLLEGE,
        "SCTEK_ABOUT_HUMANITY"      => self::SCTEK_ABOUT_HUMANITY,
        "SCTEK_ABOUT_JOIN"          => self::SCTEK_ABOUT_JOIN
    ];
    public static function _getRegion($code){
        if (isset(self::$RegionCode[$code])) {
            return self::$RegionCode[$code];
        }
        return false;
    }
}