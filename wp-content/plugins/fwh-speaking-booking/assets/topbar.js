/* FWH Speaking Booking - top bar and modal. The booking widget itself loads on first open. */
(function () {
	'use strict';

	var cfg = window.fwhSbTopbar;
	var bar = document.querySelector('.fwh-sb-topbar');
	var modal = document.getElementById('fwh-sb-modal');
	if (!cfg || !bar || !modal) { return; }

	var openBtn = bar.querySelector('.fwh-sb-topbar__btn');
	var panel = modal.querySelector('.fwh-sb-modal__panel');
	var head = modal.querySelector('.fwh-sb-modal__head');
	var root = document.documentElement;
	var lastFocus = null;
	var loaded = false;
	var closeTimer = null;
	var CLOSE_MS = 240;

	function reducedMotion() {
		return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
	}

	function load() {
		if (loaded) { return; }
		loaded = true;
		window.fwhSbConfig = { restUrl: cfg.restUrl, remote: true };
		var link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = cfg.css;
		document.head.appendChild(link);
		var script = document.createElement('script');
		script.src = cfg.js;
		document.body.appendChild(script);
	}

	function focusables() {
		return Array.prototype.slice.call(panel.querySelectorAll('button:not(:disabled), a[href], input:not([type="hidden"]):not(:disabled), select:not(:disabled), [tabindex]:not([tabindex="-1"])'))
			.filter(function (n) { return n.offsetParent !== null || n === document.activeElement; });
	}

	function lockScroll() {
		// Compensate for the vanishing scrollbar so the page behind does not jump sideways.
		var gap = window.innerWidth - root.clientWidth;
		root.style.setProperty('--fwh-sb-sbw', (gap > 0 ? gap : 0) + 'px');
		root.classList.add('fwh-sb-lock');
	}

	function unlockScroll() {
		root.classList.remove('fwh-sb-lock');
		root.style.removeProperty('--fwh-sb-sbw');
	}

	function open() {
		if (!modal.hidden && !modal.classList.contains('is-closing')) { panel.focus(); return; } // Already open: keep the original focus target.
		if (modal.hidden) { lastFocus = document.activeElement; }
		window.clearTimeout(closeTimer);
		modal.classList.remove('is-closing');
		modal.hidden = false;
		lockScroll();
		openBtn.setAttribute('aria-expanded', 'true');
		load();
		void modal.offsetWidth; // Force a reflow so the entrance transition runs.
		modal.classList.add('is-open');
		panel.focus();
	}

	function finishClose() {
		modal.hidden = true;
		modal.classList.remove('is-closing');
		unlockScroll();
		if (lastFocus && lastFocus.focus) { lastFocus.focus(); } else { openBtn.focus(); }
	}

	function close() {
		if (modal.hidden || modal.classList.contains('is-closing')) { return; }
		openBtn.setAttribute('aria-expanded', 'false');
		modal.classList.remove('is-open');
		if (reducedMotion()) { finishClose(); return; }
		modal.classList.add('is-closing');
		closeTimer = window.setTimeout(finishClose, CLOSE_MS);
	}

	openBtn.addEventListener('click', open);
	// Any link or button pointing at #fwh-book (or marked data-fwh-book) opens the booking window, e.g. "Enroll now" on the Courses page.
	document.addEventListener('click', function (ev) {
		var t = ev.target && ev.target.closest ? ev.target.closest('a[href="#fwh-book"], [data-fwh-book]') : null;
		if (!t || modal.contains(t)) { return; }
		ev.preventDefault();
		open();
	});
	modal.addEventListener('click', function (ev) {
		if (ev.target.closest && ev.target.closest('[data-fwh-close]')) { close(); }
	});
	panel.addEventListener('scroll', function () {
		head.classList.toggle('is-scrolled', panel.scrollTop > 4);
	}, { passive: true });
	document.addEventListener('keydown', function (ev) {
		if (modal.hidden) { return; }
		if (ev.key === 'Escape') { ev.preventDefault(); close(); return; }
		if (ev.key === 'Tab') {
			// Keep keyboard focus inside the dialog.
			var items = focusables();
			if (!items.length) { ev.preventDefault(); panel.focus(); return; }
			var first = items[0], last = items[items.length - 1];
			if (ev.shiftKey && (document.activeElement === first || document.activeElement === panel)) { ev.preventDefault(); last.focus(); }
			else if (!ev.shiftKey && document.activeElement === last) { ev.preventDefault(); first.focus(); }
		}
	});

	// Coming back from Stripe: reopen straight onto the confirmation / cancel message.
	if (/[?&]fwh_sb_return=/.test(window.location.search) || window.location.hash === '#fwh-book') { open(); }
})();
