<?php

class View
{
	private $params = [];
	private $default = ['pageTitle' => 'Inicio'];
	private $session;
	private $styles = '';
	private $scripts = '';
	private $messages = '';
	private $headContent = '';
	private $footerContent = '';
	public $currentRoute = '';


	public function render($view, $params = [], $assets = [])
	{
		$this->params = array_merge($this->default, $params);
		$this->session = $_SESSION ?? [];

		// Cargar primero assets para que los CSS estén disponibles al construir el head
		$this->processAssets($assets);
		$this->processHead();
		$this->processMessages();
		$this->processFooter();

		$useSidebar = $params['useSidebar'] ?? false;
		$useLogin = $params['useLogin'] ?? false;

		if (isset($_SESSION['username'])) {
			if ($useSidebar) {
				$headerPath = $this->getViewPath('templates/header-menu');
			} else {
				$headerPath = $this->getViewPath('templates/header');
			}
		} else {
			if ($useLogin) {
				$headerPath = $this->getViewPath('templates/header-login');
			} else {
				$headerPath = $this->getViewPath('templates/header');
			}
		}

		$viewPath = $this->getViewPath($view);
		$footerPath = $this->getViewPath('templates/footer');

		if (!file_exists($headerPath)) {
			throw new Exception("Header template not found: {$headerPath}");
		}

		if (!file_exists($viewPath)) {
			throw new Exception("View file not found: {$viewPath}");
		}

		if (!file_exists($footerPath)) {
			throw new Exception("Footer template not found: {$footerPath}");
		}

		require_once $headerPath;
		require_once $viewPath;
		require_once $footerPath;
	}

	private function processAssets($assets)
	{
		if (empty($assets)) {
			return;
		}

		$baseUrl = rtrim($this->getBaseUrl(), '/');

		$assetsPath = $baseUrl . '/assets/';

		if (defined('ASSETS_PATH') && ASSETS_PATH) {
			$configured = ASSETS_PATH;
			if (preg_match('#^https?://#i', $configured)) {
				$assetsPath = rtrim($configured, '/') . '/';
			} else {
				$configured = ltrim($configured, '/');
				$assetsPath = $baseUrl . '/' . ($configured !== '' ? rtrim($configured, '/') . '/' : '');
			}
		}

		foreach ($assets as $asset) {
			if (!is_string($asset) || empty($asset)) {
				continue;
			}

			$ext = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
			$safeAsset = htmlspecialchars($asset, ENT_QUOTES);

			switch ($ext) {
				case 'css':
					$this->styles .= '<link rel="stylesheet" href="' . $assetsPath . 'css/' . $safeAsset . '">';
					break;
				case 'js':
					$this->scripts .= '<script src="' . $assetsPath . 'js/' . $safeAsset . '"></script>';
					break;
			}
		}
	}

	private function processMessages()
	{
		if (isset($_SESSION['error']) && !empty(trim($_SESSION['error']))) {
			$this->messages .= '<div class="alert alert-danger d-flex align-items-center" role="alert" data-bs-dismiss="alert">';
			$this->messages .= '<i class="fas fa-exclamation-circle me-2"></i>';
			$this->messages .= htmlspecialchars($_SESSION['error']);
			$this->messages .= '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>';
			$this->messages .= '</div>';
			unset($_SESSION['error']);
		}

		if (isset($_SESSION['success']) && !empty(trim($_SESSION['success']))) {
			$this->messages .= '<div class="alert alert-success d-flex align-items-center" role="alert" data-bs-dismiss="alert">';
			$this->messages .= '<i class="fas fa-check-circle me-2"></i>';
			$this->messages .= htmlspecialchars($_SESSION['success']);
			$this->messages .= '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>';
			$this->messages .= '</div>';
			unset($_SESSION['success']);
		}

		if (isset($_SESSION['info']) && !empty(trim($_SESSION['info']))) {
			$this->messages .= '<div class="alert alert-info d-flex align-items-center" role="alert" data-bs-dismiss="alert">';
			$this->messages .= '<i class="fas fa-info-circle me-2"></i>';
			$this->messages .= htmlspecialchars($_SESSION['info']);
			$this->messages .= '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>';
			$this->messages .= '</div>';
			unset($_SESSION['info']);
		}

		if (isset($_SESSION['warning']) && !empty(trim($_SESSION['warning']))) {
			$this->messages .= '<div class="alert alert-warning d-flex align-items-center" role="alert" data-bs-dismiss="alert">';
			$this->messages .= '<i class="fas fa-exclamation-triangle me-2"></i>';
			$this->messages .= htmlspecialchars($_SESSION['warning']);
			$this->messages .= '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>';
			$this->messages .= '</div>';
			unset($_SESSION['warning']);
		}
	}

