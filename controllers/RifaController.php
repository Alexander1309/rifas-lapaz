<?php


class RifaController extends Controller
{
	private $args;
	private $config;

	public function __construct($args)
	{
		parent::__construct();
		$this->args = $args;
		require_once MODELS_PATH . 'ConfiguracionModel.php';
		$this->config = new ConfiguracionModel();
	}

	public function index()
	{
		$this->view->render('Rifa/index', [
			'pageTitle' => 'Rifas La Paz - Participa',
		]);
	}

	public function seleccionar()
	{
		// Cargar config clave para JS (precio y total)
		$cfg = $this->config->getMany(['precio_boleto', 'total_boletos', 'rifa_activa']);
		$rifaActiva = isset($cfg['rifa_activa']) ? (int)$cfg['rifa_activa'] : 0;

		require_once MODELS_PATH . 'BoletoModel.php';
		$limite = isset($cfg['total_boletos']) ? (int)$cfg['total_boletos'] : 100000;
		$vendidos = (new BoletoModel())->getVendidosNumeros($limite);

		$this->view->render('Rifa/seleccionar', [
			'pageTitle' => 'Seleccionar Boletos - Rifas La Paz',
			"useLogin" => true,
			'precio_boleto' => $cfg['precio_boleto'] ?? 20,
			'total_boletos' => $cfg['total_boletos'] ?? 100000,
			'boletos_vendidos' => json_encode($vendidos),
			'rifa_activa' => $rifaActiva,
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

		// Normalizar boletos a 6 dígitos (defensivo por si el cliente envía 5 dígitos)
		$boletos = array_values(array_unique(array_filter(array_map(function ($n) {
			$n = preg_replace('/\D/', '', (string)$n);
			return $n === '' ? null : str_pad($n, 5, '0', STR_PAD_LEFT);
		}, $boletos))));

		// Verificar que existan boletos seleccionados
		if (empty($boletos)) {
			header('Location: ' . constant('URL') . 'rifa/seleccionar');
			exit();
		}

		// Guardar boletos en sesión para la página de pago
		$_SESSION['boletos_seleccionados'] = $boletos;

		// Config desde DB
		$precio = (float)($this->config->get('precio_boleto', 20));
		$banco = [
			'banco_nombre' => $this->config->get('banco_nombre', 'Banco'),
			'cuenta_banco' => $this->config->get('cuenta_banco', ''),
			'numero_cuenta' => $this->config->get('numero_cuenta', ''),
			'cuenta_clave' => $this->config->get('cuenta_clave', ''),
			'titular_cuenta' => $this->config->get('titular_cuenta', ''),
		];

		$whats = $this->config->get('whatsapp_numero', '528661126294');
		$whats = preg_replace('/\D+/', '', (string)$whats);
		$this->view->render('Rifa/pago', [
			'pageTitle' => 'Procesar Pago - Rifas La Paz',
			"useLogin" => true,
			'precio_boleto' => $precio,
			'banco' => $banco,
			'tiempo_expiracion' => (int)$this->config->get('tiempo_expiracion', 20),
			'whatsapp' => $whats,
		], ["pago.css", "pago.js"]);
	}

	public function confirmarPago()
	{
		$this->requireMethod('POST');
		require_once MODELS_PATH . 'OrdenModel.php';
		require_once MODELS_PATH . 'BoletoModel.php';
		require_once MODELS_PATH . 'ClienteModel.php';

		$boletos = $_SESSION['boletos_seleccionados'] ?? [];
		// Normalizar de nuevo por seguridad
		$boletos = array_values(array_unique(array_filter(array_map(function ($n) {
			$n = preg_replace('/\D/', '', (string)$n);
			return $n === '' ? null : str_pad($n, 5, '0', STR_PAD_LEFT);
		}, $boletos))));
		if (empty($boletos) || !is_array($boletos)) {
			$this->setMessageAndRedirect('error', 'No hay boletos seleccionados', constant('URL') . 'rifa/seleccionar');
		}

		// Validar datos comprador
		$nombre = trim($_POST['nombre'] ?? '');
		$telefono = trim($_POST['telefono'] ?? '');
		$correo = trim($_POST['correo'] ?? '');
		if ($nombre === '' || $telefono === '' || $correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			$this->setMessageAndRedirect('error', 'Datos del comprador inválidos', constant('URL') . 'rifa/pago');
		}

		// Precio y totales desde DB
		$precio = (float)$this->config->get('precio_boleto', 20);
		$total = $precio * count($boletos);
		// Método de pago
		$metodo = trim($_POST['metodo_pago'] ?? 'transferencia');
		$esEfectivo = ($metodo === 'efectivo' && count($boletos) > 10);

		// Crear o reutilizar cliente y usar SIEMPRE su id para la orden (FK a clientes)
		$usuarioId = 0; // no usar id de tabla user aquí
		$clienteModel = new ClienteModel();
		$clienteId = $clienteModel->getOrCreate($nombre, $telefono, $correo);
		$usuarioId = $clienteId;

		// Verificar que el cliente exista realmente antes de crear la orden (defensivo)
		$dbCheck = Database::getInstance();
		$exists = $dbCheck->fetchOne("SELECT id FROM clientes WHERE id = :id", [':id' => $clienteId]);
		if (!$exists) {
			// Reintentar creación simple
			$clienteId = $clienteModel->getOrCreate($nombre, $telefono, $correo);
			$usuarioId = $clienteId;
			$exists2 = $dbCheck->fetchOne("SELECT id FROM clientes WHERE id = :id", [':id' => $clienteId]);
			if (!$exists2) {
				// No continuar si no existe para evitar FK error
				$logger = new Logger();
				$logger->logError('Cliente no existe tras getOrCreate', __FILE__, __LINE__, [
					'nombre' => $nombre,
					'telefono' => $telefono,
					'correo' => $correo
				]);
				$this->setMessageAndRedirect('error', 'Hubo un problema al registrar tus datos. Intenta de nuevo.', constant('URL') . 'rifa/pago');
			}
		}

		// Log informativo
		$logger = new Logger();
		$logger->logInfo('Preparando creación de orden', null, 'RifaController::confirmarPago', [
			'cliente_id' => $clienteId,
			'usuario_id' => $usuarioId,
			'boletos' => $boletos,
			'total' => $total
		]);

		// Crear orden pendiente con expiración
		$expMins = (int)$this->config->get('tiempo_expiracion', 20);
		$fechaExp = (new DateTime())->modify("+{$expMins} minutes")->format('Y-m-d H:i:s');

		$ordenModel = new OrdenModel();
		$db = Database::getInstance();
		$db->beginTransaction();
		try {
			// Generar un código explícito para poder localizar la orden si lastInsertId falla
			$codigoOrden = strtoupper(bin2hex(random_bytes(5)));
			$ordenId = $ordenModel->crear($usuarioId, count($boletos), $total, $fechaExp, $codigoOrden);
			if ($ordenId <= 0) {
				// Fallback defensivo: intentar localizar por codigo_orden
				$rowOrden = $db->fetchOne("SELECT id FROM ordenes WHERE codigo_orden = :cod ORDER BY id DESC LIMIT 1", [':cod' => $codigoOrden]);
				$ordenId = $rowOrden && isset($rowOrden['id']) ? (int)$rowOrden['id'] : 0;
			}
			if ($ordenId <= 0) {
				// No continuar para evitar violar la FK de boletos.orden_id
				$db->rollback();
				$logger = new Logger();
				$logger->logError('ID de orden inválido tras crear', __FILE__, __LINE__, [
					'cliente_id' => $clienteId,
					'usuario_id' => $usuarioId,
					'codigo_orden' => $codigoOrden,
					'total' => $total,
					'cantidad' => count($boletos)
				]);
				$this->setMessageAndRedirect('error', 'No se pudo crear tu orden. Intenta nuevamente.', constant('URL') . 'rifa/pago');
			}

			// Manejar comprobante/folio según método
			$rutaComprobante = null;
			$nombreComprobante = null;
			$folio = trim($_POST['folio'] ?? '');
			if ($esEfectivo) {
				$nombreComprobante = 'EFECTIVO';
			} else {
				if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
					$nombreComprobante = basename($_FILES['comprobante']['name']);
					$ext = strtolower(pathinfo($nombreComprobante, PATHINFO_EXTENSION));
					if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
						$this->setMessageAndRedirect('error', 'Formato de comprobante inválido', constant('URL') . 'rifa/pago');
					}
					$dir = __DIR__ . '/../uploads/comprobantes';
					if (!is_dir($dir)) {
						@mkdir($dir, 0777, true);
					}
					$dest = $dir . '/' . $ordenId . '_' . time() . '.' . $ext;
					if (!move_uploaded_file($_FILES['comprobante']['tmp_name'], $dest)) {
						$this->setMessageAndRedirect('error', 'No se pudo guardar el comprobante', constant('URL') . 'rifa/pago');
					}
					$rutaComprobante = str_replace('..', '', $dest);
				} elseif ($folio !== '') {
					$nombreComprobante = 'FOLIO:' . $folio;
				} else {
					$this->setMessageAndRedirect('error', 'Debes subir un comprobante o escribir tu folio de transferencia', constant('URL') . 'rifa/pago');
				}
			}

			$ordenModel->adjuntarComprobante($ordenId, $rutaComprobante, $nombreComprobante);

			// Bloquear boletos con control de concurrencia (dentro de la misma transacción)
			$boletoModel = new BoletoModel();
			$resultado = $boletoModel->bloquearTemporal($boletos, $ordenId);

			if (!$resultado['ok']) {
				// Si falla, revertimos todo
				$db->rollback();
				// Limpiar comprobante si se guardó físicamente
				if ($rutaComprobante && is_file($rutaComprobante)) {
					@unlink($rutaComprobante);
				}
				$this->setMessageAndRedirect('error', 'Algunos boletos ya no están disponibles: ' . implode(', ', $resultado['conflictos'] ?? []), constant('URL') . 'rifa/seleccionar');
			}

			// Todo ok, commit
			$db->commit();
		} catch (Throwable $e) {
			$db->rollback();
			if ($rutaComprobante && is_file($rutaComprobante)) {
				@unlink($rutaComprobante);
			}
			$logger = new Logger();
			$logger->logError('Error en confirmarPago (transacción)', __FILE__, __LINE__, ['exception' => $e->getMessage()]);
			$this->setMessageAndRedirect('error', 'No se pudo procesar tu pago. Intenta nuevamente.', constant('URL') . 'rifa/seleccionar');
		}

		// Guardar en sesión datos mínimos de seguimiento
		$_SESSION['orden_pendiente_id'] = $ordenId;

		// Flujo posterior: efectivo => redirigir a WhatsApp con mensaje; transfer => mostrar confirmación
		$orden = $ordenModel->getById($ordenId);
		if ($esEfectivo) {
			$numeros = (new BoletoModel())->obtenerNumerosPorOrden($ordenId);
			$codigo = $orden['codigo_orden'] ?? (string)$ordenId;
			$whats = $this->config->get('whatsapp_numero', '528661126294');
			$whats = preg_replace('/\D+/', '', (string)$whats);
			$msg = "Hola, quiero pagar en efectivo mi orden de rifas.%0A"
				. "Nombre: " . rawurlencode($nombre) . "%0A"
				. "Tel: " . rawurlencode($telefono) . "%0A"
				. "Código: " . rawurlencode($codigo) . "%0A"
				. "Boletos: " . rawurlencode(implode(', ', $numeros)) . "%0A"
				. "Total: $ " . rawurlencode(number_format($total, 2));
			header('Location: https://wa.me/' . $whats . '?text=' . $msg);
			exit;
		} else {
			$this->view->render('Rifa/confirmacion', [
				'pageTitle' => 'Pago en revisión - Rifas La Paz',
				'nombre' => $nombre,
				'telefono' => $telefono,
				'email' => $correo,
				'boletos' => $resultado['bloqueados'],
				'total' => $total,
				'codigo_orden' => $orden['codigo_orden'] ?? null,
				'precio_boleto' => $precio,
				'observaciones' => 'Tu pago está en revisión. Te notificaremos cuando sea aprobado.'
			]);
		}
	}

