<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 21:02
 */

namespace Cundd\Noshi;


class Response {
	/**
	 * Body
	 * @var string
	 */
	protected $body = '';

	/**
	 * Status code
	 * @var int
	 */
	protected $statusCode;

	/**
	 * Status text
	 * @var string
	 */
	protected $statusText = '';

	/**
	 * Protocol
	 * @var string
	 */
	protected $protocol = 'HTTP/1.1';

	function __construct($body = '', $statusCode = 200, $statusText = 'OK', $protocol = NULL) {
		$this->body       = $body;
		$this->statusCode = $statusCode;
		$this->statusText = $statusText;

		if ($protocol) {
			$this->protocol   = $protocol;
		}
	}


	/**
	 * @param string $body
	 */
	public function setBody($body) {
		$this->body = $body;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param int $statusCode
	 */
	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
	}

	/**
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * @param string $statusText
	 */
	public function setStatusText($statusText) {
		$this->statusText = $statusText;
	}

	/**
	 * @return string
	 */
	public function getStatusText() {
		return $this->statusText;
	}

	/**
	 * @param string $protocol
	 */
	public function setProtocol($protocol) {
		$this->protocol = $protocol;
	}

	/**
	 * @return string
	 */
	public function getProtocol() {
		return $this->protocol;
	}

	/**
	 * Sends the headers
	 */
	public function sendHeaders() {
		header($this->protocol . ' ' . $this->statusCode . ' ' . $this->statusText);
	}

	function __toString() {
		$this->sendHeaders();
		return $this->getBody();
	}


} 