	private function processHead()
	{
		$pageTitle = $this->getParam('pageTitle', 'Iniciar Sesión');
		$metaDescription = $this->getParam('metaDescription');
		$metaKeywords = $this->getParam('metaKeywords');
		$additionalCSS = $this->getParam('additionalCSS');
		$inlineCSS = $this->getParam('inlineCSS');
		$baseUrl = $this->getBaseUrl();

		$this->headContent = '<head>';
		$this->headContent .= '<meta charset="UTF-8">';
		$this->headContent .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		$this->headContent .= '<title>' . htmlspecialchars($pageTitle) . '</title>';

		if ($metaDescription) {
			$this->headContent .= '<meta name="description" content="' . htmlspecialchars($metaDescription) . '">';
		}

		if ($metaKeywords) {
			$this->headContent .= '<meta name="keywords" content="' . htmlspecialchars($metaKeywords) . '">';
		}

		$this->headContent .= '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">';
		$this->headContent .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">';
		$this->headContent .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';

		// DataTables CSS
		$this->headContent .= '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">';
		$this->headContent .= '<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">';
		$this->headContent .= '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">';

		$this->headContent .= '<link rel="stylesheet" href="' . $baseUrl . '/assets/css/styles.css">';
		$this->headContent .= '<link rel="stylesheet" href="' . $baseUrl . '/assets/css/sidebar.css">';

		if ($additionalCSS) {
			foreach ((array)$additionalCSS as $css) {
				$this->headContent .= '<link rel="stylesheet" href="' . htmlspecialchars($css) . '">';
			}
		}

		if ($inlineCSS) {
			$this->headContent .= '<style>' . $inlineCSS . '</style>';
		}

		$this->headContent .= $this->styles;

		$this->headContent .= '</head>';
	}

	private function processFooter()
	{
		$bodyJS = $this->getParam('bodyJS');
		$inlineBodyJS = $this->getParam('inlineBodyJS');
		$csrfToken = $_SESSION['csrf_token'] ?? null;
		$baseUrl = $this->getBaseUrl();

		$this->footerContent = '<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>';
		$this->footerContent .= '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>';

		// DataTables JS
		$this->footerContent .= '<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>';
		$this->footerContent .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>';
		$this->footerContent .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>';
		$this->footerContent .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>';
		$this->footerContent .= '<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>';

		// DataTable Universal Configuration
		$this->footerContent .= '<script src="' . $baseUrl . '/assets/js/datatable.js"></script>';

		$this->footerContent .= '<script src="' . $baseUrl . '/assets/js/sidebar.js"></script>';

		if ($bodyJS) {
			foreach ((array)$bodyJS as $js) {
				$this->footerContent .= '<script src="' . htmlspecialchars($js) . '"></script>';
			}
		}

		if ($inlineBodyJS) {
			$this->footerContent .= '<script>' . $inlineBodyJS . '</script>';
		}

		// Incluir scripts dinámicos de assets
		$this->footerContent .= $this->scripts;

		$this->footerContent .= '<script>';
		$this->footerContent .= 'document.addEventListener("DOMContentLoaded", function() {';
		$this->footerContent .= 'const alerts = document.querySelectorAll(".alert[data-bs-dismiss=\\"alert\\"]");';
		$this->footerContent .= 'alerts.forEach(alert => {';
		$this->footerContent .= 'setTimeout(() => {';
		$this->footerContent .= 'const bsAlert = new bootstrap.Alert(alert);';
		$this->footerContent .= 'bsAlert.close();';
		$this->footerContent .= '}, 5000);';
		$this->footerContent .= '});';
		$this->footerContent .= '});';
		$this->footerContent .= 'window.addEventListener("error", function(e) {';
		$this->footerContent .= 'console.error("Error detectado:", e.error);';
		$this->footerContent .= '});';

		// Exponer baseUrl para navegación en subcarpetas
		$this->footerContent .= 'window.baseUrl = ' . json_encode($baseUrl) . ';';

		if ($csrfToken) {
			$this->footerContent .= 'window.csrfToken = "' . $csrfToken . '";';
		}

		$this->footerContent .= '</script>';
		$this->footerContent .= '</body></html>';
	}

