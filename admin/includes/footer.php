<?php
// /eaglesuite/admin/includes/footer.php
?>
    </main> <!-- .app-main -->
    <!-- footer scripts -->
    <script src="/eaglesuite/public/js/jquery-3.7.0.min.js"></script>
    <script src="/eaglesuite/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/eaglesuite/public/js/datatables.min.js"></script>

    <script>
    (function(){
        // Sidebar toggle (mobile)
        const btn = document.getElementById('btnToggleSidebar');
        const sidebar = document.getElementById('appSidebar');
        if (btn && sidebar) {
            btn.addEventListener('click', function(e){
                e.preventDefault();
                sidebar.classList.toggle('open');
            });
        }

        // Fermer le sidebar si on clique en dehors (mobile)
        document.addEventListener('click', function(e){
            if (window.innerWidth < 992 && sidebar && !sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Auto highlight: elements with href matching window.location
        document.querySelectorAll('.app-sidebar .nav-link').forEach(function(a){
            try {
                const url = new URL(a.href);
                if (url.pathname === window.location.pathname && url.search === window.location.search) {
                    a.classList.add('active');
                }
            } catch(e){}
        });

        // Simple DataTables init for any table with .datatable class
        if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
            $('.datatable').each(function(){
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        pageLength: 10,
                        responsive: true,
                        lengthChange: false
                    });
                }
            });
        }
    })();
    </script>

</body>
</html>
