# 🏥 Sistema de Gestión - OMAPED AUCALLAMA

Este es un sistema web integral diseñado para la Oficina Municipal de Atención a la Persona con Discapacidad (OMAPED) de Aucallama. Permite gestionar el padrón de beneficiarios, realizar seguimiento de casos y visualizar datos estadísticos.

## 🚀 Características Principales

* **Panel de Control (Dashboard):** Visualización de estadísticas en tiempo real mediante gráficos de barras y circulares sobre tipos de discapacidad y distribución por género/edad.
* **Gestión de Beneficiarios:** Registro completo de datos personales, detalles de discapacidad, estado de carné CONADIS y programa CONTIGO.
* **Geolocalización:** Integración con mapas interactivos para ubicar geográficamente a los beneficiarios.
* **Seguridad:** Sistema de autenticación con roles diferenciados como Jefa, Ayudante o Colaborador.
* **Reportes:** Función para exportar la lista de beneficiarios filtrada directamente a formato Excel.

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP usando PDO para conexiones seguras a bases de datos.
* **Frontend:** HTML5, CSS3 y JavaScript.
* **Librerías Externas:**
    * **Chart.js:** Para la generación de gráficos estadísticos.
    * **Leaflet.js:** Para la gestión de mapas interactivos.
    * **FontAwesome:** Para la iconografía del sistema.

## 📂 Estructura del Proyecto

* `/assets`: Contiene archivos CSS, imágenes y scripts JS del sistema.
* `/config`: Archivos de configuración de la base de datos.
* `/controllers`: Lógica de negocio para autenticación y gestión de beneficiarios.
* `/views`: Interfaces de usuario divididas por módulos de Auth y Dashboard.

---
Desarrollado con ❤️ para la comunidad de Aucallama.