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
				<table class="table table-striped align-middle display nowrap" style="width:100%" id="tablaPendientes">
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
					<tbody></tbody>
				</table>
			</div>
		</div>

		<div class="tab-pane fade" id="vendidos" role="tabpanel">
			<div class="table-responsive">
				<table class="table table-bordered display nowrap" style="width:100%" id="tablaVendidos">
					<thead>
						<tr>
							<th>#</th>
							<th>Número</th>
							<th>Orden</th>
							<th>Fecha venta</th>
							<th>Cliente</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>

		<div class="tab-pane fade" id="bloqueados" role="tabpanel">
			<div class="table-responsive">
				<table class="table table-bordered display nowrap" style="width:100%" id="tablaBloqueados">
					<thead>
						<tr>
							<th>#</th>
							<th>Número</th>
							<th>Orden</th>
							<th>Expira</th>
							<th>Cliente</th>
						</tr>
					</thead>
					<tbody></tbody>
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

	function clienteHTML(row) {
		const nombre = row.nombre_completo || '';
		const telefono = row.telefono || '';
		const correo = row.correo || '';
		return `${nombre} <br><small>${telefono} · ${correo}</small>`;
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
				if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
					try {
						jQuery('#tablaPendientes').DataTable().ajax.reload(null, false);
					} catch (e) {}
					try {
						jQuery('#tablaVendidos').DataTable().ajax.reload(null, false);
					} catch (e) {}
				}
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
				if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
					try {
						jQuery('#tablaPendientes').DataTable().ajax.reload(null, false);
					} catch (e) {}
					try {
						jQuery('#tablaBloqueados').DataTable().ajax.reload(null, false);
					} catch (e) {}
				}
			} else {
				showAlert('danger', data.message || 'No se pudo denegar la orden.');
			}
		} catch (e) {
			showAlert('danger', 'Error de red al denegar.');
		} finally {
			btns.forEach(b => b.disabled = false);
		}
	}

	// Inicialización DataTables server-side y carga inicial
	let dtPend = null,
		dtVend = null,
		dtBloq = null;
	window.addEventListener('DOMContentLoaded', () => {
		const dtCommon = {
			processing: true,
			serverSide: true,
			deferRender: true,
			scrollY: '50vh',
			scrollX: true,
			searchDelay: 300,
			pageLength: 25,
			lengthMenu: [25, 50, 100, 250],
			language: {
				url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
			},
		};

		// Pendientes
		dtPend = jQuery('#tablaPendientes').DataTable({
			...dtCommon,
			ajax: {
				url: base + 'dashboard/pendientes',
				type: 'POST'
			},
			order: [
				[0, 'desc']
			],
			columns: [{
					data: 'id'
				},
				{
					data: 'codigo_orden'
				},
				{
					data: null,
					render: (d, t, row) => clienteHTML(row)
				},
				{
					data: 'cantidad_boletos'
				},
				{
					data: 'total',
					render: (d) => '$ ' + Number(d || 0).toFixed(2)
				},
				{
					data: null,
					orderable: false,
					render: (d, t, row) => {
						const url = base + 'dashboard/comprobante?orden_id=' + row.id;
						return `<a class="btn btn-sm btn-outline-secondary" href="${url}" target="_blank">Ver</a>`;
					}
				},
				{
					data: null,
					orderable: false,
					render: (d, t, row) => {
						return `<div class=\"btn-group\">\n\						<button class=\"btn btn-success btn-sm\" onclick=\"aprobar(${row.id})\"><i class=\\\"bi bi-check2-circle\\\"></i> Aceptar</button>\n\						<button class=\"btn btn-danger btn-sm\" onclick=\"denegar(${row.id})\"><i class=\\\"bi bi-x-circle\\\"></i> Denegar</button>\n\					</div>`;
					}
				}
			]
		});

		// Vendidos (inicializa al mostrar pestaña)
		const vendTab = document.getElementById('vendidos-tab');
		vendTab?.addEventListener('shown.bs.tab', () => {
			if (dtVend) return;
			dtVend = jQuery('#tablaVendidos').DataTable({
				...dtCommon,
				ajax: {
					url: base + 'dashboard/vendidos',
					type: 'POST'
				},
				order: [
					[3, 'desc']
				],
				columns: [{
						data: 'rownum'
					},
					{
						data: 'numero'
					},
					{
						data: 'codigo_orden'
					},
					{
						data: 'fecha_venta'
					},
					{
						data: null,
						render: (d, t, row) => clienteHTML(row)
					},
				]
			});
		});

		// Bloqueados (inicializa al mostrar pestaña)
		const bloqTab = document.getElementById('bloqueados-tab');
		bloqTab?.addEventListener('shown.bs.tab', () => {
			if (dtBloq) return;
			dtBloq = jQuery('#tablaBloqueados').DataTable({
				...dtCommon,
				ajax: {
					url: base + 'dashboard/bloqueados',
					type: 'POST'
				},
				order: [
					[3, 'desc']
				],
				columns: [{
						data: 'rownum'
					},
					{
						data: 'numero'
					},
					{
						data: 'codigo_orden'
					},
					{
						data: 'fecha_expiracion'
					},
					{
						data: null,
						render: (d, t, row) => clienteHTML(row)
					},
				]
			});
		});

		// Botón refrescar: recargar tablas y resumen
		document.getElementById('btnRefresh')?.addEventListener('click', () => {
			dtPend?.ajax?.reload(null, false);
			dtVend?.ajax?.reload(null, false);
			dtBloq?.ajax?.reload(null, false);
			cargarResumen();
		});

		// Abrir pestaña por hash
		if (location.hash === '#vendidos' && vendTab) {
			new bootstrap.Tab(vendTab).show();
		} else if (location.hash === '#bloqueados' && bloqTab) {
			new bootstrap.Tab(bloqTab).show();
		}

		// Cargar resumen inicial
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

			const pctRaw = totalBoletos > 0 ? ((vendidos / totalBoletos) * 100) : 0;
			const pctClamped = Math.min(100, Math.max(0, pctRaw));
			document.getElementById('lblAvance').textContent = `${vendidos.toLocaleString()} / ${totalBoletos.toLocaleString()}`;
			const bar = document.getElementById('barAvance');
			// Asegurar que se vea aunque sea < 1%
			let barWidth = pctClamped;
			if (pctClamped > 0 && pctClamped < 1) barWidth = 1;
			bar.style.width = barWidth.toFixed(2) + '%';
			bar.textContent = pctClamped.toFixed(2) + '%';
			bar.setAttribute('aria-valuenow', String(Math.round(pctClamped)));
			bar.setAttribute('aria-valuemin', '0');
			bar.setAttribute('aria-valuemax', '100');
			// Semáforo: 0-33 rojo, 34-66 amarillo, 67-100 verde
			bar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
			if (pctClamped < 34) {
				bar.classList.add('bg-danger');
			} else if (pctClamped < 67) {
				bar.classList.add('bg-warning');
			} else {
				bar.classList.add('bg-success');
			}
		} catch (e) {
			// silencioso
		}
	}
</script>