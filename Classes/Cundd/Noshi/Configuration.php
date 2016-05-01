<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:42
 */

namespace Cundd\Noshi;


use Cundd\Noshi\Exception\SecurityException;

class Configuration implements \ArrayAccess {
	/**
	 * @var bool
	 */
	protected $_useVendorDirectory = TRUE;

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
		$this->_useVendorDirectory = file_exists($this->getThemePath());
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
				. (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http')
				. '://'
				. $this->getHost()
				. '/'
			;
		}
		return $baseUrl;
	}

	/**
	 * Returns the sanitized hostname
	 *
	 * @throws Exception\SecurityException if the host could not be detected
	 * @return string
	 */
	public function getHost() {
		$host = '';

		if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== '0.0.0.0') {
			$host = $_SERVER['SERVER_NAME'];
			if (!$this->_validateHost($host)) {
				$host = '';
			}

			// Add the port
			if ($host && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']) {
				$port = $_SERVER['SERVER_PORT'];
				if (ctype_digit($port)) {
					$host .= ':' . $port;
				}
			}
		}

		if (!$host && isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
			if (!$this->_validateHost($host)) {
				$host = '';
			}
		}
		if (!$host) throw new SecurityException('Host could not be detected', 1394796366);
		return $host;
	}

	/**
	 * Returns if the given host is valid
	 *
	 * @param string $host
	 * @return boolean
	 */
	protected function _validateHost($host) {
		// Remove any dash ('-'), dot ('.') and colon (':', allowed because of the port)
		return ctype_alnum(str_replace(array('-', '.', ':'), '', $host));
	}

	/**
	 * Returns the path to the configured theme
	 *
	 * @return string
	 */
	public function getThemePath() {
		return $this->get('basePath') .
			($this->_useVendorDirectory ? 'vendor/' . $this->get('theme') : '')
			. '/';
	}

	/**
	 * Returns the URI of the path to the configured theme
	 *
	 * @return string
	 */
	public function getThemeUri() {
		return $this->getBaseUrl() .
			($this->_useVendorDirectory ? 'vendor/' . $this->get('theme') : '')
			. '/';
	}

	/**
	 * Returns the URI of the resource directory
	 * @return string
	 */
	public function getResourceDirectoryUri() {
		return $this->getThemeUri() . $this->get('resourcePath');
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
