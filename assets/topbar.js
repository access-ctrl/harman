/* FWH Speaking Booking - top bar and modal. The booking widget itself loads on first open. */
(function () {
	'use strict';

	var cfg = window.fwhSbTopbar;
	var bar = document.querySelector('.fwh-sb-topbar');
	var modal = document.getElementById('fwh-sb-modal');
	if (!cfg || !bar || !modal) { return; }

	var openBtn = bar.querySelector('.fwh-sb-topbar__btn');
	var panel = modal.querySelector('.fwh-sb-modal__panel');
	var lastFocus = null;
	var loaded = false;

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

	function open() {
		if (!modal.hidden) { panel.focus(); return; } // Already open: keep the original focus target.
		lastFocus = document.activeElement;
		modal.hidden = false;
		document.documentElement.classList.add('fwh-sb-lock');
		openBtn.setAttribute('aria-expanded', 'true');
		load();
		panel.focus();
	}

	function close() {
		modal.hidden = true;
		document.documentElement.classList.remove('fwh-sb-lock');
		openBtn.setAttribute('aria-expanded', 'false');
		if (lastFocus && lastFocus.focus) { lastFocus.focus(); } else { openBtn.focus(); }
	}

	openBtn.addEventListener('click', open);
	modal.addEventListener('click', function (ev) {
		if (ev.target.closest && ev.target.closest('[data-fwh-close]')) { close(); }
	});
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
	if (/[?&]fwh_sb_return=/.test(window.location.search)) { open(); }
})();
