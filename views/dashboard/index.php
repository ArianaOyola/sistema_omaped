<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: ../auth/login.php"); exit(); }
$nombreUsuario = $_SESSION['nombre'];

$total = 0; $varones = 0; $mujeres = 0; $mesActual = 0; $ninos = 0; $ninas = 0; // NUEVAS VARIABLES
$labelsDiscapacidad = []; $dataDiscapacidad = [];
$labelsMeses = []; $dataMeses = []; $puntosMapa = [];

try {
    $total = $pdo->query("SELECT COUNT(*) FROM Tbl_Beneficiarios")->fetchColumn();
    // Filtros por edad: >= 18 para Adultos, < 18 para Niños
    $varones = $pdo->query("SELECT COUNT(*) FROM Tbl_Beneficiarios WHERE genero = 'Masculino' AND DATEDIFF(CURRENT_DATE(), fecha_nacimiento) / 365.25 >= 18")->fetchColumn();
    $mujeres = $pdo->query("SELECT COUNT(*) FROM Tbl_Beneficiarios WHERE genero = 'Femenino' AND DATEDIFF(CURRENT_DATE(), fecha_nacimiento) / 365.25 >= 18")->fetchColumn();
    $ninos = $pdo->query("SELECT COUNT(*) FROM Tbl_Beneficiarios WHERE genero = 'Masculino' AND DATEDIFF(CURRENT_DATE(), fecha_nacimiento) / 365.25 < 18")->fetchColumn();
    $ninas = $pdo->query("SELECT COUNT(*) FROM Tbl_Beneficiarios WHERE genero = 'Femenino' AND DATEDIFF(CURRENT_DATE(), fecha_nacimiento) / 365.25 < 18")->fetchColumn();

    $mesActual = $pdo->query("SELECT COUNT(*) FROM Tbl_Beneficiarios WHERE MONTH(fecha_registro) = MONTH(CURRENT_DATE()) AND YEAR(fecha_registro) = YEAR(CURRENT_DATE())")->fetchColumn();

    $stmt = $pdo->query("SELECT tipo_discapacidad, COUNT(*) as cantidad FROM Tbl_Detalle_Discapacidad GROUP BY tipo_discapacidad");
    $tiposData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($tiposData as $row) { $labelsDiscapacidad[] = $row['tipo_discapacidad']; $dataDiscapacidad[] = $row['cantidad']; }

    $sqlHistorial = "SELECT MONTH(fecha_registro) as mes, COUNT(*) as total FROM Tbl_Beneficiarios WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(fecha_registro) ORDER BY fecha_registro ASC";
    $historialData = $pdo->query($sqlHistorial)->fetchAll(PDO::FETCH_ASSOC);
    $nombresMeses = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    foreach($historialData as $h) { $labelsMeses[] = $nombresMeses[$h['mes']]; $dataMeses[] = $h['total']; }
    if (empty($labelsMeses)) { $labelsMeses[] = $nombresMeses[date('n')]; $dataMeses[] = $mesActual; }

    $checkCols = $pdo->query("SHOW COLUMNS FROM Tbl_Beneficiarios LIKE 'latitud'");
    if ($checkCols->rowCount() > 0) {
        $sqlMap = "SELECT latitud, longitud, nombres_apellidos, tipo_discapacidad FROM Tbl_Beneficiarios LEFT JOIN Tbl_Detalle_Discapacidad ON Tbl_Beneficiarios.id_beneficiario = Tbl_Detalle_Discapacidad.id_beneficiario WHERE latitud IS NOT NULL AND longitud IS NOT NULL AND latitud != ''";
        $puntosMapa = $pdo->query($sqlMap)->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMAPED AUCALLAMA</title>
    <link rel="icon" href="../../assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style> #mapaGeneral { z-index: 1; } </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <div class="nav-brand"><i class="fas fa-heart"></i> OMAPED AUCALLAMA</div>
            <div class="nav-links">
                <a href="index.php" class="nav-button active">Dashboard</a>
                <a href="formulario.php" class="nav-button">Nuevo Registro</a>
                <a href="lista.php" class="nav-button">Beneficiarios</a>
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

    <div class="dashboard-content">
        <header class="dashboard-header">
            <div><h2 class="main-title">Panel de Control</h2><p class="subtitle">Resumen Estratégico de OMAPED</p></div>
        </header>

        <div class="stats-cards-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
            <div class="stat-card bg-gradient-blue" onclick="window.location.href='lista.php'" title="Ver Todos"><div class="stat-info"><span class="stat-number"><?php echo $total; ?></span><span class="stat-label">Total Beneficiarios</span></div><div class="stat-icon-container"><i class="fas fa-users"></i></div></div>
            <div class="stat-card bg-gradient-green" onclick="window.location.href='lista.php?filtro=mes'" title="Ver Nuevos"><div class="stat-info"><span class="stat-number"><?php echo $mesActual; ?></span><span class="stat-label">Nuevos (Mes)</span></div><div class="stat-icon-container"><i class="fas fa-plus-circle"></i></div></div>
            <div class="stat-card bg-gradient-purple" onclick="window.location.href='lista.php?filtro=mujeres'" title="Ver Mujeres Adultas"><div class="stat-info"><span class="stat-number"><?php echo $mujeres; ?></span><span class="stat-label">Mujeres (Adultas)</span></div><div class="stat-icon-container"><i class="fas fa-female"></i></div></div>
            <div class="stat-card bg-gradient-orange" onclick="window.location.href='lista.php?filtro=varones'" title="Ver Varones Adultos"><div class="stat-info"><span class="stat-number"><?php echo $varones; ?></span><span class="stat-label">Varones (Adultos)</span></div><div class="stat-icon-container"><i class="fas fa-male"></i></div></div>
        </div>

        <div class="stats-cards-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; max-width: 50%; margin-left: 0;">
            <div class="stat-card bg-gradient-purple" style="background: linear-gradient(135deg, #a77cff 0%, #6f34a5 100%) !important;" onclick="window.location.href='lista.php?filtro=ninas'" title="Ver Niñas"><div class="stat-info"><span class="stat-number"><?php echo $ninas; ?></span><span class="stat-label">Niñas (< 18 años)</span></div><div class="stat-icon-container"><i class="fas fa-child"></i></div></div>
            <div class="stat-card bg-gradient-orange" style="background: linear-gradient(135deg, #ffb88c 0%, #d56a38 100%) !important;" onclick="window.location.href='lista.php?filtro=ninos'" title="Ver Niños"><div class="stat-info"><span class="stat-number"><?php echo $ninos; ?></span><span class="stat-label">Niños (< 18 años)</span></div><div class="stat-icon-container"><i class="fas fa-child"></i></div></div>
        </div>
        <section class="quick-actions" style="margin-bottom: 40px;">
            <h3 class="section-title">Acciones Rápidas</h3>
            <div class="actions-grid">
                <div class="action-card" onclick="window.location.href='formulario.php'"><i class="fas fa-user-plus action-icon"></i><h4>Registrar Beneficiario</h4><p>Agregar un nuevo beneficiario</p></div>
                <div class="action-card" onclick="window.location.href='lista.php'"><i class="fas fa-users action-icon"></i><h4>Ver Beneficiarios</h4><p>Consultar lista completa</p></div>
            </div>
        </section>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px;">
            <div class="chart-box"><h3 class="chart-title">Evolución de Registros</h3><div style="position: relative; height: 280px;"><canvas id="historyChart"></canvas></div></div>
            <div class="chart-box"><h3 class="chart-title">Distribución por Género y Edad</h3><div style="position: relative; height: 230px; display: flex; justify-content: center;"><canvas id="genderChart"></canvas></div></div>
        </div>

        <div class="bottom-grid">
            <div class="chart-box"><h3 class="chart-title">Por Tipo de Discapacidad</h3><div style="position: relative; height: 350px; width: 90%; margin: 0 auto;"><canvas id="disabilityChart"></canvas></div></div>
            <div class="chart-box"><h3 class="chart-title"><i class="fas fa-map-marked-alt"></i> Mapa de Distribución</h3><div id="mapaGeneral" style="height: 350px; width: 100%; border-radius: 10px;"></div></div>
        </div>
    </div>
    <script src="../../assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('genderChart'), { type: 'doughnut', data: { labels: ['Varones Adultos', 'Mujeres Adultas', 'Niños', 'Niñas'], datasets: [{ data: [<?php echo $varones; ?>, <?php echo $mujeres; ?>, <?php echo $ninos; ?>, <?php echo $ninas; ?>], backgroundColor: ['#182848', '#9346f7', '#ff9966', '#43cea2'], borderWidth: 0, hoverOffset: 10 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '70%' } });
            new Chart(document.getElementById('historyChart'), { type: 'line', data: { labels: <?php echo json_encode($labelsMeses); ?>, datasets: [{ label: 'Nuevos', data: <?php echo json_encode($dataMeses); ?>, borderColor: '#4b6cb7', backgroundColor: 'rgba(75, 108, 183, 0.1)', borderWidth: 3, tension: 0.4, fill: true }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } });
            new Chart(document.getElementById('disabilityChart'), { type: 'polarArea', data: { labels: <?php echo json_encode($labelsDiscapacidad); ?>, datasets: [{ label: 'Cantidad', data: <?php echo json_encode($dataDiscapacidad); ?>, backgroundColor: ['rgba(75, 108, 183, 0.7)', 'rgba(67, 206, 162, 0.7)', 'rgba(255, 153, 102, 0.7)', 'rgba(147, 70, 247, 0.7)', 'rgba(255, 94, 98, 0.7)'] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, scales: { r: { ticks: { backdropColor: 'transparent' } } } } });
            var map = L.map('mapaGeneral').setView([-11.55, -77.20], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
            var puntos = <?php echo json_encode($puntosMapa); ?>;
            if (puntos && puntos.length > 0) { puntos.forEach(function(punto) { if(punto.latitud && punto.longitud) { L.circleMarker([punto.latitud, punto.longitud], { radius: 8, fillColor: "#ff7800", color: "#000", weight: 1, opacity: 1, fillOpacity: 0.8 }).addTo(map).bindPopup("<b>" + punto.nombres_apellidos + "</b><br>" + punto.tipo_discapacidad); } }); }
        });
    </script>
</body>
</html>