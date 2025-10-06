<!DOCTYPE html>
<html lang="es">

<?php echo $this->getHeadContent(); ?>

<body class="<?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
	<button class="mobile-toggle" id="mobileToggle">
		<i class="fas fa-bars"></i>
	</button>

	<div class="sidebar-overlay" id="sidebarOverlay"></div>
	<nav class="sidebar" id="sidebar">
		<div class="sidebar-header">
			<h3>Colegio La Paz</h3>
			<div class="expand-indicator" id="expandIndicator" title="Expandir menú">
				<i class="fas fa-chevron-right"></i>
			</div>
			<button class="sidebar-toggle" id="sidebarToggle">
				<i class="fas fa-chevron-left"></i>
			</button>
		</div>

		<div class="nav-container">
			<ul class="sidebar-nav">
				<li class="nav-item">
					<a href="/dashboard" class="nav-link <?php echo $this->isActiveRoute('dashboard'); ?>" title="Panel Principal">
						<i class="fas fa-tachometer-alt"></i>
						<span class="nav-text">Dashboard</span>
					</a>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('student'); ?>">
					<a href="#" class="nav-link" title="Gestión de Estudiantes">
						<i class="fas fa-user-graduate"></i>
						<span class="nav-text">Estudiantes</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('student'); ?>">
						<li class="nav-item">
							<a href="/students/list" class="nav-link <?php echo $this->isActiveRoute('student'); ?>" title="Ver lista de estudiantes">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/students/families" class="nav-link <?php echo $this->isActiveRoute('students/families'); ?>" title="Familias (hermanos)">
								<i class="fas fa-users"></i>
								<span class="nav-text">Familias</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/students/debtors" class="nav-link <?php echo $this->isActiveRoute('student/debtors'); ?>" title="Alumnos con adeudo">
								<i class="fas fa-exclamation-circle"></i>
								<span class="nav-text">Deudores</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/students/add" class="nav-link <?php echo $this->isActiveRoute('student/create'); ?>" title="Registrar nuevo estudiante">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Registrar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('level'); ?>">
					<a href="#" class="nav-link" title="Gestión de Niveles">
						<i class="fas fa-layer-group"></i>
						<span class="nav-text">Niveles</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('level'); ?>">
						<li class="nav-item">
							<a href="/levels/list" class="nav-link <?php echo $this->isActiveRoute('level'); ?>" title="Ver lista de niveles">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/levels/add" class="nav-link <?php echo $this->isActiveRoute('level/create'); ?>" title="Registrar nuevo estudiante">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Registrar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('finances'); ?>">
					<a href="#" class="nav-link" title="Gestión Financiera">
						<i class="fas fa-dollar-sign"></i>
						<span class="nav-text">Finanzas</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('finances'); ?>">
						<li class="nav-item">
							<a href="/books/list" class="nav-link <?php echo $this->isActiveRoute('book'); ?>" title="Precios de libros por nivel">
								<i class="fas fa-book"></i>
								<span class="nav-text">Libros</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/monthlypayments/list" class="nav-link <?php echo $this->isActiveRoute('monthly-payment'); ?>" title="Pagos mensuales">
								<i class="fas fa-calendar-check"></i>
								<span class="nav-text">Pagos Mensuales</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/scholarships/list" class="nav-link <?php echo $this->isActiveRoute('scholarship'); ?>" title="Gestión de becas">
								<i class="fas fa-award"></i>
								<span class="nav-text">Becas</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('admin'); ?>">
					<a href="#" class="nav-link" title="Administración">
						<i class="fas fa-cogs"></i>
						<span class="nav-text">Administración</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('admin'); ?>">
						<li class="nav-item">
							<a href="/users/list" class="nav-link <?php echo $this->isActiveRoute('role'); ?>" title="Gestión de role">
								<i class="fas fa-user-cog"></i>
								<span class="nav-text">Usuarios</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/users/add" class="nav-link <?php echo $this->isActiveRoute('user'); ?>" title="Gestión de usuarios">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Crear Usuario</span>
							</a>
						</li>
					</ul>
				</li>




			</ul>
		</div>

		<!-- Área de Perfil de Usuario -->
		<div class="user-profile-container">
			<div class="nav-item user-profile">
				<div class="user-info">
					<div class="user-avatar">
						<div class="avatar-placeholder">
							<i class="fas fa-user"></i>
						</div>
					</div>
					<div class="user-details">
						<span class="user-name"><?php echo $_SESSION['username']; ?></span>
						<span class="user-role"><?php echo $_SESSION['user_role']; ?></span>
					</div>
					<i class="fas fa-chevron-up profile-arrow"></i>
				</div>
				<ul class="user-dropdown">
					<li class="nav-item">
						<a href="/perfil" class="nav-link" title="Ver mi perfil">
							<i class="fas fa-user"></i>
							<span class="nav-text">Perfil</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="/configuracion" class="nav-link" title="Configuración del sistema">
							<i class="fas fa-cog"></i>
							<span class="nav-text">Configuración</span>
						</a>
					</li>
					<li class="nav-item">
						<a href="/auth/logout" class="nav-link" title="Cerrar sesión">
							<i class="fas fa-sign-out-alt"></i>
							<span class="nav-text">Cerrar Sesión</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="main-content" id="mainContent">
		<?php echo $this->getMessages(); ?>