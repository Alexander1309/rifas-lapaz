<!DOCTYPE html>
<html lang="es">

<?php echo $this->getHeadContent(); ?>

<body class="no-sidebar <?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
	<?php echo $this->getMessages(); ?>

	<nav class="navbar navbar-expand-lg bg-dark">
		<div class="container">
			<a class="navbar-brand text-white" href="#">
				<img src="/docs/5.3/assets/brand/bootstrap-logo.svg" alt="Bootstrap" width="30" height="24">
				Rifas La Paz
			</a>
			<button class="navbar-toggler bg-body" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link active text-white" aria-current="page" href="#">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link text-white" href="#">Features</a>
					</li>
					<li class="nav-item">
						<a class="nav-link text-white" href="#">Pricing</a>
					</li>
					<li class="nav-item">
						<a class="nav-link disabled text-white" aria-disabled="true">Disabled</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>