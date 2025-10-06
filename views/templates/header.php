<!DOCTYPE html>
<html lang="es">
<?php echo $this->getHeadContent(); ?>

<body class="no-sidebar <?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
	<?php echo $this->getMessages(); ?>

	<nav class="navbar navbar-expand-lg bg-dark">
		<div class="container">
			<a class="navbar-brand text-white" href="#">
				<img src="/assets/images/logo.png" alt="Rifas La Paz" width="110" height="55">
			</a>
			<button class=" navbar-toggler bg-body" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto align-items-center">
					<li class="nav-item">
						<a class="nav-link text-white" href="#">
							<i class="bi bi-info-circle-fill"></i> Sobre Nosotros
						</a>
					</li>
					<li class="nav-item ms-3">
						<a class="nav-link text-white" href="#" title="Facebook">
							<i class="bi bi-facebook fs-5"></i>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link text-white" href="#" title="Instagram">
							<i class="bi bi-instagram fs-5"></i>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link text-white" href="#" title="WhatsApp">
							<i class="bi bi-whatsapp fs-5"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>