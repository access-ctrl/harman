/* FWH Speaking Booking - public booking widget. Vanilla JS, no dependencies. */
(function () {
	'use strict';

	var cfg = window.fwhSbConfig;
	var root = document.getElementById('fwh-sb-app');
	if (!cfg || !root) { return; }
	var body = root.querySelector('.fwh-sb-body');

	var ALIASES = {
		'Asia/Calcutta': 'Asia/Kolkata', 'Asia/Saigon': 'Asia/Ho_Chi_Minh', 'Asia/Katmandu': 'Asia/Kathmandu',
		'Asia/Rangoon': 'Asia/Yangon', 'Asia/Dacca': 'Asia/Dhaka', 'America/Buenos_Aires': 'America/Argentina/Buenos_Aires',
		'Europe/Kiev': 'Europe/Kyiv', 'Pacific/Samoa': 'Pacific/Pago_Pago'
	};

	var state = {
		slots: [],          // UTC ISO strings
		tz: detectTz(),
		monthKey: null,     // 'YYYY-MM'
		day: null,          // 'YYYY-MM-DD' in state.tz
		slot: null          // ISO string
	};
	var els = {};
	var fmtCache = {};

	function detectTz() {
		var tz = '';
		try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) { tz = ''; }
		tz = ALIASES[tz] || tz;
		return cfg.timezones.indexOf(tz) !== -1 ? tz : cfg.homeTz;
	}

	function fmt(key, tz, opts) {
		var k = key + '|' + tz;
		if (!fmtCache[k]) { fmtCache[k] = new Intl.DateTimeFormat(undefined, Object.assign({ timeZone: tz }, opts)); }
		return fmtCache[k];
	}
	function dayKey(iso, tz) {
		if (!fmtCache['key|' + tz]) {
			fmtCache['key|' + tz] = new Intl.DateTimeFormat('en-CA', { timeZone: tz, year: 'numeric', month: '2-digit', day: '2-digit' });
		}
		return fmtCache['key|' + tz].format(new Date(iso));
	}
	function timeLabel(iso, tz) { return fmt('t', tz, { hour: 'numeric', minute: '2-digit' }).format(new Date(iso)); }
	function longLabel(iso, tz) {
		return fmt('l', tz, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: '2-digit' }).format(new Date(iso));
	}

	function money() {
		if (!(cfg.price > 0)) { return ''; }
		try {
			return new Intl.NumberFormat(undefined, { style: 'currency', currency: cfg.currency }).format(cfg.price);
		} catch (e) {
			return cfg.price.toFixed(2) + ' ' + cfg.currency;
		}
	}

	function el(tag, attrs, text) {
		var n = document.createElement(tag);
		if (attrs) { Object.keys(attrs).forEach(function (k) { n.setAttribute(k, attrs[k]); }); }
		if (text != null) { n.textContent = text; }
		return n;
	}
	function clear(n) { while (n.firstChild) { n.removeChild(n.firstChild); } }

	function addDays(ymd, n) {
		var p = ymd.split('-');
		var d = new Date(Date.UTC(+p[0], +p[1] - 1, +p[2] + n));
		return d.toISOString().slice(0, 10);
	}

	function api(path) {
		return fetch(cfg.restUrl + path, { credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce } })
			.then(function (r) {
				if (!r.ok) { throw new Error('http ' + r.status); }
				return r.json();
			});
	}

	function loadSlots() {
		var calls = [];
		for (var from = cfg.from; from <= cfg.to; from = addDays(from, 31)) {
			var to = addDays(from, 30);
			if (to > cfg.to) { to = cfg.to; }
			calls.push(api('slots?from=' + from + '&to=' + to));
		}
		return Promise.all(calls).then(function (results) {
			var all = [];
			results.forEach(function (res) {
				Object.keys(res.days || {}).forEach(function (d) { all = all.concat(res.days[d]); });
			});
			all.sort();
			return all;
		});
	}

	function build() {
		clear(body);
		var tzRow = el('div', { 'class': 'fwh-sb-tzrow' });
		var tzLabel = el('label', { 'for': 'fwh-sb-tz' }, 'Your time zone');
		els.tz = el('select', { id: 'fwh-sb-tz' });
		cfg.timezones.forEach(function (z) {
			var o = el('option', { value: z }, z.replace(/_/g, ' '));
			if (z === state.tz) { o.selected = true; }
			els.tz.appendChild(o);
		});
		els.tz.addEventListener('change', function () {
			state.tz = els.tz.value; state.day = null; state.slot = null; state.monthKey = null;
			render();
		});
		tzRow.appendChild(tzLabel); tzRow.appendChild(els.tz);
		body.appendChild(tzRow);

		els.status = el('p', { 'class': 'fwh-sb-status', role: 'status' });
		els.cal = el('div', { 'class': 'fwh-sb-cal' });
		els.slots = el('div', { 'class': 'fwh-sb-slots' });
		els.form = el('div', { 'class': 'fwh-sb-form' });
		[els.status, els.cal, els.slots, els.form].forEach(function (n) { body.appendChild(n); });
	}

	function showLoading(msg) {
		clear(els.cal); clear(els.slots); clear(els.form);
		els.status.textContent = '';
		var box = el('div', { 'class': 'fwh-sb-loading' });
		box.appendChild(el('span', { 'class': 'fwh-sb-spinner', 'aria-hidden': 'true' }));
		box.appendChild(el('span', null, msg));
		els.cal.appendChild(box);
		body.setAttribute('aria-busy', 'true');
	}

	function showLoadError() {
		body.removeAttribute('aria-busy');
		clear(els.cal); clear(els.slots); clear(els.form);
		els.status.textContent = '';
		var box = el('div', { 'class': 'fwh-sb-errorbox', role: 'alert' });
		box.appendChild(el('p', null, 'Sorry, we could not load the available times.'));
		var retry = el('button', { type: 'button', 'class': 'fwh-sb-secondary' }, 'Try again');
		retry.addEventListener('click', start);
		box.appendChild(retry);
		els.cal.appendChild(box);
		retry.focus();
	}

	function slotsByDay() {
		var map = {};
		state.slots.forEach(function (iso) {
			var k = dayKey(iso, state.tz);
			(map[k] = map[k] || []).push(iso);
		});
		return map;
	}

	function render() {
		body.removeAttribute('aria-busy');
		var map = slotsByDay();
		var days = Object.keys(map).sort();
		clear(els.cal); clear(els.slots); clear(els.form);
		if (!days.length) {
			els.status.textContent = 'No times are available right now. Please check back soon.';
			return;
		}
		els.status.textContent = '';
		var months = [];
		days.forEach(function (d) { var m = d.slice(0, 7); if (months.indexOf(m) === -1) { months.push(m); } });
		if (!state.monthKey || months.indexOf(state.monthKey) === -1) { state.monthKey = months[0]; }
		renderCalendar(map, months);
		if (state.day && map[state.day]) { renderSlots(map[state.day]); }
		if (state.slot) { renderForm(); }
	}

	function renderCalendar(map, months) {
		var idx = months.indexOf(state.monthKey);
		var p = state.monthKey.split('-');
		var y = +p[0], m = +p[1];
		var head = el('div', { 'class': 'fwh-sb-calhead' });
		var prev = el('button', { type: 'button', 'class': 'fwh-sb-nav', 'aria-label': 'Previous month' }, '‹');
		var next = el('button', { type: 'button', 'class': 'fwh-sb-nav', 'aria-label': 'Next month' }, '›');
		prev.disabled = idx <= 0; next.disabled = idx >= months.length - 1;
		prev.addEventListener('click', function () { state.monthKey = months[idx - 1]; render(); });
		next.addEventListener('click', function () { state.monthKey = months[idx + 1]; render(); });
		var title = new Intl.DateTimeFormat(undefined, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(y, m - 1, 1)));
		head.appendChild(prev); head.appendChild(el('strong', { 'aria-live': 'polite' }, title)); head.appendChild(next);
		els.cal.appendChild(head);

		var grid = el('div', { 'class': 'fwh-sb-grid', role: 'group', 'aria-label': title });
		['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(function (d) { grid.appendChild(el('span', { 'class': 'fwh-sb-dow', 'aria-hidden': 'true' }, d)); });
		var offset = (new Date(Date.UTC(y, m - 1, 1)).getUTCDay() + 6) % 7;
		for (var i = 0; i < offset; i++) { grid.appendChild(el('span')); }
		var count = new Date(Date.UTC(y, m, 0)).getUTCDate();
		var enabled = [];
		for (var d = 1; d <= count; d++) {
			var key = state.monthKey + '-' + (d < 10 ? '0' + d : d);
			var has = !!map[key];
			var b = el('button', { type: 'button', 'class': 'fwh-sb-day' + (key === state.day ? ' is-selected' : '') + (key === todayKey() ? ' is-today' : ''), 'data-day': key }, String(d));
			b.disabled = !has;
			if (has) {
				b.setAttribute('aria-label', new Intl.DateTimeFormat(undefined, { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(y, m - 1, d))) + ', times available');
				b.setAttribute('aria-pressed', key === state.day ? 'true' : 'false');
				b.setAttribute('tabindex', '-1');
				b.addEventListener('click', onDay);
				b.addEventListener('keydown', onDayKey);
				enabled.push(b);
			}
			grid.appendChild(b);
		}
		// Roving tabindex: one tab stop for the whole grid, arrow keys move inside it.
		var stop = enabled.filter(function (n) { return n.getAttribute('data-day') === state.day; })[0] || enabled[0];
		if (stop) { stop.setAttribute('tabindex', '0'); }
		els.cal.appendChild(grid);
	}

	function todayKey() { return dayKey(new Date().toISOString(), state.tz); }

	function onDayKey(ev) {
		var step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[ev.key];
		if (!step) { return; }
		var days = Array.prototype.slice.call(els.cal.querySelectorAll('.fwh-sb-day:not(:disabled)'));
		var all = Array.prototype.slice.call(els.cal.querySelectorAll('.fwh-sb-day'));
		var from = all.indexOf(ev.currentTarget);
		var target = null;
		if (Math.abs(step) === 1) {
			target = days[days.indexOf(ev.currentTarget) + step];
		} else if (all[from + step] && !all[from + step].disabled) {
			target = all[from + step];
		}
		if (target) {
			ev.preventDefault();
			ev.currentTarget.setAttribute('tabindex', '-1');
			target.setAttribute('tabindex', '0');
			target.focus();
		}
	}

	function onDay(ev) {
		state.day = ev.currentTarget.getAttribute('data-day');
		state.slot = null;
		render();
		var first = els.slots.querySelector('button');
		if (first) { first.focus(); }
	}

	function renderSlots(list) {
		els.slots.appendChild(el('h3', null, 'Available times'));
		var parts = [cfg.duration + ' minutes'];
		if (money()) { parts.push(money()); }
		parts.push('times shown in ' + state.tz.replace(/_/g, ' '));
		els.slots.appendChild(el('p', { 'class': 'fwh-sb-note' }, parts.join(' · ')));
		var wrap = el('div', { 'class': 'fwh-sb-slotlist', role: 'group', 'aria-label': 'Available times' });
		list.forEach(function (iso) {
			var b = el('button', { type: 'button', 'class': 'fwh-sb-slot' + (iso === state.slot ? ' is-selected' : ''), 'aria-pressed': iso === state.slot ? 'true' : 'false' }, timeLabel(iso, state.tz));
			b.addEventListener('click', function () { state.slot = iso; render(); var f = els.form.querySelector('input'); if (f) { f.focus(); } });
			wrap.appendChild(b);
		});
		els.slots.appendChild(wrap);
	}

	function field(id, label, type, extra, hint) {
		var wrap = el('p', { 'class': 'fwh-sb-field' });
		wrap.appendChild(el('label', { 'for': id }, label + ' *'));
		var attrs = Object.assign({ id: id, name: id, type: type, required: 'required', 'aria-required': 'true' }, extra || {});
		if (hint) { attrs['aria-describedby'] = id + '-hint'; }
		wrap.appendChild(el('input', attrs));
		if (hint) { wrap.appendChild(el('span', { 'class': 'fwh-sb-hint', id: id + '-hint' }, hint)); }
		return wrap;
	}

	function renderForm() {
		els.form.appendChild(el('h3', null, 'Your details'));
		els.form.appendChild(el('p', { 'class': 'fwh-sb-summary' }, longLabel(state.slot, state.tz) + ' (' + state.tz.replace(/_/g, ' ') + ') \u00B7 ' + cfg.duration + ' min' + (money() ? ' \u00B7 ' + money() : '')));
		var form = el('form', { novalidate: 'novalidate' });
		form.appendChild(field('fwh-sb-name', 'Full name', 'text', { autocomplete: 'name', maxlength: '190' }));
		form.appendChild(field('fwh-sb-email', 'Email', 'email', { autocomplete: 'email', maxlength: '190' }));
		form.appendChild(field('fwh-sb-phone', 'Phone with country code', 'tel', { autocomplete: 'tel', placeholder: '+1 416 555 0123', maxlength: '25' }, 'Start with + and your country code, e.g. +91 98765 43210'));
		var hp = el('p', { 'class': 'fwh-sb-hp', 'aria-hidden': 'true' });
		hp.appendChild(el('label', { 'for': 'fwh-sb-website' }, 'Leave this field empty'));
		hp.appendChild(el('input', { id: 'fwh-sb-website', name: 'website', type: 'text', tabindex: '-1', autocomplete: 'off' }));
		form.appendChild(hp);
		var err = el('p', { 'class': 'fwh-sb-error', role: 'alert', id: 'fwh-sb-error' });
		var submit = el('button', { type: 'submit', 'class': 'fwh-sb-submit' }, 'Continue to payment');
		form.appendChild(err); form.appendChild(submit);
		form.appendChild(el('p', { 'class': 'fwh-sb-hint' }, 'You will pay securely by card on the next page (Stripe). Your time is held for a few minutes while you pay.'));
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			var v = readForm(form);
			Array.prototype.forEach.call(form.querySelectorAll('input'), function (i) { i.removeAttribute('aria-invalid'); });
			err.textContent = v.error || '';
			if (v.error) {
				var bad = form.elements[v.field];
				if (bad) { bad.setAttribute('aria-invalid', 'true'); bad.focus(); }
				return;
			}
			submit.disabled = true;
			submit.textContent = 'Please wait\u2026';
			book(v.data).then(function (res) {
				window.location.href = res.checkout_url;
			}).catch(function (e) {
				err.textContent = e.message;
				submit.disabled = false;
				submit.textContent = 'Continue to payment';
				if (e.status === 409) { refreshSlots(e.message); }
			});
		});
		els.form.appendChild(form);
	}

	function book(d) {
		var payload = {
			start: state.slot, name: d.name, email: d.email, phone: d.phone,
			student_tz: state.tz, website: d.website,
			page_url: window.location.origin + window.location.pathname
		};
		return fetch(cfg.restUrl + 'book', {
			method: 'POST', credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
			body: JSON.stringify(payload)
		}).then(function (r) {
			return r.json().catch(function () { return {}; }).then(function (j) {
				if (!r.ok) {
					var e = new Error(j && j.message ? j.message : 'Something went wrong. Please try again.');
					e.status = r.status;
					throw e;
				}
				return j;
			});
		});
	}

	function refreshSlots(message) {
		state.slot = null;
		loadSlots().then(function (slots) {
			state.slots = slots;
			render();
			if (message) { els.status.textContent = message; }
		}).catch(function () {});
	}

	function params() {
		var out = {};
		window.location.search.replace(/^\?/, '').split('&').forEach(function (kv) {
			var p = kv.split('=');
			if (p[0]) { out[decodeURIComponent(p[0])] = decodeURIComponent((p[1] || '').replace(/\+/g, ' ')); }
		});
		return out;
	}

	function cleanUrl() {
		try { window.history.replaceState(null, '', window.location.pathname); } catch (e) { /* ignore */ }
	}

	function showReturn(q) {
		clear(body);
		var box = el('div', { 'class': 'fwh-sb-return', role: 'status' });
		body.appendChild(box);
		var started = Date.now();
		box.appendChild(el('span', { 'class': 'fwh-sb-spinner', 'aria-hidden': 'true' }));
		box.appendChild(el('h3', null, 'Confirming your payment'));
		box.appendChild(el('p', null, 'Please keep this page open\u2026'));

		function done(title, msg, restart) {
			clear(box);
			box.appendChild(el('h3', null, title));
			box.appendChild(el('p', null, msg));
			if (restart) {
				var b = el('button', { type: 'button', 'class': 'fwh-sb-submit' }, 'Choose another time');
				b.addEventListener('click', function () { cleanUrl(); window.location.reload(); });
				box.appendChild(b);
			}
		}
		function poll() {
			api('booking-status?ref=' + encodeURIComponent(q.ref) + '&session_id=' + encodeURIComponent(q.session_id)).then(function (res) {
				if (res.status === 'paid') {
					var tz = res.student_tz || state.tz;
					done('Payment received', 'Your session is booked for ' + longLabel(res.start, tz) + ' (' + tz.replace(/_/g, ' ') + '). Booking reference ' + q.ref + '. We are sending your confirmation email with the meeting link.', false);
					return;
				}
				if (res.status === 'expired' || res.status === 'cancelled') {
					done('Payment not completed', 'Your payment was not completed and the time has been released.', true);
					return;
				}
				next();
			}).catch(next);
		}
		function next() {
			if (Date.now() - started > 120000) {
				done('Still confirming', 'We have not received confirmation yet. If you paid, you will get an email shortly' + (cfg.contactEmail ? '; otherwise contact ' + cfg.contactEmail + ' quoting ' + q.ref : '') + '.', false);
				return;
			}
			window.setTimeout(poll, 3000);
		}
		poll();
	}

	function readForm(form) {
		var d = {
			name: form.elements['fwh-sb-name'].value.trim(),
			email: form.elements['fwh-sb-email'].value.trim(),
			phone: form.elements['fwh-sb-phone'].value.trim(),
			website: form.elements['website'].value
		};
		if (!d.name) { return { error: 'Please enter your name.', field: 'fwh-sb-name' }; }
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email)) { return { error: 'Please enter a valid email address.', field: 'fwh-sb-email' }; }
		if (!/^\+\d[\d\s\-()]{6,19}$/.test(d.phone)) { return { error: 'Please enter your phone number starting with the country code, e.g. +1 416 555 0123.', field: 'fwh-sb-phone' }; }
		return { data: d };
	}

	var q = params();
	if (q.fwh_sb_return === 'success' && q.ref && q.session_id) {
		showReturn(q);
		return;
	}
	build();
	var cancelled = q.fwh_sb_return === 'cancel';
	if (cancelled) { cleanUrl(); }

	function start() {
		showLoading('Loading available times\u2026');
		loadSlots().then(function (slots) {
			state.slots = slots;
			render();
			if (cancelled) {
				els.status.textContent = 'Payment cancelled. Your time has been released; please choose a time again.';
			}
		}).catch(showLoadError);
	}
	start();
})();
