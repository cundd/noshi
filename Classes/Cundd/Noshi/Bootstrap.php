<?php

namespace Cundd\Noshi;

use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Utilities\Profiler;

class Bootstrap
{

    /**
     * Dispatcher instance
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    function __construct($basePath)
    {
        if (substr($basePath, -1) !== DIRECTORY_SEPARATOR) {
            $basePath .= DIRECTORY_SEPARATOR;
        }
        ConfigurationManager::initializeConfiguration($basePath);
        Profiler::start();
    }

    /**
     * Invokes the dispatcher
     */
    public function run()
    {
        Dispatcher::getSharedDispatcher()->dispatch($this->getUri(), $this->getMethod(), $this->getArguments());
    }

    /**
     * Invokes the CLI dispatcher
     *
     * @param array $arguments
     */
    public function runCli($arguments)
    {
        $commandController = new \Cundd\Noshi\Command\NoshiCommandController($arguments);
        $commandController->dispatch();
    }

    /**
     * @return array
     */
    private function getArguments()
    {
        $argumentsString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $arguments = [];
        if ($argumentsString) {
            parse_str($argumentsString, $tempArguments);
            foreach ($tempArguments as $argumentKey => $argumentValue) {
                $argumentKey = filter_var($argumentKey, FILTER_SANITIZE_STRING);
                $argumentValue = filter_var($argumentValue, FILTER_SANITIZE_STRING);
                $arguments[$argumentKey] = $argumentValue;
            }
        }

        return $arguments;
    }

    /**
     * @return string
     */
    private function getUri()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
            $uri = $uriParts[0];
        } elseif (isset($_GET['u'])) {
            $uri = $_GET['u'];
        } else {
            return '';
        }

        $uri = str_replace(' ', Page::URI_WHITESPACE_REPLACE, $uri);
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

        return $uri;
    }

    /**
     * @return string
     */
    private function getMethod()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        return $method;
    }
}
