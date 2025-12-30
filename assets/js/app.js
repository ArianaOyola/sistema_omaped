function cerrarSesion() {
    window.location.href = '../../controllers/Logout.php';
}
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (menu) menu.classList.toggle('show');
}
window.onclick = function(event) {
    if (!event.target.closest('.user-profile-widget')) {
        const menu = document.getElementById('userMenu');
        if (menu && menu.classList.contains('show')) menu.classList.remove('show');
    }
}