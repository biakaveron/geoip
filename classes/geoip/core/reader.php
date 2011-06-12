<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class GeoIp_Core_Reader {

	/**
	 * @var Array
	 */
	protected $_config;

	protected function _ip2long($ip)
	{
		if (strpos('.', $ip) !== FALSE)
		{
			$ip = ip2long($ip);
		}
		return sprintf("%u", $ip);
	}

	protected function _long2ip($ip)
	{
		if ( ! is_numeric($ip))
		{
			$ip = long2ip($ip);
		}
		return $ip;
	}

	public $type;

	abstract public function get_country_code($ip);
	abstract public function get_country_name($ip);

	public function __construct($config)
	{
		$this->_config = $config;
	}

}