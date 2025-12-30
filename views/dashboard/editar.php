<?php
session_start();
require_once '../../config/db.php';
if (!isset($_GET['id'])) { header("Location: lista.php"); exit(); }
$nombreUsuario = $_SESSION['nombre'];
$id = $_GET['id'];
// Incluimos los campos del cuidador en la consulta
$stmt = $pdo->prepare("SELECT b.*, d.* FROM Tbl_Beneficiarios b LEFT JOIN Tbl_Detalle_Discapacidad d ON b.id_beneficiario = d.id_beneficiario WHERE b.id_beneficiario = :id");
$stmt->execute([':id' => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) die("No encontrado.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OMAPED AUCALLAMA</title>
    <link rel="icon" href="../../assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../../assets/css/style.css?v=5.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <div class="nav-brand"><i class="fas fa-heart"></i> OMAPED AUCALLAMA</div>
            <div class="nav-links"><a href="lista.php" class="nav-button active">Volver</a></div>
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
    <div class="register-main-content">
        <div class="register-card">
            <h2 class="register-main-title">Editar: <?php echo htmlspecialchars($data['nombres_apellidos']); ?></h2>
            <form action="../../controllers/BeneficiarioController.php" method="POST" class="register-form">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_beneficiario" value="<?php echo $data['id_beneficiario']; ?>">
                
                <h3 class="form-section-title">Datos</h3>
                <div class="form-group"><label>DNI</label><input type="text" value="<?php echo $data['dni']; ?>" readonly style="background:#eee;"></div>
                <div class="form-group"><label>Nombres</label><input type="text" name="nombres" value="<?php echo $data['nombres_apellidos']; ?>" required></div>
                <div class="form-group"><label>Género</label><select name="genero"><option value="Masculino" <?php if($data['genero']=='Masculino') echo 'selected'; ?>>Masculino</option><option value="Femenino" <?php if($data['genero']=='Femenino') echo 'selected'; ?>>Femenino</option></select></div>
                <h3 class="form-section-title">Discapacidad</h3>
                <div class="form-group"><label>Tipo</label><select name="tipo_discapacidad"><?php foreach(['Motora','Sensorial','Mental','Visceral','Visual'] as $t) { $sel = ($data['tipo_discapacidad'] == $t) ? 'selected' : ''; echo "<option value='$t' $sel>$t</option>"; } ?></select></div>
                <div class="form-group"><label>CONADIS</label><select name="tiene_carnet" id="edit_tiene_carnet" onchange="toggleEditCarnet()"><?php foreach(['NO','SI','EN TRAMITE','NO ELEGIBLE'] as $op) { $sel = ($data['tiene_carnet_conadis'] == $op) ? 'selected' : ''; echo "<option value='$op' $sel>$op</option>"; } ?></select></div>
                <div class="form-group" id="edit_grupo_num" style="display: <?php echo ($data['tiene_carnet_conadis'] == 'SI') ? 'flex' : 'none'; ?>;"><label>N° Carné</label><input type="text" name="numero_carnet" value="<?php echo $data['numero_carnet']; ?>"></div>
                <div class="form-group" id="edit_grupo_fec" style="display: <?php echo ($data['tiene_carnet_conadis'] == 'SI') ? 'flex' : 'none'; ?>;"><label>Vencimiento</label><input type="date" name="fecha_vencimiento" value="<?php echo $data['fecha_vencimiento_carnet']; ?>"></div>
                <div class="form-group"><label>CONTIGO</label><select name="programa_contigo"><?php foreach(['NO','SI','EN TRAMITE','NO ELEGIBLE'] as $op) { $sel = ($data['programa_contigo'] == $op) ? 'selected' : ''; echo "<option value='$op' $sel>$op</option>"; } ?></select></div>
                
                <h3 class="form-section-title"><i class="fas fa-hand-holding-heart"></i> Datos del Cuidador</h3>
                <p class="subtitle" style="margin-bottom: 20px; grid-column: 1 / -1; font-size: 0.9rem; color: #777;">Completar solo si el beneficiario es menor de edad.</p>
                <div class="form-group"><label>Nombres Cuidador</label><input type="text" name="nombre_cuidador" value="<?php echo htmlspecialchars($data['nombre_cuidador'] ?? ''); ?>"></div>
                <div class="form-group"><label>DNI Cuidador</label><input type="text" name="dni_cuidador" value="<?php echo htmlspecialchars($data['dni_cuidador'] ?? ''); ?>"></div>
                <div class="form-group"><label>Teléfono Cuidador</label><input type="text" name="telefono_cuidador" value="<?php echo htmlspecialchars($data['telefono_cuidador'] ?? ''); ?>"></div>

                <h3 class="form-section-title">Ubicación</h3>
                <div class="form-group"><label>Dirección</label><input type="text" name="direccion" value="<?php echo $data['direccion']; ?>"></div>
                <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="<?php echo $data['telefono']; ?>"></div>
                <div class="form-group"><label>Distrito</label><input type="text" name="distrito" value="<?php echo $data['distrito']; ?>"></div>
                <div class="form-group"><label>Provincia</label><input type="text" name="provincia" value="<?php echo $data['provincia']; ?>"></div>
                <div class="form-group full-width"><label>Ubicación Geográfica</label><div id="mapaRegistro" style="height: 300px; border-radius: 8px;"></div><input type="hidden" name="latitud" id="latitud" value="<?php echo $data['latitud']; ?>"><input type="hidden" name="longitud" id="longitud" value="<?php echo $data['longitud']; ?>"></div>
                <div class="form-actions"><button type="submit" class="btn-primary btn-save">Actualizar</button></div>
            </form>
        </div>
    </div>
    <script src="../../assets/js/app.js"></script>
    <script>
        function toggleEditCarnet() { const estado = document.getElementById('edit_tiene_carnet').value; const display = (estado === 'SI') ? 'flex' : 'none'; document.getElementById('edit_grupo_num').style.display = display; document.getElementById('edit_grupo_fec').style.display = display; }
        var latDB = "<?php echo $data['latitud']; ?>"; var lngDB = "<?php echo $data['longitud']; ?>";
        var map = L.map('mapaRegistro').setView([-11.55, -77.20], 13);
        if(latDB && lngDB) map.setView([latDB, lngDB], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
        var marker; if(latDB && lngDB) marker = L.marker([latDB, lngDB]).addTo(map);
        map.on('click', function(e) { if (marker) marker.setLatLng(e.latlng); else marker = L.marker(e.latlng).addTo(map); document.getElementById('latitud').value = e.latlng.lat; document.getElementById('longitud').value = e.latlng.lng; });
    </script>
</body>
</html>