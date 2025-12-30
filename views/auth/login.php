<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMAPED AUCALLAMA</title>
    <link rel="icon" href="/sistema_omaped/assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="/sistema_omaped/assets/css/style.css?v=4.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="center-page body-login"> 
    <div class="login-container">
        <div class="header"><i class="fas fa-heart login-icon"></i></div>
        <h1>OMAPED AUCALLAMA</h1>
        <p class="subtitle">Sistema de Registro</p>
        <form action="../../controllers/AuthController.php" method="POST">
            <input type="hidden" name="accion" value="login">
            <?php if (isset($_GET['error'])): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem; text-align: left; border: 1px solid #f5c6cb;"><i class="fas fa-exclamation-circle"></i> Credenciales incorrectas.</div>
            <?php endif; ?>
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'pass_actualizado'): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem; text-align: left; border: 1px solid #c3e6cb;"><i class="fas fa-check-circle"></i> Contraseña actualizada.</div>
            <?php endif; ?>
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'registro_exitoso'): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem; text-align: left; border: 1px solid #c3e6cb;"><i class="fas fa-check-circle"></i> ¡Registro exitoso!</div>
            <?php endif; ?>
            <div class="input-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Correo Electrónico" required></div>
            <div class="input-group"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Contraseña" required></div>
            <button type="submit" class="btn-primary"><i class="fas fa-arrow-right"></i> Ingresar</button>
        </form>
        <div class="forgot-password-container"><a href="recuperar.php" style="color: #666; font-size: 0.9rem; text-decoration: none;">¿Olvidaste tu contraseña?</a></div>
        <div class="footer-link"><i class="fas fa-user-plus"></i><a href="registro_user.php">Crear nuevo usuario</a></div>
    </div>
    <script src="/sistema_omaped/assets/js/app.js"></script>
</body>
</html>