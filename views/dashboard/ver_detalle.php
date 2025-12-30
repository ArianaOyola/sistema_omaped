<?php
session_start();
require_once '../../config/db.php';

if (!isset($_GET['id'])) { header("Location: lista.php"); exit(); }
$nombreUsuario = $_SESSION['nombre'];
$id = $_GET['id'];

// Consulta completa, incluyendo campos del cuidador
$stmt = $pdo->prepare("SELECT b.*, d.* FROM Tbl_Beneficiarios b 
                       LEFT JOIN Tbl_Detalle_Discapacidad d ON b.id_beneficiario = d.id_beneficiario 
                       WHERE b.id_beneficiario = :id");
$stmt->execute([':id' => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("No encontrado.");

// Cálculo de edad
$fechaNacimiento = new DateTime($data['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fechaNacimiento)->y;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha - <?php echo htmlspecialchars($data['nombres_apellidos']); ?></title>
    <link rel="icon" href="../../assets/img/logo.png" type="image/png">
    
    <link rel="stylesheet" href="../../assets/css/style.css?v=7.0"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>

    <nav class="navbar">
        <div class="nav-left">
            <div class="nav-brand">OMAPED AUCALLAMA</div>
            <div class="nav-links">
                <a href="lista.php" class="nav-button active">Volver</a>
            </div>
        </div>
        <div class="header-info">
            <div class="user-profile-widget">
                <div class="user-avatar-circle"><?php echo strtoupper(substr($nombreUsuario, 0, 1)); ?></div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="register-main-content">
        <div class="register-card">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="register-main-title" style="margin:0;">Ficha de Beneficiario</h2>
                <div class="action-footer" style="margin:0;">
                    <button onclick="window.print()" class="btn-secondary">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>

            <div class="detail-grid">
                
                <div class="info-box">
                    <h3 class="form-section-title">Datos Personales</h3>
                    <p><strong>DNI:</strong> <?php echo htmlspecialchars($data['dni']); ?></p>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($data['nombres_apellidos']); ?></p>
                    <p><strong>Género:</strong> <?php echo htmlspecialchars($data['genero']); ?></p>
                    <p><strong>Fecha Nac:</strong> <?php echo date('d/m/Y', strtotime($data['fecha_nacimiento'])); ?> (Edad: <strong><?php echo $edad; ?></strong>)</p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($data['telefono']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email_contacto']); ?></p>
                </div>
                
                <div class="info-box">
                    <h3 class="form-section-title">Discapacidad y Programas</h3>
                    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($data['tipo_discapacidad']); ?></p>
                    <p><strong>Grado:</strong> <?php echo htmlspecialchars($data['grado_discapacidad']); ?></p>
                    
                    <hr class="detail-divider">
                    <p><strong>Estado CONADIS:</strong> <span class="text-highlight-blue"><?php echo htmlspecialchars($data['tiene_carnet_conadis']); ?></span></p>
                    <?php if ($data['tiene_carnet_conadis'] == 'SI'): ?>
                        <p><strong>N° Carné:</strong> <?php echo htmlspecialchars($data['numero_carnet']); ?></p>
                        <p><strong>Vencimiento:</strong> 
                            <?php 
                                if($data['fecha_vencimiento_carnet']) { 
                                    $vence = new DateTime($data['fecha_vencimiento_carnet']); 
                                    $hoy = new DateTime(); 
                                    $clase = ($vence < $hoy) ? 'text-danger' : ''; 
                                    echo "<span class='$clase'>" . $vence->format('d/m/Y') . "</span>"; 
                                    if ($vence < $hoy) echo " <b class='text-danger'>(VENCIDO)</b>"; 
                                } 
                            ?>
                        </p>
                    <?php endif; ?>
                    
                    <hr class="detail-divider">
                    <p><strong>Estado CONTIGO:</strong> <span class="text-highlight-purple"><?php echo htmlspecialchars($data['programa_contigo'] ?? 'NO'); ?></span></p>
                </div>
            </div>
            
            <?php if (!empty($data['nombre_cuidador'])): ?>
            <div class="info-box" style="margin-top: 20px;">
                <h3 class="form-section-title"><i class="fas fa-hand-holding-heart"></i> Datos del Cuidador (Tutor)</h3>
                <div class="detail-grid">
                    <div>
                        <p><strong>Nombre:</strong> <span class="text-highlight-blue"><?php echo htmlspecialchars($data['nombre_cuidador']); ?></span></p>
                        <p><strong>DNI:</strong> <?php echo htmlspecialchars($data['dni_cuidador']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($data['telefono_cuidador']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="info-box" style="margin-top: 20px;">
                <h3 class="form-section-title">Ubicación Geográfica</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($data['direccion']); ?></p>
                        <p><strong>Distrito:</strong> <?php echo htmlspecialchars($data['distrito']); ?></p>
                        <p><strong>Provincia:</strong> <?php echo htmlspecialchars($data['provincia']); ?></p>
                    </div>
                    
                    <div style="height: 300px; border-radius: 8px; border: 1px solid #ddd; overflow: hidden;">
                        <div id="mapaDetalle" style="height: 100%; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <?php 
                // Consultar última observación para mostrarla
                $obsSql = "SELECT observacion FROM Tbl_Seguimiento WHERE id_beneficiario = :id ORDER BY id_seguimiento ASC LIMIT 1";
                $stmtObs = $pdo->prepare($obsSql);
                $stmtObs->execute([':id' => $id]);
                $obs = $stmtObs->fetchColumn();
            ?>
            <?php if($obs): ?>
                <div class="info-box" style="margin-top: 20px;">
                    <h3 class="form-section-title">Observaciones Iniciales</h3>
                    <p><?php echo nl2br(htmlspecialchars($obs)); ?></p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../../assets/js/app.js"></script>

    <script>
        var lat = "<?php echo $data['latitud']; ?>";
        var lng = "<?php echo $data['longitud']; ?>";

        if (lat && lng) {
            var map = L.map('mapaDetalle').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup("<b><?php echo htmlspecialchars($data['nombres_apellidos']); ?></b><br><?php echo htmlspecialchars($data['direccion']); ?>")
                .openPopup();
        } else {
            document.getElementById('mapaDetalle').innerHTML = '<div style="display:flex; align-items:center; justify-content:center; height:100%; color:#777;">Sin ubicación registrada en el mapa.</div>';
        }
    </script>
</body>
</html>