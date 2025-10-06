<div class="login-container d-flex align-items-center justify-content-center">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12 col-md-6 col-lg-4">
				<div class="card shadow login-card mt-5">
					<div class="card-header">
						<h5 class="text-center fs-2 mt-2">Iniciar Sesión</h5>
					</div>
					<div class="card-body p-4">
						<form id="loginForm" method="POST" action="<?php echo isset($_SERVER['BASE_URL']) ? $_SERVER['BASE_URL'] : ''; ?>/auth/login">
							<div class="mb-3">
								<label for="username" class="form-label fw-medium">
									<i class="fas fa-user me-2 text-muted"></i>
									Usuario
								</label>
								<input
									type="text"
									id="username"
									name="username"
									required
									class="form-control"
									placeholder="Ingresa tu usuario"
									autocomplete="username">
							</div>

							<div class="mb-3">
								<label for="password" class="form-label fw-medium">
									<i class="fas fa-lock me-2 text-muted"></i>
									Contraseña
								</label>
								<div class="input-group">
									<input
										type="password"
										id="password"
										name="password"
										required
										class="form-control"
										placeholder="Ingresa tu contraseña"
										autocomplete="current-password">
									<button
										type="button"
										id="togglePassword"
										class="btn btn-outline-secondary">
										<i class="fas fa-eye" id="eyeIcon"></i>
									</button>
								</div>
							</div>

							<!-- Recordar sesión -->
							<div class="d-flex justify-content-between align-items-center mb-3">
								<div class="form-check">
									<input
										type="checkbox"
										name="remember"
										class="form-check-input"
										id="rememberCheck">
									<label class="form-check-label" for="rememberCheck">
										Recordar sesión
									</label>
								</div>
								<a href="#" class="text-decoration-none">
									¿Olvidaste tu contraseña?
								</a>
							</div>

							<button
								type="submit"
								class="btn btn-primary w-100 py-2 fw-medium">
								<i class="fas fa-sign-in-alt me-2"></i>
								Iniciar Sesión
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>

<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
	<div class="d-flex justify-content-center align-items-center h-100">
		<div class="bg-white rounded p-4 d-flex align-items-center">
			<div class="spinner-custom me-3"></div>
			<span>Iniciando sesión...</span>
		</div>
	</div>
</div>
</div>
</div>