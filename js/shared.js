/* Shared front-end logic for MSU-MCEST CEMS.
   - Sidebar toggle (mobile)
   - Modal open/close
   - Tab switching
   - Active sidebar link based on filename
   - Lightweight client-side table search
   - Page fade-in
   This file does NOT manage data. All real data should come from PHP.
*/

(function () {
  'use strict';

  // ---------- Page fade-in ----------
  // Mark JS available so the fade-in CSS path activates.
  document.documentElement.classList.add('js-ready');

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.page-fade').forEach(function (el) {
      requestAnimationFrame(function () { el.classList.add('is-ready'); });
    });
    highlightActiveNav();
    bindSidebarToggle();
    bindModals();
    bindTabs();
    bindTableSearch();
    bindConfirmActions();
  });

  // ---------- Active sidebar nav ----------
  function highlightActiveNav() {
    var current = (location.pathname.split('/').pop() || '').replace(/\.php$/, '.html');
    document.querySelectorAll('.nav-link').forEach(function (a) {
      var href = (a.getAttribute('href') || '').replace(/\.php$/, '.html');
      if (href && href === current) a.classList.add('active');
    });
  }

  // ---------- Sidebar (mobile) ----------
  function bindSidebarToggle() {
    var btn = document.querySelector('[data-sidebar-toggle]');
    var aside = document.querySelector('aside.sidebar');
    if (!btn || !aside) return;
    btn.addEventListener('click', function () { aside.classList.toggle('is-open'); });
  }

  // ---------- Modals ----------
  function bindModals() {
    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-modal-open');
        var m = document.getElementById(id);
        if (m) m.classList.add('is-open');
      });
    });
    document.querySelectorAll('.modal').forEach(function (m) {
      m.addEventListener('click', function (e) {
        if (e.target === m || e.target.hasAttribute('data-modal-close')) {
          m.classList.remove('is-open');
        }
      });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal.is-open').forEach(function (m) { m.classList.remove('is-open'); });
      }
    });
  }

  // ---------- Tabs ----------
  function bindTabs() {
    document.querySelectorAll('[data-tabs]').forEach(function (group) {
      var tabs = group.querySelectorAll('[data-tab]');
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          tabs.forEach(function (t) { t.classList.remove('is-active'); });
          tab.classList.add('is-active');
          var filter = tab.getAttribute('data-tab');
          var targetSel = group.getAttribute('data-tabs-target');
          var rows = document.querySelectorAll(targetSel + ' [data-status]');
          rows.forEach(function (row) {
            var s = row.getAttribute('data-status');
            row.style.display = (filter === 'all' || filter === s) ? '' : 'none';
          });
        });
      });
    });
  }

  // ---------- Client-side search (preview only) ----------
  function bindTableSearch() {
    document.querySelectorAll('[data-search-target]').forEach(function (input) {
      input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var sel = input.getAttribute('data-search-target');
        document.querySelectorAll(sel + ' tbody tr, ' + sel + ' [data-searchable]').forEach(function (row) {
          var text = (row.textContent || '').toLowerCase();
          row.style.display = !q || text.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    });
  }

  // ---------- Confirm destructive actions ----------
  function bindConfirmActions() {
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        var msg = el.getAttribute('data-confirm') || 'Are you sure?';
        if (!confirm(msg)) e.preventDefault();
      });
    });
  }
})();
