<?php
namespace Bitrix\KdaImportexcel;

class HttpClient extends \Bitrix\Main\Web\HttpClient
{
	protected static $mProxyList = null;
	protected static $useProxy = true;
	protected static $notAcceptHeaders = false;
	protected static $arDomainsConnect = array();
	var $lastError = '';
	var $lastErrorHost = '';
	
	public function __construct(array $options = null)
	{
		if($options['socketTimeout']) $options['socketTimeout'] = min($options['socketTimeout'], ($options['socketTimeout'] > 1800 ? 60 : 15));
		if($options['useProxy']===false) self::$useProxy = false;
		parent::__construct($options);
	}
	
	public function mInitProxyParams()
	{
		if(!isset(self::$mProxyList))
		{
			$moduleId = IUtils::$moduleId;
			$arProxies = \KdaIE\Utils::Unserialize(\Bitrix\Main\Config\Option::get($moduleId, 'PROXIES'));
			if(!is_array($arProxies)) $arProxies = array();
			if(count($arProxies)==0)
			{
				$arProxies[] = array(
					'HOST' => \Bitrix\Main\Config\Option::get($moduleId, 'PROXY_HOST', ''), 
					'PORT' => \Bitrix\Main\Config\Option::get($moduleId, 'PROXY_PORT', ''), 
					'USER' => \Bitrix\Main\Config\Option::get($moduleId, 'PROXY_USER', ''), 
					'PASSWORD' => \Bitrix\Main\Config\Option::get($moduleId, 'PROXY_PASSWORD', '')
				);
			}
			foreach($arProxies as $k=>$v)
			{
				if(!$v['HOST'] || !$v['PORT']) unset($arProxies[$k]);
			}
			self::$mProxyList = array_values($arProxies);
		}

		while(count(self::$mProxyList) > 0)
		{
			$key = rand(0, count(self::$mProxyList) - 1);
			$p = self::$mProxyList[$key];
			if(!array_key_exists('CHECKED', $p))
			{
				if($fp = fsockopen($p['HOST'], $p['PORT'], $errno, $errstr, 3))
				{
					self::$mProxyList[$key]['CHECKED'] = true;
					fclose($fp);
				}
				else
				{
					unset(self::$mProxyList[$key]);
					self::$mProxyList = array_values(self::$mProxyList);
					continue;
				}
			}
			$this->setProxy($p['HOST'], $p['PORT'], $p['USER'], $p['PASSWORD']);
			return $p;
		}
		return false;
	}
	
	public function cdownload($url, $filePath)
	{
		$this->lastError = $this->lastErrorHost = '';
		$arUrl = parse_url(ToLower($url));
		$domain = $arUrl['scheme'].'://'.$arUrl['host'];
			
		if(preg_match('/^(https?:\/\/)([^:]*):(.*)@(.*\/.*)$/is', $url, $m))
		{
			$this->setHeader('Authorization', 'Basic '.base64_encode($m[2].':'.$m[3]));
			$url = $m[1].$m[4];
		}
		elseif(preg_match('/^(https?:\/\/)([^:]*)@(.*\/.*)$/is', $url, $m))
		{
			$this->setHeader('Authorization', 'Basic '.base64_encode($m[2].':'));
			$url = $m[1].$m[3];
		}

		if(self::$useProxy && ($p = $this->mInitProxyParams()) && preg_match('/^\s*https:/i', $url) && ($res = $this->mDownloadCurl($url, $filePath, $p))) return $res;
		
		if(!$this->isHostAvailable($domain))
		{
			return $this->mDownloadCurl($url, $filePath);
		}
		
		try{
			/*
			//big waste of time
			if(is_callable(array($this, 'head')))
			{
				$ob = new HttpClient();
				$ob->head($url);
			}
			*/

			$res = $this->parentDownload($url, $filePath);
			if(in_array($this->getStatus(), array(301, 302))) return $res;
		}catch(\Exception $ex){
			self::$notAcceptHeaders = true;
			return $this->mDownloadCurl($url, $filePath);
		}
		

		
		//$filePath2 = \Bitrix\Main\IO\Path::convertPhysicalToLogical($filePath);
		$filePath2 = \Bitrix\Main\IO\Path::convertLogicalToPhysical($filePath);
		if(!file_exists($filePath2) || filesize($filePath2)==0 || in_array($this->getStatus(), array(426, 505)))
		{
			if(file_exists($filePath2)) unlink($filePath2);
			$res = $this->mDownloadCurl($url, $filePath);
		}
		return $res;
	}
	
