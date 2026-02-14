document.addEventListener('DOMContentLoaded', () => {
    // Menú Móvil
    const menuBtn = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Simulación de búsqueda
    const searchForm = document.getElementById('hero-search');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = searchForm.querySelector('input');
            const district = input ? input.value : '';
            alert(`Buscando paseadores en: ${district || 'tu zona'}`);
        });
    }
});