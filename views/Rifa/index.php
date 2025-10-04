<header>
	<img src="https://upload.wikimedia.org/wikipedia/commons/a/ab/Logo_TV_2015.png" alt="Logo Rifa" class="logo">
	<div class="header-text">
		<h1>RIFAS LA PAZ</h1>
		<p>¡Participa comprando tu boleto y gana uno de estos increíbles premios!</p>
	</div>
</header>

<main>
	<section class="rifa">
		<div class="premios-rifa" id="premiosRifa">
			<div class="premio-item">
				<h3>🥇 Primer lugar</h3>
				<img src="https://mac-center.com/cdn/shop/files/IMG-18067880_m_jpeg_1.jpg?v=1757469572&width=823" alt="iPhone 17 512GB">
				<p>Iphone 17 512GB</p>
				<ul class="detalles">
					<li>Pantalla OLED 6.9"</li>
					<li>512 GB almacenamiento</li>
					<li>Cámara 48 MP</li>
					<li>Batería de larga duración</li>
				</ul>
			</div>
			<div class="premio-item">
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
			<div class="premio-item">
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

		<!-- ------------------- FAQ ------------------- -->
		<section class="faq">
			<div class="faq-box">
				<h2>PREGUNTAS FRECUENTES</h2>
				<p><strong>¿CÓMO SE ELIGE A LOS GANADORES?</strong><br>
					Todos nuestros sorteos se realizan en base a la <strong>Lotería Nacional para la Asistencia Pública</strong> mexicana.
					El ganador de Rifas La Paz será el participante cuyo número de boleto coincida con las últimas cifras del primer premio ganador de la Lotería Nacional (las fechas serán publicadas en nuestra página oficial).</p>

				<p><strong>¿QUÉ SUCEDE CUANDO EL NÚMERO GANADOR ES UN BOLETO NO VENDIDO?</strong><br>
					Se elige un nuevo ganador realizando la misma dinámica en otra fecha cercana (se anunciará la nueva fecha).
					Esto significa que, ¡Tendrías el doble de oportunidades de ganar con tu mismo boleto!</p>

				<p><strong>¿DÓNDE SE PUBLICA A LOS GANADORES?</strong><br>
					En nuestra página oficial de Facebook <strong>Rifas La Paz</strong> puedes encontrar todos y cada uno de nuestros sorteos anteriores, así como las transmisiones en vivo con Lotería Nacional y las entregas de premios a los ganadores!
					Encuentra transmisión en vivo de los sorteos en nuestra página de Facebook en las fechas indicadas a las 20:00 hrs CDMX. ¡No te lo pierdas!</p>
			</div>
		</section>

		<!-- ------------------- ACERCA DE NOSOTROS ------------------- -->
		<section class="about-us">
			<div class="about-box">
				<h2>ACERCA DE NOSOTROS</h2>
				<p>
					Somos <strong>RIFAS LA PAZ</strong>, dedicados a organizar sorteos y rifas de manera transparente y divertida. Nuestro objetivo es que todos los participantes tengan la oportunidad de ganar increíbles premios electrónicos y gadgets de última generación.
				</p>
				<p>
					Nos enfocamos en experiencias seguras, confiables y dinámicas para nuestra comunidad. ¡Participar con nosotros es fácil, rápido y emocionante!
				</p>
				<div class="social-buttons">
					<a href="https://www.facebook.com/" target="_blank" class="social-btn fb">Facebook</a>
					<a href="https://www.instagram.com/" target="_blank" class="social-btn ig">Instagram</a>
					<a href="https://wa.me/529876543210" target="_blank" class="social-btn wa">WhatsApp</a>
				</div>
			</div>
		</section>

	</section>
</main>

<!-- Modal Boletos -->
<div class="modal" id="ticketModal">
	<div class="modal-content ticket-box">
		<span class="close">&times;</span>
		<h2>Selecciona tus boletos</h2>
		<p>Máximo 10 boletos por persona</p>
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
			<p><strong>🎁 Premio:</strong> <span id="premio"></span></p>
			<p><strong>🎟 Boletos:</strong> <span id="boleto"></span></p>
			<input type="text" id="nombre" name="nombre" placeholder="👤 Nombre completo" required>
			<input type="email" id="correo" name="correo" placeholder="📧 Correo electrónico" required>
			<input type="tel" id="telefono" name="telefono" placeholder="📱 Teléfono" required>
			<button type="submit">✨ Pagar Ahora</button>
		</form>
	</div>
</div>