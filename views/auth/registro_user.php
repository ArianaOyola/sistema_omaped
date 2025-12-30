<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMAPED AUCALLAMA</title>
    <link rel="icon" href="/sistema_omaped/assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="/sistema_omaped/assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="center-page body-login"> 
    <div class="login-container">
        <a href="login.php" class="back-arrow"><i class="fas fa-arrow-left"></i></a>
        <div class="header"><i class="fas fa-user-plus register-icon"></i></div>
        <h2 class="create-user-header">Crear Usuario</h2>
        <p class="subtitle">Registra una nueva cuenta</p>

        <form action="../../controllers/AuthController.php" method="POST">
            <input type="hidden" name="accion" value="registro">

            <label class="input-label">Correo Electrónico</label>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="ej: tu.nombre@omaped.gob" required>
            </div>
            
            <label class="input-label">Nombre Completo</label>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="nombre" placeholder="Tu Nombre" required>
            </div>

            <label class="input-label">Cargo / Rol</label>
            <div class="input-group">
                <i class="fas fa-id-badge"></i>
                <select name="rol" required style="width: 100%; border: none; background: transparent; outline: none; color: #555;">
                    <option value="Colaborad@r">Colaborad@r</option>
                    <option value="Ayudante de omaped">Ayudante de omaped</option>
                    <option value="Jefa de omaped">Jefa de omaped</option>
                </select>
            </div>

            <label class="input-label">Contraseña</label>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6" required> 
            </div>

            <button type="submit" class="btn-primary btn-register">
                <i class="fas fa-user-plus"></i> Registrar
            </button>
        </form>
    </div>
    <script src="/sistema_omaped/assets/js/app.js"></script>
</body>
</html>