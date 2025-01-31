<?php

namespace Molayli;

use Closure;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\ServerBag;

class CloudflareRealIpServiceProvider extends ServiceProvider
{

	public function boot(){

		if( static::isTrustedRequest() ){

		    $currentIp = request()->getClientIps();
		    $realIp =  static::ip();
		    $serverParams = request()->server->all();

		    request()->server = new ServerBag([
                'BACKUP_REMOTE_ADDR'=> $currentIp, //backup the current ip
                'REMOTE_ADDR' => $realIp //set the real ip
            ] + $serverParams);


		}

	}


	/**
   * List of IP's used by CloudFlare.
   * @var array
   */
	protected static $ips = [
		'103.21.244.0/22',
		'103.22.200.0/22',
		'103.31.4.0/22',
		'104.16.0.0/13',
		'104.24.0.0/14',
		'108.162.192.0/18',
		'131.0.72.0/22',
		'141.101.64.0/18',
		'162.158.0.0/15',
		'172.64.0.0/13',
		'173.245.48.0/20',
		'188.114.96.0/20',
		'190.93.240.0/20',
		'197.234.240.0/22',
		'198.41.128.0/17',
		'2400:cb00::/32',
		'2405:8100::/32',
		'2405:b500::/32',
		'2606:4700::/32',
		'2803:f800::/32',
		'2c0f:f248::/32',
		'2a06:98c0::/29'
	];
    
	/**
	 * Checks if current request is coming from CloudFlare servers.
	 *
	 * @return bool
	 */
	public static function isTrustedRequest()
	{
		return IpUtils::checkIp(request()->ip(), static::$ips);
	}


	/**
	 * Executes a callback on a trusted request.
	 *
	 * @param  Closure $callback
	 *
	 * @return mixed
	 */
	public static function onTrustedRequest(Closure $callback)
	{
		if (static::isTrustedRequest()) {
			return $callback();
		}
	}


	/**
	 * Determines "the real" IP address from the current request.
	 *
	 * @return string
	 */
	public static function ip()
	{
		return static::onTrustedRequest(function () {
			return filter_var(request()->header('CF_CONNECTING_IP'), FILTER_VALIDATE_IP);
		}) ?: request()->ip();
	}



	/**
	 * Determines country from the current request.
	 *
	 * @return string
	 */
	public static function country()
	{
		return static::onTrustedRequest(function () {
			return request()->header('CF_IPCOUNTRY');
		}) ?: '';
	}

}
