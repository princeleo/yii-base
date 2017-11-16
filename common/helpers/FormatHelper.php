<?php

namespace app\common\helpers;

use yii;

class FormatHelper
{
    /**
     *  Array 转 QueryString
     * @param array $params 支付参数
     * @return array
     */
    public static function ArrayToQueryString($params){
        $retData = "";
        foreach ($params as $k => $v) {
            $retData .= $k."=".$v."&";
        }
        return substr($retData, 0, -1);
    }

    /**
     *  Array 转 XML
     * @param array $Array
     * @return string $xml
     */
    public static function ArrayToXml($Array){
        $xml = "<root>";
        foreach ($Array as $key=>$val){
            if(is_array($val)){
                $xml.="<".$key.">".self::ArrayToXml($val)."</".$key.">";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        $xml.="</root>";
        return $xml;
    }

    /**
     *  Array 转 XML
     * @param array $xml
     * @return string $xml
     */
    public static function XmlToArray($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        $XmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($XmlString),true);

        return $val;
    }

    /**
     *  字符串是否为 XML 格式
     * @param string $str
     * @return bool
     */
    public static function _isXml($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            return true;
        }
    }
}