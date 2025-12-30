<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - OMAPED</title>
    <link rel="icon" href="/sistema_omaped/assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="/sistema_omaped/assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="center-page body-login"> 
    
    <div class="login-container">
        <a href="login.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
        
        <div class="header">
            <i class="fas fa-unlock-alt login-icon" style="background-color: #f0ad4e;"></i>
        </div>
        
        <h2 class="create-user-header">Recuperar Contraseña</h2>
        <p class="subtitle">Ingresa tu correo para buscar tu cuenta.</p>

        <form action="../../controllers/AuthController.php" method="POST">
            <input type="hidden" name="accion" value="recuperar">
            
            <?php if (isset($_GET['error'])): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;">
                    <i class="fas fa-exclamation-circle"></i> Correo no encontrado.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['token_demo'])): ?>
                <div style="background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 15px; text-align: left; font-size: 0.9rem;">
                    <strong>📧 [MODO DEMO]</strong><br>
                    El sistema "envió" un correo. Haz clic aquí para resetear:<br><br>
                    <a href="cambiar_password.php?token=<?php echo $_GET['token_demo']; ?>" style="font-weight:bold; text-decoration:underline; color:#0c5460;">RESTABLECER AHORA</a>
                </div>
            <?php endif; ?>

            <label class="input-label">Correo Registrado</label>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="ej: usuario@omaped.gob" required>
            </div>

            <button type="submit" class="btn-primary" style="background-image: linear-gradient(to right, #f0ad4e, #d9534f);">
                Enviar Enlace
            </button>
        </form>
    </div>

    <script src="/sistema_omaped/assets/js/app.js"></script>
</body>
</html>