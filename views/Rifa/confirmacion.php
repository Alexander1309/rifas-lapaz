<div class="container mt-5 mb-5">
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<!-- Mensaje de Éxito -->
			<div class="text-center mb-4">
				<div class="success-animation mb-4">
					<i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
				</div>
				<h1 class="text-success fw-bold mb-3">¡Compra Exitosa!</h1>
				<p class="lead text-muted">Tu compra ha sido procesada correctamente</p>
			</div>

			<!-- Tarjeta de Confirmación -->
			<div class="card shadow-lg mb-4">
				<div class="card-header bg-success text-white text-center py-3">
					<h4 class="mb-0">
						<i class="bi bi-receipt"></i> Comprobante de Compra
					</h4>
				</div>
				<div class="card-body p-4">
					<!-- Número de Orden -->
					<div class="alert alert-success text-center mb-4">
						<h5 class="mb-0">
							<i class="bi bi-hash"></i> Orden: <strong><?php echo strtoupper(uniqid('RIF-')); ?></strong>
						</h5>
						<small class="text-muted">Guarda este número para futuras consultas</small>
					</div>

					<!-- Información del Comprador -->
					<div class="mb-4">
						<h5 class="fw-semibold border-bottom pb-2 mb-3">
							<i class="bi bi-person-fill"></i> Información del Comprador
						</h5>
						<div class="row">
							<div class="col-md-6 mb-2">
								<strong>Nombre:</strong> <?php echo htmlspecialchars($this->params['nombre'] ?? 'N/A'); ?>
							</div>
							<div class="col-md-6 mb-2">
								<strong>Teléfono:</strong> <?php echo htmlspecialchars($this->params['telefono'] ?? 'N/A'); ?>
							</div>
							<div class="col-md-6 mb-2">
								<strong>Email:</strong> <?php echo htmlspecialchars($this->params['email'] ?? 'N/A'); ?>
							</div>
							<div class="col-md-6 mb-2">
								<strong>CI:</strong> <?php echo htmlspecialchars($this->params['ci'] ?? 'N/A'); ?>
							</div>
						</div>
					</div>

					<!-- Detalles de la Compra -->
					<div class="mb-4">
						<h5 class="fw-semibold border-bottom pb-2 mb-3">
							<i class="bi bi-ticket-perforated"></i> Detalles de la Compra
						</h5>
						<div class="table-responsive">
							<table class="table table-sm">
								<tbody>
									<tr>
										<td><strong>Cantidad de Boletos:</strong></td>
										<td class="text-end"><?php echo count($this->params['boletos'] ?? []); ?></td>
									</tr>
									<tr>
										<td><strong>Precio por Boleto:</strong></td>
										<td class="text-end">Bs. 10</td>
									</tr>
									<tr>
										<td><strong>Método de Pago:</strong></td>
										<td class="text-end text-capitalize"><?php echo htmlspecialchars($this->params['metodoPago'] ?? 'N/A'); ?></td>
									</tr>
									<tr class="table-success">
										<td><strong>Total Pagado:</strong></td>
										<td class="text-end"><strong class="fs-5">Bs. <?php echo htmlspecialchars($this->params['total'] ?? 0); ?></strong></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Boletos Comprados -->
					<div class="mb-4">
						<h5 class="fw-semibold border-bottom pb-2 mb-3">
							<i class="bi bi-ticket-detailed"></i> Tus Boletos
						</h5>
						<div class="boletos-comprados p-3 bg-light rounded">
							<?php
							if (isset($this->params['boletos']) && is_array($this->params['boletos'])) {
								foreach ($this->params['boletos'] as $boleto) {
									echo '<span class="badge bg-success me-2 mb-2 p-2 fs-6">#' . htmlspecialchars($boleto) . '</span>';
								}
							}
							?>
						</div>
					</div>

					<!-- Observaciones -->
					<?php if (!empty($this->params['observaciones'])): ?>
						<div class="mb-4">
							<h5 class="fw-semibold border-bottom pb-2 mb-3">
								<i class="bi bi-chat-left-text"></i> Observaciones
							</h5>
							<p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($this->params['observaciones'])); ?></p>
						</div>
					<?php endif; ?>

					<!-- Información Adicional -->
					<div class="alert alert-info mb-0">
						<h6 class="alert-heading">
							<i class="bi bi-info-circle"></i> Información Importante
						</h6>
						<ul class="mb-0">
							<li>Recibirás un email de confirmación con tus boletos en breve</li>
							<li>Guarda este comprobante para futuras referencias</li>
							<li>Los boletos también están disponibles en tu cuenta</li>
							<li>El sorteo se realizará en la fecha indicada</li>
						</ul>
					</div>
				</div>
			</div>

			<!-- Botones de Acción -->
			<div class="d-grid gap-2 d-md-flex justify-content-md-center">
				<button onclick="window.print()" class="btn btn-primary btn-lg">
					<i class="bi bi-printer"></i> Imprimir Comprobante
				</button>
				<a href="<?php echo constant('URL'); ?>rifa/seleccionar" class="btn btn-success btn-lg">
					<i class="bi bi-ticket-perforated"></i> Comprar Más Boletos
				</a>
				<a href="<?php echo constant('URL'); ?>rifa" class="btn btn-outline-secondary btn-lg">
					<i class="bi bi-house"></i> Volver al Inicio
				</a>
			</div>
		</div>
	</div>
</div>

<style>
	.success-animation {
		animation: scaleIn 0.5s ease-in-out;
	}

	@keyframes scaleIn {
		0% {
			transform: scale(0);
			opacity: 0;
		}

		50% {
			transform: scale(1.1);
		}

		100% {
			transform: scale(1);
			opacity: 1;
		}
	}

	.boletos-comprados {
		max-height: 300px;
		overflow-y: auto;
	}

	.card {
		border: none;
		border-radius: 15px;
	}

	.card-header {
		border-radius: 15px 15px 0 0 !important;
	}

	@media print {

		.btn,
		nav,
		footer {
			display: none !important;
		}

		.card {
			box-shadow: none !important;
		}
	}
</style>