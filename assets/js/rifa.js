// ------------------- JS FONDO INTERACTIVO -------------------
document.addEventListener("mousemove", (e) => {
  const x = (e.clientX / window.innerWidth) * 100;
  const y = (e.clientY / window.innerHeight) * 100;
  document.body.style.background = `radial-gradient(circle at ${x}% ${y}%, #003300 0%, #000 100%)`;
});

// ------------------- JS MODALES Y BOLETOS -------------------
document.addEventListener("DOMContentLoaded", () => {
  const btnComprar = document.getElementById("btnComprarBoleto");
  const ticketModal = document.getElementById("ticketModal");
  const pagoModal = document.getElementById("pagoModal");
  const boletosContainer = document.getElementById("boletosContainer");
  const continuarPagoBtn = document.getElementById("continuarPagoBtn");
  const premioSpan = document.getElementById("premio");
  const boletoSpan = document.getElementById("boleto");

  let boletosSeleccionados = [];
  let premioSeleccionado = "Participación en la rifa";

  for (let i = 0; i <= 1000; i++) {
    const num = i.toString().padStart(5, "0");
    const div = document.createElement("div");
    div.classList.add("boleto");
    div.textContent = num;
    div.addEventListener("click", () => {
      if (boletosSeleccionados.includes(num)) {
        boletosSeleccionados = boletosSeleccionados.filter((b) => b !== num);
        div.classList.remove("selected");
      } else {
        if (boletosSeleccionados.length < 10) {
          boletosSeleccionados.push(num);
          div.classList.add("selected");
        } else {
          alert("⚠ Máximo 10 boletos por persona");
        }
      }
    });
    boletosContainer.appendChild(div);
  }

  btnComprar.addEventListener("click", () => abrirModal(ticketModal));
  continuarPagoBtn.addEventListener("click", () => {
    if (boletosSeleccionados.length === 0) {
      alert("⚠ Debes seleccionar al menos un boleto.");
      return;
    }
    cerrarModal(ticketModal);
    abrirModal(pagoModal);
    premioSpan.textContent = premioSeleccionado;
    boletoSpan.textContent = boletosSeleccionados.join(", ");
  });

  function abrirModal(modal) {
    modal.style.display = "flex";
    setTimeout(() => modal.classList.add("show"), 10);
  }
  function cerrarModal(modal) {
    modal.classList.remove("show");
    setTimeout(() => {
      modal.style.display = "none";
    }, 300);
  }
  document
    .querySelectorAll(".modal .close")
    .forEach((btn) =>
      btn.addEventListener("click", (e) =>
        cerrarModal(e.target.closest(".modal"))
      )
    );

  const paymentForm = document.getElementById("paymentForm");
  paymentForm.addEventListener("submit", (e) => {
    e.preventDefault();
    alert("✅ Pago registrado. ¡Gracias por participar!");
    boletosSeleccionados = [];
    cerrarModal(pagoModal);
    paymentForm.reset();
  });
});
