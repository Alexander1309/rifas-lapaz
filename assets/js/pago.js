const STORAGE_KEY = "rifa_pago_tiempo";
const STORAGE_START_KEY = "rifa_pago_inicio";
const countdownElement = document.getElementById("countdown");
let tiempoRestante;
let intervalo;

// Inicializar o recuperar el tiempo
function inicializarTiempo() {
  const tiempoGuardado = localStorage.getItem(STORAGE_KEY);
  const inicioGuardado = localStorage.getItem(STORAGE_START_KEY);

  if (tiempoGuardado && inicioGuardado) {
    // Verificar si la sesión sigue siendo válida
    const ahora = Date.now();
    const tiempoTranscurrido = Math.floor(
      (ahora - parseInt(inicioGuardado)) / 1000
    );
    tiempoRestante = parseInt(tiempoGuardado) - tiempoTranscurrido;

    // Si el tiempo ya expiró
    if (tiempoRestante <= 0) {
      tiempoRestante = 0;
      limpiarStorage();
      mostrarMensajeCancelacion();
      return false;
    }
  } else {
    // Primera vez, iniciar con 10 minutos
    tiempoRestante = 1200;
    localStorage.setItem(STORAGE_KEY, tiempoRestante);
    localStorage.setItem(STORAGE_START_KEY, Date.now());
  }

  return true;
}

function guardarTiempo() {
  localStorage.setItem(STORAGE_KEY, tiempoRestante);
}

function limpiarStorage() {
  localStorage.removeItem(STORAGE_KEY);
  localStorage.removeItem(STORAGE_START_KEY);
}

function actualizarCountdown() {
  const minutos = Math.floor(tiempoRestante / 60);
  const segundos = tiempoRestante % 60;

  // Formatear con ceros a la izquierda
  const minutosFormateados = minutos.toString().padStart(2, "0");
  const segundosFormateados = segundos.toString().padStart(2, "0");

  countdownElement.textContent = `${minutosFormateados}:${segundosFormateados}`;

  // Cambiar color cuando quedan menos de 2 minutos
  if (tiempoRestante <= 120) {
    countdownElement.style.color = "#dc3545";
    countdownElement.parentElement.style.borderColor = "#dc3545";
    countdownElement.parentElement.style.backgroundColor =
      "rgba(220, 53, 69, 0.2)";
  }

  // Cuando llega a 0
  if (tiempoRestante <= 0) {
    clearInterval(intervalo);
    limpiarStorage();
    mostrarMensajeCancelacion();
    return;
  }

  tiempoRestante--;
  guardarTiempo();
}

function mostrarMensajeCancelacion() {
  // Deshabilitar el formulario
  document.getElementById("formPago").style.pointerEvents = "none";
  document.getElementById("formPago").style.opacity = "0.5";

  // Mostrar alerta
  const alertaCancelacion = document.createElement("div");
  alertaCancelacion.className =
    "alert alert-danger alert-dismissible fade show position-fixed top-50 start-50 translate-middle";
  alertaCancelacion.style.zIndex = "9999";
  alertaCancelacion.style.minWidth = "400px";
  alertaCancelacion.style.boxShadow = "0 4px 20px rgba(0,0,0,0.3)";
  alertaCancelacion.innerHTML = `
			<h4 class="alert-heading">
				<i class="bi bi-x-circle-fill"></i> Compra Cancelada
			</h4>
			<p class="mb-0">El tiempo para completar la compra ha expirado por falta de tiempo.</p>
			<hr>
			<p class="mb-0">Serás redirigido a la selección de boletos en <span id="redirectCountdown">5</span> segundos...</p>
		`;
  document.body.appendChild(alertaCancelacion);

  // Countdown de redirección
  let segundosRedireccion = 5;
  const redirectCountdownElement = document.getElementById("redirectCountdown");

  const intervaloRedireccion = setInterval(() => {
    segundosRedireccion--;
    redirectCountdownElement.textContent = segundosRedireccion;

    if (segundosRedireccion <= 0) {
      clearInterval(intervaloRedireccion);
      window.location.href = "/rifa/seleccionar";
    }
  }, 1000);
}

// Limpiar el storage cuando el formulario se envía exitosamente
document.getElementById("formPago").addEventListener("submit", function () {
  limpiarStorage();
});

// Limpiar el storage cuando se hace clic en el botón de volver
document.getElementById("btnVolver").addEventListener("click", function () {
  limpiarStorage();
});

// Iniciar el countdown
if (inicializarTiempo()) {
  actualizarCountdown();
  intervalo = setInterval(actualizarCountdown, 1000);
}