	private function getViewPath($view)
	{
		$view = str_replace(['../', '..\\'], '', $view);
		return 'views/' . $view . '.php';
	}

	public function getParam($key, $default = null)
	{
		return $this->params[$key] ?? $default;
	}

	public function getStyles()
	{
		return $this->styles;
	}

	public function getScripts()
	{
		return $this->scripts;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function getHeadContent()
	{
		return $this->headContent;
	}

	public function getFooterContent()
	{
		return $this->footerContent;
	}

	public function getBodyClass()
	{
		return $this->getParam('bodyClass', '');
	}

	public function getBodyAttributes()
	{
		return $this->getParam('bodyAttributes', '');
	}

	public function getSession($key = null)
	{
		return $key ? ($this->session[$key] ?? null) : $this->session;
	}

	public function isAuthenticated()
	{
		return isset($_SESSION["id"]);
	}

	public function __get($name)
	{
		return $this->params[$name] ?? null;
	}

	public function __isset($name)
	{
		return isset($this->params[$name]);
	}

	private function getBaseUrl()
	{
		if (defined('BASE_URL')) {
			return constant('BASE_URL');
		}

		if (isset($_SERVER['BASE_URL'])) {
			return $_SERVER['BASE_URL'];
		}

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

		// Obtener el directorio base del script
		$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
		$baseDir = ($scriptPath === '/') ? '' : $scriptPath;

		return $protocol . '://' . $host . $baseDir;
	}

	public function setFlashMessage($type, $message)
	{
		if (!in_array($type, ['error', 'success', 'info', 'warning'])) {
			throw new InvalidArgumentException("Invalid message type: {$type}");
		}

		if (!empty(trim($message))) {
			$_SESSION[$type] = $message;
		}
	}

	public function hasFlashMessage($type)
	{
		return isset($_SESSION[$type]) && !empty(trim($_SESSION[$type]));
	}

	public function escape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	public function isActiveRoute($route)
	{
		return ($this->currentRoute == $route) ? 'active' : '';
	}

	public function isDropdownOpen($routePattern)
	{
		return (strpos($this->currentRoute, $routePattern) !== false) ? 'open' : '';
	}

	public function isDropdownShow($routePattern)
	{
		return (strpos($this->currentRoute, $routePattern) !== false) ? 'show' : '';
	}

	public function renderTableRows($data, $columns, $buttons = null, $options = [])
	{
		$html = '';
		$i = 1;
		foreach ($data as $row) {
			$id = isset($row['id']) ? $row['id'] : (isset($row->id) ? $row->id : $i);

			$rowClass = $this->determineRowClass($row, $options);

			$html .= '<tr' . $rowClass . '>';
			$html .= '<td>' . htmlspecialchars($id) . '</td>';
			foreach ($columns as $col) {
				$value = isset($row[$col]) ? $row[$col] : (isset($row->$col) ? $row->$col : '');

				$cellClass = $this->determineCellClass($row, $col, $options);
				$html .= '<td' . $cellClass . '>' . htmlspecialchars($value) . '</td>';
			}

			if ($buttons !== null) {
				$html .= '<td>';
				$html .= '<div class="btn-group w-100" role="group">';

				if (is_array($buttons) && isset($buttons[0]) && is_string($buttons[0])) {
					$html .= '<a href="' . $buttons[0] . '" class="btn btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>';
					if (isset($buttons[1])) {
						$html .= '<a href="' . $buttons[1] . '" class="btn btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></a>';
					}
				} else if (is_array($buttons)) {
					foreach ($buttons as $button) {
						if (is_array($button)) {
							if (!$this->shouldShowButton($button, $row, $options)) {
								continue;
							}

							$href = $button['href'] ?? '#';
							$class = $this->determineButtonClass($button, $row, $options);
							$title = $button['title'] ?? '';
							$icon = $button['icon'] ?? '';
							$text = $button['text'] ?? '';
							$target = isset($button['target']) ? ' target="' . htmlspecialchars($button['target']) . '"' : '';
							$onclick = isset($button['onclick']) ? ' onclick="' . htmlspecialchars($button['onclick']) . '"' : '';
							$disabled = $this->isButtonDisabled($button, $row, $options) ? ' disabled' : '';

							// Reemplazar {id} y otros placeholders en href y onclick
							$href = $this->replacePlaceholders($href, $row, $id);
							$onclick = $this->replacePlaceholders($onclick, $row, $id);

							$html .= '<a href="' . htmlspecialchars($href) . '" class="' . htmlspecialchars($class) . '" title="' . htmlspecialchars($title) . '"' . $target . $onclick . $disabled . '>';
							if ($icon) {
								$html .= '<i class="' . htmlspecialchars($icon) . '"></i>';
							}
							if ($text) {
								$html .= ($icon ? ' ' : '') . htmlspecialchars($text);
							}
							$html .= '</a>';
						}
					}
				}

				$html .= '</div>';
				$html .= '</td>';
			}

			$html .= '</tr>';
			$i++;
		}
		return $html;
	}

	/**
	 * Determina la clase CSS de la fila basada en condiciones
	 */
	private function determineRowClass($row, $options)
	{
		$rowClass = '';

		$hidden = isset($row['hidden']) ? $row['hidden'] : (isset($row->hidden) ? $row->hidden : null);
		if ($hidden !== null) {
			if ($hidden == 0) {
				$rowClass = ' class="table-warning"';
			} else if ($hidden == 1) {
				$rowClass = ' class="table-light"';
			}
		}

		// Lógica personalizada de clases
		if (isset($options['rowClass'])) {
			if (is_callable($options['rowClass'])) {
				$customClass = $options['rowClass']($row);
				if ($customClass) {
					$rowClass = ' class="' . htmlspecialchars($customClass) . '"';
				}
			} else if (is_array($options['rowClass'])) {
				foreach ($options['rowClass'] as $condition) {
					if (isset($condition['field'], $condition['value'], $condition['class'])) {
						$fieldValue = isset($row[$condition['field']]) ? $row[$condition['field']] : (isset($row->{$condition['field']}) ? $row->{$condition['field']} : null);
						if ($this->evaluateCondition($fieldValue, $condition['value'], $condition['operator'] ?? '==')) {
							$rowClass = ' class="' . htmlspecialchars($condition['class']) . '"';
							break;
						}
					}
				}
			}
		}

		return $rowClass;
	}

	/**
	 * Determina si un botón debe mostrarse
	 */
	private function shouldShowButton($button, $row, $options)
	{
		// Condición de visibilidad específica del botón
		if (isset($button['visible'])) {
			if (is_callable($button['visible'])) {
				return $button['visible']($row);
			} else if (is_array($button['visible'])) {
				$field = $button['visible']['field'] ?? null;
				$value = $button['visible']['value'] ?? null;
				$operator = $button['visible']['operator'] ?? '==';
				if ($field && $value !== null) {
					$fieldValue = isset($row[$field]) ? $row[$field] : (isset($row->$field) ? $row->$field : null);
					return $this->evaluateCondition($fieldValue, $value, $operator);
				}
			}
		}

		return true; // Por defecto, mostrar el botón
	}

	/**
	 * Determina la clase CSS del botón
	 */
	private function determineButtonClass($button, $row, $options)
	{
		$baseClass = $button['class'] ?? 'btn btn-outline-secondary';

		// Clase condicional del botón
		if (isset($button['conditionalClass'])) {
			if (is_callable($button['conditionalClass'])) {
				$conditionalClass = $button['conditionalClass']($row);
				if ($conditionalClass) {
					return $conditionalClass;
				}
			} else if (is_array($button['conditionalClass'])) {
				foreach ($button['conditionalClass'] as $condition) {
					if (isset($condition['field'], $condition['value'], $condition['class'])) {
						$fieldValue = isset($row[$condition['field']]) ? $row[$condition['field']] : (isset($row->{$condition['field']}) ? $row->{$condition['field']} : null);
						if ($this->evaluateCondition($fieldValue, $condition['value'], $condition['operator'] ?? '==')) {
							return $condition['class'];
						}
					}
				}
			}
		}

		return $baseClass;
	}

	/**
	 * Determina si un botón debe estar deshabilitado
	 */
	private function isButtonDisabled($button, $row, $options)
	{
		if (isset($button['disabled'])) {
			if (is_callable($button['disabled'])) {
				return $button['disabled']($row);
			} else if (is_array($button['disabled'])) {
				$field = $button['disabled']['field'] ?? null;
				$value = $button['disabled']['value'] ?? null;
				$operator = $button['disabled']['operator'] ?? '==';
				if ($field && $value !== null) {
					$fieldValue = isset($row[$field]) ? $row[$field] : (isset($row->$field) ? $row->$field : null);
					return $this->evaluateCondition($fieldValue, $value, $operator);
				}
			}
		}

		return false;
	}

	/**
	 * Reemplaza placeholders en strings
	 */
	private function replacePlaceholders($string, $row, $id)
	{
		$string = str_replace('{id}', $id, $string);

		// Reemplazar otros campos de la fila
		foreach ($row as $key => $value) {
			if (is_scalar($value)) {
				$string = str_replace('{' . $key . '}', $value, $string);
			}
		}

		return $string;
	}

	/**
	 * Evalúa una condición
	 */
	private function evaluateCondition($fieldValue, $compareValue, $operator = '==')
	{
		switch ($operator) {
			case '==':
				return $fieldValue == $compareValue;
			case '!=':
				return $fieldValue != $compareValue;
			case '>':
				return $fieldValue > $compareValue;
			case '<':
				return $fieldValue < $compareValue;
			case '>=':
				return $fieldValue >= $compareValue;
			case '<=':
				return $fieldValue <= $compareValue;
			case 'in':
				return is_array($compareValue) && in_array($fieldValue, $compareValue);
			case 'not_in':
				return is_array($compareValue) && !in_array($fieldValue, $compareValue);
			default:
				return $fieldValue == $compareValue;
		}
	}

	/**
	 * Determina la clase CSS de una celda específica
	 */
	private function determineCellClass($row, $column, $options)
	{
		$cellClass = '';

		// Verificar si hay configuración de clases para celdas
		if (isset($options['cellClass'])) {
			if (is_callable($options['cellClass'])) {
				$customClass = $options['cellClass']($row, $column);
				if ($customClass) {
					$cellClass = ' class="' . htmlspecialchars($customClass) . '"';
				}
			} else if (is_array($options['cellClass'])) {
				foreach ($options['cellClass'] as $condition) {
					// Verificar si la condición aplica a esta columna
					if (isset($condition['column']) && $condition['column'] !== $column) {
						continue;
					}

					if (isset($condition['field'], $condition['value'], $condition['class'])) {
						$fieldValue = isset($row[$condition['field']]) ? $row[$condition['field']] : (isset($row->{$condition['field']}) ? $row->{$condition['field']} : null);
						if ($this->evaluateCondition($fieldValue, $condition['value'], $condition['operator'] ?? '==')) {
							$cellClass = ' class="' . htmlspecialchars($condition['class']) . '"';
							break;
						}
					}
				}
			}
		}

		return $cellClass;
	}

	public function getOptions($data, $selectedId = null, $defaultText = 'Seleccionar')
	{
		$options = '';
		$defaultSelected = ($selectedId === null) ? ' selected' : '';
		$options .= "<option disabled{$defaultSelected}>" . htmlspecialchars($defaultText) . "</option>\n";
		foreach ($data as $d) {
			$selected = ($selectedId !== null && $d['id'] == $selectedId) ? ' selected' : '';
			$options .= "<option value=\"{$d['id']}\"{$selected}>{$d['name']}</option>\n";
		}
		return $options;
	}

	/**
	 * Genera la URL completa para una imagen
	 * @param string $imagePath Ruta relativa de la imagen (ej: "products/imagen.jpg")
	 * @return string URL completa de la imagen
	 */
	public function getImageUrl($imagePath)
	{
		$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim($this->getBaseUrl(), '/');
		return $base . '/assets/images/' . ltrim($imagePath, '/');
	}

	/**
	 * Genera la URL completa para un asset
	 * @param string $assetPath Ruta relativa del asset (ej: "css/styles.css")
	 * @return string URL completa del asset
	 */
	public function getAssetUrl($assetPath)
	{
		$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim($this->getBaseUrl(), '/');
		return $base . '/assets/' . ltrim($assetPath, '/');
	}
}
