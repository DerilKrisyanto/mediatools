/* ============================================================
   MEDIATOOLS — app.js
   Global interactive behaviours
   ============================================================ */

(function () {
    'use strict';

    /* ── Navbar scroll tint ── */
    var nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 20);
        }, { passive: true });
    }

    /* ================================================================
       DESKTOP TOOLS DROPDOWN
    ================================================================ */
    var toolsWrap    = document.getElementById('toolsWrap');
    var toolsBtn     = document.getElementById('toolsBtn');
    var toolsDropdown= document.getElementById('toolsDropdown');
    var toolsOpen    = false;

    function openToolsMenu() {
        toolsOpen = true;
        toolsDropdown && toolsDropdown.classList.add('open');
        toolsBtn && toolsBtn.setAttribute('aria-expanded', 'true');
    }
    function closeToolsMenu() {
        toolsOpen = false;
        toolsDropdown && toolsDropdown.classList.remove('open');
        toolsBtn && toolsBtn.setAttribute('aria-expanded', 'false');
    }
    window.toggleToolsMenu = function () { toolsOpen ? closeToolsMenu() : openToolsMenu(); };

    document.addEventListener('click', function (e) {
        if (toolsOpen && toolsWrap && !toolsWrap.contains(e.target)) closeToolsMenu();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeToolsMenu(); closeMobileMenu(); closeSearch(); }
    });

    /* ================================================================
       MOBILE MENU
    ================================================================ */
    var mobileMenu   = document.getElementById('mobileMenu');
    var mobileToggle = document.getElementById('mobileToggle');
    var menuIconOpen = document.getElementById('menuIconOpen');
    var menuIconClose= document.getElementById('menuIconClose');
    var mobileOpen   = false;

    function openMobileMenu() {
        mobileOpen = true;
        mobileMenu   && mobileMenu.classList.remove('hidden');
        menuIconOpen  && (menuIconOpen.style.display  = 'none');
        menuIconClose && (menuIconClose.style.display = 'block');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        mobileOpen = false;
        mobileMenu   && mobileMenu.classList.add('hidden');
        menuIconOpen  && (menuIconOpen.style.display  = 'block');
        menuIconClose && (menuIconClose.style.display = 'none');
        document.body.style.overflow = '';
    }
    window.toggleMobileMenu = function () { mobileOpen ? closeMobileMenu() : openMobileMenu(); };

    /* Close mobile menu on link click */
    if (mobileMenu) {
        mobileMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMobileMenu);
        });
    }

    /* Resize guard */
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768 && mobileOpen) closeMobileMenu();
    });

    /* ================================================================
       SEARCH OVERLAY — full featured
    ================================================================ */
    var searchOverlay  = document.getElementById('searchOverlay');
    var searchInput    = document.getElementById('searchInput');
    var searchBrowse   = document.getElementById('searchBrowse');
    var searchResults  = document.getElementById('searchResults');
    var searchGrid     = document.getElementById('searchResultsGrid');
    var searchEmpty    = document.getElementById('searchEmpty');
    var searchIsOpen   = false;
    var focusIdx       = -1;
    var visibleItems   = [];

    window.openSearch = function () {
        searchIsOpen = true;
        searchOverlay && searchOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { searchInput && searchInput.focus(); }, 60);
        resetSearch();
    };
    window.closeSearch = function () {
        searchIsOpen = false;
        searchOverlay && searchOverlay.classList.remove('open');
        document.body.style.overflow = '';
        if (searchInput) searchInput.value = '';
        resetSearch();
    };

    function resetSearch() {
        searchBrowse  && searchBrowse.classList.remove('hidden');
        searchResults && searchResults.classList.add('hidden');
        searchEmpty   && searchEmpty.classList.add('hidden');
        focusIdx = -1;
        visibleItems = [];
        document.querySelectorAll('.search-tool-row.focused').forEach(function (el) {
            el.classList.remove('focused');
        });
    }

    /* Live search */
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            if (!q) { resetSearch(); return; }

            searchBrowse  && searchBrowse.classList.add('hidden');
            searchResults && searchResults.classList.remove('hidden');
            if (searchGrid) searchGrid.innerHTML = '';
            visibleItems = [];
            focusIdx = -1;

            var allItems = document.querySelectorAll('#searchBrowse .search-tool-row');
            allItems.forEach(function (item) {
                var name = (item.dataset.name || '').toLowerCase();
                var tags = (item.dataset.tags || '').toLowerCase();
                if (name.includes(q) || tags.includes(q)) {
                    var clone = item.cloneNode(true);
                    clone.addEventListener('click', closeSearch);
                    /* Highlight */
                    var nameEl = clone.querySelector('.search-tool-name');
                    if (nameEl) {
                        var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                        nameEl.innerHTML = nameEl.textContent.replace(re, '<mark class="search-highlight">$1</mark>');
                    }
                    searchGrid && searchGrid.appendChild(clone);
                    visibleItems.push(clone);
                }
            });

            searchEmpty && searchEmpty.classList.toggle('hidden', visibleItems.length > 0);
        });
    }

    /* Keyboard navigation */
    document.addEventListener('keydown', function (e) {
        /* ⌘K / Ctrl+K */
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            searchIsOpen ? closeSearch() : openSearch();
            return;
        }
        if (!searchIsOpen) return;
        if (e.key === 'Escape') { closeSearch(); return; }

        var items = visibleItems.length
            ? visibleItems
            : Array.from(document.querySelectorAll('#searchBrowse .search-tool-row'));

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            focusIdx = Math.min(focusIdx + 1, items.length - 1);
            updateFocus(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            focusIdx = Math.max(focusIdx - 1, 0);
            updateFocus(items);
        } else if (e.key === 'Enter' && focusIdx >= 0) {
            e.preventDefault();
            items[focusIdx] && items[focusIdx].click();
        }
    });

    function updateFocus(items) {
        items.forEach(function (el, i) {
            el.classList.toggle('focused', i === focusIdx);
        });
        if (items[focusIdx]) items[focusIdx].scrollIntoView({ block: 'nearest' });
        searchInput && searchInput.focus();
    }

})();