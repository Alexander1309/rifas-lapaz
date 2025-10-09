// ===========================
// VARIABLES GLOBALES
// ===========================
let boletosSeleccionados = new Set();
// Config por defecto, se sobrescribe en DOMContentLoaded
let PRECIO_BOLETO = 20;
let TOTAL_BOLETOS = 100000;
let boletosVendidos = new Set();

// Lazy Loading Configuration
const BOLETOS_POR_LOTE = 100;
const MAX_BOLETOS_ALEATORIOS = 1000; // Máximo para selección aleatoria
let boletosActuales = 0;
let cargandoBoletos = false;

// ===========================
// INICIALIZACIÓN
// ===========================
document.addEventListener("DOMContentLoaded", function () {
  // Leer configuración desde inputs ocultos (ya presentes en el DOM)
  const precioInput = document.getElementById("configPrecioBoleto");
  const totalInput = document.getElementById("configTotalBoletos");
  const vendidosScript = document.getElementById("boletosVendidosData");

  PRECIO_BOLETO = precioInput ? Number(precioInput.value) || 20 : 20;
  TOTAL_BOLETOS = totalInput ? Number(totalInput.value) || 100000 : 100000;

  if (vendidosScript && vendidosScript.textContent) {
    try {
      const parsed = JSON.parse(vendidosScript.textContent);
      if (Array.isArray(parsed)) {
        // Internamente usamos 5 dígitos para coincidir con data-numero del DOM
        const normalizados = parsed.map((n) =>
          String(n).replace(/\D/g, "").padStart(5, "0")
        );
        boletosVendidos = new Set(normalizados);
      }
    } catch (e) {
      console.warn("boletosVendidosData JSON inválido", e);
    }
  }

  cargarBoletosIniciales();
  configurarScrollListener();
  configurarEventListeners();
});

// ===========================
// LAZY LOADING DE BOLETOS
// ===========================
function cargarBoletosIniciales() {
  cargarLoteBoletos();
}

function crearBoletoHTML(numero) {
  const numeroKey = String(numero).padStart(5, "0");

  const boleto = document.createElement("div");
  boleto.className = "boleto";
  boleto.setAttribute("data-numero", numeroKey);

  boleto.innerHTML = `
    <div class="boleto-superior">
      <span class="boleto-numero">${numeroKey}</span>
    </div>
    <div class="boleto-perforacion"></div>
    <div class="boleto-inferior">
      <span class="boleto-precio">Rifa La Paz</span>
    </div>
  `;

  // Marcar como vendido si está en la lista
  if (boletosVendidos.has(numeroKey)) {
    boleto.classList.add("vendido");
    boleto.style.pointerEvents = "none";
  } else if (boletosSeleccionados.has(numeroKey)) {
    // Marcar como seleccionado si ya está en la lista de seleccionados
    boleto.classList.add("seleccionado");
  }

  // Agregar event listener si no está vendido
  if (!boletosVendidos.has(numeroKey)) {
    boleto.addEventListener("click", function () {
      toggleBoleto(this);
    });
  }

  return boleto;
}

function cargarLoteBoletos() {
  if (cargandoBoletos || boletosActuales >= TOTAL_BOLETOS) {
    return;
  }

  cargandoBoletos = true;
  const loadingIndicator = document.getElementById("loading-indicator");

  // Mostrar indicador solo si hay más boletos por cargar
  if (loadingIndicator && boletosActuales < TOTAL_BOLETOS) {
    loadingIndicator.style.display = "block";
  }

  // Usar requestAnimationFrame para mejor rendimiento
  requestAnimationFrame(() => {
    const gridBoletos = document.getElementById("grid-boletos");

    if (!gridBoletos) {
      console.error("No se encontró el contenedor grid-boletos");
      cargandoBoletos = false;
      return;
    }

    const fragment = document.createDocumentFragment();

    const inicio = boletosActuales + 1;
    const fin = Math.min(boletosActuales + BOLETOS_POR_LOTE, TOTAL_BOLETOS);

    console.log(`Cargando boletos del ${inicio} al ${fin}`);

    for (let i = inicio; i <= fin; i++) {
      const boleto = crearBoletoHTML(i);
      fragment.appendChild(boleto);
    }

    gridBoletos.appendChild(fragment);
    boletosActuales = fin;

    // Marcar boletos vendidos en el nuevo lote
    marcarBoletosVendidos();

    cargandoBoletos = false;

    // Ocultar indicador después de cargar
    if (loadingIndicator) {
      if (boletosActuales >= TOTAL_BOLETOS) {
        loadingIndicator.style.display = "none";
      } else {
        // Ocultar temporalmente y dejar visible para el observer
        loadingIndicator.style.display = "block";
      }
    }

    console.log(`Total cargados: ${boletosActuales}/${TOTAL_BOLETOS}`);
  });
}

