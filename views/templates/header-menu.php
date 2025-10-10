<!DOCTYPE html>
<html lang="es">

<?php echo $this->getHeadContent(); ?>

<body class="<?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
	<!-- Quitar controles de colapso: solo botón móvil -->
	<button class="mobile-toggle" id="mobileToggle">
		<i class="fas fa-bars"></i>
	</button>

	<!-- Sin overlay si no usamos apertura/cierre en desktop -->
	<div class="sidebar-overlay" id="sidebarOverlay"></div>
	<nav class="sidebar" id="sidebar" style="background:#ffffff;border-right:1px solid #e9ecef;">
		<div class="sidebar-header" style="background:#28a745;color:#fff;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;">
			<div class="d-flex align-items-center gap-2">
				<img src="/assets/images/logo.png" alt="RIFAS LA PAZ" style="height:36px;width:auto" />
				<h3 style="margin:0;font-size:1.05rem;letter-spacing:.5px;">RIFAS LA PAZ</h3>
			</div>
			<!-- Eliminado: botón de colapso desktop -->
		</div>

		<div class="nav-container" style="padding:10px 10px 80px 10px;">
			<ul class="sidebar-nav" style="list-style:none;margin:0;padding:0;">
				<li class="nav-item">
					<a href="/dashboard" class="nav-link <?php echo $this->isActiveRoute('dashboard'); ?>" title="Panel Principal" style="display:flex;align-items:center;gap:10px;color:#212529;padding:10px 12px;border-radius:8px;">
						<i class="fas fa-gauge" style="color:#28a745"></i>
						<span class="nav-text" style="font-weight:600">Dashboard</span>
					</a>
				</li>

				<li class="nav-item" style="margin-top:8px;">
					<a href="/rifa/seleccionar" class="nav-link" title="Comprar boletos" style="display:flex;align-items:center;gap:10px;color:#212529;padding:10px 12px;border-radius:8px;">
						<i class="fas fa-ticket" style="color:#28a745"></i>
						<span class="nav-text">Comprar boletos</span>
					</a>
				</li>

				<!-- Nuevo: Configuración de la rifa -->
				<li class="nav-item" style="margin-top:8px;">
					<a href="/configuracion" class="nav-link <?php echo $this->isActiveRoute('configuracion'); ?>" title="Configuración de la rifa" style="display:flex;align-items:center;gap:10px;color:#212529;padding:10px 12px;border-radius:8px;">
						<i class="fas fa-sliders-h" style="color:#28a745"></i>
						<span class="nav-text">Configuración</span>
					</a>
				</li>
			</ul>
		</div>

		<!-- Área de Perfil de Usuario -->
		<div class="user-profile-container" style="position:absolute;bottom:0;left:0;right:0;border-top:1px solid #e9ecef;background:#f8f9fa;padding:10px 12px;">
			<div class="d-flex align-items-center justify-content-between">
				<div class="d-flex align-items-center gap-2">
					<div class="rounded-circle" style="width:36px;height:36px;background:#28a745;color:#fff;display:flex;align-items:center;justify-content:center;">
						<i class="fas fa-user"></i>
					</div>
					<div>
						<div style="font-weight:600;line-height:1;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></div>
						<div style="font-size:.8rem;color:#6c757d;"><?php echo htmlspecialchars($_SESSION['user_role'] ?? ''); ?></div>
					</div>
				</div>
				<a href="/auth/logout" class="btn btn-outline-success btn-sm"><i class="fas fa-right-from-bracket"></i> Salir</a>
			</div>
		</div>
	</nav>

	<div class="main-content" id="mainContent">
		<?php echo $this->getMessages(); ?>