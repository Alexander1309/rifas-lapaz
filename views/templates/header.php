<!DOCTYPE html>
<html lang="es">

<?php echo $this->getHeadContent(); ?>

<body class="no-sidebar <?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
	<?php echo $this->getMessages(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php echo $this->getHeadContent(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIFAS LA PAZ</title>
    <style>
        /* Tu CSS personalizado del prompt aquí */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
        body {
            min-height: 100vh;
            overflow-x: hidden;
            color: #fff;
            transition: background 0.2s;
            background: radial-gradient(circle at 50% 50%,#003300 0%,#000 100%);
        }
        header {
            padding: 15px 20px;
            background: rgba(0,0,0,0.7);
            border-bottom: 2px solid #00ff88;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        header .logo { width: 80px; }
        header .header-text { flex: 1; text-align: center; }
        header h1 { font-size: 2rem; color: #00ff88; text-shadow: 0 0 10px #00ff88; }
        header p { font-size: 1rem; opacity: 0.8; margin-top: 4px; }
        .rifa { display: flex; flex-direction: column; align-items: center; margin: 20px; }
        .premios-rifa { display: flex; gap: 25px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px; }
        .premio-item {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 12px;
            width: 250px;
            text-align: center;
            border: 2px solid #00ff88;
            cursor: pointer;
            transition: all 0.4s ease;
        }
        .premio-item:hover { transform: scale(1.05); box-shadow: 0 0 20px #00ff88; }
        .premio-item img { width: 100%; border-radius: 10px; margin: 10px 0; transition: transform 0.4s ease; }
        .premio-item:hover img { transform: scale(1.1); }
        .premio-item h3 { color: #00ff88; margin-bottom: 5px; }
        .premio-item p { font-weight: 600; }
        .detalles { list-style: none; text-align: left; margin-top: 10px; color: #00ff99; }
        .detalles li { opacity: 0; transform: translateX(-20px); animation: detalleAnim 0.5s forwards; }
        .detalles li:nth-child(1) { animation-delay: 0.1s; }
        .detalles li:nth-child(2) { animation-delay: 0.3s; }
        .detalles li:nth-child(3) { animation-delay: 0.5s; }
        .detalles li:nth-child(4) { animation-delay: 0.7s; }
        @keyframes detalleAnim { to { opacity: 1; transform: translateX(0); } }
        button {
            padding: 14px 30px;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(90deg,#00cc66,#00ffcc);
            color: #000;
            box-shadow: 0 0 15px #00ff88;
        }
        button:hover { transform: scale(1.05); box-shadow: 0 0 25px #00ffcc; }
        .info-box {
            background: rgba(0,255,136,0.05);
            border: 2px solid #00ff88;
            border-radius: 15px;
            padding: 20px;
            max-width: 900px;
            width: 100%;
            margin: 30px auto;
            transition: transform 0.3s ease,box-shadow 0.3s ease;
        }
        .info-box:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 10px 25px rgba(0,255,136,0.4); }
        .info-box h2 { color: #00ff88; text-align: center; margin-bottom: 15px; font-size: 1.8rem; text-shadow: 0 0 10px #00ff88; }
        .info-box p { color: #ccffcc; font-size: 1rem; margin-bottom: 15px; line-height: 1.6; }
        .info-box strong { color: #00ffcc; }
        .social-buttons { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; }
        .social-btn { padding: 10px 20px; border-radius: 10px; font-weight: bold; color: #fff; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 0 10px #00ff88; }
        .social-btn:hover { transform: scale(1.05); box-shadow: 0 0 20px #00ffcc; }
        .social-btn.fb { background: #3b5998; }
        .social-btn.ig { background: #e1306c; }
        .social-btn.wa { background: #25d366; }
        @media(max-width: 768px) { .premios-rifa { flex-direction:column; align-items: center; } }
    </style>
</head>
<body class="no-sidebar <?php echo $this->getBodyClass(); ?>" <?php echo $this->getBodyAttributes(); ?>>
    <?php echo $this->getMessages(); ?>

    <header>
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/ab/Logo_TV_2015.png" alt="Logo Rifa" class="logo">
        <div class="header-text">
		<h1><?php echo $this->params["title"] ?></h1>
            <p>¡Participa comprando tu boleto y gana uno de estos increíbles premios!</p>
        </div>
    </header>
    <main>
    