<div class="container-fluid py-4">
	<div class="row">
		<div class="col-lg-10 col-xl-8 mx-auto">
			<form action="/rifa/confirmarPago" method="POST" id="formPago" enctype="multipart/form-data">
				<input type="hidden" name="total" value="<?php echo (count($_SESSION['boletos_seleccionados'] ?? []) * ($this->params['precio_boleto'] ?? 20)); ?>">
				<input type="hidden" name="cantidad" value="<?php echo count($_SESSION['boletos_seleccionados'] ?? []); ?>">

				<div class="card shadow-sm">
					<div class="card-header bg-success text-white">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<a href="/rifa/seleccionar" class="btn btn-outline-light btn-sm" id="btnVolverSeleccion">
								<i class="bi bi-arrow-left"></i> Volver
							</a>
							<h4 class="mb-0">
								<i class="bi bi-credit-card-2-front me-2"></i>Procesar Pago
							</h4>
							<div class="alert alert-warning mb-0 d-inline-block px-3 py-1" style="background-color: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107;">
								<i class="bi bi-clock-history"></i>
								<strong>Tiempo:</strong>
								<span id="countdown" style="font-weight: bold;">20:00</span>
							</div>
						</div>

						<!-- Barra de progreso -->
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-danger" id="progress-bar" role="progressbar" style="width: 33%"></div>
						</div>

						<!-- Labels de pasos -->
						<div class="d-flex justify-content-between mt-2">
							<small class="step-label active text-white" data-step="1">1. Condiciones</small>
							<small class="step-label text-white-50" data-step="2">2. Tus datos</small>
							<small class="step-label text-white-50" data-step="3">3. Pago y comprobante</small>
						</div>
					</div>

					<div class="card-body p-4">
						<!-- Resumen de compra -->
						<div class="mb-4 pb-4 border-bottom">
							<h5 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2"></i>Resumen de Compra</h5>
							<div class="row g-3 align-items-center">
								<div class="col-md-7">
									<div class="row">
										<div class="col-sm-6">
											<p class="mb-2"><strong>Cantidad de boletos:</strong> <span id="cantidadBoletos"><?php echo count($_SESSION['boletos_seleccionados'] ?? []); ?></span></p>
											<p class="mb-0"><strong>Precio por boleto:</strong> $<span id="precioPorBoleto"><?php echo number_format(($this->params['precio_boleto'] ?? 20), 2); ?></span></p>
										</div>
										<div class="col-sm-6">
											<h5 class="text-success mb-1"><strong>Total:</strong></h5>
											<h3 class="text-success mb-0">
												<strong>$<span id="totalPagar"><?php echo number_format((count($_SESSION['boletos_seleccionados'] ?? []) * ($this->params['precio_boleto'] ?? 20)), 2); ?></span></strong>
											</h3>
										</div>
									</div>
								</div>
								<div class="col-md-5">
									<div class="small text-muted mb-2"><i class="bi bi-ticket-perforated"></i> Boletos Seleccionados</div>
									<div class="boletos-lista p-2 bg-light rounded" style="max-height: 120px; overflow-y: auto;">
										<?php
										if (isset($_SESSION['boletos_seleccionados']) && is_array($_SESSION['boletos_seleccionados'])) {
											foreach ($_SESSION['boletos_seleccionados'] as $boleto) {
												echo '<span class="badge bg-primary me-2 mb-2 p-2">#' . htmlspecialchars($boleto) . '</span>';
											}
										} else {
											echo '<p class="text-muted text-center mb-0">No hay boletos seleccionados</p>';
										}
										?>
									</div>
								</div>
							</div>
						</div>

						<!-- PASO 1: Condiciones -->
						<div class="form-step active" id="step-1">
							<h5 class="text-secondary mb-3">
								<i class="bi bi-exclamation-triangle-fill me-2"></i>¿Cómo funciona?
							</h5>
							<div class="alert alert-warning border-warning">
								<div class="mb-3">
									<p class="mb-2"><i class="bi bi-lock-fill text-warning"></i> <strong>Bloqueo Temporal:</strong></p>
									<p class="mb-3 ms-4">Los boletos seleccionados quedarán <strong>bloqueados temporalmente</strong> hasta que se valide tu pago.</p>

									<p class="mb-2"><i class="bi bi-clock-fill text-info"></i> <strong>Tiempo de Validación:</strong></p>
									<p class="mb-3 ms-4">La validación de tu comprobante se realizará en un plazo de <strong>24 horas hábiles</strong>.</p>

									<p class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Pago Validado:</strong></p>
									<p class="mb-3 ms-4">Una vez validado el pago, tus boletos serán <strong>acreditados</strong> y recibirás un <strong>correo electrónico de confirmación</strong> con el detalle de tus números.</p>

									<p class="mb-2"><i class="bi bi-x-circle-fill text-danger"></i> <strong>Pago No Validado:</strong></p>
									<p class="mb-0 ms-4">Si no es posible validar el pago, los boletos se <strong>desbloquearán automáticamente</strong> y estarán disponibles para otros usuarios. Se te notificará por correo.</p>
								</div>
							</div>

							<div class="form-check mb-3">
								<input class="form-check-input" type="checkbox" value="1" id="aceptaTerminos">
								<label class="form-check-label" for="aceptaTerminos">
									He leído y acepto las condiciones de participación y validación del pago.
								</label>
								<div class="invalid-feedback d-block" id="errorTerminos" style="display:none;">
									Debes aceptar las condiciones para continuar.
								</div>
							</div>
						</div>

						<!-- PASO 2: Datos de contacto -->
						<div class="form-step" id="step-2" style="display: none;">
							<h5 class="text-secondary mb-3">
								<i class="bi bi-person-fill me-2"></i>Datos de Contacto
							</h5>
							<div class="row g-3">
								<div class="col-md-6">
									<label for="nombre" class="form-label fw-bold">Nombre Completo *</label>
									<div class="input-group">
										<span class="input-group-text"><i class="bi bi-person"></i></span>
										<input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa tu nombre completo" required>
									</div>
									<div class="invalid-feedback">Ingresa tu nombre completo.</div>
								</div>
								<div class="col-md-6">
									<label for="telefono" class="form-label fw-bold">Número de Teléfono *</label>
									<div class="input-group">
										<span class="input-group-text"><i class="bi bi-telephone"></i></span>
										<input type="tel" class="form-control" id="telefono" name="telefono" placeholder="10 dígitos" required pattern="[0-9\s+()-]{7,}">
									</div>
									<div class="invalid-feedback">Ingresa un teléfono válido.</div>
								</div>
							</div>
							<div class="row g-3 mt-2">
								<div class="col-12">
									<label for="correo" class="form-label fw-bold">Correo Electrónico *</label>
									<div class="input-group">
										<span class="input-group-text"><i class="bi bi-envelope"></i></span>
										<input type="email" class="form-control" id="correo" name="correo" placeholder="tucorreo@ejemplo.com" required>
									</div>
									<div class="invalid-feedback">Ingresa un correo válido.</div>
								</div>
							</div>
						</div>

						<!-- PASO 3: Pago y comprobante -->
						<div class="form-step" id="step-3" style="display: none;">
							<h5 class="text-secondary mb-3">
								<i class="bi bi-bank me-2"></i>Información de Cuenta para Depósito
							</h5>
							<?php $cantBoletos = count($_SESSION['boletos_seleccionados'] ?? []);
							$puedeEfectivo = ($cantBoletos > 10); ?>
							<?php if ($puedeEfectivo): ?>
								<div class="alert alert-info border-info">
									<div class="d-flex align-items-start">
										<i class="bi bi-cash-coin fs-4 me-2 text-success"></i>
										<div>
											<strong>Pago en efectivo disponible:</strong>
											<div>Al comprar más de 10 boletos, puedes pagar en efectivo. Se generará tu orden sin comprobante y te redirigiremos a WhatsApp para crear tu ticket de pago.</div>
										</div>
									</div>
								</div>
							<?php endif; ?>
							<div class="alert alert-success border-success">
								<h6 class="alert-heading mb-2"><i class="bi bi-bank2"></i> Datos Bancarios</h6>
								<div class="row">
									<div class="col-md-6">
										<p class="mb-1"><strong>Banco:</strong> <?php echo htmlspecialchars($this->params['banco']['banco_nombre'] ?? ''); ?></p>
										<p class="mb-1"><strong>N° Cuenta:</strong> <?php echo htmlspecialchars($this->params['banco']['numero_cuenta'] ?? ''); ?></p>
										<p class="mb-1"><strong>Tarjeta:</strong> <?php echo htmlspecialchars($this->params['banco']['cuenta_banco'] ?? ''); ?></p>
										<p class="mb-1"><strong>Cuenta Clabe:</strong> <?php echo htmlspecialchars($this->params['banco']['cuenta_clave'] ?? ''); ?></p>
									</div>
									<div class="col-md-6">
										<p class="mb-0"><strong>Titular:</strong> <?php echo htmlspecialchars($this->params['banco']['titular_cuenta'] ?? ''); ?></p>
									</div>
								</div>
							</div>

							<h5 class="text-secondary mb-3 mt-4">
								<i class="bi bi-cloud-upload me-2"></i>Comprobante de Transferencia
							</h5>

							<div class="row g-3">
								<div class="col-md-6">
									<label for="comprobante" class="form-label fw-bold">Subir Comprobante de Pago</label>
									<div class="input-group">
										<span class="input-group-text"><i class="bi bi-file-earmark-arrow-up"></i></span>
										<input type="file" class="form-control" id="comprobante" name="comprobante" accept="image/*,application/pdf">
									</div>
									<small class="text-muted">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>
									<div class="invalid-feedback d-block" id="fileSizeError" style="display:none">El archivo supera 5MB.</div>
									<div class="text-success mt-2" id="comprobanteNombre" style="display:none">
										<i class="bi bi-check-circle me-1"></i><span id="nombreArchivo"></span>
									</div>
								</div>

								<div class="col-md-6">
									<label for="folio" class="form-label fw-bold">O Folio/Clave de rastreo</label>
									<div class="input-group">
										<span class="input-group-text"><i class="bi bi-receipt"></i></span>
										<input type="text" class="form-control" id="folio" name="folio" placeholder="Ej. TRX12345 o clave">
									</div>
									<small class="text-muted">Comprobante o folio requerido</small>
								</div>
							</div>

							<div class="invalid-feedback d-block" id="folioComprobanteError" style="display:none">
								<i class="bi bi-exclamation-triangle me-1"></i>Debes subir un comprobante o ingresar el folio/clave de rastreo.
							</div>

							<!-- Selector de método de pago -->
							<div class="mt-4">
								<h6 class="fw-bold mb-2"><i class="bi bi-ui-checks-grid me-2"></i>Método de pago</h6>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="metodo_pago" id="metodo_transferencia" value="transferencia" checked>
									<label class="form-check-label" for="metodo_transferencia">
										Transferencia/depósito bancario (requiere comprobante o folio)
									</label>
								</div>
								<div class="form-check mt-2" id="opcion-efectivo" style="display:none;">
									<input class="form-check-input" type="radio" name="metodo_pago" id="metodo_efectivo" value="efectivo">
									<label class="form-check-label" for="metodo_efectivo">
										Efectivo (sin comprobante, te redirigimos a WhatsApp)
									</label>
								</div>
								<small class="text-muted d-block mt-1">Si eliges efectivo y tienes más de 10 boletos, no es necesario subir comprobante.</small>
							</div>
						</div>
					</div>

					<div class="card-footer">
						<div class="d-flex justify-content-between">
							<button type="button" class="btn btn-secondary" id="btn-anterior" style="display: none;">
								<i class="bi bi-arrow-left me-2"></i>Anterior
							</button>
							<button type="button" class="btn btn-success" id="btn-siguiente">
								Siguiente<i class="bi bi-arrow-right ms-2"></i>
							</button>
							<button type="button" class="btn btn-outline-success btn-lg me-2" id="btn-pagar-efectivo" style="display: none;">
								<i class="bi bi-cash-coin me-2"></i>Pagar en efectivo
							</button>
							<button type="submit" class="btn btn-success btn-lg" id="btn-pagar" style="display: none;">
								<i class="bi bi-check-circle-fill me-2"></i>Pagar
								<span id="spinnerPagar" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	window.CONFIG = window.CONFIG || {};
	window.CONFIG.tiempo_expiracion = <?php echo json_encode((int)($this->params['tiempo_expiracion'] ?? 20)); ?>;
	window.CONFIG.precio_boleto = <?php echo json_encode((float)($this->params['precio_boleto'] ?? 20)); ?>;
</script>