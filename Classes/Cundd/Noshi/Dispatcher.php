<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:16
 */

namespace Cundd\Noshi;


use Cundd\Noshi\Domain\Model\Page;
use Cundd\Noshi\Domain\Repository\PageRepository;
use Cundd\Noshi\Ui\View;
use Parsedown;

class Dispatcher {
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
	protected $raw = FALSE;

	/**
	 * Page object for the current URI
	 *
	 * @var Page
	 */
	protected $page = NULL;

	/**
	 * Shared Dispatcher instance
	 *
	 * @var Dispatcher
	 */
	static protected $sharedDispatcher = NULL;

	/**
	 * Dispatch the given request URI
	 *
	 * @param string $uri
	 * @param string $method
	 * @return Response
	 */
	public function dispatch($uri, $method = 'GET') {
		$this->originalUri = filter_var($uri, FILTER_SANITIZE_URL);
		$this->uri = $this->getAliasForUri($this->originalUri);

		$methods      = array(
			'GET',
			'HEAD',
			'POST',
			'PUT',
			'DELETE',
			'TRACE',
			'OPTIONS',
			'CONNECT',
			'PATCH',
		);
		$method       = strtoupper($method);
		$this->method = in_array($method, $methods) ? $method : 'GET';

		try {
			// Get the response
			$response = $this->getResponse();

			if (!$this->raw) {
				$response = $this->createTemplateResponse($response);
			}

			// Output the response
			echo $response;
			return $response;
		} catch (\Exception $exception) {
			$fileHandle = fopen('php://stderr', 'w');
			fwrite($fileHandle, (string)$exception);
			echo new Response('An error occurred', 500);
		}
		return '';
	}

	/**
	 * Fill the template with the contents
	 *
	 * @param Response $response
	 * @return Response
	 */
	public function createTemplateResponse($response) {
		$page = $this->getPage();

		$view = new View();
		$view->setContext($this);
		$view->setTemplatePath($this->getTemplatePath());
		$view->assignMultiple(array(
			'meta' => $page ? $page->getMeta() : array(),
			'content' => $response->getBody(),
			'response' => $response,
			'resourcePath' => ConfigurationManager::getConfiguration()->getResourceDirectoryUri(),

		));
		return new Response(
			$view->render(),
			$response->getStatusCode(),
			$response->getStatusText()
		);
	}

	/**
	 * Returns the template
	 *
	 * @return string
	 */
	public function getTemplatePath() {
		$configuration = ConfigurationManager::getConfiguration();
		return $configuration->getThemePath() . $configuration->get('templatePath') . 'Page.html';
	}

	/**
	 * Returns the Page object for the current URI
	 *
	 * @return Page
	 */
	public function getPage() {
		if (!$this->page) {
			$this->page = $this->getPageForUri($this->uri);
		}
		return $this->page;
	}

	/**
	 * Returns the page data
	 *
	 * @return mixed
	 */
	public function getNotFoundPage() {
		return $this->getPageForUri('/NotFound/');
	}

	/**
	 * Returns the response for the current page URI
	 *
	 * If the current page URI was not found, the URI "/NotFound/" will be checked
	 *
	 * @return Response|NULL
	 */
	public function getResponse() {
		$statusCode = 200;
		$page = $this->getPage();
		if (!$page) {
			$statusCode = 404;
			$page = $this->getNotFoundPage();
		}
		return new Response(
			$page ? $this->parseMarkdown($page->getRawContent()) : 'Not found',
			$statusCode
		);
	}

	/**
	 * Returns the response for the page URI or NULL if no data was found
	 *
	 * @param string $uri
	 * @return Response|NULL
	 */
	public function buildResponseForUri($uri) {
		$page = $this->getPageForUri($uri);
		if (!$page) {
			return NULL;
		}
		return new Response(
			$this->parseMarkdown($page->getRawContent())
		);
	}

	/**
	 * Returns the Page for the page URI or NULL if no data was found
	 *
	 * @param string $uri
	 * @return Page
	 */
	public function getPageForUri($uri) {
		$pageIdentifier = urldecode($uri);
		if ($pageIdentifier[0] === '/') {
			$pageIdentifier = substr($pageIdentifier, 1);
		}
		if (substr($pageIdentifier, -1) === '/') {
			$pageIdentifier = substr($pageIdentifier, 0, -1);
		}

		$pageRepository = new PageRepository();
		return $pageRepository->findByIdentifier($pageIdentifier);
	}

	/**
	 * Returns the alias for the given URI, or the original URI if no alias is defined
	 *
	 * At the current stage this method doesn't do much. It simply returns "/Home/" if the current URI is "/"
	 *
	 * @param string $uri
	 * @return string
	 */
	public function getAliasForUri($uri) {
		$routingConfiguration = ConfigurationManager::getConfiguration()->get('routing');
		$aliasConfiguration = isset($routingConfiguration['alias']) ? $routingConfiguration['alias'] : array();
		return isset($aliasConfiguration[$uri]) ? $aliasConfiguration[$uri] : $uri;
	}

	/**
	 * Parse the given Markdown code
	 *
	 * @param $markdown
	 * @return mixed|string
	 */
	protected function parseMarkdown($markdown) {
		return Parsedown::instance()->parse($markdown);
	}

	/**
	 * Returns the shared dispatcher instance
	 *
	 * @return Dispatcher
	 */
	static public function getSharedDispatcher() {
		if (!static::$sharedDispatcher) {
			static::$sharedDispatcher = new static();
		}
		return static::$sharedDispatcher;
	}

}