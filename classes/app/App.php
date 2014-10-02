<?php
namespace DemoShop;

class App {
	protected $routes = array();
	protected $infoPopupMessages = array();

	public function page($path, $callback) {
		$this->routes[$path] = $callback;
		return $this;
	}

	public function run() {
		session_start();

		// route request
		$requestedPath = $_SERVER['REQUEST_URI'];
		$handler = null;
		foreach ($this->routes as $path => $callback) {
			if (strtolower(substr($requestedPath, 0, strlen($path))) === $path) {
				$handler = $callback;
				break;
			}
		}

		if (!$handler) {
			$this->redirect('/');
			return '';
		}

		// get handler params from request
		$handlerParams = array();
		$rf = new \ReflectionFunction($handler);
		foreach ($rf->getParameters() as $rfParam) {
			$paramName = $rfParam->getName();
			$paramValue = null;
			if ($rfParam->isDefaultValueAvailable()) {
				$paramValue = $rfParam->getDefaultValue();
			}
			if (isset($_GET[$paramName])) {
				$paramValue = $_GET[$paramName];
			}
			if (isset($_POST[$paramName])) {
				$paramValue = $_POST[$paramName];
			}

			if ($rfParam->getClass() && is_subclass_of($rfParam->getClass()->getName(), '\DemoShop\AbstractDbObject')) {
				$className = $rfParam->getClass()->getName();
				if ($paramValue) {
					$paramValue = $className::getById($paramValue);
				} else {
					if ($rfParam->allowsNull()) {
						$paramValue = null;
					} else {
						$this->redirect('/');
						return '';
					}
				}
			} else {
				$paramValue = htmlspecialchars($paramValue);
			}
			$handlerParams[$paramName] = $paramValue;
		}

		return call_user_func_array($handler, $handlerParams);
	}

	public function render($template, array $data = array(), $layout = 'layout_default') {
		extract($data);
		ob_start();
		if ($layout) {
			require self::getTemplatePath($layout);
		} else {
			require self::getTemplatePath($template);
		}
		return ob_get_clean();
	}

	public static function getTemplatePath($templateName) {
		$filename = DEMOSHOP_PATH . '/templates/' . $templateName . '.php';
		if (!file_exists($filename)) {
			throw new \Exception('template not found');
		}
		return $filename;
	}

	public function redirect($url) {
		header('Location: ' . $url);
	}

	public function session($key, $value = null) {
		if (!is_null($value)) {
			return $_SESSION[$key] = $value;
		} else {
			return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}
	}

	public function addInfoPopup($message) {
		$this->infoPopupMessages []= $message;
		return $this;
	}
}