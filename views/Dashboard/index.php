<div class="container py-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
		<h2 class="mb-0">Dashboard</h2>
		<div>
			<button class="btn btn-outline-success btn-sm" id="btnRefresh"><i class="bi bi-arrow-clockwise"></i> Refrescar</button>
		</div>
	</div>

	<!-- Métricas principales -->
	<div class="row g-3 mb-4">
		<div class="col-6 col-md-3">
			<div class="card stat-card fade-in">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<p class="stat-label mb-1">Pendientes</p>
						<p class="stat-number mb-0" id="statPendientes">0</p>
					</div>
					<div class="stat-icon bg-warning-light">
						<i class="bi bi-hourglass-split"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-3">
			<div class="card stat-card fade-in">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<p class="stat-label mb-1">Vendidos</p>
						<p class="stat-number mb-0" id="statVendidos">0</p>
					</div>
					<div class="stat-icon bg-success-light">
						<i class="bi bi-check2-circle"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-3">
			<div class="card stat-card fade-in">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<p class="stat-label mb-1">Bloqueados</p>
						<p class="stat-number mb-0" id="statBloqueados">0</p>
					</div>
					<div class="stat-icon bg-info-light">
						<i class="bi bi-lock-fill"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-md-3">
			<div class="card stat-card fade-in">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<p class="stat-label mb-1">Recaudado</p>
						<p class="stat-number mb-0" id="statRecaudado">$ 0.00</p>
					</div>
					<div class="stat-icon bg-danger-light">
						<i class="bi bi-cash-coin"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Avance de ventas -->
	<div class="card mb-4">
		<div class="card-body">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<strong>Avance de ventas</strong>
				<small id="lblAvance">0 / 0</small>
			</div>
			<div class="progress">
				<div id="barAvance" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
			</div>
		</div>
	</div>

	<ul class="nav nav-tabs" id="dashTabs" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">Pendientes</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="vendidos-tab" data-bs-toggle="tab" data-bs-target="#vendidos" type="button" role="tab">Vendidos</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="bloqueados-tab" data-bs-toggle="tab" data-bs-target="#bloqueados" type="button" role="tab">Bloqueados Temporales</button>
		</li>
	</ul>

	<div class="tab-content mt-3">
		<div class="tab-pane fade show active" id="pendientes" role="tabpanel">
			<div class="table-responsive">
				<table class="table table-striped align-middle table-custom" id="tablaPendientes">
					<thead>
						<tr>
							<th>ID</th>
							<th>Código</th>
							<th>Cliente</th>
							<th>Boletos</th>
							<th>Total</th>
							<th>Comprobante</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<tr class="empty-state">
							<td colspan="7"><i class="bi bi-inbox"></i><br>Sin órdenes pendientes</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="tab-pane fade" id="vendidos" role="tabpanel">
			<div class="table-responsive">
				<table class="table table-bordered table-custom" id="tablaVendidos">
					<thead>
						<tr>
							<th>#</th>
							<th>Número</th>
							<th>Orden</th>
							<th>Fecha venta</th>
							<th>Cliente</th>
						</tr>
					</thead>
					<tbody>
						<tr class="empty-state">
							<td colspan="5"><i class="bi bi-inbox"></i><br>Sin boletos vendidos</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="tab-pane fade" id="bloqueados" role="tabpanel">
			<div class="table-responsive">
				<table class="table table-bordered table-custom" id="tablaBloqueados">
					<thead>
						<tr>
							<th>#</th>
							<th>Número</th>
							<th>Orden</th>
							<th>Expira</th>
							<th>Cliente</th>
						</tr>
					</thead>
					<tbody>
						<tr class="empty-state">
							<td colspan="5"><i class="bi bi-inbox"></i><br>Sin boletos bloqueados</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	const base = window.baseUrl || '';

	async function fetchJSON(url, opts) {
		const res = await fetch(url, opts);
		if (!res.ok) throw new Error('HTTP ' + res.status);
		return res.json();
	}

	async function cargarPendientes() {
		const data = await fetchJSON(base + 'dashboard/pendientes');
		const tbody = document.querySelector('#tablaPendientes tbody');
		tbody.innerHTML = '';
		if (!Array.isArray(data) || data.length === 0) {
			return; // deja el estado vacío
		}
		tbody.innerHTML = '';
		data.forEach(row => {
			const tr = document.createElement('tr');
			const cliente = `${row.nombre_completo || ''} <br><small>${row.telefono || ''} · ${row.correo || ''}</small>`;
			const compUrl = base + 'dashboard/comprobante?orden_id=' + row.id;
			tr.innerHTML = `
      <td>${row.id}</td>
      <td>${row.codigo_orden}</td>
      <td>${cliente}</td>
      <td>${row.cantidad_boletos}</td>
      <td>$ ${Number(row.total).toFixed(2)}</td>
      <td>
        <a class="btn btn-sm btn-outline-secondary" href="${compUrl}" target="_blank">
          Ver
        </a>
      </td>
      <td>
        <div class="btn-group">
          <button class="btn btn-success btn-sm" onclick="aprobar(${row.id})"><i class="bi bi-check2-circle"></i> Aceptar</button>
          <button class="btn btn-danger btn-sm" onclick="denegar(${row.id})"><i class="bi bi-x-circle"></i> Denegar</button>
        </div>
      </td>
    `;
			tbody.appendChild(tr);
		});
	}

	async function cargarVendidos() {
		const data = await fetchJSON(base + 'dashboard/vendidos');
		const tbody = document.querySelector('#tablaVendidos tbody');
		tbody.innerHTML = '';
		if (!Array.isArray(data) || data.length === 0) {
			return;
		}
		tbody.innerHTML = '';
		data.forEach((row, i) => {
			const tr = document.createElement('tr');
			const cliente = `${row.nombre_completo || ''} <br><small>${row.telefono || ''} · ${row.correo || ''}</small>`;
			tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${row.numero}</td>
      <td>${row.codigo_orden || ''}</td>
      <td>${row.fecha_venta || ''}</td>
      <td>${cliente}</td>
    `;
			tbody.appendChild(tr);
		});
	}

	async function cargarBloqueados() {
		const data = await fetchJSON(base + 'dashboard/bloqueados');
		const tbody = document.querySelector('#tablaBloqueados tbody');
		tbody.innerHTML = '';
		if (!Array.isArray(data) || data.length === 0) {
			return;
		}
		tbody.innerHTML = '';
		data.forEach((row, i) => {
			const tr = document.createElement('tr');
			const cliente = `${row.nombre_completo || ''} <br><small>${row.telefono || ''} · ${row.correo || ''}</small>`;
			tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${row.numero}</td>
      <td>${row.codigo_orden || ''}</td>
      <td>${row.fecha_expiracion || ''}</td>
      <td>${cliente}</td>
	`;
			tbody.appendChild(tr);
		});
	}

	function showAlert(type, message) {
		const container = document.querySelector('.container');
		const alert = document.createElement('div');
		alert.className = `alert alert-${type} alert-dismissible fade show`;
		alert.role = 'alert';
		alert.innerHTML = `
			${message}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		`;
		container.prepend(alert);
		setTimeout(() => {
			const bs = bootstrap.Alert.getOrCreateInstance(alert);
			bs.close();
		}, 4000);
	}

	async function aprobar(id) {
		if (!confirm('¿Confirmas aprobar esta orden? Los boletos quedarán como vendidos.')) return;
		const btns = document.querySelectorAll(`button[onclick="aprobar(${id})"], button[onclick="denegar(${id})"]`);
		btns.forEach(b => b.disabled = true);
		try {
			const fd = new FormData();
			fd.append('orden_id', id);
			const res = await fetch(base + 'dashboard/aprobar', {
				method: 'POST',
				body: fd
			});
			const data = await res.json();
			if (data.ok) {
				showAlert('success', data.message || 'Orden aprobada.');
				await cargarPendientes();
				await cargarVendidos();
			} else {
				showAlert('danger', data.message || 'No se pudo aprobar la orden.');
			}
		} catch (e) {
			showAlert('danger', 'Error de red al aprobar.');
		} finally {
			btns.forEach(b => b.disabled = false);
		}
	}

	async function denegar(id) {
		const notas = prompt('Motivo de denegación (opcional)');
		if (!confirm('¿Confirmas denegar esta orden? Los boletos serán liberados.')) return;
		const btns = document.querySelectorAll(`button[onclick="aprobar(${id})"], button[onclick="denegar(${id})"]`);
		btns.forEach(b => b.disabled = true);
		try {
			const fd = new FormData();
			fd.append('orden_id', id);
			if (notas) fd.append('notas', notas);
			const res = await fetch(base + 'dashboard/denegar', {
				method: 'POST',
				body: fd
			});
			const data = await res.json();
			if (data.ok) {
				showAlert('success', data.message || 'Orden cancelada.');
				await cargarPendientes();
				await cargarBloqueados();
			} else {
				showAlert('danger', data.message || 'No se pudo denegar la orden.');
			}
		} catch (e) {
			showAlert('danger', 'Error de red al denegar.');
		} finally {
			btns.forEach(b => b.disabled = false);
		}
	}

	// Inicialización de pestañas y carga inicial
	window.addEventListener('DOMContentLoaded', () => {
		cargarPendientes();
		const vendTab = document.getElementById('vendidos-tab');
		const bloqTab = document.getElementById('bloqueados-tab');
		if (vendTab) vendTab.addEventListener('shown.bs.tab', cargarVendidos);
		if (bloqTab) bloqTab.addEventListener('shown.bs.tab', cargarBloqueados);

		// Refrescar
		document.getElementById('btnRefresh')?.addEventListener('click', () => {
			Promise.all([cargarPendientes(), cargarVendidos(), cargarBloqueados(), cargarResumen()]);
		});

		// Abrir pestaña según hash
		if (location.hash === '#vendidos' && vendTab) {
			new bootstrap.Tab(vendTab).show();
			cargarVendidos();
		} else if (location.hash === '#bloqueados' && bloqTab) {
			new bootstrap.Tab(bloqTab).show();
			cargarBloqueados();
		}

		// Cargar resumen
		cargarResumen();
	});

	async function cargarResumen() {
		try {
			const [pend, vend, bloq, cfg] = await Promise.all([
				fetchJSON(base + 'dashboard/pendientes'),
				fetchJSON(base + 'dashboard/vendidos'),
				fetchJSON(base + 'dashboard/bloqueados'),
				fetchJSON(base + 'rifa/config')
			]);
			const pendientes = Array.isArray(pend) ? pend.length : 0;
			const vendidos = Array.isArray(vend) ? vend.length : 0;
			const bloqueados = Array.isArray(bloq) ? bloq.length : 0;
			const precio = cfg?.precio_boleto ? Number(cfg.precio_boleto) : 20;
			const totalBoletos = cfg?.total_boletos ? Number(cfg.total_boletos) : (vendidos + bloqueados);

			document.getElementById('statPendientes').textContent = pendientes;
			document.getElementById('statVendidos').textContent = vendidos;
			document.getElementById('statBloqueados').textContent = bloqueados;
			document.getElementById('statRecaudado').textContent = `$ ${ (vendidos * precio).toFixed(2) }`;

			const pct = totalBoletos > 0 ? Math.round((vendidos / totalBoletos) * 100) : 0;
			document.getElementById('lblAvance').textContent = `${vendidos.toLocaleString()} / ${totalBoletos.toLocaleString()}`;
			const bar = document.getElementById('barAvance');
			bar.style.width = pct + '%';
			bar.textContent = pct + '%';
		} catch (e) {
			// silencioso
		}
	}
</script>