<?php

class Mailer
{
	private $mail; // instancia de PHPMailer si está disponible
	private Logger $logger;
	private bool $available = false;

	public function __construct()
	{
		$this->logger = new Logger();
		$this->initialize();
	}

	private function initialize(): void
	{
		// Cargar PHPMailer desde libs/PHPMailer (sin Composer)
		$libBase = defined('LIBS_PATH') ? LIBS_PATH : (__DIR__ . '/');
		$phpmDir = rtrim($libBase, '/\\') . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR;
		$files = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];
		foreach ($files as $f) {
			$path = $phpmDir . $f;
			if (file_exists($path)) {
				require_once $path;
			}
		}
		// Intentar instanciar PHPMailer si está disponible
		try {
			if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
				$this->mail = new \PHPMailer\PHPMailer\PHPMailer(true);
				$this->configure();
				$this->available = true;
				$this->logger->logInfo('Mailer inicializado', null, 'Mailer::initialize', [
					'host' => defined('SMTP_HOST') ? SMTP_HOST : null,
					'port' => defined('SMTP_PORT') ? SMTP_PORT : null,
					'secure' => defined('SMTP_SECURE') ? SMTP_SECURE : null,
					'from' => defined('MAIL_FROM') ? MAIL_FROM : null
				]);
			} else {
				$this->available = false;
				$this->logger->logInfo('PHPMailer no disponible, modo log-only', null, 'Mailer::initialize');
			}
		} catch (\Throwable $e) {
			$this->available = false;
			$this->logger->logError('No se pudo inicializar PHPMailer: ' . $e->getMessage(), __FILE__, __LINE__);
		}
	}

	private function configure(): void
	{
		if (!$this->available) return;
		$this->mail->CharSet = 'UTF-8';
		$this->mail->isSMTP();
		$this->mail->Host = SMTP_HOST;
		$this->mail->Port = SMTP_PORT;
		$this->mail->SMTPAuth = true;
		$this->mail->Username = SMTP_USER;
		$this->mail->Password = SMTP_PASS;
		// Timeouts razonables
		if (property_exists($this->mail, 'Timeout')) {
			$this->mail->Timeout = 20;
		}
		if (property_exists($this->mail, 'SMTPKeepAlive')) {
			$this->mail->SMTPKeepAlive = false;
		}

		// Ajuste automático de seguridad según puerto si no está claro
		$secure = SMTP_SECURE;
		if (empty($secure)) {
			$secure = (SMTP_PORT == 465) ? 'ssl' : ((SMTP_PORT == 587) ? 'tls' : '');
		}
		if (!empty($secure)) {
			$this->mail->SMTPSecure = $secure; // 'tls' o 'ssl'
		}

		// Debug SMTP si estamos en modo debug, y redirigir salida al logger
		if (defined('DEBUG_MODE') && DEBUG_MODE === true && property_exists($this->mail, 'SMTPDebug')) {
			$this->mail->SMTPDebug = 2; // Verbose
			$this->mail->Debugoutput = function ($str, $level) {
				$this->logger->logInfo('SMTPDebug', null, 'Mailer::SMTP', [
					'level' => $level,
					'message' => $str
				]);
			};
		}
		$this->mail->isHTML(true);

		$from = MAIL_FROM ?: SMTP_USER;
		$fromName = MAIL_FROM_NAME ?: 'Rifas La Paz';
		$this->mail->setFrom($from, $fromName);
		// Envelope sender (Return-Path)
		if (property_exists($this->mail, 'Sender') && !empty($from)) {
			$this->mail->Sender = $from;
		}
		if (!empty(MAIL_REPLY_TO)) {
			$this->mail->addReplyTo(MAIL_REPLY_TO, MAIL_REPLY_TO_NAME ?: $fromName);
		}

		// DKIM opcional
		if (
			defined('DKIM_DOMAIN') && DKIM_DOMAIN &&
			defined('DKIM_SELECTOR') && DKIM_SELECTOR &&
			defined('DKIM_PRIVATE_KEY_PATH') && DKIM_PRIVATE_KEY_PATH && is_readable(DKIM_PRIVATE_KEY_PATH)
		) {
			$this->mail->DKIM_domain = DKIM_DOMAIN;
			$this->mail->DKIM_selector = DKIM_SELECTOR;
			$this->mail->DKIM_private = DKIM_PRIVATE_KEY_PATH;
			if (defined('DKIM_PASSPHRASE') && DKIM_PASSPHRASE) {
				$this->mail->DKIM_passphrase = DKIM_PASSPHRASE;
			}
			$this->mail->DKIM_identity = defined('DKIM_IDENTITY') && DKIM_IDENTITY ? DKIM_IDENTITY : $from;
		}

		// List-Unsubscribe opcional
		if (defined('MAIL_LIST_UNSUBSCRIBE') && MAIL_LIST_UNSUBSCRIBE) {
			$this->mail->addCustomHeader('List-Unsubscribe', MAIL_LIST_UNSUBSCRIBE);
		}

		// Opciones SSL configurables desde settings
		if (method_exists($this->mail, 'set')) {
			// Compat con versiones antiguas (no necesario en PHPMailer moderno)
		}
		$this->mail->SMTPOptions = [
			'ssl' => [
				'verify_peer' => defined('SMTP_VERIFY_PEER') ? SMTP_VERIFY_PEER : true,
				'verify_peer_name' => defined('SMTP_VERIFY_PEER_NAME') ? SMTP_VERIFY_PEER_NAME : true,
				'allow_self_signed' => defined('SMTP_ALLOW_SELF_SIGNED') ? SMTP_ALLOW_SELF_SIGNED : false,
			],
		];

		// Validaciones de configuración
		if (empty(SMTP_USER) || empty(SMTP_PASS)) {
			$this->logger->logWarning('SMTP_USER o SMTP_PASS vacíos. No se podrá autenticar con el servidor SMTP.', [
				'host' => SMTP_HOST,
				'port' => SMTP_PORT,
				'secure' => $secure
			]);
		}
	}

	public function send(string $to, string $subject, string $html, ?string $altText = null, array $attachments = []): bool
	{
		if (!MAIL_ENABLED) {
			$this->logger->logInfo('MAIL_DISABLED', null, 'Mailer::send', ['to' => $to, 'subject' => $subject]);
			return true; // no-op cuando está deshabilitado
		}

		if (!$this->available) {
			$this->logger->logInfo('MAIL_FALLBACK_LOG', null, 'Mailer::send', [
				'to' => $to,
				'subject' => $subject,
				'html' => $html
			]);
			return true; // evitar romper el flujo
		}

		try {
			$this->logger->logInfo('Intentando enviar email', null, 'Mailer::send', [
				'to' => $to,
				'subject' => $subject
			]);
			$this->mail->clearAllRecipients();
			$this->mail->clearAttachments();
			$this->mail->addAddress($to);
			$this->mail->Subject = $subject;
			$this->mail->Body = $html;
			$this->mail->AltBody = $altText ?: strip_tags($html);

			foreach ($attachments as $file) {
				// Adjuntos desde string en memoria
				if (is_array($file) && (isset($file['data']) || isset($file['string']))) {
					$data = $file['data'] ?? $file['string'];
					$name = $file['name'] ?? 'archivo.txt';
					$type = $file['type'] ?? 'application/octet-stream';
					$this->mail->addStringAttachment($data, $name, 'base64', $type);
					continue;
				}

				// Adjuntos desde archivo en disco
				$path = is_array($file) ? ($file['path'] ?? '') : (string)$file;
				$name = is_array($file) ? ($file['name'] ?? basename($path)) : basename($path);
				if ($path && is_readable($path)) {
					$this->mail->addAttachment($path, $name);
				}
			}

			$ok = $this->mail->send();
			$msgId = method_exists($this->mail, 'getLastMessageID') ? $this->mail->getLastMessageID() : null;
			$this->logger->logInfo('Resultado envío email', null, 'Mailer::send', [
				'ok' => $ok,
				'to' => $to,
				'subject' => $subject,
				'messageId' => $msgId
			]);
			return $ok;
		} catch (\Throwable $e) {
			$this->logger->logError('MAIL_ERROR: ' . $e->getMessage(), __FILE__, __LINE__, [
				'errorInfo' => (property_exists($this->mail, 'ErrorInfo') ? $this->mail->ErrorInfo : null),
				'host' => SMTP_HOST,
				'port' => SMTP_PORT,
				'secure' => SMTP_SECURE,
				'user' => SMTP_USER ? '[set]' : '[empty]'
			]);
			return false;
		}
	}

	// ============ Plantillas predefinidas ============

	public function enviarConfirmacionBoletos(string $correo, string $nombre, array $boletos, string $codigoOrden, float $total, float $precioBoleto): bool
	{
		$lista = implode(', ', array_map('htmlspecialchars', $boletos));
		$subject = 'Confirmación de compra - Rifas La Paz';
		$html = "<h2>¡Gracias por tu compra, {$this->escape($nombre)}!</h2>
		<p>Confirmamos tus boletos:</p>
		<p><strong>Boletos:</strong> {$lista}</p>
		<p><strong>Código de orden:</strong> {$this->escape($codigoOrden)}</p>
		<p><strong>Total pagado:</strong> $ " . number_format($total, 2) . " (precio por boleto: $ " . number_format($precioBoleto, 2) . ")</p>
		<p>¡Mucha suerte!</p>";
		return $this->send($correo, $subject, $html);
	}

	public function enviarNegacionBoletos(string $correo, string $nombre, array $boletos, string $codigoOrden, ?string $motivo = null): bool
	{
		$lista = implode(', ', array_map('htmlspecialchars', $boletos));
		$subject = 'Actualización de tu orden - Rifas La Paz';
		$motivoText = $motivo ? '<p><strong>Motivo:</strong> ' . $this->escape($motivo) . '</p>' : '';
		$html = "<h2>Hola {$this->escape($nombre)},</h2>
		<p>Lamentamos informarte que tu orden fue cancelada y los boletos han sido liberados:</n>
		<p><strong>Boletos:</strong> {$lista}</p>
		<p><strong>Código de orden:</strong> {$this->escape($codigoOrden)}</p>
		{$motivoText}
		<p>Si crees que se trata de un error, por favor contáctanos respondiendo este correo.</p>";
		return $this->send($correo, $subject, $html);
	}

	public function enviarRecepcionPago(string $correo, string $nombre, array $boletos, string $codigoOrden, float $total): bool
	{
		$lista = implode(', ', array_map('htmlspecialchars', $boletos));
		$subject = 'Pago recibido - En revisión';
		$html = "<h2>Gracias, {$this->escape($nombre)}.</h2>
		<p>Hemos recibido tu pago y está en revisión. Pronto te confirmaremos la compra de tus boletos.</p>
		<p><strong>Código de orden:</strong> {$this->escape($codigoOrden)}</p>
		<p><strong>Boletos bloqueados:</strong> {$lista}</p>
		<p><strong>Total:</strong> $ " . number_format($total, 2) . "</p>";
		return $this->send($correo, $subject, $html);
	}

	// ============ Helpers ============
	private function escape(string $str): string
	{
		return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
}
