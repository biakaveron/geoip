<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class GeoIp_Core_Reader_DB extends GeoIp_Reader {

	/**
	 * @var Database
	 */
	protected $_db;
	/**
	 * @var Cache
	 */
	protected $_cache;

	protected $_caching = TRUE;

	protected $_fields = array(
		'country'        => array(
			'id'            => 'id',
			'name'          => 'name',
			'code'          => 'code',
		),
		'ip'             => array(
			'start'         => 'start',
			'end'           => 'end',
			'country_id'     => 'country_id',
		),
	);

	protected $_tables = array(
		'country'        => 'countries',
		'ip'             => 'ip',
	);

	protected function _get_country_from_ip($ip)
	{
		$ip = $this->_ip2long($ip);
		$country_id = DB::select()
			->from($this->_tables['ip'])
			->where($this->_fields['ip']['start'], '<=', $ip)
			->where($this->_fields['ip']['end'], '>=', $ip)
			->limit(1)
			->execute($this->_db)
			->as_array();
		if ( empty($country_id) )
		{
			return FALSE;
		}
		
		return $country_id[$this->_fields['country_id']];
	}

	protected function _get_country($id)
	{
		$country = DB::select()
			->from($this->_tables['country'])
			->where($this->_fields['country']['id'], '=', $id)
			->limit(1)
			->execute($this->_db);
		return $country ? $country : FALSE;
	}

	const COUNTRY_NAME = 'name';
	const COUNTRY_CODE = 'code';

	public $type = 'db';

	public function __construct($config)
	{
		parent::__construct($config);
		$config = Arr::get($config, 'database');

		$db = Arr::get($config, 'instance');
		$this->_db = Database::instance($db);

		$cache = Arr::get($this->_config, 'cache');
		$this->_cache = Cache::instance($cache);

		$this->_caching = (bool)Arr::get($this->_config, 'caching', $this->_caching);

		if ($db_tables = Arr::get($config, 'tables'))
		{
			$this->_fields += $db_tables;
		}

		if ($db_fields = Arr::get($config, 'fields'))
		{
			$this->_fields += $db_fields;
		}
	}

	/**
	 * @param  string   ip value
	 * @return string   country code
	 */
	public function get_country_code($ip)
	{
		return $this->get_country_info($ip, array(self::COUNTRY_CODE));
	}

	/**
	 * @param  string   ip value
	 * @return string   country name
	 */
	public function get_country_name($ip)
	{
		return $this->get_country_info($ip, array(self::COUNTRY_NAME));
	}

	/**
	 * @param  string   ip value
	 * @param  array    required result params
	 * @return mixed
	 */
	public function get_country_info($ip, array $params = NULL)
	{
		$full_info = array(self::COUNTRY_CODE, self::COUNTRY_NAME);
		if (empty($params))
		{
			$params = $full_info;
		}
		else
		{
			$params = array_intersect($full_info, $params);
			if (empty($params))
			{
				$params = $full_info;
			}
		}
		$country_id = $this->_get_country_from_ip($ip);
		if ( $country_id === FALSE)
		{
			return NULL;
		}

		$country = $this->_get_country($country_id);
		if ( ! $country )
		{
			return NULL;
		}

		if (count($params) > 1)
		{
			$result = array();
			foreach($params as $field)
			{
				$result[$field] = $country[$this->_field['country'][$field]];
			}
		}
		else
		{
			$result = $country[$this->_field['country'][current($params)]];
		}

		return $result;
	}

}