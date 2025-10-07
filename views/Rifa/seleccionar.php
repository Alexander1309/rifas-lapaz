<div class="boletos-container">
	<!-- Grid de Boletos -->
	<div class="boletos-grid">
		<h2 class="mb-4 text-center fw-bold">Selecciona tus Boletos</h2>
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
		<div class="panel-header">
			<h3 class="fw-bold mb-3">Configuración</h3>
		</div>

		<div class="panel-body">
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

<!-- Datos para JavaScript -->
<script>
	// Boletos ya vendidos
	window.BOLETOS_VENDIDOS_DATA = <?php echo $boletos_vendidos ?? '[]'; ?>;
	window.CONFIG = {
		precio_boleto: <?php echo json_encode($this->params['precio_boleto'] ?? 20); ?>,
		total_boletos: <?php echo json_encode($this->params['total_boletos'] ?? 100000); ?>
	};
	try {
		window.BOLETOS_VENDIDOS_DATA = <?php echo $this->params['boletos_vendidos'] ?? '[]'; ?>;
	} catch (e) {}
</script>

<!-- Formulario oculto para enviar datos por POST -->
<form id="formPagoOculto" action="/rifa/pago" method="POST" style="display: none;">
	<input type="hidden" name="boletos" id="boletosInput">
	<input type="hidden" name="total" id="totalInput">
</form>