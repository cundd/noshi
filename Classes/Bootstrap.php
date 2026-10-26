<?php

declare(strict_types=1);

namespace Cundd\Noshi;

use Cundd\Noshi\Command\NoshiCommandController;
use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Utilities\Profiler;

class Bootstrap
{
    protected DispatcherInterface $dispatcher;

    public function __construct(
        string $basePath,
        ?string $dataPath = null,
    ) {
        if (DIRECTORY_SEPARATOR !== substr($basePath, -1)) {
            $basePath .= DIRECTORY_SEPARATOR;
        }

        $this->configureEnvironment();

        ConfigurationManager::initializeConfiguration($basePath, $dataPath);
        Profiler::start();
    }

    /**
     * Invoke the web dispatcher
     */
    public function run(): void
    {
        echo Dispatcher::getSharedDispatcher()->dispatch(
            $this->getUri(),
            $this->getMethod(),
            $this->getArguments()
        );
    }

    /**
     * Invokes the CLI dispatcher
     *
     * @param array<string,mixed> $arguments
     */
    public function runCli(array $arguments): void
    {
        $commandController = new NoshiCommandController($arguments);
        $commandController->dispatch();
    }

    /**
     * @return array<string,mixed>
     */
    private function getArguments(): array
    {
        $argumentsString = $_SERVER['QUERY_STRING'] ?? '';
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

    private function getUri(): string
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

    private function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    private function configureEnvironment(): void
    {
        ini_set('default_charset', 'utf-8');
        ini_set('mbstring.encoding_translation', 'true');
    }
}
