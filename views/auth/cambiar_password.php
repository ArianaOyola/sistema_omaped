<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - OMAPED</title>
    <link rel="icon" href="/sistema_omaped/assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="/sistema_omaped/assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="center-page body-login"> 
    
    <div class="login-container">
        <div class="header">
            <i class="fas fa-key login-icon" style="background-color: #5cb85c;"></i>
        </div>
        
        <h2 class="create-user-header">Nueva Contraseña</h2>
        <p class="subtitle">Escribe tu nueva clave de acceso.</p>

        <form action="../../controllers/AuthController.php" method="POST">
            <input type="hidden" name="accion" value="cambiar_pass">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

            <label class="input-label">Nueva Contraseña</label>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6" required>
            </div>
            
            <button type="submit" class="btn-primary" style="background-image: linear-gradient(to right, #5cb85c, #4cae4c);">
                Guardar Cambios
            </button>
        </form>
    </div>

    <script src="/sistema_omaped/assets/js/app.js"></script>
</body>
</html>