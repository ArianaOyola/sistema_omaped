<?php
// controllers/AuthController.php
session_start();
require_once '../config/db.php';

// Verificamos que sea una petición POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $accion = $_POST['accion'];

    // ==========================================
    // 1. REGISTRO DE NUEVO USUARIO
    // ==========================================
    if ($accion == 'registro') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $rol = $_POST['rol']; // Recibimos el rol seleccionado (Jefa, Ayudante, etc.)
        
        // Encriptamos la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO Tbl_Usuarios (email, password, nombre_completo, rol) VALUES (:email, :pass, :nombre, :rol)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $email, 
                ':pass' => $passwordHash, 
                ':nombre' => $nombre,
                ':rol' => $rol
            ]);
            
            header("Location: ../views/auth/login.php?mensaje=registro_exitoso");
            exit();
        } catch (PDOException $e) {
            die("Error al registrar: " . $e->getMessage());
        }
    }

    // ==========================================
    // 2. INICIO DE SESIÓN (LOGIN)
    // ==========================================
    if ($accion == 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $sql = "SELECT * FROM Tbl_Usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Guardamos datos en sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol']; // Guardamos el rol específico
            
            header("Location: ../views/dashboard/index.php");
            exit();
        } else {
            header("Location: ../views/auth/login.php?error=credenciales_invalidas");
            exit();
        }
    }

    // ==========================================
    // 3. SOLICITAR RECUPERACIÓN (OLVIDÉ CONTRASEÑA)
    // ==========================================
    if ($accion == 'recuperar') {
        $email = trim($_POST['email']);
        
        // Verificamos si existe el correo
        $stmt = $pdo->prepare("SELECT id_usuario FROM Tbl_Usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            // Generamos token único
            $token = bin2hex(random_bytes(16));
            $update = $pdo->prepare("UPDATE Tbl_Usuarios SET token_recuperacion = :token WHERE email = :email");
            $update->execute([':token' => $token, ':email' => $email]);
            
            // Simulación de envío de correo
            header("Location: ../views/auth/recuperar.php?token_demo=" . $token);
        } else {
            header("Location: ../views/auth/recuperar.php?error=no_existe");
        }
        exit();
    }

    // ==========================================
    // 4. CAMBIAR CONTRASEÑA (RESTABLECER)
    // ==========================================
    if ($accion == 'cambiar_pass') {
        $token = $_POST['token'];
        $pass = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        
        // Verificar token
        $stmt = $pdo->prepare("SELECT id_usuario FROM Tbl_Usuarios WHERE token_recuperacion = :token");
        $stmt->execute([':token' => $token]);
        
        if ($stmt->fetch()) {
            // Actualizar contraseña y borrar token
            $update = $pdo->prepare("UPDATE Tbl_Usuarios SET password = :pass, token_recuperacion = NULL WHERE token_recuperacion = :token");
            $update->execute([':pass' => $pass, ':token' => $token]);
            
            header("Location: ../views/auth/login.php?mensaje=pass_actualizado");
        } else {
            die("Error: Token inválido o expirado.");
        }
        exit();
    }

    // ==========================================
    // 5. ACTUALIZAR CONTRASEÑA (DESDE PERFIL)
    // ==========================================
    if ($accion == 'actualizar_perfil') {
        $id_usuario = $_POST['id_usuario'];
        $pass_actual = trim($_POST['password_actual']);
        $pass_nueva = trim($_POST['password_nueva']);

        // Verificar la contraseña actual
        $stmt = $pdo->prepare("SELECT password FROM Tbl_Usuarios WHERE id_usuario = :id");
        $stmt->execute([':id' => $id_usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass_actual, $user['password'])) {
            $hashNuevo = password_hash($pass_nueva, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE Tbl_Usuarios SET password = :pass WHERE id_usuario = :id");
            $update->execute([':pass' => $hashNuevo, ':id' => $id_usuario]);

            header("Location: ../views/dashboard/perfil.php?mensaje=clave_actualizada");
        } else {
            header("Location: ../views/dashboard/perfil.php?error=clave_incorrecta");
        }
        exit();
    }

    // ==========================================
    // 6. ACTUALIZAR DATOS PERSONALES (DESDE PERFIL)
    // ==========================================
    if ($accion == 'actualizar_datos') {
        $id_usuario = $_POST['id_usuario'];
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $rol = $_POST['rol'];

        try {
            $sql = "UPDATE Tbl_Usuarios SET nombre_completo = :nom, email = :email, rol = :rol WHERE id_usuario = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nombre,
                ':email' => $email,
                ':rol' => $rol,
                ':id' => $id_usuario
            ]);

            // Actualizamos la sesión para que los cambios se reflejen al instante
            $_SESSION['nombre'] = $nombre;
            $_SESSION['rol'] = $rol;

            header("Location: ../views/dashboard/perfil.php?mensaje=datos_actualizados");
            exit();
        } catch (PDOException $e) {
            die("Error al actualizar datos: " . $e->getMessage());
        }
    }
}
?>