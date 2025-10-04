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
			<h3>Mi Sistema</h3>
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
					<a href="/sales" class="nav-link <?php echo $this->isActiveRoute('Dashboard'); ?>" title="Gestión de Ventas">
						<i class="fas fa-chart-line"></i>
						<span class="nav-text">Ventas</span>
					</a>
				</li>
				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('customers'); ?>">
					<a href="#" class="nav-link" title="Gestión de Proveedores">
						<i class="fas fa-truck"></i>
						<span class="nav-text">Customers (pendiente)</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('customers'); ?>">
						<li class="nav-item">
							<a href="/customers/list" class="nav-link <?php echo $this->isActiveRoute('customers/list'); ?>" title="Ver lista de proveedores">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/customers/add" class="nav-link <?php echo $this->isActiveRoute('customers/add'); ?>" title="Agregar nuevo proveedor">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('products'); ?>">
					<a href="#" class="nav-link" title="Gestión de Productos">
						<i class="fas fa-box"></i>
						<span class="nav-text">Productos</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('products'); ?>">
						<li class="nav-item">
							<a href="/products/list" class="nav-link <?php echo $this->isActiveRoute('products/list'); ?>" title="Ver lista de productos">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/products/add" class="nav-link <?php echo $this->isActiveRoute('products/add'); ?>" title="Agregar nuevo producto">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('brands'); ?>">
					<a href="#" class="nav-link" title="Gestión de Marcas">
						<i class="fas fa-tags"></i>
						<span class="nav-text">Marcas</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('brands'); ?>">
						<li class="nav-item">
							<a href="/brands/list" class="nav-link <?php echo $this->isActiveRoute('brands/list'); ?>" title="Ver lista de marcas">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/brands/add" class="nav-link <?php echo $this->isActiveRoute('brands/add'); ?>" title="Agregar nueva marca">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('categories'); ?>">
					<a href="#" class="nav-link" title="Gestión de Categorías">
						<i class="fas fa-layer-group"></i>
						<span class="nav-text">Categorias</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('categories'); ?>">
						<li class="nav-item">
							<a href="/categories/list" class="nav-link <?php echo $this->isActiveRoute('categories/list'); ?>" title="Ver lista de categorías">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/categories/add" class="nav-link <?php echo $this->isActiveRoute('categories/add'); ?>" title="Agregar nueva categoría">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('units'); ?>">
					<a href="#" class="nav-link" title="Gestión de Unidades">
						<i class="fas fa-layer-group"></i>
						<span class="nav-text">Unidades</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('units'); ?>">
						<li class="nav-item">
							<a href="/units/list" class="nav-link <?php echo $this->isActiveRoute('units/list'); ?>" title="Ver lista de unidades">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/units/add" class="nav-link <?php echo $this->isActiveRoute('units/add'); ?>" title="Agregar nueva unidad">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('users'); ?>">
					<a href="#" class="nav-link" title="Gestión de Usuarios">
						<i class="fas fa-users"></i>
						<span class="nav-text">Usuarios</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('users'); ?>">
						<li class="nav-item">
							<a href="/users/list" class="nav-link <?php echo $this->isActiveRoute('users/list'); ?>" title="Ver lista de usuarios">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/users/add" class="nav-link <?php echo $this->isActiveRoute('users/add'); ?>" title="Agregar nuevo usuario">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>


				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('warehouses'); ?>">
					<a href="#" class="nav-link" title="Gestión de Almacenes">
						<i class="fas fa-warehouse"></i>
						<span class="nav-text">Almacenes</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('warehouses'); ?>">
						<li class="nav-item">
							<a href="/warehouses/list" class="nav-link <?php echo $this->isActiveRoute('warehouses/list'); ?>" title="Ver lista de almacenes">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/warehouses/add" class="nav-link <?php echo $this->isActiveRoute('warehouses/add'); ?>" title="Agregar nuevo almacén">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>
				
				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('vendors'); ?>">
					<a href="#" class="nav-link" title="Gestión de Proveedores">
						<i class="fas fa-truck"></i>
						<span class="nav-text">Proveedores</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('vendors'); ?>">
						<li class="nav-item">
							<a href="/vendors/list" class="nav-link <?php echo $this->isActiveRoute('vendors/list'); ?>" title="Ver lista de proveedores">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/vendors/add" class="nav-link <?php echo $this->isActiveRoute('vendors/add'); ?>" title="Agregar nuevo proveedor">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>
				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('stockTypes'); ?>">
					<a href="#" class="nav-link" title="Gestión de Tipos de Stock">
						<i class="fas fa-clipboard-list"></i>
						<span class="nav-text">Tipos de Stock</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('stockTypes'); ?>">
						<li class="nav-item">
							<a href="/stockTypes/list" class="nav-link <?php echo $this->isActiveRoute('stockTypes/list'); ?>" title="Ver lista de tipos de stock">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/stockTypes/add" class="nav-link <?php echo $this->isActiveRoute('stockTypes/add'); ?>" title="Agregar nuevo tipo de stock">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('priceList'); ?>">
					<a href="#" class="nav-link" title="Gestión de Listas de Precios">
						<i class="fas fa-dollar-sign"></i>
						<span class="nav-text">Listas de Precios</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('priceList'); ?>">
						<li class="nav-item">
							<a href="/priceList/list" class="nav-link <?php echo $this->isActiveRoute('priceList/list'); ?>" title="Ver listas de precios">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/priceList/add" class="nav-link <?php echo $this->isActiveRoute('priceList/add'); ?>" title="Agregar nueva lista de precios">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
							</a>
						</li>
					</ul>
				</li>

				<li class="nav-item dropdown <?php echo $this->isDropdownOpen('inventory'); ?>">
					<a href="#" class="nav-link" title="Gestión de Inventario">
						<i class="fas fa-boxes"></i>
						<span class="nav-text">Inventario</span>
						<i class="fas fa-chevron-down dropdown-arrow"></i>
					</a>
					<ul class="nav-dropdown <?php echo $this->isDropdownShow('inventory'); ?>">
						<li class="nav-item">
							<a href="/inventory/list" class="nav-link <?php echo $this->isActiveRoute('inventory/list'); ?>" title="Ver inventario">
								<i class="fas fa-list"></i>
								<span class="nav-text">Lista</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="/inventory/add" class="nav-link <?php echo $this->isActiveRoute('inventory/add'); ?>" title="Agregar registro de inventario">
								<i class="fas fa-plus"></i>
								<span class="nav-text">Agregar</span>
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
						<span class="user-role"><?php echo $_SESSION['user_branch']; ?></span>
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