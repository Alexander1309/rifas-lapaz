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
			"useLogin" => true
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

		// Verificar que existan boletos seleccionados
		if (!isset($_POST['boletos']) || !is_array($_POST['boletos']) || empty($_POST['boletos'])) {
			header('Location: ' . constant('URL') . 'rifa/seleccionar');
			exit();
		}

		$this->view->render('Rifa/pago', [
			'pageTitle' => 'Procesar Pago - Rifas La Paz',
			"useLogin" => true
		], ["pago.css", "pago.js"]);
	}

	public function confirmarPago()
	{
		// Verificar que se haya enviado el formulario por POST
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ' . constant('URL') . 'rifa/seleccionar');
			exit();
		}

		// Aquí procesarías el pago y guardarías en la base de datos
		// Por ahora solo mostramos los datos recibidos

		$boletos = $_POST['boletos'] ?? [];
		$total = $_POST['total'] ?? 0;
		$nombre = $_POST['nombre'] ?? '';
		$telefono = $_POST['telefono'] ?? '';
		$email = $_POST['email'] ?? '';
		$ci = $_POST['ci'] ?? '';
		$metodoPago = $_POST['metodo_pago'] ?? '';
		$observaciones = $_POST['observaciones'] ?? '';

		// TODO: Guardar en base de datos
		// TODO: Enviar email de confirmación
		// TODO: Generar comprobante

		$this->view->render('Rifa/confirmacion', [
			'pageTitle' => 'Confirmación de Compra - Rifas La Paz',
			'boletos' => $boletos,
			'total' => $total,
			'nombre' => $nombre,
			'telefono' => $telefono,
			'email' => $email,
			'ci' => $ci,
			'metodoPago' => $metodoPago,
			'observaciones' => $observaciones,
		]);
	}
}
