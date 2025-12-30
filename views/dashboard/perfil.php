<?php
session_start();
require_once '../../config/db.php';

// 1. Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Obtener datos del usuario logueado
$id_usuario = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT * FROM Tbl_Usuarios WHERE id_usuario = :id");
$stmt->execute([':id' => $id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Por si acaso no existe
if (!$usuario) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

$nombreUsuario = $usuario['nombre_completo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - OMAPED AUCALLAMA</title>
    <link rel="icon" href="../../assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>

    <nav class="navbar">
        <div class="nav-left">
            <div class="nav-brand"><i class="fas fa-heart"></i> OMAPED AUCALLAMA</div>
            <div class="nav-links">
                <a href="index.php" class="nav-button">Dashboard</a>
                <a href="formulario.php" class="nav-button">Nuevo Registro</a>
                <a href="lista.php" class="nav-button">Beneficiarios</a>
            </div>
        </div>
        <div class="header-info">
            <div class="user-profile-widget" onclick="toggleUserMenu()">
                <div class="user-avatar-circle"><?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?></div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($usuario['rol']); ?></span>
                </div>
                <i class="fas fa-chevron-down user-toggle-icon"></i>
            </div>
            <div class="user-dropdown-menu" id="userMenu">
                <a href="perfil.php" class="user-dropdown-item"><i class="fas fa-user-circle"></i> Mi Perfil</a>
                <div class="dropdown-divider"></div>
                <a href="../../controllers/Logout.php" class="user-dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="register-main-content">
        <div class="register-card">
            <h2 class="register-main-title"><i class="fas fa-user-cog"></i> Mi Perfil</h2>

            <?php if (isset($_GET['mensaje'])): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php 
                        if($_GET['mensaje'] == 'clave_actualizada') echo '<i class="fas fa-check-circle"></i> Contraseña actualizada correctamente.';
                        if($_GET['mensaje'] == 'datos_actualizados') echo '<i class="fas fa-check-circle"></i> Datos de perfil actualizados correctamente.';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'clave_incorrecta'): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> Error: La contraseña actual no es correcta.
                </div>
            <?php endif; ?>

            <div class="detail-grid">
                
                <div class="info-box">
                    <h3 class="form-section-title">Información de la Cuenta</h3>
                    
                    <form action="../../controllers/AuthController.php" method="POST">
                        <input type="hidden" name="accion" value="actualizar_datos">
                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">

                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label>Correo Electrónico</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label>Rol del Sistema</label>
                            <select name="rol" required style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; background: white;">
                                <option value="Colaborad@r" <?php if($usuario['rol'] == 'Colaborad@r') echo 'selected'; ?>>Colaborad@r</option>
                                <option value="Ayudante de omaped" <?php if($usuario['rol'] == 'Ayudante de omaped') echo 'selected'; ?>>Ayudante de omaped</option>
                                <option value="Jefa de omaped" <?php if($usuario['rol'] == 'Jefa de omaped') echo 'selected'; ?>>Jefa de omaped</option>
                                <option value="Operador" <?php if($usuario['rol'] == 'Operador') echo 'selected'; ?>>Operador</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-top: 25px;">
                            <button type="submit" class="btn-primary btn-save" style="width: 100%; background-image: linear-gradient(to right, #5bc0de, #4b6cb7);">
                                <i class="fas fa-save"></i> Guardar Datos
                            </button>
                        </div>
                    </form>

                    <div class="form-group" style="margin-top: 15px;">
                        <label style="font-size: 0.8rem; color: #888;">Fecha de Creación</label>
                        <input type="text" class="input-readonly" value="<?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>" readonly>
                    </div>
                </div>

                <div class="info-box">
                    <h3 class="form-section-title"><i class="fas fa-key"></i> Seguridad</h3>
                    <p class="subtitle" style="margin-bottom: 20px;">Actualiza tu contraseña periódicamente.</p>

                    <form action="../../controllers/AuthController.php" method="POST">
                        <input type="hidden" name="accion" value="actualizar_perfil">
                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">

                        <div class="form-group">
                            <label>Contraseña Actual</label>
                            <input type="password" name="password_actual" placeholder="Escribe tu clave actual" required>
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label>Nueva Contraseña</label>
                            <input type="password" name="password_nueva" placeholder="Mínimo 6 caracteres" minlength="6" required>
                        </div>

                        <div class="form-group" style="margin-top: 25px;">
                            <button type="submit" class="btn-primary btn-save" style="width: 100%;">
                                Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="../../assets/js/app.js"></script>
</body>
</html>