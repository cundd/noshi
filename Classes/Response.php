<?php
declare(strict_types=1);

namespace Cundd\Noshi;

class Response
{
    /**
     * Body
     *
     * @var string
     */
    protected $body = '';

    /**
     * Status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Status text
     *
     * @var string
     */
    protected $statusText = '';

    /**
     * @var array
     */
    protected $codeToTextMap = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
    ];

    /**
     * Protocol
     *
     * @var string
     */
    protected $protocol = 'HTTP/1.1';

    function __construct($body = '', $statusCode = 200, $statusText = null, $protocol = null)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->statusText = $statusText || isset($this->codeToTextMap[$statusCode]) ? $this->codeToTextMap[$statusCode] : '';
        if ($protocol) {
            $this->protocol = $protocol;
        }
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $statusText
     */
    public function setStatusText($statusText)
    {
        $this->statusText = $statusText;
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Sends the headers
     */
    public function sendHeaders()
    {
        header($this->protocol . ' ' . $this->statusCode . ' ' . $this->statusText);
    }

    function __toString()
    {
        $this->sendHeaders();

        return $this->getBody();
    }

}
