        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script>
(function () {
    /* ── Sidebar Toggle ──────────────────────────────────── */
    var sidebar   = document.getElementById('adminSidebar');
    var toggle    = document.getElementById('adminMenuToggle');
    var backdrop  = document.getElementById('sidebarBackdrop');
    var closeBtn  = document.getElementById('sidebarCloseBtn');

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden'; // prevent background scroll on mobile
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggle && sidebar) {
        // On desktop, keep sidebar open by default via CSS (transform: none)
        // On mobile, toggle open/close
        toggle.addEventListener('click', function () {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    // Close sidebar when a nav link is clicked (mobile UX)
    if (sidebar) {
        sidebar.querySelectorAll('.admin-sidebar-nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 900) closeSidebar();
            });
        });
    }

    /* ── Responsive Table: auto-inject data-label from <th> ─ */
    document.querySelectorAll('.data-table').forEach(function (table) {
        var headers = [];
        table.querySelectorAll('thead th').forEach(function (th) {
            headers.push(th.textContent.trim());
        });
        if (!headers.length) return;
        table.querySelectorAll('tbody tr').forEach(function (row) {
            row.querySelectorAll('td').forEach(function (td, i) {
                if (headers[i]) td.setAttribute('data-label', headers[i]);
            });
        });
    });

    /* ── Modal: close on backdrop click ─────────────────── */
    document.querySelectorAll('.admin-modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    });

    /* ── Form row stacking: fix flex price/stock pairs on mobile ─ */
    if (window.innerWidth <= 520) {
        document.querySelectorAll('.admin-modal-content > form .form-row-pair').forEach(function(row) {
            row.style.flexDirection = 'column';
        });
    }
})();
</script>

<?php if (isset($admin_extra_js)) echo $admin_extra_js; ?>

</body>
</html>
