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
				<table class="table table-striped align-middle" id="tablaPendientes">
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
				<table class="table table-bordered" id="tablaVendidos">
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
				<table class="table table-bordered" id="tablaBloqueados">
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

	async function cargarPendientes() {
		const data = await fetchJSON(base + 'dashboard/pendientes');
		const tbody = document.querySelector('#tablaPendientes tbody');
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
		cargarPendientes();
		document.getElementById('vendidos-tab').addEventListener('shown.bs.tab', cargarVendidos);
		document.getElementById('bloqueados-tab').addEventListener('shown.bs.tab', cargarBloqueados);
	});
</script>