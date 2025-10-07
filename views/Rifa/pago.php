<div class="container mt-5 mb-5">
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<div class="card shadow-lg">
				<div class="card-header bg-success text-white text-center py-4">
					<h2 class="mb-0">
						<i class="bi bi-credit-card-2-front"></i> Procesar Pago
					</h2>
					<div class="mt-3">
						<div class="alert alert-warning mb-0 d-inline-block px-4 py-2" style="background-color: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107;">
							<i class="bi bi-clock-history"></i>
							<strong>Tiempo restante:</strong>
							<span id="countdown" style="font-size: 1.3rem; font-weight: bold;">20:00</span>
						</div>
					</div>
				</div>
				<div class="card-body p-5">
					<!-- Accordion de Información -->
					<div class="accordion mb-4" id="accordionInformacion">

						<!-- Resumen de Compra -->
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingResumen">
								<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResumen" aria-expanded="true" aria-controls="collapseResumen">
									<i class="bi bi-info-circle me-2"></i> <strong>Resumen de Compra</strong>
								</button>
							</h2>
							<div id="collapseResumen" class="accordion-collapse collapse show" aria-labelledby="headingResumen" data-bs-parent="#accordionInformacion">
								<div class="accordion-body">
									<div class="row">
										<div class="col-md-6">
											<p class="mb-2"><strong>Cantidad de boletos:</strong> <span id="cantidadBoletos"><?php echo count($_SESSION['boletos_seleccionados'] ?? []); ?></span></p>
											<p class="mb-0"><strong>Precio por boleto:</strong> $<span id="precioPorBoleto">20.00</span></p>
										</div>
										<div class="col-md-6 text-end">
											<h3 class="text-success mb-0">
												<strong>Total: $<span id="totalPagar"><?php echo number_format((count($_SESSION['boletos_seleccionados'] ?? []) * 20), 2); ?></span></strong>
											</h3>
										</div>
									</div>
									<hr>
									<!-- Boletos Seleccionados -->
									<h6 class="fw-semibold mb-3">
										<i class="bi bi-ticket-perforated"></i> Boletos Seleccionados
									</h6>
									<div class="boletos-lista p-3 bg-light rounded">
										<?php
										if (isset($_SESSION['boletos_seleccionados']) && is_array($_SESSION['boletos_seleccionados'])) {
											foreach ($_SESSION['boletos_seleccionados'] as $boleto) {
												echo '<span class="badge bg-primary me-2 mb-2 p-2">#' . htmlspecialchars($boleto) . '</span>';
											}
										} else {
											echo '<p class="text-muted text-center">No hay boletos seleccionados</p>';
										}
										?>
									</div>
								</div>
							</div>
						</div>

						<!-- Información de Cuenta para Depósito -->
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingCuenta">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCuenta" aria-expanded="false" aria-controls="collapseCuenta">
									<i class="bi bi-bank me-2"></i> <strong>Información de Cuenta para Depósito</strong>
								</button>
							</h2>
							<div id="collapseCuenta" class="accordion-collapse collapse" aria-labelledby="headingCuenta" data-bs-parent="#accordionInformacion">
								<div class="accordion-body">
									<div class="alert alert-success mb-0">
										<h6 class="alert-heading"><i class="bi bi-bank2"></i> Datos Bancarios</h6>
										<hr>
										<p class="mb-1"><strong>Banco:</strong> Banco Nacional</p>
										<p class="mb-1"><strong>Tipo de Cuenta:</strong> Caja de Ahorro</p>
										<p class="mb-1"><strong>Número de Cuenta:</strong> 1234567890</p>
										<p class="mb-0"><strong>Titular:</strong> Rifas La Paz</p>
									</div>
								</div>
							</div>
						</div>

						<!-- Proceso de Validación -->
						<div class="accordion-item">
							<h2 class="accordion-header" id="headingValidacion">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseValidacion" aria-expanded="false" aria-controls="collapseValidacion">
									<i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Proceso de Validación y Acreditación</strong>
								</button>
							</h2>
							<div id="collapseValidacion" class="accordion-collapse collapse" aria-labelledby="headingValidacion" data-bs-parent="#accordionInformacion">
								<div class="accordion-body">
									<div class="alert alert-warning border-warning mb-0">
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
								</div>
							</div>
						</div>

					</div>

					<!-- Formulario de Datos del Comprador -->
					<form action="/rifa/confirmarPago" method="POST" id="formPago" enctype="multipart/form-data">
						<!-- Los boletos ya están guardados en la sesión, no necesitamos campos ocultos -->
						<input type="hidden" name="total" value="<?php echo (count($_SESSION['boletos_seleccionados'] ?? []) * 20); ?>">
						<input type="hidden" name="cantidad" value="<?php echo count($_SESSION['boletos_seleccionados'] ?? []); ?>">

						<h5 class="fw-semibold mb-3">
							<i class="bi bi-person-fill"></i> Datos del Comprador
						</h5>

						<div class="row">
							<div class="col-md-6 mb-3">
								<label for="nombre" class="form-label">Nombre Completo *</label>
								<input type="text" class="form-control" id="nombre" name="nombre" required>
							</div>
							<div class="col-md-6 mb-3">
								<label for="telefono" class="form-label">Número de Teléfono *</label>
								<input type="tel" class="form-control" id="telefono" name="telefono" required>
							</div>
						</div>

						<div class="mb-3">
							<label for="correo" class="form-label">Correo Electrónico *</label>
							<input type="email" class="form-control" id="correo" name="correo" required>
						</div>

						<h5 class="fw-semibold mb-3 mt-4">
							<i class="bi bi-cloud-upload"></i> Comprobante de Transferencia
						</h5>

						<div class="mb-4">
							<label for="comprobante" class="form-label">Subir Comprobante de Pago *</label>
							<input type="file" class="form-control" id="comprobante" name="comprobante" accept="image/*,application/pdf" required>
							<small class="form-text text-muted">Formatos aceptados: JPG, PNG, PDF (Máx. 5MB)</small>
						</div>

						<div class="d-grid gap-2">
							<button type="submit" class="btn btn-success btn-lg py-3">
								<i class="bi bi-check-circle-fill"></i> Confirmar y Procesar Pago
							</button>
							<a href="/rifa/seleccionar" class="btn btn-outline-secondary" id="btnVolver">
								<i class="bi bi-arrow-left"></i> Volver a Selección
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>