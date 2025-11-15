</main> <!-- Penutup .content-wrapper container -->

<footer class="main-footer">
    <div class="container">
        <!-- Teks footer dihapus Sesuai permintaan -->
    </div>
</footer>

<!-- JavaScript untuk Hamburger Menu -->
<script>
    // Pastikan DOM sudah dimuat
    document.addEventListener('DOMContentLoaded', function() {
        
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const navMenu = document.getElementById('nav-menu');
        
        // Cek jika elemennya ada
        if (hamburgerBtn && navMenu) {
            
            hamburgerBtn.addEventListener('click', function() {
                // Toggle class 'is-active' pada menu
                const isActive = navMenu.classList.toggle('is-active');
                
                // Update ARIA attribute untuk aksesibilitas
                hamburgerBtn.setAttribute('aria-expanded', isActive);
                
                // (Opsional) Ubah ikon hamburger menjadi 'X'
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