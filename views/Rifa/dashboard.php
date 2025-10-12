<div class="container py-4">
	<h2 class="mb-4">Dashboard</h2>

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

	function clienteHTML(row) {
		const nombre = row.nombre_completo || '';
		const telefono = row.telefono || '';
		const correo = row.correo || '';
		return `${nombre} <br><small>${telefono} · ${correo}</small>`;
	}

	async function aprobar(id) {
		const fd = new FormData();
		fd.append('orden_id', id);
		const res = await fetch(base + 'dashboard/aprobar', {
			method: 'POST',
			body: fd
		});
		await cargarPendientes();
		await cargarVendidos();
	}

	async function denegar(id) {
		const notas = prompt('Motivo de denegación (opcional)');
		const fd = new FormData();
		fd.append('orden_id', id);
		if (notas) fd.append('notas', notas);
		const res = await fetch(base + 'dashboard/denegar', {
			method: 'POST',
			body: fd
		});
		await cargarPendientes();
		await cargarBloqueados();
	}

	window.addEventListener('DOMContentLoaded', () => {
		// Requiere jQuery y DataTables cargados por el layout
		const dtCommon = {
			processing: true,
			serverSide: true,
			deferRender: true,
			scrollY: '50vh',
			scrollX: true,
			scroller: true,
			searchDelay: 300,
			pageLength: 25,
			lengthMenu: [25, 50, 100, 250],
			language: {
				url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
			},
		};

		// Pendientes
		const dtPend = jQuery('#tablaPendientes').DataTable({
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
						return `<div class="btn-group">
						<button class="btn btn-success btn-sm" onclick="aprobar(${row.id})"><i class=\"bi bi-check2-circle\"></i> Aceptar</button>
						<button class="btn btn-danger btn-sm" onclick="denegar(${row.id})"><i class=\"bi bi-x-circle\"></i> Denegar</button>
					</div>`;
					}
				}
			]
		});

		// Vendidos
		let vendInit = false;
		document.getElementById('vendidos-tab').addEventListener('shown.bs.tab', () => {
			if (vendInit) return;
			vendInit = true;
			jQuery('#tablaVendidos').DataTable({
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

		// Bloqueados
		let bloqInit = false;
		document.getElementById('bloqueados-tab').addEventListener('shown.bs.tab', () => {
			if (bloqInit) return;
			bloqInit = true;
			jQuery('#tablaBloqueados').DataTable({
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
	});
</script>