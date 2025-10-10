<?php

class ConfiguracionController extends Controller
{
	private ConfiguracionModel $model;

	public function __construct($args)
	{
		parent::__construct();
		$this->requireLogin();
		require_once MODELS_PATH . 'ConfiguracionModel.php';
		$this->model = new ConfiguracionModel();
	}

	public function index()
	{
		$claves = [
			'precio_boleto',
			'total_boletos',
			'tiempo_expiracion',
			'banco_nombre',
			'cuenta_banco',
			'numero_cuenta',
			'cuenta_clave',
			'titular_cuenta',
			'rifa_activa'
		];
		$cfg = $this->model->getMany($claves);
		$this->view->render('Configuracion/index', [
			'pageTitle' => 'Configuración de la Rifa',
			'useSidebar' => true,
			'cfg' => $cfg
		], ['dashboard.css']);
	}

	public function guardar()
	{
		$this->requireMethod('POST');
		$this->requireLogin();
		require_once MODELS_PATH . 'ConfiguracionModel.php';
		$model = new ConfiguracionModel();

		$campos = [
			'precio_boleto' => 'decimal',
			'total_boletos' => 'numero',
			'tiempo_expiracion' => 'numero',
			'banco_nombre' => 'texto',
			'cuenta_banco' => 'texto',
			'numero_cuenta' => 'texto',
			'cuenta_clave' => 'texto',
			'titular_cuenta' => 'texto',
			'rifa_activa' => 'boolean'
		];

		$ok = true;
		foreach ($campos as $clave => $tipo) {
			$valor = $_POST[$clave] ?? null;
			if ($tipo === 'numero') $valor = (int)$valor;
			elseif ($tipo === 'decimal') $valor = (float)$valor;
			elseif ($tipo === 'boolean') $valor = isset($_POST[$clave]) ? 1 : 0;
			else $valor = trim((string)$valor);

			$ok = $ok && $model->set($clave, $valor, $tipo);
		}

		if ($ok) $this->setSuccess('Configuración guardada correctamente');
		else $this->setError('No se pudo guardar toda la configuración');

		header('Location: ' . constant('URL') . 'configuracion');
		exit;
	}
}
