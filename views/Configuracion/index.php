<div class="container py-4">
	<h2 class="mb-3">Configuración de la rifa</h2>

	<form action="/configuracion/guardar" method="post" class="card p-3">
		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Precio por boleto</label>
				<input type="number" step="0.01" min="0" name="precio_boleto" class="form-control" value="<?php echo htmlspecialchars($cfg['precio_boleto'] ?? 20); ?>">
			</div>
			<div class="col-md-4">
				<label class="form-label">Total de boletos</label>
				<input type="number" min="1" name="total_boletos" class="form-control" value="<?php echo htmlspecialchars($cfg['total_boletos'] ?? 100000); ?>">
			</div>
			<div class="col-md-4">
				<label class="form-label">Tiempo de expiración (minutos)</label>
				<input type="number" min="1" name="tiempo_expiracion" class="form-control" value="<?php echo htmlspecialchars($cfg['tiempo_expiracion'] ?? 20); ?>">
			</div>
		</div>

		<hr>
		<h5>Datos bancarios</h5>
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Nombre del banco</label>
				<input type="text" name="banco_nombre" class="form-control" value="<?php echo htmlspecialchars($cfg['banco_nombre'] ?? ''); ?>">
			</div>
			<div class="col-md-6">
				<label class="form-label">Nombre de la cuenta</label>
				<input type="text" name="cuenta_banco" class="form-control" value="<?php echo htmlspecialchars($cfg['cuenta_banco'] ?? ''); ?>">
			</div>
			<div class="col-md-4">
				<label class="form-label">Número de cuenta</label>
				<input type="text" name="numero_cuenta" class="form-control" value="<?php echo htmlspecialchars($cfg['numero_cuenta'] ?? ''); ?>">
			</div>
			<div class="col-md-4">
				<label class="form-label">CLABE</label>
				<input type="text" name="cuenta_clave" class="form-control" value="<?php echo htmlspecialchars($cfg['cuenta_clave'] ?? ''); ?>">
			</div>
			<div class="col-md-4">
				<label class="form-label">Titular</label>
				<input type="text" name="titular_cuenta" class="form-control" value="<?php echo htmlspecialchars($cfg['titular_cuenta'] ?? ''); ?>">
			</div>
		</div>

		<hr>
		<div class="form-check form-switch">
			<input class="form-check-input" type="checkbox" role="switch" id="rifa_activa" name="rifa_activa" <?php echo !empty($cfg['rifa_activa']) ? 'checked' : ''; ?>>
			<label class="form-check-label" for="rifa_activa">Rifa activa</label>
		</div>

		<div class="mt-3 d-flex gap-2">
			<button class="btn btn-success" type="submit">Guardar</button>
			<a class="btn btn-outline-secondary" href="/dashboard">Cancelar</a>
		</div>
	</form>
</div>