// ==================== VERSIÓN SIMPLE Y ROBUSTA ====================
console.log("🚀 Cargando pago.js...");

document.addEventListener("DOMContentLoaded", function () {
  console.log("✅ DOM Cargado");

  // ==================== CONTADOR DE TIEMPO ====================
  const STORAGE_KEY = "rifa_pago_tiempo";
  const STORAGE_START_KEY = "rifa_pago_inicio";
  const countdownElement = document.getElementById("countdown");
  let tiempoRestante;
  let intervalo;

  function inicializarTiempo() {
    const tiempoGuardado = localStorage.getItem(STORAGE_KEY);
    const inicioGuardado = localStorage.getItem(STORAGE_START_KEY);

    if (tiempoGuardado && inicioGuardado) {
      const ahora = Date.now();
      const tiempoTranscurrido = Math.floor(
        (ahora - parseInt(inicioGuardado)) / 1000
      );
      tiempoRestante = parseInt(tiempoGuardado) - tiempoTranscurrido;

      if (tiempoRestante <= 0) {
        tiempoRestante = 0;
        limpiarStorage();
        mostrarMensajeCancelacion();
        return false;
      }
    } else {
      const cfgMins =
        (window.CONFIG && Number(window.CONFIG.tiempo_expiracion)) || 20;
      tiempoRestante = Math.max(1, cfgMins) * 60;
      localStorage.setItem(STORAGE_KEY, tiempoRestante);
      localStorage.setItem(STORAGE_START_KEY, Date.now());
    }
    return true;
  }

  function actualizarCountdown() {
    if (!countdownElement) return;

    const minutos = Math.floor(tiempoRestante / 60);
    const segundos = tiempoRestante % 60;
    countdownElement.textContent = `${minutos
      .toString()
      .padStart(2, "0")}:${segundos.toString().padStart(2, "0")}`;

    if (tiempoRestante <= 120) {
      countdownElement.style.color = "#dc3545";
      countdownElement.parentElement.style.borderColor = "#dc3545";
      countdownElement.parentElement.style.backgroundColor =
        "rgba(220, 53, 69, 0.2)";
    }

    if (tiempoRestante <= 0) {
      clearInterval(intervalo);
      limpiarStorage();
      mostrarMensajeCancelacion();
      return;
    }

    tiempoRestante--;
    localStorage.setItem(STORAGE_KEY, tiempoRestante);
  }

  function mostrarMensajeCancelacion() {
    const formPago = document.getElementById("formPago");
    if (formPago) {
      formPago.style.pointerEvents = "none";
      formPago.style.opacity = "0.5";
    }

    const alertaCancelacion = document.createElement("div");
    alertaCancelacion.className =
      "alert alert-danger alert-dismissible fade show position-fixed top-50 start-50 translate-middle";
    alertaCancelacion.style.zIndex = "9999";
    alertaCancelacion.style.minWidth = "400px";
    alertaCancelacion.style.boxShadow = "0 4px 20px rgba(0,0,0,0.3)";
    alertaCancelacion.innerHTML = `
      <h4 class="alert-heading"><i class="bi bi-x-circle-fill"></i> Compra Cancelada</h4>
      <p class="mb-0">El tiempo para completar la compra ha expirado.</p>
      <hr>
      <p class="mb-0">Serás redirigido en <span id="redirectCountdown">5</span> segundos...</p>
    `;
    document.body.appendChild(alertaCancelacion);

    let segundosRedireccion = 5;
    const redirectCountdownElement =
      document.getElementById("redirectCountdown");
    const intervaloRedireccion = setInterval(() => {
      segundosRedireccion--;
      if (redirectCountdownElement) {
        redirectCountdownElement.textContent = segundosRedireccion;
      }
      if (segundosRedireccion <= 0) {
        clearInterval(intervaloRedireccion);
        window.location.href = "/rifa/seleccionar";
      }
    }, 1000);
  }

  function limpiarStorage() {
    localStorage.removeItem(STORAGE_KEY);
    localStorage.removeItem(STORAGE_START_KEY);
  }

  // Iniciar countdown si el elemento existe
  if (countdownElement && inicializarTiempo()) {
    actualizarCountdown();
    intervalo = setInterval(actualizarCountdown, 1000);
  }

  // ==================== WIZARD DE PASOS ====================
  let currentStep = 1;
  const totalSteps = 3;

  const btnSiguiente = document.getElementById("btn-siguiente");
  const btnAnterior = document.getElementById("btn-anterior");
  const btnPagar = document.getElementById("btn-pagar");
  const progressBar = document.getElementById("progress-bar");

  console.log("🔍 Verificando elementos del wizard:", {
    btnSiguiente: !!btnSiguiente,
    btnAnterior: !!btnAnterior,
    btnPagar: !!btnPagar,
    progressBar: !!progressBar,
  });

  if (!btnSiguiente || !btnAnterior || !btnPagar || !progressBar) {
    console.error("❌ ERROR: Faltan elementos del wizard");
    return;
  }

  console.log("✅ Todos los elementos encontrados");

  function actualizarVista() {
    console.log("📍 Actualizando vista al paso:", currentStep);

    // Ocultar todos los pasos
    document.querySelectorAll(".form-step").forEach((step) => {
      step.style.display = "none";
      step.classList.remove("active");
    });

    // Mostrar paso actual
    const stepActual = document.getElementById(`step-${currentStep}`);
    if (stepActual) {
      stepActual.style.display = "block";
      stepActual.classList.add("active");
      console.log("✅ Mostrando paso", currentStep);
    } else {
      console.error("❌ No se encontró el paso", currentStep);
    }

    // Actualizar labels
    document.querySelectorAll(".step-label").forEach((label) => {
      const stepNum = parseInt(label.getAttribute("data-step"));
      label.classList.remove("active", "text-white-50");

      if (stepNum === currentStep) {
        label.classList.add("active");
      } else {
        label.classList.add("text-white-50");
      }
    });

    // Actualizar barra de progreso
    const porcentaje = (currentStep / totalSteps) * 100;
    progressBar.style.width = `${porcentaje}%`;
    console.log("📊 Barra de progreso:", porcentaje + "%");

    // Mostrar/ocultar botones
    btnAnterior.style.display = currentStep === 1 ? "none" : "inline-block";
    btnSiguiente.style.display =
      currentStep === totalSteps ? "none" : "inline-block";
    btnPagar.style.display =
      currentStep === totalSteps ? "inline-block" : "none";
    const btnPagarEfectivo = document.getElementById("btn-pagar-efectivo");
    if (btnPagarEfectivo) {
      const cantBoletos = parseInt(
        document.getElementById("cantidadBoletos")?.textContent || "0",
        10
      );
      const puedeEfectivo = cantBoletos > 10;
      btnPagarEfectivo.style.display =
        currentStep === totalSteps && puedeEfectivo ? "inline-block" : "none";
    }

    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function validarPaso(paso) {
    console.log("🔍 Validando paso:", paso);

    if (paso === 1) {
      const checkbox = document.getElementById("aceptaTerminos");
      const error = document.getElementById("errorTerminos");

      if (!checkbox) {
        console.error("❌ No se encontró el checkbox");
        return false;
      }

      console.log("Checkbox marcado:", checkbox.checked);

      if (!checkbox.checked) {
        if (error) error.style.display = "block";
        checkbox.focus();
        const step1 = document.getElementById("step-1");
        if (step1) {
          step1.classList.add("shake");
          setTimeout(() => step1.classList.remove("shake"), 500);
        }
        console.log("❌ Validación falló: checkbox no marcado");
        return false;
      }
      if (error) error.style.display = "none";
      console.log("✅ Paso 1 validado");
      return true;
    }

    if (paso === 2) {
      const nombre = document.getElementById("nombre");
      const telefono = document.getElementById("telefono");
      const correo = document.getElementById("correo");

      if (!nombre || !telefono || !correo) {
        console.error("❌ Faltan campos del paso 2");
        return false;
      }

      let valido = true;

      [nombre, telefono, correo].forEach((input) => {
        if (!input.checkValidity() || input.value.trim() === "") {
          input.classList.add("is-invalid");
          input.classList.remove("is-valid");
          valido = false;
        } else {
          input.classList.remove("is-invalid");
          input.classList.add("is-valid");
        }
      });

      if (!valido) {
        const primerInvalido = document.querySelector("#step-2 .is-invalid");
        if (primerInvalido) primerInvalido.focus();
        console.log("❌ Validación falló: campos inválidos");
      } else {
        console.log("✅ Paso 2 validado");
      }
      return valido;
    }

    if (paso === 3) {
      const comprobante = document.getElementById("comprobante");
      const folio = document.getElementById("folio");
      const error = document.getElementById("folioComprobanteError");
      const metodoEfectivo = document.getElementById("metodo_efectivo");
      const cantBoletos = parseInt(
        document.getElementById("cantidadBoletos")?.textContent || "0",
        10
      );

      if (!comprobante || !folio) {
        console.error("❌ Faltan elementos del paso 3");
        return false;
      }

      const tieneArchivo = comprobante.files && comprobante.files.length > 0;
      const tieneFolio = folio.value.trim().length > 0;
      const esEfectivo =
        metodoEfectivo && metodoEfectivo.checked && cantBoletos > 10;

      console.log("Archivo:", tieneArchivo, "Folio:", tieneFolio);

      if (!tieneArchivo && !tieneFolio && !esEfectivo) {
        if (error) error.style.display = "block";
        folio.focus();
        console.log("❌ Validación falló: falta comprobante o folio");
        return false;
      }
      if (error) error.style.display = "none";
      console.log("✅ Paso 3 validado");
      return true;
    }

    return true;
  }

  // EVENT LISTENERS
  console.log("📝 Registrando event listeners...");

  btnSiguiente.addEventListener("click", function (e) {
    e.preventDefault();
    console.log("🖱️ CLICK en Siguiente - Paso actual:", currentStep);

    const esValido = validarPaso(currentStep);
    console.log("Resultado validación:", esValido);

    if (esValido) {
      currentStep++;
      console.log("➡️ Avanzando al paso:", currentStep);
      actualizarVista();
    } else {
      console.log("⛔ No se avanza - validación falló");
    }
  });

  btnAnterior.addEventListener("click", function (e) {
    e.preventDefault();
    console.log("🖱️ CLICK en Anterior");
    if (currentStep > 1) {
      currentStep--;
      console.log("⬅️ Retrocediendo al paso:", currentStep);
      actualizarVista();
    }
  });

  console.log("✅ Event listeners registrados");

  // ==================== VALIDACIONES EN TIEMPO REAL ====================

  const aceptaTerminos = document.getElementById("aceptaTerminos");
  if (aceptaTerminos) {
    aceptaTerminos.addEventListener("change", (e) => {
      const error = document.getElementById("errorTerminos");
      if (error) {
        error.style.display = e.target.checked ? "none" : "block";
      }
    });
  }

  // ==================== TOGGLE UI POR MÉTODO DE PAGO ====================
  function actualizarUIMetodoPago() {
    const metodoEfectivo = document.getElementById("metodo_efectivo");
    const opcionEfectivo = document.getElementById("opcion-efectivo");
    const cantBoletos = parseInt(
      document.getElementById("cantidadBoletos")?.textContent || "0",
      10
    );
    const puedeEfectivo = cantBoletos > 10;
    if (opcionEfectivo) {
      opcionEfectivo.style.display = puedeEfectivo ? "block" : "none";
      if (!puedeEfectivo && metodoEfectivo) metodoEfectivo.checked = false;
    }
    const esEfectivoElegible =
      metodoEfectivo && metodoEfectivo.checked && puedeEfectivo;

    const inputComprobante = document.getElementById("comprobante");
    const inputFolio = document.getElementById("folio");
    const colComprobante = inputComprobante
      ? inputComprobante.closest(".col-md-6")
      : null;
    const colFolio = inputFolio ? inputFolio.closest(".col-md-6") : null;

    const errorSize = document.getElementById("fileSizeError");
    const nombreArchivo = document.getElementById("comprobanteNombre");
    const errorFolio = document.getElementById("folioComprobanteError");

    if (esEfectivoElegible) {
      if (colComprobante) colComprobante.style.display = "none";
      if (colFolio) colFolio.style.display = "none";
      if (errorSize) errorSize.style.display = "none";
      if (nombreArchivo) nombreArchivo.style.display = "none";
      if (errorFolio) errorFolio.style.display = "none";
      if (inputComprobante) {
        inputComprobante.value = "";
        inputComprobante.disabled = true;
      }
      if (inputFolio) {
        inputFolio.value = "";
        inputFolio.disabled = true;
      }
    } else {
      if (colComprobante) colComprobante.style.display = "";
      if (colFolio) colFolio.style.display = "";
      if (inputComprobante) inputComprobante.disabled = false;
      if (inputFolio) inputFolio.disabled = false;
    }
  }

  const metodoTransfer = document.getElementById("metodo_transferencia");
  const metodoEfectivo = document.getElementById("metodo_efectivo");
  if (metodoTransfer)
    metodoTransfer.addEventListener("change", actualizarUIMetodoPago);
  if (metodoEfectivo)
    metodoEfectivo.addEventListener("change", actualizarUIMetodoPago);

  // Paso 2: Validar campos en tiempo real
  ["nombre", "telefono", "correo"].forEach((id) => {
    const input = document.getElementById(id);
    if (input) {
      ["input", "blur"].forEach((evento) => {
        input.addEventListener(evento, () => {
          if (input.checkValidity() && input.value.trim() !== "") {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
          } else if (input.value.trim() !== "") {
            input.classList.add("is-invalid");
            input.classList.remove("is-valid");
          }
        });
      });
    }
  });

  // Paso 3: Validar archivo
  const comprobante = document.getElementById("comprobante");
  if (comprobante) {
    comprobante.addEventListener("change", (e) => {
      const file = e.target.files[0];
      const maxSize = 5 * 1024 * 1024;
      const errorSize = document.getElementById("fileSizeError");
      const nombreArchivo = document.getElementById("comprobanteNombre");
      const spanNombre = document.getElementById("nombreArchivo");
      const errorFolio = document.getElementById("folioComprobanteError");

      if (file) {
        if (file.size > maxSize) {
          if (errorSize) errorSize.style.display = "block";
          e.target.value = "";
          if (nombreArchivo) nombreArchivo.style.display = "none";
          return;
        }
        if (errorSize) errorSize.style.display = "none";
        if (errorFolio) errorFolio.style.display = "none";
        if (nombreArchivo) nombreArchivo.style.display = "block";
        if (spanNombre) spanNombre.textContent = file.name;
      } else {
        if (nombreArchivo) nombreArchivo.style.display = "none";
        if (errorSize) errorSize.style.display = "none";
      }
    });
  }

  // Ocultar error al escribir folio
  const folio = document.getElementById("folio");
  if (folio) {
    folio.addEventListener("input", () => {
      const error = document.getElementById("folioComprobanteError");
      if (error && folio.value.trim().length > 0) {
        error.style.display = "none";
      }
    });
  }

  // ==================== ENVÍO DEL FORMULARIO ====================
  const formPago = document.getElementById("formPago");
  if (formPago) {
    const btnPagarEfectivo = document.getElementById("btn-pagar-efectivo");
    if (btnPagarEfectivo) {
      btnPagarEfectivo.addEventListener("click", function () {
        // Seleccionar método efectivo
        const metodoEfectivo = document.getElementById("metodo_efectivo");
        const metodoTransfer = document.getElementById("metodo_transferencia");
        if (metodoEfectivo) metodoEfectivo.checked = true;
        if (metodoTransfer) metodoTransfer.checked = false;

        // Enviar sin exigir comprobante/folio
        const spinner = document.getElementById("spinnerPagar");
        if (spinner) spinner.classList.remove("d-none");
        const btnPagar = document.getElementById("btn-pagar");
        if (btnPagar) btnPagar.disabled = true;
        limpiarStorage();
        formPago.submit();
      });
    }
    formPago.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validarPaso(3)) {
        return false;
      }

      const spinner = document.getElementById("spinnerPagar");
      if (spinner) spinner.classList.remove("d-none");
      if (btnPagar) btnPagar.disabled = true;

      limpiarStorage();
      this.submit();
    });
  }

  // Limpiar storage al volver
  const btnVolverSeleccion = document.getElementById("btnVolverSeleccion");
  if (btnVolverSeleccion) {
    btnVolverSeleccion.addEventListener("click", () => {
      limpiarStorage();
    });
  }

  // Inicializar vista
  console.log("🎬 Inicializando vista...");
  actualizarVista();
  actualizarUIMetodoPago();
  console.log("✅ Inicialización completa");
});

console.log("📄 pago.js cargado completamente");
