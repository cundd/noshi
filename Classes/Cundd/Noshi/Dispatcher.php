<?php
declare(strict_types=1);

namespace Cundd\Noshi;

use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;
use Cundd\Noshi\Expression\ExpressionProcessor;
use Cundd\Noshi\Expression\ExpressionProcessorInterface;
use Cundd\Noshi\Helpers\MarkdownFactory;
use Cundd\Noshi\Helpers\MarkdownFactoryInterface;
use Cundd\Noshi\Ui\UiInterface;
use Cundd\Noshi\Ui\View;
use Exception;

class Dispatcher implements UiInterface, DispatcherInterface
{
    /**
     * Request URI
     *
     * @var string
     */
    protected $uri = '';

    /**
     * Original request URI
     *
     * @var string
     */
    protected $originalUri = '';

    /**
     * Request method
     *
     * @var string
     */
    protected $method = '';

    /**
     * Return the raw result
     *
     * @var bool
     */
    protected $raw = false;

    /**
     * Page object for the current URI
     *
     * @var Page
     */
    protected $page = null;

    /**
     * Shared Dispatcher instance
     *
     * @var DispatcherInterface
     */
    static protected $sharedDispatcher = null;

    /**
     * @var MarkdownFactoryInterface
     */
    private $markdownFactory;

    /**
     * @var ExpressionProcessorInterface
     */
    private $expressionProcessor;

    /**
     * Dispatcher constructor.
     *
     * @param MarkdownFactoryInterface     $markdownFactory
     * @param ExpressionProcessorInterface $expressionProcessor
     */
    public function __construct(
        MarkdownFactoryInterface $markdownFactory,
        ExpressionProcessorInterface $expressionProcessor
    ) {
        $this->markdownFactory = $markdownFactory;
        $this->expressionProcessor = $expressionProcessor;
    }

    public function dispatch(string $uri, string $method = 'GET', array $arguments = []): Response
    {
        $this->originalUri = $uri;
        $this->uri = $this->prepareUri($uri);

        $methods = [
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'DELETE',
            'TRACE',
            'OPTIONS',
            'CONNECT',
            'PATCH',
        ];
        $method = strtoupper($method);
        $this->method = in_array($method, $methods) ? $method : 'GET';

        try {
            // Get the response
            $response = $this->getResponse();

            if (!$this->raw) {
                $response = $this->createTemplateResponse($response);
            }

            return $response;
        } catch (Exception $exception) {
            $fileHandle = fopen('php://stderr', 'w');
            fwrite($fileHandle, (string)$exception);
            if (ConfigurationManager::getConfiguration()->isDevelopmentMode()) {
                return new Response('An error occurred: ' . $exception, 500);
            } else {
                return new Response('An error occurred', 500);
            }
        }
    }

    /**
     * Fill the template with the contents
     *
     * @param Response $response
     * @return Response
     * @throws Ui\Exception\InvalidExpressionException
     */
    public function createTemplateResponse($response)
    {
        $page = $this->getPage();

        $view = new View('', [], $this->expressionProcessor);
        $view->setContext($this);
        $view->setTemplatePath($this->getTemplatePath());
        $configuration = ConfigurationManager::getConfiguration();
        $view->assignMultiple(
            [
                'page'          => $page,
                'meta'          => $page ? $page->getMeta() : [],
                'content'       => $response->getBody(),
                'response'      => $response,
                'resourcePath'  => $configuration->getResourceDirectoryUri(),
                'configuration' => $configuration,
            ]
        );

        return new Response(
            $view->render(),
            $response->getStatusCode(),
            $response->getStatusText()
        );
    }

    /**
     * Return the template
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $configuration = ConfigurationManager::getConfiguration();

        return $configuration->getThemePath() . $configuration->get('templatePath') . 'Page.html';
    }

    /**
     * Return the Page object for the current URI
     *
     * @return Page|null
     */
    public function getPage(): ?Page
    {
        if (!$this->page) {
            $this->page = $this->getPageForUri($this->uri);
        }

        return $this->page;
    }

    /**
     * Return the page data
     *
     * @return Page|null
     */
    public function getNotFoundPage(): ?Page
    {
        return $this->getPageForUri('/NotFound/');
    }

    /**
     * Return the response for the current page URI
     *
     * If the current page URI was not found, the URI "/NotFound/" will be checked
     *
     * @return Response
     * @throws Ui\Exception\InvalidExpressionException
     */
    public function getResponse(): Response
    {
        $statusCode = 200;
        $page = $this->getPage();
        if (!$page) {
            $statusCode = 404;
            $page = $this->getNotFoundPage();
        }

        if ($page) {
            $content = $this->expressionProcessor->process($page->getContent(), $this, []);
        } else {
            $content = 'Not found';
        }

        return new Response($content, $statusCode);
    }

    /**
     * Return the response for the page URI or NULL if no data was found
     *
     * @param string $uri
     * @return Response|NULL
     * @deprecated will be removed in 3.0.0
     */
    public function buildResponseForUri(string $uri): ?Response
    {
        $page = $this->getPageForUri($uri);
        if (!$page) {
            return null;
        }

        return new Response($page->getContent());
    }

    /**
     * Return the Page for the page URI or NULL if no data was found
     *
     * @param string $uri
     * @return Page|null
     */
    public function getPageForUri(string $uri): ?Page
    {
        $pageIdentifier = trim(urldecode($uri), '/');
        if ($pageIdentifier === '') {
            $pageIdentifier = '/';
        }

        $pageRepository = new PageRepository($this->markdownFactory);

        return $pageRepository->findByIdentifier($pageIdentifier);
    }

    /**
     * Return the alias for the given URI, or the original URI if no alias is defined
     *
     * At the current stage this method doesn't do much. It simply returns "/Home/" if the current URI is "/"
     *
     * @param string $uri
     * @return string
     */
    public function getAliasForUri($uri): string
    {
        $routingConfiguration = ConfigurationManager::getConfiguration()->get('routing');
        $aliasConfiguration = isset($routingConfiguration['alias']) ? $routingConfiguration['alias'] : [];

        return isset($aliasConfiguration[$uri]) ? (string)$aliasConfiguration[$uri] : $uri;
    }

    public function setContext($context)
    {
        // noop
    }

    /**
     * Return the shared dispatcher instance
     *
     * @return DispatcherInterface
     */
    static public function getSharedDispatcher()
    {
        if (!static::$sharedDispatcher) {
            static::$sharedDispatcher = new static(new MarkdownFactory(), new ExpressionProcessor());
        }

        return static::$sharedDispatcher;
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function prepareUri(string $uri): string
    {
        $basePath = ConfigurationManager::getConfiguration()->getRequestBasePath();
        $basePathLength = strlen($basePath);
        if ($basePath && substr($uri, 0, $basePathLength) === $basePath) {
            $uri = '/' . substr($uri, $basePathLength);
        }

        return $this->getAliasForUri($uri);
    }
}
