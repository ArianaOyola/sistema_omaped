<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: ../auth/login.php"); exit(); }
$nombreUsuario = $_SESSION['nombre'];

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$where = ""; $params = []; $titulo_lista = "Mostrando todos los registros";

if ($busqueda != '') {
    $where = "WHERE b.dni LIKE :b1 OR b.nombres_apellidos LIKE :b2"; $params = [':b1' => "%$busqueda%", ':b2' => "%$busqueda%"]; $titulo_lista = "Resultados para: '" . htmlspecialchars($busqueda) . "'";
} elseif ($filtro != '') {
    // Filtros por Adultos (>= 18 años)
    if ($filtro == 'varones') { $where = "WHERE b.genero = 'Masculino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 >= 18"; $titulo_lista = "Filtrado por: Varones (Adultos)"; }
    elseif ($filtro == 'mujeres') { $where = "WHERE b.genero = 'Femenino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 >= 18"; $titulo_lista = "Filtrado por: Mujeres (Adultas)"; }
    // Filtros por Menores (< 18 años)
    elseif ($filtro == 'ninos') { $where = "WHERE b.genero = 'Masculino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 < 18"; $titulo_lista = "Filtrado por: Niños (< 18)"; }
    elseif ($filtro == 'ninas') { $where = "WHERE b.genero = 'Femenino' AND DATEDIFF(CURRENT_DATE(), b.fecha_nacimiento) / 365.25 < 18"; $titulo_lista = "Filtrado por: Niñas (< 18)"; }
    elseif ($filtro == 'mes') { $where = "WHERE MONTH(b.fecha_registro) = MONTH(CURRENT_DATE()) AND YEAR(b.fecha_registro) = YEAR(CURRENT_DATE())"; $titulo_lista = "Registros de Este Mes"; }
}

try {
    // Se agregan los campos del cuidador a la consulta SELECT
    $sql = "SELECT b.*, d.tipo_discapacidad, d.grado_discapacidad, d.tiene_carnet_conadis, d.programa_contigo, b.nombre_cuidador, b.dni_cuidador, b.telefono_cuidador, (SELECT observacion FROM Tbl_Seguimiento WHERE id_beneficiario = b.id_beneficiario ORDER BY id_seguimiento ASC LIMIT 1) as observacion_inicial FROM Tbl_Beneficiarios b LEFT JOIN Tbl_Detalle_Discapacidad d ON b.id_beneficiario = d.id_beneficiario $where ORDER BY b.fecha_registro DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $beneficiarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OMAPED AUCALLAMA</title>
    <link rel="icon" href="../../assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <div class="nav-brand"><i class="fas fa-heart"></i> OMAPED AUCALLAMA</div>
            <div class="nav-links">
                <a href="index.php" class="nav-button">Dashboard</a>
                <a href="formulario.php" class="nav-button">Nuevo Registro</a>
                <a href="lista.php" class="nav-button active">Beneficiarios</a>
            </div>
        </div>
        <div class="header-info">
            <div class="user-profile-widget" onclick="toggleUserMenu()">
                <div class="user-avatar-circle"><?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?></div>
                <div class="user-details"><span class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></span><span class="user-role"><?php echo htmlspecialchars($_SESSION['rol'] ?? 'Usuario'); ?></span></div>
                <i class="fas fa-chevron-down user-toggle-icon"></i>
            </div>
            <div class="user-dropdown-menu" id="userMenu">
                <a href="perfil.php" class="user-dropdown-item"><i class="fas fa-user-circle"></i> Mi Perfil</a>
                <div class="dropdown-divider"></div>
                <a href="../../controllers/Logout.php" class="user-dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    <div class="beneficiarios-content">
        <header class="beneficiarios-header">
            <div><h2 class="beneficiarios-title">Padrón de Beneficiarios</h2><p class="beneficiarios-total"><?php echo $titulo_lista; ?><?php if($busqueda != '' || $filtro != ''): ?><a href="lista.php" class="badge-filter"><i class="fas fa-times"></i> Quitar Filtros</a><?php endif; ?></p></div>
            <div class="header-actions"><button class="btn-excel" onclick="window.location.href='../../controllers/ExportarExcel.php?busqueda=<?php echo urlencode($busqueda); ?>&filtro=<?php echo urlencode($filtro); ?>'"><i class="fas fa-file-excel"></i> Exportar</button><button class="btn-new-registro" onclick="window.location.href='formulario.php'"><i class="fas fa-user-plus"></i> Nuevo</button></div>
        </header>
        <div class="table-container">
            <form action="lista.php" method="GET" class="search-bar"><i class="fas fa-search"></i><input type="text" name="busqueda" placeholder="Buscar por DNI o Apellidos..." value="<?php echo htmlspecialchars($busqueda); ?>"></form>
            <?php if (isset($_GET['mensaje'])): ?><div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;"><?php if ($_GET['mensaje'] == 'registrado') echo "✅ Registrado correctamente."; if ($_GET['mensaje'] == 'eliminado') echo "🗑️ Eliminado correctamente."; if ($_GET['mensaje'] == 'actualizado') echo "🔄 Actualizado correctamente."; ?></div><?php endif; ?>
            <div class="table-responsive"> 
                <table class="beneficiarios-table">
                    <thead><tr><th>DNI</th><th>Nombres</th><th>Discapacidad</th><th>CONADIS</th><th>CONTIGO</th><th>Cuidador (DNI)</th><th>Observaciones</th><th>Distrito</th><th>Provincia</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php if (empty($beneficiarios)): ?><tr><td colspan="10" style="text-align:center; padding: 20px;">No se encontraron registros.</td></tr><?php else: foreach ($beneficiarios as $b): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['dni']); ?></strong></td>
                            <td><?php echo htmlspecialchars($b['nombres_apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($b['tipo_discapacidad']); ?></td>
                            <td><?php echo htmlspecialchars($b['tiene_carnet_conadis']); ?></td>
                            <td><?php echo htmlspecialchars($b['programa_contigo']); ?></td>
                            <td>
                                <?php if (!empty($b['dni_cuidador'])): ?>
                                    <span title="<?php echo htmlspecialchars($b['nombre_cuidador'] . ' - Tel: ' . $b['telefono_cuidador']); ?>"><?php echo htmlspecialchars($b['dni_cuidador']); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="col-obs" title="<?php echo htmlspecialchars($b['observacion_inicial']); ?>"><?php echo htmlspecialchars($b['observacion_inicial'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($b['distrito']); ?></td>
                            <td><?php echo htmlspecialchars($b['provincia']); ?></td>
                            <td style="white-space: nowrap;"><a href="ver_detalle.php?id=<?php echo $b['id_beneficiario']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a><a href="editar.php?id=<?php echo $b['id_beneficiario']; ?>" class="btn-action edit"><i class="fas fa-pen"></i></a><button onclick="confirmarEliminar(<?php echo $b['id_beneficiario']; ?>)" class="btn-action delete"><i class="fas fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../../assets/js/app.js"></script>
    <script>function confirmarEliminar(id) { if (confirm("¿Eliminar beneficiario?")) { const form = document.createElement('form'); form.method = 'POST'; form.action = '../../controllers/BeneficiarioController.php'; const i1 = document.createElement('input'); i1.type='hidden'; i1.name='accion'; i1.value='eliminar'; const i2 = document.createElement('input'); i2.type='hidden'; i2.name='id'; i2.value=id; form.appendChild(i1); form.appendChild(i2); document.body.appendChild(form); form.submit(); } }</script>
</body>
</html>