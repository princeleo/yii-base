<?php
/**
 * Author: MaChenghang
 * Date: 2015/06/17
 * Time: 14:19
 */

namespace app\common\vendor\captcha;

use app\common\cache\RedisCache;
use app\common\cache\Session;

class CaptchaLib {

    //随机因子
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ123456789';
    //验证码
    private $code;
    //验证码长度
    private $codelen = 4;
    //宽度
    private $width = 130;
    //高度
    private $height = 50;
    //图形资源句柄
    private $img;
    //指定的字体
    private $font ;
    //指定字体大小
    private $fontsize = 20;
    //指定字体颜色
    private $fontcolor;
    //缓存KEY
    public $cache_key;
    public $expire;
    public $is_num = true;

    public function __construct($cache_key,$expire = 600,$config = []) {
        $this->cache_key = $cache_key;
        $this->expire = $expire;
        if(isset($config['codelen'])) $this->codelen = $config['codelen'];
        if(isset($config['is_num'])) $this->is_num = $config['is_num'];
        $this->font = dirname(__FILE__).'/elephant.ttf';//注意字体路径要写对，否则显示不了图片
    }

    /**
     * 生成背景
     */
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
        imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
    }

    /**
     * 生成文字
     */
    private function createFont() {
        $_x = $this->width / $this->codelen;
        for ($i=0;$i<$this->codelen;$i++) {
            $this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
        }
    }

    /**
     * 生成线条、雪花
     */
    private function createLine() {
        //线条
        for ($i=0;$i<6;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }
        //雪花
        for ($i=0;$i<100;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }

    /**
     * 输出验证码图片
     */
    private function outPut() {
        ob_clean();
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    /**
     * 生成验证码图片
     */
    public function getImage() {
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->outPut();
    }

    /**
     * 生成随机码
     */
    public  function createCode() {
        if($this->is_num){
            $this->charset = '1234567890';
        }
        $_len = strlen($this->charset)-1;
        for ($i=0;$i<$this->codelen;$i++) {
            $this->code .= $this->charset[mt_rand(0,$_len)];
        }
        RedisCache::bulid()->set($this->cache_key,$this->code,'EX', $this->expire);
        return $this->code;
    }

    /**
     * 获取验证码
     */
    public function getCode() {
        return RedisCache::bulid()->get($this->cache_key);
    }
}
