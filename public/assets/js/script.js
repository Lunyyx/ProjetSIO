// Script principal pour l'application Fit&Fun

document.addEventListener('DOMContentLoaded', function() {
    // Fermeture automatique des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.remove();
        }, 5000);
    });
    
    // Confirmation de suppression
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Menu mobile (responsive)
    const navToggle = document.querySelector('.nav-toggle');
    if (navToggle) {
        navToggle.addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    }
});
