/**
 * E-SPMI Admin — Sidebar, Dropdown & UI JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    const sidebar     = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const toggle      = document.getElementById('sidebarToggle');
    const overlay     = document.getElementById('sidebarOverlay');
    const body        = document.body;

    if (!sidebar) return;

    /* ====================================================
       SIDEBAR TOGGLE — Desktop & Mobile
    ==================================================== */
    function isDesktop() { return window.innerWidth > 992; }

    function openSidebar() {
        if (isDesktop()) {
            sidebar.classList.remove('collapsed');
            if (mainContent) mainContent.classList.remove('expanded');
        } else {
            sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
            body.classList.add('sidebar-open');
        }
        saveSidebarState('open');
    }

    function closeSidebar() {
        if (isDesktop()) {
            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
        } else {
            sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        }
        saveSidebarState('closed');
    }

    function isSidebarOpen() {
        if (isDesktop()) return !sidebar.classList.contains('collapsed');
        return sidebar.classList.contains('active');
    }

    function saveSidebarState(state) {
        try { localStorage.setItem('espmi_sidebar', state); } catch (e) {}
    }

    function getSidebarState() {
        try { return localStorage.getItem('espmi_sidebar'); } catch (e) { return null; }
    }

    // Restore state on desktop load
    if (isDesktop()) {
        const saved = getSidebarState();
        if (saved === 'closed') {
            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
        }
    }

    // Toggle button
    if (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            isSidebarOpen() ? closeSidebar() : openSidebar();
        });
    }

    // Overlay click (mobile)
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Menu item click — close on mobile
    document.querySelectorAll('.sidebar .menu-item > a:not([onclick])').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!isDesktop()) closeSidebar();
        });
    });

    // Handle resize
    window.addEventListener('resize', function () {
        if (isDesktop()) {
            // Remove mobile classes
            sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        } else {
            // Remove desktop classes
            sidebar.classList.remove('collapsed');
            if (mainContent) mainContent.classList.remove('expanded');
        }
    });

    /* ====================================================
       SIDEBAR DROPDOWN ACCORDION
    ==================================================== */
    window.toggleDropdown = function (dropdownId, menuItemId) {
        const dd = document.getElementById(dropdownId);
        const mi = document.getElementById(menuItemId);
        if (!dd || !mi) return;

        const isOpen = dd.classList.contains('open');

        // Close other open dropdowns
        document.querySelectorAll('.dropdown-nav.open').forEach(function (el) {
            if (el.id !== dropdownId) {
                el.classList.remove('open');
                el.style.maxHeight = '0';
                const parent = el.closest('.menu-item');
                if (parent) parent.classList.remove('open');
            }
        });

        if (isOpen) {
            dd.classList.remove('open');
            dd.style.maxHeight = '0';
            mi.classList.remove('open');
        } else {
            dd.classList.add('open');
            dd.style.maxHeight = dd.scrollHeight + 'px';
            mi.classList.add('open');
            setTimeout(function () {
                mi.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 150);
        }
    };

    // Init already-open dropdowns (on page load, e.g. active page)
    document.querySelectorAll('.dropdown-nav.open').forEach(function (el) {
        el.style.maxHeight = el.scrollHeight + 'px';
    });

    /* ====================================================
       KEYBOARD SEARCH SHORTCUT  Ctrl/Cmd + K
    ==================================================== */
    const searchInput = document.getElementById('navSearchInput');
    if (searchInput) {
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            if (e.key === 'Escape' && searchInput === document.activeElement) {
                searchInput.blur();
            }
        });
    }

    /* ====================================================
       COUNTER ANIMATION (stat cards)
    ==================================================== */
    function animateCounter(el) {
        const target = parseInt(el.textContent, 10);
        if (isNaN(target) || target === 0) return;
        let current = 0;
        const step  = Math.max(1, Math.floor(target / 30));
        const timer = setInterval(function () {
            current += step;
            if (current >= target) { el.textContent = target; clearInterval(timer); }
            else el.textContent = current;
        }, 30);
    }

    const counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-card .stat-value').forEach(function (el) {
        counterObserver.observe(el);
    });

    /* ====================================================
       TOOLTIP INIT
    ==================================================== */
    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    }

    /* ====================================================
       RIPPLE EFFECT (sidebar links)
    ==================================================== */
    document.querySelectorAll('.menu-item > a').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.style.cssText = [
                'position:absolute;border-radius:50%;pointer-events:none;z-index:9;',
                'transform:scale(0);animation:ripple 0.45s ease;',
                'background:rgba(255,255,255,0.08);width:16px;height:16px;',
                'left:' + (e.offsetX - 8) + 'px;top:' + (e.offsetY - 8) + 'px;'
            ].join('');
            if (getComputedStyle(link).position === 'static') link.style.position = 'relative';
            link.appendChild(ripple);
            ripple.addEventListener('animationend', function () { ripple.remove(); });
        });
    });

});