	// Endpoint JSON para obtener configuración usada por JS
	public function config()
	{
		header('Content-Type: application/json');
		$cfg = $this->config->getMany(['precio_boleto', 'total_boletos', 'tiempo_expiracion']);
		echo json_encode([
			'precio_boleto' => (float)($cfg['precio_boleto'] ?? 20),
			'total_boletos' => (int)($cfg['total_boletos'] ?? 100000),
			'tiempo_expiracion' => (int)($cfg['tiempo_expiracion'] ?? 20)
		]);
		exit;
	}

	// Acciones de admin: aprobar o denegar
	public function aprobar()
	{
		$this->requireMethod('POST');
		$this->requireLogin();
		require_once MODELS_PATH . 'OrdenModel.php';
		$ordenId = (int)($_POST['orden_id'] ?? 0);
		if ($ordenId <= 0) ErrorHandler::handleCustom(400, 'Solicitud inválida', 'Orden inválida');
		$ok = (new OrdenModel())->aprobar($ordenId, (int)$_SESSION['user_id']);
		header('Content-Type: application/json');
		echo json_encode(['ok' => $ok]);
		exit;
	}

	public function denegar()
	{
		$this->requireMethod('POST');
		$this->requireLogin();
		require_once MODELS_PATH . 'OrdenModel.php';
		$ordenId = (int)($_POST['orden_id'] ?? 0);
		$notas = trim($_POST['notas'] ?? '');
		if ($ordenId <= 0) ErrorHandler::handleCustom(400, 'Solicitud inválida', 'Orden inválida');
		$ok = (new OrdenModel())->denegar($ordenId, (int)$_SESSION['user_id'], $notas);
		header('Content-Type: application/json');
		echo json_encode(['ok' => $ok]);
		exit;
	}

