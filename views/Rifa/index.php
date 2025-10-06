 <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>RIFAS LA PAZ</title>
    </head>

 <main>
            <!-- PREMIOS -->
            <section class="rifa">
                <div class="premios-rifa" id="premiosRifa">
                        <div class="premio-item" style="width: 18rem;">
                        <img src="https://mac-center.com/cdn/shop/files/IMG-18067880_m_jpeg_1.jpg?v=1757469572&width=823" class="card-img-top" alt="iphone 17 256 gb">
                        <div class="iphone 17 256gb ">
                        <p class="card-text">Pantalla OLED 256gb Almacenamiento Camara 48 MP Bateria de larga duracion</p>
                        </div>
                        </ul>
                    </div>
                    <div class="premio-item" data-premio="PlayStation 5 PRO">
                        <h3>🥈 Segundo lugar</h3>
                        <img src="https://http2.mlstatic.com/D_Q_NP_708759-MLA92042691589_092025-O.webp" alt="PlayStation 5 PRO">
                        <p>PlayStation 5 PRO</p>
                        <ul class="detalles">
                            <li>8K compatible</li>
                            <li>SSD ultrarrápido</li>
                            <li>Mando DualSense Pro</li>
                            <li>Retrocompatibilidad PS4</li>
                        </ul>
                    </div>
                    <div class="premio-item" data-premio="Laptop Gamer RTX 4090">
                        <h3>🥉 Tercer lugar</h3>
                        <img src="https://i5.walmartimages.com/asr/148eb753-90ff-40c4-930f-bb0f69fdbb66.0dea8a120732d5dd113f0e345d5662e9.jpeg?odnHeight=612&odnWidth=612&odnBg=FFFFFF" alt="Laptop Gamer RTX 4090">
                        <p>Laptop Gamer RTX 4090</p>
                        <ul class="detalles">
                            <li>RTX 4090 16GB</li>
                            <li>32 GB RAM DDR5</li>
                            <li>SSD 2TB NVMe</li>
                            <li>Pantalla 240Hz</li>
                        </ul>
                    </div>
                </div>
                <button id="btnComprarBoleto">🎟 Comprar Boleto</button>
            </section>
            <!-- FAQ -->
            <section class="info-box">
                <h2>PREGUNTAS FRECUENTES</h2>
                <p>
                    <strong>¿CÓMO SE ELIGE A LOS GANADORES?</strong>
                    <br>
                    Con la <strong>Lotería Nacional</strong>
                    . El número ganador se determina con las últimas cifras del primer premio.
                </p>
                <p>
                    <strong>¿Y SI EL NÚMERO GANADOR NO FUE VENDIDO?</strong>
                    <br>Se repite el sorteo en una nueva fecha cercana.
                </p>
                <p>
                    <strong>¿DÓNDE SE PUBLICAN LOS GANADORES?</strong>
                    <br>
                    En Facebook <strong>Rifas La Paz</strong>
                    y transmisiones en vivo.
                </p>
                <p>
                    <strong>¿PUEDO COMPRAR VARIOS BOLETOS?</strong>
                    <br>
                    Sí, claro ya que por persona <strong>NO HAY</strong>
                    limite de boletos.
                </p>
                <p>
                    <strong>¿CÓMO RECIBO MI PREMIO?</strong>
                    <br>Nos pondremos en <strong>CONTACTO CONTIGO</strong>por teléfono o WhatsApp para coordinar la entrega.
                </p>
            </section>
            <!-- ABOUT US -->
            <section class="info-box">
                <h2>ACERCA DE NOSOTROS</h2>
                <p>
                    Somos <strong>RIFAS LA PAZ</strong>
                    , organizamos rifas transparentes y divertidas. Nuestro objetivo: dar la oportunidad de ganar premios tecnológicos de última generación.
                </p>
                <div class="social-buttons">
                    <a href="https://www.facebook.com/" target="_blank" class="social-btn fb">Facebook</a>
                    <a href="https://www.instagram.com/" target="_blank" class="social-btn ig">Instagram</a>
                    <a href="https://wa.me/529876543210" target="_blank" class="social-btn wa">WhatsApp</a>
                </div>
            </section>
        </main>
        <!-- Modal Boletos -->
        <div class="modal" id="ticketModal">
            <div class="modal-content ticket-box">
                <span class="close">&times;</span>
                <h2>Selecciona tus boletos</h2>
                <p>Máximo 1000 boletos por persona</p>
                <div class="boletos-container" id="boletosContainer"></div>
                <button id="continuarPagoBtn">✅ Continuar al Pago</button>
            </div>
        </div>
        <!-- Modal Pago -->
        <div class="modal" id="pagoModal">
            <div class="modal-content pago-box">
                <span class="close">&times;</span>
                <h2>💳 Completa tu pago</h2>
                <form id="paymentForm">
                    <p>
                        <strong>🎁 Premio:</strong>
                        <span id="premio"></span>
                    </p>
                    <p>
                        <strong>🎟 Boletos:</strong>
                        <span id="boleto"></span>
                    </p>
                    <input type="text" id="nombre" name="nombre" placeholder="👤 Nombre completo" required>
                    <input type="email" id="correo" name="correo" placeholder="📧 Correo electrónico" required>
                    <input type="tel" id="telefono" name="telefono" placeholder="📱 Teléfono" required>
                    <button type="submit">✨ Pagar Ahora</button>
                </form>
            </div>
        </div>
        <!-- WhatsApp flotante -->
        <a href="https://wa.me/529876543210" target="_blank" class="whatsapp-float" aria-label="Contactar por WhatsApp">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
        </a>
        <script>
            // ------------------- FONDO INTERACTIVO -------------------
            document.addEventListener('mousemove', e => {
                const x = (e.clientX / window.innerWidth) * 100;
                const y = (e.clientY / window.innerHeight) * 100;
                document.body.style.background = `radial-gradient(circle at ${x}% ${y}%, #003300 0%, #000 100%)`;
            }
            );

            // ------------------- MODALES Y BOLETOS -------------------
            document.addEventListener("DOMContentLoaded", () => {
                const btnComprar = document.getElementById("btnComprarBoleto");
                const ticketModal = document.getElementById("ticketModal");
                const pagoModal = document.getElementById("pagoModal");
                const boletosContainer = document.getElementById("boletosContainer");
                const continuarPagoBtn = document.getElementById("continuarPagoBtn");
                const premioSpan = document.getElementById("premio");
                const boletoSpan = document.getElementById("boleto");
                let boletosSeleccionados = [];
                let premioSeleccionado = "";

                // Selección de premio
                document.querySelectorAll(".premio-item").forEach(item => {
                    item.addEventListener("click", () => {
                        premioSeleccionado = item.dataset.premio;
                        alert(`Has seleccionado: ${premioSeleccionado}`);
                    }
                    );
                }
                );

                // Generar boletos 00000 - 10000
                for (let i = 0; i <= 10000; i++) {
                    const num = i.toString().padStart(5, "0");
                    const div = document.createElement("div");
                    div.classList.add("boleto");
                    div.textContent = num;
                    div.addEventListener("click", () => {
                        if (boletosSeleccionados.includes(num)) {
                            boletosSeleccionados = boletosSeleccionados.filter(b => b !== num);
                            div.classList.remove("selected");
                        } else {
                            if (boletosSeleccionados.length < 1000) {
                                boletosSeleccionados.push(num);
                                div.classList.add("selected");
                            } else {
                                alert("⚠ Máximo 1000 boletos por persona");
                            }
                        }
                    }
                    );
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
                    premioSpan.textContent = premioSeleccionado || "Participación en la rifa";
                    boletoSpan.textContent = boletosSeleccionados.join(", ");
                }
                );

                function abrirModal(modal) {
                    modal.style.display = "flex";
                    setTimeout( () => modal.classList.add("show"), 10);
                }
                function cerrarModal(modal) {
                    modal.classList.remove("show");
                    setTimeout( () => {
                        modal.style.display = "none";
                    }
                    , 300);
                }
                document.querySelectorAll(".modal .close").forEach(btn => btn.addEventListener("click", e => cerrarModal(e.target.closest(".modal"))));

                const paymentForm = document.getElementById("paymentForm");
                paymentForm.addEventListener("submit", e => {
                    e.preventDefault();
                    alert("✅ Pago registrado. ¡Gracias por participar!");
                    boletosSeleccionados = [];
                    premioSeleccionado = "";
                    cerrarModal(pagoModal);
                    paymentForm.reset();
                }
                );
            
            );
        </script>
        </style>
    </body>
</html>