<?php

namespace app\common\vendor\request;

use app\common\log\Log;
use Yii;

/**
 * 发送http或https请求, 返回响应内容.
 *
 * @requires cURL
 * @author sangechen
 *
 */
class HttpClient
{
	protected $sRequestMethod;
	const GET_REQUEST = 'GET';
	const POST_REQUEST = 'POST';

	protected $sGateUrl;

	protected $sQueryString;

	protected $arrProxyOptions;

	protected $arrSslOptions;

	protected $arrHeaders;

	protected $arrCookies;

	protected $iTimeout;

	protected $iCurlErrno;
	protected $sCurlErrMsg;

	protected $iResponseCode;
	protected $sResponseBody;
	protected $arrCurlInfo;

	public function __construct()
	{
		$this->init();
	}

	protected function init()
	{
		$this->sRequestMethod = self::GET_REQUEST;
		$this->sGateUrl = 'http://localhost/cgi';
		$this->sQueryString = '';
		$this->arrProxyOptions = array();
		$this->arrSslOptions = array();
		$this->arrHeaders = array();
		$this->arrCookies = array();
		$this->iTimeout = 3;
		$this->iCurlErrno = 0;
		$this->sCurlErrMsg = '';
		$this->iResponseCode = 0;
		$this->sResponseBody = '';
		$this->arrCurlInfo = array();
	}

	/**
	 * @param string $requestMethod HttpClient::GET_REQUEST | HttpClient::POST_REQUEST
	 */
	public function setMethod($requestMethod)
	{
		$this->sRequestMethod = $requestMethod;
	}

	/**
	 * 设置请求cgi地址, 不带请求串(即不带'?').
	 * 同时初始化$this
	 * @param string $gateUrl
	 */
	public function setGateURL($gateUrl)
	{
		$this->init();

		$this->sGateUrl = $gateUrl;
	}

	/**
	 * 设置请求串, 使用http_build_query()生成
	 * @param string $queryString
	 */
	public function setQueryString($queryString)
	{
		$this->sQueryString = $queryString;
	}

	/**
	 * 返回GET形式的请求URL
	 * @return string 请求URL
	 */
	public function getRequestURL()
	{
		if (!empty($this->sQueryString))
		{
			return $this->sGateUrl . '?' . $this->sQueryString;
		}
		else
		{
			return $this->sGateUrl;
		}
	}

	const HTTP_PROXY1 = '172.23.28.199:8080';//电信
	const HTTP_PROXY2 = '172.27.28.234:8080'; //电信
	const HTTP_PROXY3 = '10.133.0.241:8080'; //联通

	/**
	 * 设置代理
	 * @param string $proxyHost CURLOPT_PROXY: "host[:port]"
	 * @param string $proxyAuth CURLOPT_PROXYUSERPWD: "user:pass"
	 * @param int $proxyType CURLPROXY_HTTP (default) or CURLPROXY_SOCKS5
	 * @param int $proxyAuthType CURLAUTH_BASIC and CURLAUTH_NTLM
	 * @param bool $tunnel CURLOPT_HTTPPROXYTUNNEL: 是否tunnel方式使用代理
	 */
	public function setProxy($proxyHost, $proxyAuth=null,
							 $proxyType=null, $proxyAuthType=null,
							 $tunnel = false)
	{
		$this->arrProxyOptions['proxyhost'] = $proxyHost;
		$this->arrProxyOptions['proxyauth'] = $proxyAuth;
		$this->arrProxyOptions['proxytype'] = $proxyType;
		$this->arrProxyOptions['proxyauthtype'] = $proxyAuthType;
		$this->arrProxyOptions['tunnel'] = $tunnel;
	}

	/**
	 * 设置证书信息, 证书文件包含了key
	 * @param string $certFile 证书文件路径(最好使用绝对路径)
	 * @param string $certPasswd 使用证书或key需要的密码
	 * @param string $certType "PEM"(默认) | "DER"
	 */
	public function setSslCert($certFile, $certPasswd, $certType="PEM")
	{
		$this->arrSslOptions['cert'] = $certFile;
		$this->arrSslOptions['certtype'] = $certType;
		$this->arrSslOptions['certpasswd'] = $certPasswd;
		$this->arrSslOptions['verifyhost'] = 1;
	}

	/**
	 * 设置CA证书路径
	 * @param string $caFile
	 */
	public function setSslCaInfo($caFile)
	{
		$this->arrSslOptions['cainfo'] = $caFile;
		$this->arrSslOptions['verifypeer'] = true;
	}

	/**
	 * 合并$headers到当前请求头
	 * @param array $headers array('Host' => 'www.xxx.com') : 'Host: www.xxx.com'
	 */
	public function addHeaders($headers)
	{
		$this->arrHeaders = array_merge($this->arrHeaders, $headers);
	}

	/**
	 * 合并$cookies到当前请求cookie
	 * @param array $cookies array('fruit' => 'apple', 'colour' => 'red') : 'Cookie: fruit=apple; colour=red'
	 */
	public function addCookies($cookies)
	{
		$this->arrCookies = array_merge($this->arrCookies, $cookies);
	}

	/**
	 * 设置请求超时
	 * @param int $timeout
	 */
	public function setTimeout($timeout)
	{
		$this->iTimeout = $timeout;
	}

	/**
	 * 初始化curl, 发送请求, 记录响应和错误.
	 * @return bool ('http_code' == 200)
	 */
	public function execute()
	{
		//启动一个CURL会话
		$ch = curl_init();

		// 获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($this->sRequestMethod == self::POST_REQUEST)
		{
			//发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样
			curl_setopt($ch, CURLOPT_URL, $this->sGateUrl);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->sQueryString);
		}
		else
		{
			curl_setopt($ch, CURLOPT_URL, $this->getRequestURL());
		}

