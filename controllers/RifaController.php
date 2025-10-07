<?php


class RifaController extends Controller
{
	private $args;

	public function __construct($args)
	{
		parent::__construct();
		$this->args = $args;
	}

	public function index()
	{
		$this->view->render('Rifa/index', [
			'pageTitle' => 'Rifas La Paz - Participa',
		]);
	}

	public function seleccionar()
	{

		$this->view->render('Rifa/seleccionar', [
			'pageTitle' => 'Seleccionar Boletos - Rifas La Paz',
			"useLogin" => true,
		], [
			'boletos.css',
			'boletos.js'
		]);
	}

	public function pago()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ' . constant('URL') . 'rifa/seleccionar');
			exit();
		}

		// Obtener boletos: primero intentar JSON, luego array tradicional
		$boletos = [];
		if (isset($_POST['boletos_json'])) {
			// Decodificar JSON
			$boletos = json_decode($_POST['boletos_json'], true);
			if (!is_array($boletos)) {
				$boletos = [];
			}
		} elseif (isset($_POST['boletos']) && is_array($_POST['boletos'])) {
			// Compatibilidad con método anterior
			$boletos = $_POST['boletos'];
		}

		// Verificar que existan boletos seleccionados
		if (empty($boletos)) {
			header('Location: ' . constant('URL') . 'rifa/seleccionar');
			exit();
		}

		// Guardar boletos en sesión para la página de pago
		$_SESSION['boletos_seleccionados'] = $boletos;

		$this->view->render('Rifa/pago', [
			'pageTitle' => 'Procesar Pago - Rifas La Paz',
			"useLogin" => true
		], ["pago.css", "pago.js"]);
	}

	public function confirmarPago()
	{
		// Este método será implementado más adelante
		echo "Funcionalidad de confirmar pago en desarrollo";
	}
}
