</main>

<footer class="main-footer">
    <div class="container">
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const navMenu = document.getElementById('nav-menu');
        
        if (hamburgerBtn && navMenu) {
            
            hamburgerBtn.addEventListener('click', function() {
                const isActive = navMenu.classList.toggle('is-active');
                
                hamburgerBtn.setAttribute('aria-expanded', isActive);
                
                const icon = hamburgerBtn.querySelector('i');
                if (isActive) {
                    icon.classList.remove('bi-list');
                    icon.classList.add('bi-x');
                    hamburgerBtn.setAttribute('aria-label', 'Tutup Menu');
                } else {
                    icon.classList.remove('bi-x');
                    icon.classList.add('bi-list');
                    hamburgerBtn.setAttribute('aria-label', 'Buka Menu');
                }
            });
        }

    });
</script>

</body>
</html>