		if (isset($this->arrProxyOptions['proxyhost'])) //初始化代理相关设置
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->arrProxyOptions['proxyhost']);
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->arrProxyOptions['proxyauth']);
			curl_setopt($ch, CURLOPT_PROXYTYPE, $this->arrProxyOptions['proxytype']);
			curl_setopt($ch, CURLOPT_PROXYAUTH, $this->arrProxyOptions['proxyauthtype']);

			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $this->arrProxyOptions['tunnel']);
		}

		if (isset($this->arrSslOptions['cert'])) //初始化ssl证书相关设置
		{
			curl_setopt($ch, CURLOPT_SSLCERT, $this->arrSslOptions['cert']);
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, $this->arrSslOptions['certtype']);
			curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->arrSslOptions['certpasswd']);

			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->arrSslOptions['verifyhost']);
		}

		if (isset($this->arrSslOptions['cainfo'])) //初始化CA相关设置
		{
			curl_setopt($ch, CURLOPT_CAINFO, $this->arrSslOptions['cainfo']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->arrSslOptions['verifypeer']);
		}
		else
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		//设置Header
		if (!empty($this->arrHeaders))
		{
			$headers = array();
			foreach ($this->arrHeaders as $key => $value)
			{
				$headers[] = "$key: $value";
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		//设置Cookie
		if (!empty($this->arrCookies))
		{
			$cookies = array();
			foreach ($this->arrCookies as $key => $value)
			{
				$encodedValue = urlencode($value);
				$cookies[] = "$key=$encodedValue";
			}
			curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
		}

		// 设置curl允许执行的最长秒数
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->iTimeout);

		// 执行操作
		$this->sResponseBody = curl_exec($ch);
		$this->iCurlErrno = curl_errno($ch);
		$this->sCurlErrMsg = curl_error($ch);
		$this->arrCurlInfo = curl_getinfo($ch);
		$this->iResponseCode = (isset($this->arrCurlInfo['http_code'])?
				intval($this->arrCurlInfo['http_code']) : 0);

		// 释放资源
		curl_close($ch);

		//return $this->sResponseBody;
		return ($this->iResponseCode == 200);
	}

	/**
	 * 返回curl执行的错误码
	 * @return int curl_errno()
	 */
	public function getCurlErrno()
	{
		return $this->iCurlErrno;
	}

	/**
	 * 返回curl执行的错误信息
	 * @return string curl_error()
	 */
	public function getCurlErrMsg()
	{
		return $this->sCurlErrMsg;
	}

	/**
	 * 返回响应的HTTP Code, 正常返回 200
	 * @return int
	 */
	public function getResponseCode()
	{
		return $this->iResponseCode;
	}

	/**
	 * 返回响应内容
	 * @return string
	 */
	public function getResponseBody()
	{
		return $this->sResponseBody;
	}


    /**
     * 直接发起post请求
     * @param string $reqURI
     * @param string $strHttpParam
     * @param string $resp
     * @param array $server
     * @param int $timeout
     * @return int
     */
    public static function CallCURLPOST($reqURI, $strHttpParam, &$resp, $server, $timeout = 3)
    {
        $host = empty($server['ip']) ? '' : $server['ip'];
        $port = empty($server['port']) ? '' : $server['port'];

        $ch = curl_init();
        if ($ch === false) {
            $resp = 'curl_init error';
            return -1;
        }

        //若不指定host，$host需要传入完整的请求路径，带http://
        if (empty($host)) {
            $reqURL = $reqURI;
        } else if (empty($port)) {
            $reqURL = 'http://' . $host . $reqURI;
        } else {
            $reqURL = 'http://' . $host . ':' . $port . $reqURI;
        }

        curl_setopt($ch, CURLOPT_URL, $reqURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strHttpParam);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        if(YII_DEBUG) Log::begin('=======CURL POST BEGIN==========',__METHOD__);
        $res = curl_exec($ch);
        $response = curl_getinfo($ch);
        if(YII_DEBUG) Log::end('=======CURL POST END==========',__METHOD__);

        if ($res === false) {
            $resp = curl_error($ch) . ' errno = ' . curl_errno($ch) . ' response: ' . str_replace("\n", "", print_r($response, true));
            curl_close($ch);
            return -1;
        }
        $resp = $res;
        curl_close($ch);
        return 0;
    }

    /**
     * CURL请求封装函数
     * 若不指定host，这需要传入完整的请求路径，带http://
     * @param string $reqURI
     * @param array $arrayHttpParam
     * @param string $resp
     * @param array $server
     * @param int $timeout
     * @return int  成功 0 没有返回 -1 返回http code不为200 -2
     */
    public static function CallCURL($reqURI, $arrayHttpParam, &$resp, $server, $timeout = 3)
    {
        $host = empty($server['ip']) ? '' : $server['ip'];
        $port = empty($server['port']) ? '' : $server['port'];

        $ch = curl_init();

        foreach ($arrayHttpParam as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        //若不指定host，$host需要传入完整的请求路径，带http://
        if (empty($host)) {
            $reqURL = $reqURI;
        } else if (empty($port)) {
            $reqURL = 'http://' . $host . $reqURI;
        } else {
            $reqURL = 'http://' . $host . ':' . $port . $reqURI;
        }


        curl_setopt($ch, CURLOPT_URL, $reqURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);

        $res = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if ($res == NULL) {
            return -1;
        } else if ($responseCode != "200") {
            return -2;
        }
        $resp = $res;
        return 0;
    }

}
