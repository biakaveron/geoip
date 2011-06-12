<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class GeoIp_Core {

	/**
	 * @var GeoIp
	 */
	protected static $_instance;

	/**
	 * @static
	 * @return GeoIp
	 */
	public static function instance()
	{
		if ( ! GeoIp::$_instance )
		{
			GeoIp::$_instance = new GeoIp;
		}

		return GeoIp::$_instance;
	}

	/**
	 * @var Array
	 */
	protected $_config;
	/**
	 * @var GeoIp_Reader
	 */
	protected $_reader;

	public function __construct()
	{
		$this->_config = (array)Kohana::config('geoip');
		$this->reader($this->_config['reader']);
	}

	/**
	 * @throws Kohana_Exception
	 * @param null $type
	 * @return GeoIp_Reader
	 */
	public function reader($type = NULL)
	{
		if ($type !== NULL)
		{
			$class = 'GeoIp_Reader_'.$type;
			if ( ! class_exists($class))
			{
				throw new Kohana_Exception('Unknown reader type :type', array(':type' => $type));
			}

			$this->_reader = new $class($this->_config);
		}

		return $this->_reader;
	}

	public function get_country_code($ip = NULL)
	{
		if ($ip === NULL)
		{
			$ip = Request::$client_ip;
		}

		return $this->reader()->get_country_code($ip);
	}

	public function get_country_name($ip = NULL)
	{
		if ($ip === NULL)
		{
			$ip = Request::$client_ip;
		}

		return $this->reader()->get_country_name($ip);
	}

}