	// Vista del dashboard de revisión (admin)
	public function revision()
	{
		$this->requireLogin();
		// Redirigir al nuevo dashboard
		header('Location: ' . constant('URL') . 'dashboard');
		exit();
	}

	public function dashboard()
	{
		$this->requireLogin();
		$this->view->render('Rifa/dashboard', [
			'pageTitle' => 'Dashboard - Rifas La Paz',
			'useSidebar' => true,
		], ['dashboard.css']);
	}

	// Opcional: listado simple de órdenes pendientes (para dashboard básico)
	public function pendientes()
	{
		$this->requireLogin();
		require_once MODELS_PATH . 'OrdenModel.php';
		$db = Database::getInstance();
		$rows = $db->fetchAll("SELECT * FROM ordenes WHERE estado='pendiente' ORDER BY created_at DESC");
		header('Content-Type: application/json');
		echo json_encode($rows);
		exit;
	}

	// Validar disponibilidad de boletos seleccionados desde el cliente
	public function validarDisponibilidad()
	{
		$this->requireMethod('POST');
		require_once MODELS_PATH . 'BoletoModel.php';
		$payload = file_get_contents('php://input');
		$data = json_decode($payload, true);
		$boletos = is_array($data) ? ($data['boletos'] ?? []) : [];
		$bm = new BoletoModel();
		$noDisp = $bm->getNoDisponiblesPorNumeros($boletos);
		header('Content-Type: application/json');
		echo json_encode(['no_disponibles' => $noDisp]);
		exit;
	}
}
