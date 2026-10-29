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

	function slotsByDay() {
		var map = {};
		state.slots.forEach(function (iso) {
			var k = dayKey(iso, state.tz);
			(map[k] = map[k] || []).push(iso);
		});
		return map;
	}

	function render() {
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
		for (var d = 1; d <= count; d++) {
			var key = state.monthKey + '-' + (d < 10 ? '0' + d : d);
			var has = !!map[key];
			var b = el('button', { type: 'button', 'class': 'fwh-sb-day' + (key === state.day ? ' is-selected' : ''), 'data-day': key }, String(d));
			b.disabled = !has;
			if (has) {
				b.setAttribute('aria-label', new Intl.DateTimeFormat(undefined, { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(y, m - 1, d))) + ', times available');
				b.setAttribute('aria-pressed', key === state.day ? 'true' : 'false');
				b.addEventListener('click', onDay);
			}
			grid.appendChild(b);
		}
		els.cal.appendChild(grid);
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
		els.slots.appendChild(el('p', { 'class': 'fwh-sb-note' }, cfg.duration + ' minutes · times shown in ' + state.tz.replace(/_/g, ' ')));
		var wrap = el('div', { 'class': 'fwh-sb-slotlist' });
		list.forEach(function (iso) {
			var b = el('button', { type: 'button', 'class': 'fwh-sb-slot' + (iso === state.slot ? ' is-selected' : ''), 'aria-pressed': iso === state.slot ? 'true' : 'false' }, timeLabel(iso, state.tz));
			b.addEventListener('click', function () { state.slot = iso; render(); var f = els.form.querySelector('input'); if (f) { f.focus(); } });
			wrap.appendChild(b);
		});
		els.slots.appendChild(wrap);
	}

	function field(id, label, type, extra) {
		var wrap = el('p', { 'class': 'fwh-sb-field' });
		wrap.appendChild(el('label', { 'for': id }, label));
		var input = el('input', Object.assign({ id: id, name: id, type: type, required: 'required' }, extra || {}));
		wrap.appendChild(input);
		return wrap;
	}

	function renderForm() {
		els.form.appendChild(el('h3', null, 'Your details'));
		els.form.appendChild(el('p', { 'class': 'fwh-sb-summary' }, longLabel(state.slot, state.tz) + ' (' + state.tz.replace(/_/g, ' ') + ')'));
		var form = el('form', { novalidate: 'novalidate' });
		form.appendChild(field('fwh-sb-name', 'Full name', 'text', { autocomplete: 'name', maxlength: '190' }));
		form.appendChild(field('fwh-sb-email', 'Email', 'email', { autocomplete: 'email', maxlength: '190' }));
		form.appendChild(field('fwh-sb-phone', 'Phone with country code', 'tel', { autocomplete: 'tel', placeholder: '+1 416 555 0123', maxlength: '25' }));
		var hp = el('p', { 'class': 'fwh-sb-hp', 'aria-hidden': 'true' });
		hp.appendChild(el('label', { 'for': 'fwh-sb-website' }, 'Leave this field empty'));
		hp.appendChild(el('input', { id: 'fwh-sb-website', name: 'website', type: 'text', tabindex: '-1', autocomplete: 'off' }));
		form.appendChild(hp);
		var err = el('p', { 'class': 'fwh-sb-error', role: 'alert' });
		var submit = el('button', { type: 'submit', 'class': 'fwh-sb-submit' }, 'Continue to payment');
		form.appendChild(err); form.appendChild(submit);
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			var v = readForm(form);
			err.textContent = v.error || '';
			if (v.error) { return; }
			submit.disabled = true;
			submit.textContent = 'Please wait\u2026';
			book(v.data).then(function (res) {
				window.location.href = res.checkout_url;
			}).catch(function (e) {
				err.textContent = e.message;
				submit.disabled = false;
				submit.textContent = 'Continue to payment';
				if (e.status === 409) { refreshSlots(); }
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

	function refreshSlots() {
		state.slot = null;
		loadSlots().then(function (slots) { state.slots = slots; render(); }).catch(function () {});
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
		if (!d.name) { return { error: 'Please enter your name.' }; }
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email)) { return { error: 'Please enter a valid email address.' }; }
		if (!/^\+\d[\d\s\-()]{6,19}$/.test(d.phone)) { return { error: 'Please enter your phone number starting with the country code, e.g. +1 416 555 0123.' }; }
		return { data: d };
	}

	var q = params();
	if (q.fwh_sb_return === 'success' && q.ref && q.session_id) {
		showReturn(q);
		return;
	}
	build();
	if (q.fwh_sb_return === 'cancel') {
		els.status.textContent = 'Payment cancelled. Your time has been released; please choose a time again.';
		cleanUrl();
	} else {
		els.status.textContent = 'Loading available times\u2026';
	}
	loadSlots().then(function (slots) {
		state.slots = slots;
		var msg = els.status.textContent;
		render();
		if (q.fwh_sb_return === 'cancel') { els.status.textContent = msg; }
	}).catch(function () {
		els.status.textContent = 'Sorry, we could not load available times. Please refresh the page and try again.';
	});
})();
