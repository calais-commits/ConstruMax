document.addEventListener('DOMContentLoaded', function() {
    // Manejo del login para usuarios no autenticados
    const protectedLinks = document.querySelectorAll('nav a:not(:first-child):not([style*="float:right"])');
    
    protectedLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!document.getElementById('loginForm')) {
                return;
            }
            
            e.preventDefault();
            document.getElementById('loginForm').style.display = 'block';
            window.scrollTo({
                top: document.getElementById('loginForm').offsetTop - 20,
                behavior: 'smooth'
            });
        });
    });


    // Actualización en tiempo real del dashboard (simulado)
    function updateDashboard() {
        // En una implementación real, aquí haríamos una petición AJAX
        console.log('Actualizando datos del dashboard...');
    }
    
    // Actualizar cada 30 segundos
    setInterval(updateDashboard, 30000);


});