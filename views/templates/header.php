<!DOCTYPE html>
<html lang="es">
<?php echo $this->getHeadContent(); ?>

<body class="no-sidebar <?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
	<?php echo $this->getMessages(); ?>

	<nav class="navbar navbar-expand-lg bg-dark fixed-top" style="box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);">
		<div class="container">
			<a class="navbar-brand text-white" href="#">
				<img src="/assets/images/logo.png" alt="Rifas La Paz" width="110" height="55">
			</a>
			<button class=" navbar-toggler bg-body" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto align-items-center">
					<li class="nav-item ms-3">
						<a class="btn btn-success btn-lg px-4 fw-bold btn-compra" href="/rifa/seleccionar" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4); animation: pulse 2s infinite;">
							<i class="bi bi-cart-fill me-2"></i>¡COMPRA YA!
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<div style="margin-top: 8%;">