	public function mDownloadCurl($url, $filePath, $p=array(), $loop=0)
	{
		if(function_exists('curl_init'))
		{
			$arUrl = parse_url(ToLower($url));
			$domain = $arUrl['scheme'].'://'.$arUrl['host'];
			if(!$this->isHostAvailable($domain, true)) return false;
			$hostAvailablePrev = $this->isHostAvailable($domain);
			
			$arOrigHeaders = array();
			if(is_callable(array($this, 'getRequestHeaders'))) $arOrigHeaders = $this->getRequestHeaders()->toArray();
			elseif(isset($this->requestHeaders)) $arOrigHeaders = $this->requestHeaders->toArray();
			$arHeaders = array();
			$arSHeaders = array();
			foreach($arOrigHeaders as $header)
			{
				foreach($header["values"] as $value)
				{
					$arHeaders[] = $header["name"] . ": ".$value;
					$arSHeaders[$header["name"]] =  $value;
				}
			}
			$cookies = '';
			if(class_exists('\Bitrix\Main\Web\Http\Response'))
			{
				$this->response = new \Bitrix\Main\Web\Http\Response(0);
			}
			if(is_callable(array($this, 'getRequestHeaders'))) $cookies = $this->getRequestHeaders()->get('Cookie');
			elseif(isset($this->requestCookies)) $cookies = $this->requestCookies->toString();
			
			CheckDirPath($filePath);
			//$filePath2 = \Bitrix\Main\IO\Path::convertPhysicalToLogical($filePath);
			$filePath2 = \Bitrix\Main\IO\Path::convertLogicalToPhysical($filePath);
			$f = fopen($filePath2, 'w');
			$timeStart = microtime(true);
			$ch = curl_init();
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_URL,$url);
			if(isset($p['HOST']) && $p['HOST']) curl_setopt($ch, CURLOPT_PROXY, $p['HOST'].':'.$p['PORT']);
			if(isset($p['USER']) && $p['USER']) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $p['USER'].':'.$p['PASSWORD']);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->redirect);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $arHeaders);
			if($arSHeaders['User-Agent']) curl_setopt($ch, CURLOPT_USERAGENT, $arSHeaders['User-Agent']);
			if(strlen($cookies) > 0) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->getConnectTimeoutByHost($domain, $hostAvailablePrev));
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->streamTimeout);
			curl_setopt($ch, CURLOPT_FILE, $f);
			if(!self::$notAcceptHeaders) curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'mCurlGetHeaders'));
			$res = curl_exec($ch);
			curl_close($ch);
			fclose($f);

			$connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
			if($connectTime===false) $connectTime = microtime(true) - $timeStart;
			$connectTime = round($connectTime, 6);
			
			$this->status = $this->mCurlStatus = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
			if($this->response && is_callable(array($this->response, 'getHeadersCollection')) && is_callable(array($this->response->getHeadersCollection(), 'setStatus')))
			{
				$this->response->getHeadersCollection()->setStatus($this->status);
			}
			
			$arUrl = parse_url(ToLower($url));
			$domain = $arUrl['scheme'].'://'.$arUrl['host'];
			self::setDomainConnect($domain, $connectTime);
			if(!$this->isHostAvailable($domain))
			{
				if($hostAvailablePrev && $loop < 2) return self::mDownloadCurl($url, $filePath, $p, $loop + 1);
				return $this->getFileFromAddon($url, $filePath2);
			}
			
			if($newRes = $this->checkBlockAnswer($url, $filePath2)) $res = $newRes;

			return $res;
		}
		return false;
	}
	
	public function getConnectTimeoutByHost($host, $available=true)
	{
		$timeout = ($available ? $this->socketTimeout : 2);
		if(array_key_exists($host, self::$arDomainsConnect))
		{
			list($prevRes, $prevTime) = explode('|', self::$arDomainsConnect[$host]);
			if($available) $timeout = max($timeout, (float)$prevTime + 5);
			else $timeout = (float)$prevTime*2 + 2;
			$timeout = ceil($timeout);
		}
		//file_put_contents(dirname(__FILE__).'/test.txt', print_r(self::$arDomainsConnect, true)."\r\n".$timeout.' - '.($available ? 1 : 0).' - '.$prevTime.' - '.(float)$prevTime."\r\n---------\r\n", FILE_APPEND);
		return $timeout;
	}
	
	public function isHostAvailable($host, $attempt=false)
	{
		if(array_key_exists($host, self::$arDomainsConnect) && /*self::$arDomainsConnect[$host]===false*/ substr(self::$arDomainsConnect[$host], 0, 1)=='f')
		{
			if($attempt)
			{
				list($prevRes, $prevTime) = explode('|', self::$arDomainsConnect[$host]);
				if((int)substr($prevRes, 1) < 3) return true;
				self::setDomainConnect($host, false);
			}
			$this->lastError = 'NOT_CONNECTING';
			$this->lastErrorHost = $host;
			return false;
		}
		return true;
	}
	
	public function getLastError()
	{
		if(!$this->lastError)
		{
			if($this->getStatus()==404) return 'STATUS_404';
			elseif(preg_match('/^[45]\d{2}$/', $this->getStatus())) return 'STATUS_'.$this->getStatus();
		}
		return $this->lastError;
	}
	
	public function getLastErrorHost()
	{
		return $this->lastErrorHost;
	}
	
	public static function setDomainConnect($domain, $connTime)
	{
		$successConn = (bool)($connTime!==false && $connTime > 0);
		$prefix = ($successConn===false ? 'f' : 't');
		$cnt = 1;
		if(array_key_exists($domain, self::$arDomainsConnect))
		{
			list($prevRes, $prevTime) = explode('|', self::$arDomainsConnect[$domain]);
			if(!$successConn) $connTime = $prevTime;
			if(substr($prevRes, 0, 1)==$prefix)
			{
				$cnt += (int)substr($prevRes, 1);
				if($cnt > 100) $cnt = $cnt%100;
			}
		}
		self::$arDomainsConnect[$domain] = $prefix.$cnt.'|'.$connTime;
	}
	
	public static function setDomainsConnect($arDomainsConnect)
	{
		if(is_array($arDomainsConnect))
		{
			self::$arDomainsConnect = $arDomainsConnect;
		}
	}
	
	public static function getDomainsConnect()
	{
		return self::$arDomainsConnect;
	}
	
	public function mCurlGetHeaders($ch, $header)
	{
		$len = mb_strlen($header);
		$header = explode(':', $header, 2);
		if(count($header) < 2) return $len;

		$headerName = trim($header[0]);
		$headerValue = trim($header[1]);
		if(ToLower($headerName)=='set-cookie')
		{
			if(isset($this->responseCookies)) $this->responseCookies->addFromString($headerValue);
			else $this->getHeaders()->add('set-cookie', array_map('trim', explode(';', $headerValue)));
		}
		
		if(strpos($headerName, "\0") === false && preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $headerName)
			&& strpos($headerValue, "\0") === false && preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/', $headerValue))
		{
			if(isset($this->responseHeaders)) $this->responseHeaders->add($headerName, $headerValue);
			else $this->getHeaders()->add($headerName, $headerValue);
		}
		return $len;
	}
	
    public function getStatus()
    {
        if(isset($this->mCurlStatus)) return $this->mCurlStatus;
        return parent::getStatus();
    }
	
	public function parentDownload($url, $filePath)
	{
		$res = parent::download($url, $filePath);
		$filePath2 = \Bitrix\Main\IO\Path::convertLogicalToPhysical($filePath);
		if($newRes = $this->checkBlockAnswer($url, $filePath2)) $res = $newRes;
		
		return $res;
	}
	
	public function checkBlockAnswer($url, $filePath)
	{
		//file_put_contents(dirname(__FILE__).'/test.txt', $this->status."\r\n".file_get_contents($filePath));
		
		/*
		if($this->status==307 && $this->getHeaders()->get('location')==$url)
		{
			$c = file_get_contents($filePath);
			if(strpos($c, 'blank')!==false)
			{
				$this->getFileFromAddon($url, $filePath);
			}
		}
		*/

		if($this->status==200 && $this->getHeaders()->get('content-type')=='text/html')
		{
			$c = file_get_contents($filePath);
			if(strpos($c, 'get_cookie_spsc_encrypted_part')!==false)
			{
				return $this->getFileFromAddon($url, $filePath);
			}
		}
		return false;
	}
	
	public function getFileFromAddon($url, $filePath)
	{
		$domain = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : \Bitrix\Main\Config\Option::get('main', 'server_name'));
		$hash = md5($domain).'#'.md5($url);
		$ob = new \Bitrix\Main\Web\HttpClient(array('disableSslVerification'=>false, 'socketTimeout'=>1, 'streamTimeout'=>22));
		if($res = $ob->post('http://downloads.esolutions.su/getfile.php', array('domain'=>$domain, 'url'=>$url, 'hash'=>$hash)))
		{
			file_put_contents($filePath, $res);
			return $res;
		}
		return false;
	}
}
