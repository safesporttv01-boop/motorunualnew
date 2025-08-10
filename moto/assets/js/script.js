// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    
    if (window.innerWidth > 768) {
        mainContent.classList.toggle('shifted');
    }
    
    // Mobilde body scroll'u engelle
    if (window.innerWidth <= 768) {
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

// Model Toggle
function toggleModels(markaId) {
    const modelsList = document.getElementById('models-' + markaId);
    modelsList.classList.toggle('active');
}

// Overlay'e tıklandığında sidebar'ı kapat
document.querySelector('.sidebar-overlay').addEventListener('click', toggleSidebar);

// Sayfa yüklendiğinde çalışacak kodlar
document.addEventListener('DOMContentLoaded', function() {
    // Navbar yüksekliğini ayarla
    const navbarHeight = document.querySelector('.navbar').offsetHeight;
    document.documentElement.style.setProperty('--navbar-height', navbarHeight + 'px');
    
    // Sidebar pozisyonunu ayarla
    document.querySelector('.sidebar').style.top = navbarHeight + 'px';
});