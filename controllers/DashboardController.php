<?php

class DashboardController extends Controller
{
	private $args;

	public function __construct($args)
	{
		parent::__construct();
		$this->args = $args;
	}

	public function index()
	{
		$this->requireLogin();
		$this->view->render('Dashboard/index', [
			'pageTitle' => 'Dashboard - Rifas',
			'useSidebar' => true,
		], ['dashboard.css']);
	}

	// API: órdenes pendientes
	public function pendientes()
	{
		$this->requireLogin();
		require_once MODELS_PATH . 'OrdenModel.php';
		// Si viene de DataTables (server-side)
		if ($this->isDataTablesRequest()) {
			[$draw, $start, $length, $search, $orderCol, $orderDir] = $this->parseDTParams([
				'id',
				'codigo_orden',
				'cliente',
				'cantidad_boletos',
				'total',
				'comprobante',
				'acciones'
			]);
			try {
				$model = new OrdenModel();
				$result = $model->sspPendientes($start, $length, $search, $orderCol, $orderDir);
				header('Content-Type: application/json');
				echo json_encode([
					'draw' => $draw,
					'recordsTotal' => $result['total'],
					'recordsFiltered' => $result['filtrado'],
					'data' => $result['rows'],
				]);
			} catch (Throwable $e) {
				header('Content-Type: application/json');
				echo json_encode([
					'draw' => $draw,
					'recordsTotal' => 0,
					'recordsFiltered' => 0,
					'data' => [],
					'error' => 'Error al cargar pendientes: ' . $e->getMessage(),
				]);
			}
			exit;
		}
		// Fallback legacy: devolver todo (no recomendado para grandes volúmenes)
		$ordenes = (new OrdenModel())->listarPendientes();
		header('Content-Type: application/json');
		echo json_encode($ordenes);
		exit;
	}

	// API: órdenes aprobadas (vendidos)
	public function vendidos()
	{
		$this->requireLogin();
		require_once MODELS_PATH . 'BoletoModel.php';
		if ($this->isDataTablesRequest()) {
			[$draw, $start, $length, $search, $orderCol, $orderDir] = $this->parseDTParams([
				'rownum',
				'numero',
				'codigo_orden',
				'fecha_venta',
				'cliente'
			]);
			try {
				$model = new BoletoModel();
				$result = $model->sspVendidos($start, $length, $search, $orderCol, $orderDir);
				// Agregar rownum en base a start
				$rows = [];
				$idx = $start + 1;
				foreach ($result['rows'] as $r) {
					$r['rownum'] = $idx++;
					$rows[] = $r;
				}
				header('Content-Type: application/json');
				echo json_encode([
					'draw' => $draw,
					'recordsTotal' => $result['total'],
					'recordsFiltered' => $result['filtrado'],
					'data' => $rows,
				]);
			} catch (Throwable $e) {
				header('Content-Type: application/json');
				echo json_encode([
					'draw' => $draw,
					'recordsTotal' => 0,
					'recordsFiltered' => 0,
					'data' => [],
					'error' => 'Error al cargar vendidos: ' . $e->getMessage(),
				]);
			}
			exit;
		}
		$boletos = (new BoletoModel())->listarVendidos();
		header('Content-Type: application/json');
		echo json_encode($boletos);
		exit;
	}

	// API: boletos bloqueados temporalmente
	public function bloqueados()
	{
		$this->requireLogin();
		require_once MODELS_PATH . 'BoletoModel.php';
		if ($this->isDataTablesRequest()) {
			[$draw, $start, $length, $search, $orderCol, $orderDir] = $this->parseDTParams([
				'rownum',
				'numero',
				'codigo_orden',
				'fecha_expiracion',
				'cliente'
			]);
			try {
				$model = new BoletoModel();
				$result = $model->sspBloqueadosTemporal($start, $length, $search, $orderCol, $orderDir);
				$rows = [];
				$idx = $start + 1;
				foreach ($result['rows'] as $r) {
					$r['rownum'] = $idx++;
					$rows[] = $r;
				}
				header('Content-Type: application/json');
				echo json_encode([
					'draw' => $draw,
					'recordsTotal' => $result['total'],
					'recordsFiltered' => $result['filtrado'],
					'data' => $rows,
				]);
			} catch (Throwable $e) {
				header('Content-Type: application/json');
				echo json_encode([
					'draw' => $draw,
					'recordsTotal' => 0,
					'recordsFiltered' => 0,
					'data' => [],
					'error' => 'Error al cargar bloqueados: ' . $e->getMessage(),
				]);
			}
			exit;
		}
		$boletos = (new BoletoModel())->listarBloqueadosTemporal();
		header('Content-Type: application/json');
		echo json_encode($boletos);
		exit;
	}

