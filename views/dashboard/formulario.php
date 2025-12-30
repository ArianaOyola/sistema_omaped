<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../auth/login.php"); exit(); }
$nombreUsuario = $_SESSION['nombre'];
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
            <div class="nav-links">
                <a href="index.php" class="nav-button">Dashboard</a>
                <a href="formulario.php" class="nav-button active">Nuevo Registro</a>
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
    <div class="register-main-content">
        <div class="register-card">
            <h2 class="register-main-title">Registro de Beneficiario</h2>
            <?php if (isset($_GET['error']) && $_GET['error'] == 'dni_duplicado'): ?><div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">Error: DNI ya registrado.</div><?php endif; ?>
            <form id="registroBeneficiario" class="register-form" action="../../controllers/BeneficiarioController.php" method="POST">
                <input type="hidden" name="accion" value="registrar">
                
                <h3 class="form-section-title"><i class="fas fa-user"></i> Datos Personales</h3>
                <div class="form-group"><label>DNI *</label><input type="number" name="dni" required minlength="8" maxlength="8"></div>
                <div class="form-group"><label>Nombres y Apellidos *</label><input type="text" name="nombres" required></div>
                <div class="form-group"><label>Género *</label><select name="genero" required><option value="">Seleccione</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option><option value="Otro">Otro</option></select></div>
                <div class="form-group"><label>Fecha de Nacimiento *</label><input type="date" name="fecha_nac" id="fecha_nac_beneficiario" required onchange="checkMinorAge()"></div>

                <h3 class="form-section-title" id="cuidador_section_title" style="display: none;"><i class="fas fa-child"></i> Datos del Cuidador (Menor de Edad)</h3>
                <div id="cuidador_fields" style="display: none;">
                    <div class="form-group">
                        <label>Nombres y Apellidos Cuidador *</label>
                        <input type="text" name="nombre_cuidador" id="nombre_cuidador" placeholder="Nombre completo del padre/tutor">
                    </div>
                    <div class="form-group">
                        <label>DNI Cuidador *</label>
                        <input type="number" name="dni_cuidador" id="dni_cuidador" minlength="8" maxlength="8">
                    </div>
                    <div class="form-group">
                        <label>Celular Cuidador</label>
                        <input type="tel" name="telefono_cuidador" id="telefono_cuidador">
                    </div>
                </div>

                <h3 class="form-section-title"><i class="fas fa-wheelchair"></i> Discapacidad</h3>
                <div class="form-group"><label>Tipo *</label><select name="tipo_discapacidad" required><option value="">Seleccione</option><option value="Motora">Motora</option><option value="Sensorial">Sensorial</option><option value="Mental">Mental</option><option value="Visceral">Visceral</option><option value="Visual">Visual</option></select></div>
                <div class="form-group"><label>Grado</label><select name="grado_discapacidad"><option value="Leve">Leve</option><option value="Moderado">Moderado</option><option value="Severo">Severo</option></select></div>
                <div class="form-group"><label>CONADIS</label><select name="tiene_carnet" id="tiene_carnet" onchange="toggleCarnet()"><option value="NO">NO</option><option value="SI">SI</option><option value="EN TRAMITE">EN TRAMITE</option><option value="NO ELEGIBLE">NO ELEGIBLE</option></select></div>
                <div class="form-group" id="grupo_numero_carnet" style="display: none;"><label>N° Carné</label><input type="text" name="numero_carnet"></div>
                <div class="form-group" id="grupo_vencimiento_carnet" style="display: none;"><label>Vencimiento</label><input type="date" name="fecha_vencimiento"></div>
                <div class="form-group"><label>CONTIGO</label><select name="programa_contigo"><option value="NO">NO</option><option value="SI">SI</option><option value="EN TRAMITE">EN TRAMITE</option><option value="NO ELEGIBLE">NO ELEGIBLE</option></select></div>
                <h3 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Ubicación</h3>
                <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                <div class="form-group full-width"><label>Dirección *</label><input type="text" name="direccion" required></div>
                <div class="form-group"><label>Distrito</label><input type="text" name="distrito" value="AUCALLAMA" required></div>
                <div class="form-group"><label>Provincia</label><input type="text" name="provincia" value="HUARAL" required></div>
                <div class="form-group full-width"><label>Ubicación Geográfica</label><div id="mapaRegistro" style="height: 300px; border-radius: 8px;"></div><input type="hidden" name="latitud" id="latitud"><input type="hidden" name="longitud" id="longitud"></div>
                <div class="form-group full-width"><label>Observaciones</label><textarea name="observaciones" rows="3"></textarea></div>
                <div class="form-actions"><button type="submit" class="btn-primary btn-save">Guardar</button><button type="button" class="btn-secondary" onclick="window.location.href='index.php'">Cancelar</button></div>
            </form>
        </div>
    </div>
    <script src="../../assets/js/app.js"></script>
    <script>
        function toggleCarnet() { const val = document.getElementById('tiene_carnet').value; const disp = val === 'SI' ? 'flex' : 'none'; document.getElementById('grupo_numero_carnet').style.display = disp; document.getElementById('grupo_vencimiento_carnet').style.display = disp; } 
        var map = L.map('mapaRegistro').setView([-11.55, -77.20], 13); 
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map); 
        var marker; 
        map.on('click', function(e) { 
            if (marker) marker.setLatLng(e.latlng); else marker = L.marker(e.latlng).addTo(map); 
            document.getElementById('latitud').value = e.latlng.lat; 
            document.getElementById('longitud').value = e.latlng.lng; 
        });
        
        // Función JS para verificar la edad y mostrar/ocultar el cuidador
        function checkMinorAge() {
            const fechaNacInput = document.getElementById('fecha_nac_beneficiario');
            const fechaNac = new Date(fechaNacInput.value);
            const hoy = new Date();
            const edad = hoy.getFullYear() - fechaNac.getFullYear();
            const esMenor = edad < 18;
            
            const cuidadorFields = document.getElementById('cuidador_fields');
            const cuidadorTitle = document.getElementById('cuidador_section_title');
            const nombreCuidadorInput = document.getElementById('nombre_cuidador');
            const dniCuidadorInput = document.getElementById('dni_cuidador');

            if (fechaNacInput.value === "") {
                cuidadorTitle.style.display = 'none';
                cuidadorFields.style.display = 'none';
                nombreCuidadorInput.removeAttribute('required');
                dniCuidadorInput.removeAttribute('required');
                return;
            }

            if (esMenor) {
                cuidadorTitle.style.display = 'block';
                cuidadorFields.style.display = 'grid'; // Usar grid si el formulario lo usa, o 'block'/'flex'
                nombreCuidadorInput.setAttribute('required', 'required');
                dniCuidadorInput.setAttribute('required', 'required');
            } else {
                cuidadorTitle.style.display = 'none';
                cuidadorFields.style.display = 'none';
                nombreCuidadorInput.removeAttribute('required');
                dniCuidadorInput.removeAttribute('required');
            }
        }
    </script>
</body>
</html>