<div class="boletos-container">
	<!-- Botón flotante para reabrir el panel en móviles -->
	<button type="button" id="panelFloatToggle" class="panel-float-toggle" aria-label="Mostrar configuración" title="Mostrar configuración" style="display:none;">
		<i class="fas fa-bars"></i>
	</button>
	<?php if (isset($this->params['rifa_activa']) && $this->params['rifa_activa'] == 0): ?>
		<!-- Mensaje de Rifa Inactiva -->
		<div class="container my-5">
			<div class="row justify-content-center">
				<div class="col-lg-6 col-md-8">
					<div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
						<div class="card-body text-center p-5">
							<div class="mb-4">
								<i class="bi bi-clock-history" style="font-size: 5rem; color: #ffc107;"></i>
							</div>
							<h2 class="fw-bold mb-3" style="color: #343a40;">Rifa Temporalmente Inactiva</h2>
							<p class="lead text-muted mb-4">
								La venta de boletos está pausada en este momento.
								Estamos preparando una nueva rifa con premios increíbles.
							</p>
							<div class="alert alert-warning d-inline-block" role="alert">
								<i class="bi bi-info-circle me-2"></i>
								<strong>¡Próximamente!</strong> Mantente atento a nuestras redes sociales para conocer la fecha de inicio.
							</div>
							<div class="mt-4 pt-3">
								<a href="/" class="btn btn-primary btn-lg px-5 me-2" style="border-radius: 50px;">
									<i class="bi bi-house-door me-2"></i>Volver al Inicio
								</a>
								<a href="https://www.facebook.com/" target="_blank" class="btn btn-outline-primary btn-lg px-4" style="border-radius: 50px;">
									<i class="bi bi-facebook me-2"></i>Síguenos
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else: ?>
		<!-- Grid de Boletos -->
		<div class="boletos-grid">
			<h2 class="mb-2 text-center fw-bold">Selecciona tus Boletos</h2>
			<div class="grid-boletos" id="grid-boletos">
				<!-- Los boletos se cargarán dinámicamente con lazy loading -->
			</div>
			<!-- Indicador de carga -->
			<div id="loading-indicator" class="text-center py-4">
				<div class="spinner-border text-success" role="status">
					<span class="visually-hidden">Cargando...</span>
				</div>
				<p class="mt-2 text-muted">Cargando más boletos...</p>
			</div>
		</div>

		<!-- Panel de Configuración Fixed -->
		<div class="panel-configuracion">
			<div class="panel-header d-flex justify-content-between align-items-center">
				<h3 class="fw-bold mb-0">Configuración</h3>
				<button type="button" class="btn btn-sm btn-outline-success" id="panelToggle" aria-expanded="true" aria-controls="panelBody">
					Ocultar
				</button>
			</div>

			<div class="panel-body" id="panelBody">
				<!-- Acordeón de filtros -->
				<div class="accordion mb-3" id="accordionFiltros">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingFiltros">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" aria-expanded="false" aria-controls="collapseFiltros">
								Filtros
							</button>
						</h2>
						<div id="collapseFiltros" class="accordion-collapse collapse" aria-labelledby="headingFiltros" data-bs-parent="#accordionFiltros">
							<div class="accordion-body">
								<div class="config-section">
									<label for="inputBuscarBoleto" class="form-label fw-semibold">Buscar boleto</label>
									<input type="text" class="form-control" id="inputBuscarBoleto" placeholder="Ingresa 5 dígitos (ej. 01234)">
									<small class="text-muted">Al escribir 5 dígitos, se mostrará solo ese boleto; presiona Esc para salir.</small>
								</div>

								<div class="config-section">
									<label for="selectFiltroEstado" class="form-label fw-semibold">Mostrar</label>
									<select id="selectFiltroEstado" class="form-select">
										<option value="todos">Todos</option>
										<option value="disponibles">Solo disponibles</option>
										<option value="vendidos">Solo vendidos</option>
									</select>
									<small class="text-muted">Aplica al listado general (no al resultado de búsqueda exacta).</small>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Acordeón de aleatorio (colapsado) -->
				<div class="accordion mb-3" id="accordionAleatorio">
					<div class="accordion-item">
						<h2 class="accordion-header" id="headingAleatorio">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAleatorio" aria-expanded="false" aria-controls="collapseAleatorio">
								Aleatorio
							</button>
						</h2>
						<div id="collapseAleatorio" class="accordion-collapse collapse" aria-labelledby="headingAleatorio" data-bs-parent="#accordionAleatorio">
							<div class="accordion-body">
								<!-- Cantidad de Boletos -->
								<div class="config-section">
									<label for="cantidadBoletos" class="form-label fw-semibold">Cantidad de Boletos Aleatorios</label>
									<input type="number" class="form-control" id="cantidadBoletos" min="1" max="1000" value="1" placeholder="Máx. 1000">
									<small class="text-muted">Máximo 1000 para selección aleatoria</small>
								</div>

								<!-- Boleto Aleatorio -->
								<div class="config-section">
									<button class="btn btn-aleatorio w-100" id="btnAleatorio">
										<i class="bi bi-shuffle"></i> Boleto Aleatorio
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Resumen de Selección -->
				<div class="config-section resumen">
					<h5 class="fw-semibold mb-3">Boletos Seleccionados</h5>
					<div class="boletos-seleccionados" id="boletosSeleccionados">
						<p class="text-muted text-center">No has seleccionado boletos aún</p>
					</div>
					<div class="resumen-total mt-3">
						<div class="d-flex justify-content-between mb-2">
							<span class="fw-semibold">Total Boletos:</span>
							<span class="badge bg-primary" id="totalBoletos">0</span>
						</div>
						<div class="d-flex justify-content-between">
							<span class="fw-semibold">Total a Pagar:</span>
							<span class="text-success fw-bold fs-5" id="totalPagar">$ 0</span>
						</div>
					</div>
				</div>

				<!-- Botón de Pagar -->
				<div class="config-section">
					<button class="btn btn-pagar w-100" id="btnPagar" disabled>
						<i class="bi bi-cart-check"></i> Pagar Boletos
					</button>
				</div>

				<!-- Botón de Limpiar Selección -->
				<div class="config-section">
					<button class="btn btn-limpiar w-100" id="btnLimpiar">
						<i class="bi bi-trash"></i> Limpiar Selección
					</button>
				</div>
			</div>
		</div>
