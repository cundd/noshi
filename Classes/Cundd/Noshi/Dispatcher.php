<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:16
 */

namespace Cundd\Noshi;


use Cundd\Noshi\Ui\Menu;
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
	 * Dispatch the given request URI
	 *
	 * @param string $uri
	 * @param string $method
	 * @return Response
	 */
	public function dispatch($uri, $method = 'GET') {
		$this->uri = filter_var($uri, FILTER_SANITIZE_URL);

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

		$response = $this->getPage();
		if (!$this->raw) {
			$response = $this->createTemplateResponse($response);
		}

		echo $response;
		return $response;
	}

	/**
	 * Fill the template with the contents
	 *
	 * @param Response $response
	 * @return Response
	 */
	public function createTemplateResponse($response) {
		$configuration = ConfigurationManager::getConfiguration();

		$view = new View();
		$view->setContext($this);
		$view->setTemplatePath($this->getTemplatePath());
		$view->assignMultiple(array(
			'meta' => $this->getMetaData(),
			'content' => $response->getBody(),
			'response' => $response,
			'resourcePath' => $configuration->getBaseUrl() . $configuration->getThemeUri() . $configuration->get('resourcePath'),

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
	 * Returns the page data
	 *
	 * @return mixed
	 */
	public function getPage() {
		$pageUri  = $this->uri === '/' ? '/Home/' : $this->uri;
		$response = $this->getResponseForUri($pageUri);
		if (!$response) {
			$response = $this->getNotFoundPage();
		}
		return $response;
	}

	/**
	 * Returns the page data
	 *
	 * @return mixed
	 */
	public function getNotFoundPage() {
		$response = $this->getResponseForUri('/NotFound/');
		if (!$response) {
			$response = new Response(
				'Not found',
				404
			);
		}
		return $response;
	}

	/**
	 * Returns the response for the page URI or NULL if no data was found
	 *
	 * @param string $uri
	 * @return Response|NULL
	 */
	public function getResponseForUri($uri) {
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');

		$pageName = urldecode($uri);
		if ($pageName[0] === '/') {
			$pageName = substr($pageName, 1);
		}
		if (substr($pageName, -1) === '/') {
			$pageName = substr($pageName, 0, -1);
		}

		$pageDataPath       = $dataPath . $pageName . '.' . $configuration->get('dataSuffix');
		$hiddenPageDataPath = $dataPath . '_' . $pageName . '.' . $configuration->get('dataSuffix');

		// Check if the node exists
		if (!file_exists($pageDataPath)) {
			if (!file_exists($hiddenPageDataPath)) {
				return NULL;
			}
			$pageDataPath = $hiddenPageDataPath;
		}

		$rawPageData = file_get_contents($pageDataPath);
		return new Response(
			$this->parseMarkdown($rawPageData)
		);
	}

	/**
	 * Returns the meta data for the page URI or an empty array if no data was found
	 *
	 * @return array
	 */
	public function getMetaData() {
		$pageUri  = $this->uri === '/' ? '/Home/' : $this->uri;
		$metaData = $this->getMetaDataForUri($pageUri);
		return $metaData ? $metaData : ConfigurationManager::getConfiguration()->get('metaData');
	}

	/**
	 * Returns the meta data for the page URI or an empty array if no data was found
	 *
	 * @param string $uri
	 * @return array
	 */
	public function getMetaDataForUri($uri) {
		$configuration = ConfigurationManager::getConfiguration();
		$dataPath      = $configuration->get('basePath') . $configuration->get('dataPath');

		$pageURI      = $dataPath . substr($uri, 1);
		$metaDataPath = substr($pageURI, 0, -1) . '.json';

		// Check if the node exists
		if (!file_exists($metaDataPath)) {
			return array();
		}

		$rawMetaData = file_get_contents($metaDataPath);
		return json_decode($rawMetaData, TRUE);
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


}