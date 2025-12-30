<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {

    // --- 1. REGISTRAR ---
    if ($_POST['accion'] == 'registrar') {
        $dni = trim($_POST['dni']);
        $nombres = trim($_POST['nombres']);
        $genero = $_POST['genero'];
        $fecha_nac = $_POST['fecha_nac'];
        $tipo_discap = $_POST['tipo_discapacidad'];
        $grado_discap = $_POST['grado_discapacidad'];
        
        $tiene_carnet = $_POST['tiene_carnet']; 
        $prog_contigo = $_POST['programa_contigo'];
        $num_carnet = ($tiene_carnet == 'SI' && !empty($_POST['numero_carnet'])) ? $_POST['numero_carnet'] : null;
        $fec_venc = ($tiene_carnet == 'SI' && !empty($_POST['fecha_vencimiento'])) ? $_POST['fecha_vencimiento'] : null;
        
        $telefono = trim($_POST['telefono']);
        $email_contacto = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);
        $observaciones = trim($_POST['observaciones']);
        $distrito = trim($_POST['distrito']);
        $provincia = trim($_POST['provincia']);
        
        // Coordenadas
        $latitud = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
        $longitud = !empty($_POST['longitud']) ? $_POST['longitud'] : null;
        
        // DATOS DEL CUIDADOR (NUEVOS CAMPOS)
        $nombre_cuidador = trim($_POST['nombre_cuidador'] ?? '');
        $dni_cuidador = trim($_POST['dni_cuidador'] ?? '');
        $telefono_cuidador = trim($_POST['telefono_cuidador'] ?? '');
        
        $usuario_id = $_SESSION['usuario_id'];

        try {
            $pdo->beginTransaction();
            $sql1 = "INSERT INTO Tbl_Beneficiarios (dni, nombres_apellidos, genero, fecha_nacimiento, telefono, email_contacto, direccion, distrito, provincia, latitud, longitud, id_usuario_registro, nombre_cuidador, dni_cuidador, telefono_cuidador) 
                     VALUES (:dni, :nombres, :genero, :fec_nac, :tel, :email, :dir, :dist, :prov, :lat, :lon, :user, :nom_cuid, :dni_cuid, :tel_cuid)";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([
                ':dni' => $dni, ':nombres' => $nombres, ':genero' => $genero, ':fec_nac' => $fecha_nac,
                ':tel' => $telefono, ':email' => $email_contacto, ':dir' => $direccion,
                ':dist' => $distrito, ':prov' => $provincia, ':lat' => $latitud, ':lon' => $longitud, 
                ':user' => $usuario_id,
                ':nom_cuid' => empty($nombre_cuidador) ? null : $nombre_cuidador,
                ':dni_cuid' => empty($dni_cuidador) ? null : $dni_cuidador,
                ':tel_cuid' => empty($telefono_cuidador) ? null : $telefono_cuidador
            ]);
            $id_beneficiario = $pdo->lastInsertId();

            $sql2 = "INSERT INTO Tbl_Detalle_Discapacidad (id_beneficiario, tipo_discapacidad, grado_discapacidad, tiene_carnet_conadis, programa_contigo, numero_carnet, fecha_vencimiento_carnet) VALUES (:id, :tipo, :grado, :tiene, :contigo, :num, :fec)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([':id' => $id_beneficiario, ':tipo' => $tipo_discap, ':grado' => $grado_discap, ':tiene' => $tiene_carnet, ':contigo' => $prog_contigo, ':num' => $num_carnet, ':fec' => $fec_venc]);

            $sql3 = "INSERT INTO Tbl_Seguimiento (id_beneficiario, id_usuario_responsable, tipo_accion, observacion) VALUES (:id, :user, 'Registro Inicial', :obs)";
            $stmt3 = $pdo->prepare($sql3);
            $stmt3->execute([':id' => $id_beneficiario, ':user' => $usuario_id, ':obs' => $observaciones]);

            $pdo->commit();
            header("Location: ../views/dashboard/lista.php?mensaje=registrado");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == '23000') { header("Location: ../views/dashboard/formulario.php?error=dni_duplicado"); } else { die("Error: " . $e->getMessage()); }
        }
    }

    // --- 2. ELIMINAR ---
    elseif ($_POST['accion'] == 'eliminar') {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM Tbl_Beneficiarios WHERE id_beneficiario = :id");
            $stmt->execute([':id' => $id]);
            header("Location: ../views/dashboard/lista.php?mensaje=eliminado");
            exit();
        } catch (PDOException $e) { die("Error: " . $e->getMessage()); }
    }

    // --- 3. EDITAR ---
    elseif ($_POST['accion'] == 'editar') {
        $id = $_POST['id_beneficiario'];
        $nombres = trim($_POST['nombres']);
        $genero = $_POST['genero'];
        $tipo = $_POST['tipo_discapacidad'];
        $tiene_carnet = $_POST['tiene_carnet'];
        $prog_contigo = $_POST['programa_contigo'];
        $num_carnet = ($tiene_carnet == 'SI' && !empty($_POST['numero_carnet'])) ? $_POST['numero_carnet'] : null;
        $fec_venc = ($tiene_carnet == 'SI' && !empty($_POST['fecha_vencimiento'])) ? $_POST['fecha_vencimiento'] : null;
        $direccion = trim($_POST['direccion']);
        $telefono = trim($_POST['telefono']);
        $distrito = trim($_POST['distrito']);
        $provincia = trim($_POST['provincia']);
        $latitud = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
        $longitud = !empty($_POST['longitud']) ? $_POST['longitud'] : null;
        
        // DATOS DEL CUIDADOR (NUEVOS CAMPOS)
        $nombre_cuidador = trim($_POST['nombre_cuidador'] ?? '');
        $dni_cuidador = trim($_POST['dni_cuidador'] ?? '');
        $telefono_cuidador = trim($_POST['telefono_cuidador'] ?? '');
        
        try {
            $pdo->beginTransaction();
            // SQL1: Actualiza datos principales, incluyendo los del cuidador
            $sql1 = "UPDATE Tbl_Beneficiarios SET nombres_apellidos = :nom, genero = :gen, direccion = :dir, telefono = :tel, distrito = :dist, provincia = :prov, latitud = :lat, longitud = :lon, nombre_cuidador = :nom_cuid, dni_cuidador = :dni_cuid, telefono_cuidador = :tel_cuid WHERE id_beneficiario = :id";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([
                ':nom' => $nombres, ':gen' => $genero, ':dir' => $direccion, 
                ':tel' => $telefono, ':dist' => $distrito, ':prov' => $provincia, 
                ':lat' => $latitud, ':lon' => $longitud, 
                ':nom_cuid' => empty($nombre_cuidador) ? null : $nombre_cuidador,
                ':dni_cuid' => empty($dni_cuidador) ? null : $dni_cuidador,
                ':tel_cuid' => empty($telefono_cuidador) ? null : $telefono_cuidador,
                ':id' => $id
            ]);

            $sql2 = "UPDATE Tbl_Detalle_Discapacidad SET tipo_discapacidad = :tipo, tiene_carnet_conadis = :tiene, programa_contigo = :contigo, numero_carnet = :num, fecha_vencimiento_carnet = :fec WHERE id_beneficiario = :id";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([':tipo' => $tipo, ':tiene' => $tiene_carnet, ':contigo' => $prog_contigo, ':num' => $num_carnet, ':fec' => $fec_venc, ':id' => $id]);

            $sql3 = "INSERT INTO Tbl_Seguimiento (id_beneficiario, id_usuario_responsable, tipo_accion, observacion) VALUES (:id, :user, 'Actualización de Datos', 'Edición desde sistema web')";
            $stmt3 = $pdo->prepare($sql3);
            $stmt3->execute([':id' => $id, ':user' => $_SESSION['usuario_id']]);

            $pdo->commit();
            header("Location: ../views/dashboard/lista.php?mensaje=actualizado");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack(); die("Error: " . $e->getMessage());
        }
    }
}
?>