function configurarScrollListener() {
  const boletosGrid = document.querySelector(".boletos-grid");

  if (!boletosGrid) return;

  boletosGrid.addEventListener("scroll", function () {
    // Verificar si estamos cerca del final (300px antes del final)
    const scrollTop = this.scrollTop;
    const scrollHeight = this.scrollHeight;
    const clientHeight = this.clientHeight;

    if (scrollTop + clientHeight >= scrollHeight - 300) {
      if (!cargandoBoletos && boletosActuales < TOTAL_BOLETOS) {
        cargarLoteBoletos();
      }
    }
  });
}

// ===========================
// MARCAR BOLETOS VENDIDOS
// ===========================
function marcarBoletosVendidos() {
  boletosVendidos.forEach((numero) => {
    const boleto = document.querySelector(`[data-numero="${numero}"]`);
    if (boleto) {
      boleto.classList.add("vendido");
      boleto.style.pointerEvents = "none";
    }
  });
}

// ===========================
// CONFIGURAR EVENT LISTENERS
// ===========================
function configurarEventListeners() {
  // Botón Aleatorio
  document
    .getElementById("btnAleatorio")
    .addEventListener("click", seleccionarBoletosAleatorios);

  // Botón Pagar
  document
    .getElementById("btnPagar")
    .addEventListener("click", enviarFormularioPago);

  // Botón Limpiar
  document
    .getElementById("btnLimpiar")
    .addEventListener("click", limpiarSeleccion);

  // Input de cantidad
  document
    .getElementById("cantidadBoletos")
    .addEventListener("input", validarCantidad);
}

// ===========================
// TOGGLE BOLETO
// ===========================
function toggleBoleto(boletoElement) {
  const numero = boletoElement.getAttribute("data-numero");

  if (boletoElement.classList.contains("seleccionado")) {
    boletoElement.classList.remove("seleccionado");
    boletosSeleccionados.delete(numero);
  } else {
    boletoElement.classList.add("seleccionado");
    boletosSeleccionados.add(numero);
  }

  actualizarResumen();
}