</div>

<?php endif; ?>

<!-- Datos para JavaScript (inputs ocultos) -->
<?php
// Asegurar valores seguros para atributos numéricos
$precioBoletoVal = htmlspecialchars((string)($this->params['precio_boleto'] ?? 20), ENT_QUOTES, 'UTF-8');
$totalBoletosVal = htmlspecialchars((string)($this->params['total_boletos'] ?? 100000), ENT_QUOTES, 'UTF-8');
// boeltos_vendidos ya viene como JSON desde el controlador
$vendidosJson = is_string($this->params['boletos_vendidos'] ?? null)
	? $this->params['boletos_vendidos']
	: json_encode($this->params['boletos_vendidos'] ?? []);
?>
<?php if (isset($this->params['rifa_activa']) && $this->params['rifa_activa'] == 1): ?>
	<input type="hidden" id="configPrecioBoleto" value="<?php echo $precioBoletoVal; ?>">
	<input type="hidden" id="configTotalBoletos" value="<?php echo $totalBoletosVal; ?>">
	<script type="application/json" id="boletosVendidosData">
		<?php echo $vendidosJson; ?>
	</script>

	<!-- Formulario oculto para enviar datos por POST -->
	<form id="formPagoOculto" action="/rifa/pago" method="POST" style="display: none;">
		<input type="hidden" name="boletos" id="boletosInput">
		<input type="hidden" name="total" id="totalInput">
	</form>
<?php endif; ?>