	// Acciones aprobar/denegar
	public function aprobar()
	{
		$this->requireMethod('POST');
		$this->requireLogin();
		require_once MODELS_PATH . 'OrdenModel.php';
		$ordenId = (int)($_POST['orden_id'] ?? 0);
		if ($ordenId <= 0) ErrorHandler::handleCustom(400, 'Solicitud inválida', 'Orden inválida');
		$ok = (new OrdenModel())->aprobar($ordenId, (int)($_SESSION['user_id'] ?? 0));
		header('Content-Type: application/json');
		echo json_encode([
			'ok' => $ok,
			'message' => $ok ? 'Orden aprobada y boletos marcados como vendidos.' : 'No se pudo aprobar la orden (ya no está pendiente o ocurrió un error).'
		]);
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
		$ok = (new OrdenModel())->denegar($ordenId, (int)($_SESSION['user_id'] ?? 0), $notas);
		header('Content-Type: application/json');
		echo json_encode([
			'ok' => $ok,
			'message' => $ok ? 'Orden cancelada y boletos liberados.' : 'No se pudo denegar la orden (ya no está pendiente o ocurrió un error).'
		]);
		exit;
	}

	// Obtener comprobante para visualizar o descargar
	public function comprobante()
	{
		$this->requireLogin();
		require_once MODELS_PATH . 'OrdenModel.php';
		$ordenId = (int)($_GET['orden_id'] ?? 0);
		if ($ordenId <= 0) ErrorHandler::handleCustom(400, 'Solicitud inválida', 'Orden inválida');
		$comp = (new OrdenModel())->getComprobante($ordenId);
		if (!$comp || (!$comp['comprobante_ruta'] && !$comp['comprobante_nombre'])) {
			ErrorHandler::handle404('Comprobante no encontrado');
			return;
		}
		if (!empty($comp['comprobante_ruta']) && is_file($comp['comprobante_ruta'])) {
			// Servir archivo
			$path = $comp['comprobante_ruta'];
			$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			$mime = 'application/octet-stream';
			if (in_array($ext, ['jpg', 'jpeg'])) $mime = 'image/jpeg';
			elseif ($ext === 'png') $mime = 'image/png';
			elseif ($ext === 'pdf') $mime = 'application/pdf';
			header('Content-Type: ' . $mime);
			header('Content-Length: ' . filesize($path));
			readfile($path);
			exit;
		}
		// Fallback: si la ruta guardada fue "controllers//uploads/...", reconstruir contra /uploads/comprobantes
		if (!empty($comp['comprobante_ruta'])) {
			$basename = basename($comp['comprobante_ruta']);
			$alt = __DIR__ . '/../uploads/comprobantes/' . $basename;
			if (is_file($alt)) {
				$ext = strtolower(pathinfo($alt, PATHINFO_EXTENSION));
				$mime = 'application/octet-stream';
				if (in_array($ext, ['jpg', 'jpeg'])) $mime = 'image/jpeg';
				elseif ($ext === 'png') $mime = 'image/png';
				elseif ($ext === 'pdf') $mime = 'application/pdf';
				header('Content-Type: ' . $mime);
				header('Content-Length: ' . filesize($alt));
				readfile($alt);
				exit;
			}
		}
		// Si no hay archivo, mostrar nombre (folio)
		header('Content-Type: text/plain; charset=utf-8');
		echo $comp['comprobante_nombre'];
		exit;
	}

	// Helpers
	private function parseDTParams(array $columnWhitelist): array
	{
		$src = array_merge($_GET ?? [], $_POST ?? []);
		$draw = intval($src['draw'] ?? 1);
		$start = max(0, intval($src['start'] ?? 0));
		$length = intval($src['length'] ?? 25);
		if ($length < 1 || $length > 1000) $length = 25;
		$search = '';
		if (isset($src['search'])) {
			if (is_array($src['search'])) {
				$search = trim($src['search']['value'] ?? '');
			} else {
				$search = trim($src['search']);
			}
		}
		$orderColIdx = 0;
		$orderDir = 'asc';
		if (isset($src['order'])) {
			if (is_array($src['order'])) {
				$first = $src['order'][0] ?? null;
				if (is_array($first)) {
					$orderColIdx = intval($first['column'] ?? 0);
					$orderDir = strtolower($first['dir'] ?? 'asc');
				}
			}
		}
		$orderDir = in_array($orderDir, ['asc', 'desc'], true) ? $orderDir : 'asc';
		$orderCol = $columnWhitelist[$orderColIdx] ?? $columnWhitelist[0];
		return [$draw, $start, $length, $search, $orderCol, $orderDir];
	}

	private function isDataTablesRequest(): bool
	{
		// Detectar por presencia de parámetros típicos de DataTables (draw, start, length)
		if (isset($_POST['draw']) || isset($_GET['draw'])) return true;
		if (isset($_POST['start']) || isset($_GET['start'])) return true;
		if (isset($_POST['length']) || isset($_GET['length'])) return true;
		// Fallback al método POST por convención
		return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
	}
}
