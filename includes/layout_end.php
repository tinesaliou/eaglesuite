</main>

<footer class="text-center py-3 small text-muted">
    &copy; <?= date('Y') ?> EagleSuite — Tous droits réservés
</footer>

<!-- JS : ordre CORRECT -->
<!-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> -->
 <script src="/eaglesuite/public/vendor/jquery/jquery.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
 <script src="/eaglesuite/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/pdfmake.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script> -->

<!-- DataTables -->
<link rel="stylesheet" href="/eaglesuite/public/vendor/datatables/css/dataTables.bootstrap5.min.css">
<script src="/eaglesuite/public/vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="/eaglesuite/public/vendor/datatables/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons -->
<script src="/eaglesuite/public/vendor/datatables/js/dataTables.buttons.min.js"></script>
<script src="/eaglesuite/public/vendor/datatables/js/buttons.bootstrap5.min.js"></script>

<!-- JSZip / PDFMake -->
<script src="/eaglesuite/public/vendor/datatables/libs/jszip.min.js"></script>
<script src="/eaglesuite/public/vendor/datatables/libs/pdfmake.min.js"></script>
<script src="/eaglesuite/public/vendor/datatables/libs/vfs_fonts.js"></script>
<script src="/eaglesuite/public/vendor/datatables/js/buttons.html5.min.js"></script>

<script src="/eaglesuite/public/js/datatables.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("appSidebar");

    if(toggle){
        toggle.addEventListener("click", e => {
            e.preventDefault();
            sidebar.classList.toggle("sidebar-open");
        });
    }

    document.querySelectorAll('.sidebar-toggle').forEach(btn => {
        btn.addEventListener("click", e => {
            e.preventDefault();
            const target = document.querySelector(btn.dataset.target);
            if(!target) return;

            document.querySelectorAll('.sidebar-dropdown').forEach(dd => {
                if(dd !== target) dd.classList.remove("open");
            });

            target.classList.toggle("open");
        });
    });
});
</script>

</body>
</html>
