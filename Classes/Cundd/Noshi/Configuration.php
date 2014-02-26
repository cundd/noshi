<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:42
 */

namespace Cundd\Noshi;


class Configuration implements \ArrayAccess {

	/**
	 * @var array
	 */
	protected $configuration = array(
		'dataPath'     => 'data/',
		'dataSuffix'   => 'md',
		'templatePath' => 'Resources/Private/Templates/',
		'resourcePath' => 'Resources/Public/',


		'metaData'     => array(
			'title' => 'NoShi',
		)
	);

	function __construct($configuration = array()) {
		$this->configuration = array_merge($this->configuration, (array)$configuration);
	}

	/**
	 * Returns the base URL
	 *
	 * @return string
	 */
	public function getBaseUrl() {
		$baseUrl = $this->_get('baseUrl');
		if (!$baseUrl) {
			$baseUrl = ''
				. isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http'
				. '://'
				. $_SERVER['HTTP_HOST']
				. '/'
			;
		}
		return $baseUrl;
	}


	/**
	 * Returns the path to the configured theme
	 *
	 * @return string
	 */
	public function getThemePath() {
		return $this->get('basePath') . 'vendor/' . $this->get('theme') . '/';
	}

	/**
	 * Returns the URI of the path to the configured theme
	 *
	 * @return string
	 */
	public function getThemeUri() {
		return 'vendor/' . $this->get('theme') . '/';
	}


	/**
	 * Returns the configuration (without checking for an accessor method)
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function _get($key) {
		if (isset($this->configuration[$key])) {
			return $this->configuration[$key];
		}
		return NULL;
	}

	/**
	 * Returns the configuration
	 *
	 * If an accessor method (i.e.: "getMyKey") is available it will be called
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		$accessorMethod = 'get' . ucfirst($key);
		if (method_exists($this, $accessorMethod)) {
			return $this->$accessorMethod();
		}
		return $this->_get($key);
	}

	/**
	 * Set the configuration
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	public function set($key, $value) {
		$this->configuration[$key] = $value;
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 * @return boolean true on success or false on failure.
	 *                      </p>
	 *                      <p>
	 *                      The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		return isset($this->configuration[$offset]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->set($offset, NULL);
	}

	function __call($name, $arguments) {
		if (substr($name, 0, 3) === 'get') {
			return $this->get(lcfirst(substr($name, 3)));
		}
	}


} 