// ===========================
// SELECCIONAR BOLETOS ALEATORIOS
// ===========================
function seleccionarBoletosAleatorios() {
  const cantidadInput = document.getElementById("cantidadBoletos");
  const cantidad = parseInt(cantidadInput.value) || 1;

  if (cantidad < 1 || cantidad > MAX_BOLETOS_ALEATORIOS) {
    mostrarAlerta(
      `La cantidad debe estar entre 1 y ${MAX_BOLETOS_ALEATORIOS} boletos para selección aleatoria`,
      "warning"
    );
    return;
  }

  // Obtener boletos disponibles del total (no solo los cargados)
  const boletosDisponibles = [];
  for (let i = 1; i <= TOTAL_BOLETOS; i++) {
    // Usar clave de 5 dígitos internamente para coincidir con DOM
    const numeroKey = String(i).padStart(5, "0");
    if (
      !boletosSeleccionados.has(numeroKey) &&
      !boletosVendidos.has(numeroKey)
    ) {
      boletosDisponibles.push(numeroKey);
    }
  }

  if (boletosDisponibles.length < cantidad) {
    mostrarAlerta(
      `Solo hay ${boletosDisponibles.length} boletos disponibles`,
      "warning"
    );
    return;
  }

  let primeraSeleccion = null;
  let boletosSeleccionadosAhora = 0;

  // Seleccionar aleatoriamente
  for (let i = 0; i < cantidad; i++) {
    const randomIndex = Math.floor(Math.random() * boletosDisponibles.length);
    const numeroSeleccionado = boletosDisponibles[randomIndex];
    boletosDisponibles.splice(randomIndex, 1);

    // Agregar a la lista de seleccionados
    boletosSeleccionados.add(numeroSeleccionado);
    boletosSeleccionadosAhora++;

    // Si el boleto ya está cargado en el DOM, marcarlo visualmente
    const boleto = document.querySelector(
      `[data-numero="${numeroSeleccionado}"]`
    );
    if (boleto && !boleto.classList.contains("seleccionado")) {
      boleto.classList.add("seleccionado");

      // Guardar el primer boleto para hacer scroll
      if (!primeraSeleccion) {
        primeraSeleccion = boleto;
      }
    }
  }

  actualizarResumen();

  // Scroll al primer boleto si está cargado
  if (primeraSeleccion) {
    primeraSeleccion.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  mostrarAlerta(
    `Se seleccionaron ${boletosSeleccionadosAhora} boleto(s) aleatorio(s). Puedes seguir seleccionando más manualmente.`,
    "success"
  );
}

// ===========================
// ACTUALIZAR RESUMEN
// ===========================
function actualizarResumen() {
  const totalBoletos = boletosSeleccionados.size;
  const totalPagar = totalBoletos * PRECIO_BOLETO;

  // Actualizar contador
  document.getElementById("totalBoletos").textContent = totalBoletos;
  document.getElementById("totalPagar").textContent = `$ ${totalPagar}`;

  // Actualizar lista de boletos seleccionados
  const contenedor = document.getElementById("boletosSeleccionados");

  if (totalBoletos === 0) {
    contenedor.innerHTML =
      '<p class="text-muted text-center">No has seleccionado boletos aún</p>';
    document.getElementById("btnPagar").disabled = true;
  } else {
    contenedor.innerHTML = "";
    const boletosArray = Array.from(boletosSeleccionados).sort();

    boletosArray.forEach((numero) => {
      const chip = document.createElement("span");
      chip.className = "boleto-chip";
      chip.textContent = `#${String(numero).padStart(5, "0")}`;
      contenedor.appendChild(chip);
    });

    document.getElementById("btnPagar").disabled = false;
  }
}

// ===========================
// LIMPIAR SELECCIÓN
// ===========================
function limpiarSeleccion() {
  if (boletosSeleccionados.size === 0) {
    mostrarAlerta("No hay boletos seleccionados", "info");
    return;
  }

  boletosSeleccionados.forEach((numero) => {
    const boleto = document.querySelector(`[data-numero="${numero}"]`);
    if (boleto) {
      boleto.classList.remove("seleccionado");
    }
  });

  boletosSeleccionados.clear();
  actualizarResumen();
  mostrarAlerta("Selección limpiada", "success");
}

// ===========================
// ENVIAR FORMULARIO DE PAGO
// ===========================
async function enviarFormularioPago() {
  if (boletosSeleccionados.size === 0) {
    mostrarAlerta("Debes seleccionar al menos un boleto", "warning");
    return;
  }

  const totalBoletos = boletosSeleccionados.size;
  const totalPagar = totalBoletos * PRECIO_BOLETO;

  // Validar disponibilidad en servidor antes de enviar
  const boletosArray = Array.from(boletosSeleccionados);
  try {
    const res = await fetch("/rifa/validarDisponibilidad", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ boletos: boletosArray }),
    });
    const data = await res.json();
    if (
      data &&
      Array.isArray(data.no_disponibles) &&
      data.no_disponibles.length > 0
    ) {
      // Quitar de la selección y marcar como vendidos en UI
      data.no_disponibles.forEach((num) => {
        // Normalizar a 5 dígitos para claves internas
        const n = String(num).replace(/\D/g, "").padStart(5, "0");
        boletosSeleccionados.delete(n);
        boletosVendidos.add(n);
        const el = document.querySelector(`[data-numero="${n}"]`);
        if (el) {
          el.classList.remove("seleccionado");
          el.classList.add("vendido");
          el.style.pointerEvents = "none";
        }
      });
      actualizarResumen();
      const listaDisplay = data.no_disponibles
        .map((n) => String(n).replace(/\D/g, "").padStart(5, "0"))
        .join(", ");
      mostrarAlerta(
        `Algunos boletos ya no están disponibles: ${listaDisplay}`,
        "warning"
      );
      return; // No enviar aún
    }
  } catch (e) {
    console.error("Error validando disponibilidad:", e);
  }

  // Obtener el formulario oculto
  const form = document.getElementById("formPagoOculto");

  // Limpiar campos anteriores
  const boletosInputsAnteriores = form.querySelectorAll(
    'input[name="boletos[]"], input[name="boletos_json"]'
  );
  boletosInputsAnteriores.forEach((input) => input.remove());

  // SOLUCIÓN: Enviar boletos como JSON en un solo campo
  // Esto evita el límite de max_input_vars de PHP
  const inputJson = document.createElement("input");
  inputJson.type = "hidden";
  inputJson.name = "boletos_json";
  inputJson.value = JSON.stringify(boletosArray);
  form.appendChild(inputJson);

  // Agregar el total
  document.getElementById("totalInput").value = totalPagar;

  // Agregar spinner al botón y deshabilitarlo
  const btnPagar = document.getElementById("btnPagar");
  const textoOriginal = btnPagar.innerHTML;
  btnPagar.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
  btnPagar.disabled = true;

  // Enviar el formulario
  form.submit();
}

// ===========================
// VALIDAR CANTIDAD
// ===========================
function validarCantidad() {
  const input = document.getElementById("cantidadBoletos");
  let valor = parseInt(input.value);

  if (valor < 1) {
    input.value = 1;
  } else if (valor > MAX_BOLETOS_ALEATORIOS) {
    input.value = MAX_BOLETOS_ALEATORIOS;
    mostrarAlerta(
      `El máximo para selección aleatoria es ${MAX_BOLETOS_ALEATORIOS} boletos. Puedes seguir seleccionando más manualmente.`,
      "info"
    );
  }
}

// ===========================
// MOSTRAR ALERTAS
// ===========================
function mostrarAlerta(mensaje, tipo = "info") {
  // Crear elemento de alerta
  const alertaDiv = document.createElement("div");
  alertaDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
  alertaDiv.style.zIndex = "9999";
  alertaDiv.style.minWidth = "300px";
  alertaDiv.style.maxWidth = "500px";
  alertaDiv.innerHTML = `
		${mensaje}
		<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
	`;

  document.body.appendChild(alertaDiv);

  // Auto cerrar después de 3 segundos
  setTimeout(() => {
    alertaDiv.classList.remove("show");
    setTimeout(() => alertaDiv.remove(), 150);
  }, 3000);
}

// ===========================
// UTILIDADES
// ===========================
function formatearNumero(numero) {
  // Clave interna en 5 dígitos (4 ceros máximo a la izquierda)
  return String(numero).padStart(5, "0");
}
