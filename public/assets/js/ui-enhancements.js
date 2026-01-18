(function() {
    'use strict';
    // Ripple effect for buttons
    document.addEventListener('click', function(ev) {
        const btn = ev.target.closest('.btn-animated, .btn');
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        const ink = document.createElement('span');
        ink.className = 'ripple-ink';
        const size = Math.max(rect.width, rect.height) * 1.2;
        ink.style.width = ink.style.height = size + 'px';
        ink.style.left = (ev.clientX - rect.left - size / 2) + 'px';
        ink.style.top = (ev.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ink);
        setTimeout(() => ink.remove(), 650);
    }, { passive: true });

    // Input label float toggles
    document.querySelectorAll('.input-enhanced').forEach(wrapper => {
        const input = wrapper.querySelector('input,textarea,select');
        if (!input) return;
        const check = () => {
            if (input.value && String(input.value).trim() !== '') wrapper.classList.add('filled');
            else wrapper.classList.remove('filled');
        };
        input.addEventListener('input', check);
        input.addEventListener('change', check);
        // initial state
        check();
    });

    // Modal open/close helpers
    document.querySelectorAll('[data-modal-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const sel = btn.getAttribute('data-modal-target');
            const modal = document.querySelector(sel);
            if (modal) modal.classList.add('is-open');
        });
    });
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.tp-modal');
            modal && modal.classList.remove('is-open');
        });
    });
    document.addEventListener('click', (e) => {
        if (e.target.classList && e.target.classList.contains('tp-modal')) e.target.classList.remove('is-open');
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') document.querySelectorAll('.tp-modal.is-open').forEach(m => m.classList.remove('is-open')); });

    // Remove skeletons after load -> simple fade-out by removing class
    window.addEventListener('load', () => {
        setTimeout(() => document.querySelectorAll('.skeleton').forEach(s => s.classList.remove('skeleton')), 350);
    });

    // Make sure cards are keyboard reachable
    document.querySelectorAll('.card').forEach(c => { if (!c.hasAttribute('tabindex')) c.setAttribute('tabindex', '0'); });
})();