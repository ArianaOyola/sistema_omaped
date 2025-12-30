<?php
// controllers/ExportarExcel.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../views/auth/login.php");
    exit();
}

$nombre_archivo = "Padron_Beneficiarios_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$nombre_archivo");
header("Pragma: no-cache"); 
header("Expires: 0");

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

$where = "";
$params = [];

if ($busqueda != '') {
    $where = "WHERE b.dni LIKE :b1 OR b.nombres_apellidos LIKE :b2";
    $params = [':b1' => "%$busqueda%", ':b2' => "%$busqueda%"];
} elseif ($filtro != '') {
    // Filtros por Adultos (>= 18 años)
    if ($filtro == 'varones') {
        $where = "WHERE b.genero = 'Masculino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 >= 18";
    } elseif ($filtro == 'mujeres') {
        $where = "WHERE b.genero = 'Femenino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 >= 18";
    } 
    // Filtros por Menores (< 18 años)
    elseif ($filtro == 'ninos') {
        $where = "WHERE b.genero = 'Masculino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 < 18";
    } elseif ($filtro == 'ninas') {
        $where = "WHERE b.genero = 'Femenino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 < 18";
    }
    elseif ($filtro == 'mes') {
        $where = "WHERE MONTH(b.fecha_registro) = MONTH(CURRENT_DATE()) AND YEAR(b.fecha_registro) = YEAR(CURRENT_DATE())";
    }
}

try {
    $sql = "SELECT 
                b.dni, b.nombres_apellidos, b.genero, b.fecha_nacimiento, 
                d.tipo_discapacidad, d.grado_discapacidad, 
                d.tiene_carnet_conadis, d.programa_contigo,
                b.nombre_cuidador, b.dni_cuidador, b.telefono_cuidador, 
                b.telefono, b.direccion, b.distrito, b.provincia, b.fecha_registro
            FROM Tbl_Beneficiarios b
            LEFT JOIN Tbl_Detalle_Discapacidad d ON b.id_beneficiario = d.id_beneficiario
            $where
            ORDER BY b.fecha_registro DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de exportación");
}

echo "<meta charset='UTF-8'>";
echo "<table border='1'>";
echo "<thead>
        <tr style='background-color: #4b6cb7; color: white;'>
            <th>DNI BENEFICIARIO</th>
            <th>NOMBRES Y APELLIDOS</th>
            <th>GÉNERO</th>
            <th>EDAD</th>
            <th>DISCAPACIDAD</th>
            <th>GRADO</th>
            <th>CONADIS</th>
            <th>CONTIGO</th>
            <th>NOMBRE CUIDADOR</th>
            <th>DNI CUIDADOR</th>
            <th>CELULAR CUIDADOR</th>
            <th>TELÉFONO BENEF.</th>
            <th>DIRECCIÓN</th>
            <th>DISTRITO</th>
            <th>PROVINCIA</th>
            <th>FECHA REGISTRO</th>
        </tr>
      </thead>";
echo "<tbody>";

foreach ($filas as $row) {
    $nac = new DateTime($row['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($nac)->y;

    echo "<tr>";
    echo "<td>" . $row['dni'] . "</td>";
    echo "<td>" . $row['nombres_apellidos'] . "</td>";
    echo "<td>" . $row['genero'] . "</td>";
    echo "<td>" . $edad . "</td>";
    echo "<td>" . $row['tipo_discapacidad'] . "</td>";
    echo "<td>" . $row['grado_discapacidad'] . "</td>";
    echo "<td>" . $row['tiene_carnet_conadis'] . "</td>";
    echo "<td>" . $row['programa_contigo'] . "</td>";
    // DATOS CUIDADOR
    echo "<td>" . $row['nombre_cuidador'] . "</td>";
    echo "<td>" . $row['dni_cuidador'] . "</td>";
    echo "<td>" . $row['telefono_cuidador'] . "</td>";
    // FIN DATOS CUIDADOR
    echo "<td>" . $row['telefono'] . "</td>";
    echo "<td>" . $row['direccion'] . "</td>";
    echo "<td>" . $row['distrito'] . "</td>";
    echo "<td>" . $row['provincia'] . "</td>";
    echo "<td>" . date('d/m/Y', strtotime($row['fecha_registro'])) . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";
exit();
?>