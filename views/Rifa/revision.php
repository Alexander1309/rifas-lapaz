<div class="container py-4">
	<h2 class="mb-4">Revisión de Pagos</h2>
	<div class="alert alert-info">Vista simple para aprobar/denegar pagos. Puedes integrarla a tu dashboard.</div>
	<table class="table table-striped" id="tablaPendientes">
		<thead>
			<tr>
				<th>ID</th>
				<th>Código</th>
				<th>Cantidad</th>
				<th>Total</th>
				<th>Estado</th>
				<th>Acciones</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
<script>
	async function cargarPendientes() {
		const res = await fetch('/rifa/pendientes');
		const data = await res.json();
		const tbody = document.querySelector('#tablaPendientes tbody');
		tbody.innerHTML = '';
		data.forEach(row => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
      <td>${row.id}</td>
      <td>${row.codigo_orden}</td>
      <td>${row.cantidad_boletos}</td>
      <td>$ ${Number(row.total).toFixed(2)}</td>
      <td>${row.estado}</td>
      <td>
        <button class="btn btn-success btn-sm" onclick="aprobar(${row.id})">Aprobar</button>
        <button class="btn btn-danger btn-sm" onclick="denegar(${row.id})">Denegar</button>
      </td>
    `;
			tbody.appendChild(tr);
		})
	}
	async function aprobar(id) {
		const fd = new FormData();
		fd.append('orden_id', id);
		const res = await fetch('/rifa/aprobar', {
			method: 'POST',
			body: fd
		});
		await cargarPendientes();
	}
	async function denegar(id) {
		const notas = prompt('Motivo de denegación (opcional)');
		const fd = new FormData();
		fd.append('orden_id', id);
		if (notas) fd.append('notas', notas);
		const res = await fetch('/rifa/denegar', {
			method: 'POST',
			body: fd
		});
		await cargarPendientes();
	}
	window.addEventListener('DOMContentLoaded', cargarPendientes);
</script>