/**
 * Copyright 2022 Design Barn Inc.
 */

!(function (e, t) {
  typeof exports == 'object' && typeof module != 'undefined'
    ? t(exports)
    : typeof define == 'function' && define.amd
    ? define(['exports'], t)
    : t(((e = e || self).LottieInteractivity = {}));
})(this, function (e) {
  function t(e) {
    return (t =
      typeof Symbol == 'function' && typeof Symbol.iterator == 'symbol'
        ? function (e) {
            return typeof e;
          }
        : function (e) {
            return e && typeof Symbol == 'function' && e.constructor === Symbol && e !== Symbol.prototype
              ? 'symbol'
              : typeof e;
          })(e);
  }
  function n(e, t) {
    if (!(e instanceof t)) throw new TypeError('Cannot call a class as a function');
  }
  function i(e, t) {
    for (let n = 0; n < t.length; n++) {
      const i = t[n];

      (i.enumerable = i.enumerable || !1),
        (i.configurable = !0),
        'value' in i && (i.writable = !0),
        Object.defineProperty(e, i.key, i);
    }
  }
  function a(e, t, n) {
    return (
      t in e ? Object.defineProperty(e, t, { value: n, enumerable: !0, configurable: !0, writable: !0 }) : (e[t] = n), e
    );
  }
  function r(e, t) {
    if (e == null) return {};
    let n;
    let i;
    const a = (function (e, t) {
      if (e == null) return {};
      let n;
      let i;
      const a = {};
      const r = Object.keys(e);

      for (i = 0; i < r.length; i++) (n = r[i]), t.indexOf(n) >= 0 || (a[n] = e[n]);

      return a;
    })(e, t);

    if (Object.getOwnPropertySymbols) {
      const r = Object.getOwnPropertySymbols(e);

      for (i = 0; i < r.length; i++)
        (n = r[i]), t.indexOf(n) >= 0 || (Object.prototype.propertyIsEnumerable.call(e, n) && (a[n] = e[n]));
    }

    return a;
  }
  function o(e, t) {
    const n = t.get(e);

    if (!n) throw new TypeError('attempted to get private field on non-instance');

    return n.get ? n.get.call(e) : n.value;
  }
  const s = { player: 'lottie-player' };
  const l = '[lottieInteractivity]:';
  const c = (function () {
    function e() {
      const i = this;
      const c = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : s;
      const A = c.actions;
      let C = c.container;
      const T = c.mode;
      let H = c.player;
      const O = r(c, ['actions', 'container', 'mode', 'player']);

      if (
        (n(this, e),
        p.set(this, {
          writable: !0,
          value() {
            if (i.player) {
              const e = function () {
                i.player.addEventListener('enterFrame', o(i, E)),
                  i.container.addEventListener('mouseenter', o(i, w)),
                  i.container.addEventListener('mouseleave', o(i, I)),
                  i.container.addEventListener('touchstart', o(i, w), { passive: !0 }),
                  i.container.addEventListener('touchend', o(i, I), { passive: !0 });
              };

              i.stateHandler.set('loop', function () {
                i.actions[i.interactionIdx].loop
                  ? (i.player.loop = parseInt(i.actions[i.interactionIdx].loop) - 1)
                  : (i.player.loop = !0),
                  (i.player.autoplay = !0);
              }),
                i.stateHandler.set('autoplay', function () {
                  (i.player.loop = !1), (i.player.autoplay = !0);
                }),
                i.stateHandler.set('click', function () {
                  (i.player.loop = !1), (i.player.autoplay = !1), i.container.addEventListener('click', o(i, d));
                }),
                i.stateHandler.set('hover', function () {
                  (i.player.loop = !1),
                    (i.player.autoplay = !1),
                    i.container.addEventListener('mouseenter', o(i, d)),
                    i.container.addEventListener('touchstart', o(i, d), { passive: !0 });
                }),
                i.transitionHandler.set('click', function () {
                  i.container.addEventListener('click', o(i, y));
                }),
                i.transitionHandler.set('hover', function () {
                  i.container.addEventListener('mouseenter', o(i, y)),
                    i.container.addEventListener('touchstart', o(i, y), { passive: !0 });
                }),
                i.transitionHandler.set('hold', e),
                i.transitionHandler.set('pauseHold', e),
                i.transitionHandler.set('repeat', function () {
                  (i.player.loop = !0), (i.player.autoplay = !0);
                  i.player.addEventListener('loopComplete', function e() {
                    o(i, L).call(i, { handler: e });
                  });
                }),
                i.transitionHandler.set('onComplete', function () {
                  i.actions[i.interactionIdx].state === 'loop'
                    ? i.player.addEventListener('loopComplete', o(i, f))
                    : i.player.addEventListener('complete', o(i, f));
                }),
                i.transitionHandler.set('seek', function () {
                  i.player.stop(),
                    i.player.addEventListener('enterFrame', o(i, g)),
                    i.container.addEventListener('mousemove', o(i, u)),
                    i.container.addEventListener('touchmove', o(i, v), { passive: !1 }),
                    i.container.addEventListener('mouseout', o(i, m));
                });
            }
          },
        }),
        d.set(this, {
          writable: !0,
          value() {
            const e = i.actions[i.interactionIdx].forceFlag;

            e || !0 !== i.player.isPaused ? e && o(i, b).call(i, !0) : o(i, b).call(i, !0);
          },
        }),
        h.set(this, {
          writable: !0,
          value() {
            i.clickCounter === 0
              ? (i.player.play(), i.clickCounter++)
              : (i.clickCounter++, i.player.setDirection(-1 * i.player.playDirection), i.player.play());
          },
        }),
        y.set(this, {
          writable: !0,
          value() {
            const e = i.actions[i.interactionIdx].forceFlag;
            const t = i.actions[i.interactionIdx].state;
            const n = i.actions[i.interactionIdx].transition;

            if (i.mode === 'chain') {
              if (i.actions[i.interactionIdx].count) {
                const a = parseInt(i.actions[i.interactionIdx].count);

                if (i.clickCounter < a - 1) return void (i.clickCounter += 1);
              }

              return (
                (i.clickCounter = 0),
                (!e && n === 'click' && t === 'click') || (n === 'hover' && t === 'hover')
                  ? i.transitionHandler.get('onComplete').call()
                  : i.nextInteraction(),
                i.container.removeEventListener('click', o(i, y)),
                void i.container.removeEventListener('mouseenter', o(i, y))
              );
            }
            e || !0 !== i.player.isPaused ? e && i.player.goToAndPlay(0, !0) : i.player.goToAndPlay(0, !0);
          },
        }),
        u.set(this, {
          writable: !0,
          value(e) {
            o(i, P).call(i, e.clientX, e.clientY);
          },
        }),
        v.set(this, {
          writable: !0,
          value(e) {
            e.cancelable && e.preventDefault(), o(i, P).call(i, e.touches[0].clientX, e.touches[0].clientY);
          },
        }),
        m.set(this, {
          writable: !0,
          value() {
            o(i, P).call(i, -1, -1);
          },
        }),
        f.set(this, {
          writable: !0,
          value() {
            i.actions[i.interactionIdx].state === 'loop'
              ? i.player.removeEventListener('loopComplete', o(i, f))
              : i.player.removeEventListener('complete', o(i, f)),
              i.nextInteraction();
          },
        }),
        L.set(this, {
          writable: !0,
          value(e) {
            const t = e.handler;
            let n = 1;

            i.actions[i.interactionIdx].repeat && (n = i.actions[i.interactionIdx].repeat),
              i.playCounter >= n - 1
                ? ((i.playCounter = 0),
                  i.player.removeEventListener('loopComplete', t),
                  (i.player.loop = !1),
                  (i.player.autoplay = !1),
                  i.nextInteraction())
                : (i.playCounter += 1);
          },
        }),
        g.set(this, {
          writable: !0,
          value() {
            const e = i.actions[i.interactionIdx].frames;

            e &&
              i.player.currentFrame >= parseInt(e[1]) - 1 &&
              (i.player.removeEventListener('enterFrame', o(i, g)),
              i.container.removeEventListener('mousemove', o(i, u)),
              i.container.removeEventListener('mouseout', o(i, m)),
              setTimeout(i.nextInteraction, 0));
          },
        }),
        E.set(this, {
          writable: !0,
          value() {
            const e = i.actions[i.interactionIdx].frames;

            ((e && i.player.currentFrame >= e[1]) || i.player.currentFrame >= i.player.totalFrames - 1) &&
              (i.player.removeEventListener('enterFrame', o(i, E)),
              i.container.removeEventListener('mouseenter', o(i, w)),
              i.container.removeEventListener('mouseleave', o(i, I)),
              i.container.removeEventListener('touchstart', o(i, w), { passive: !0 }),
              i.container.removeEventListener('touchend', o(i, I), { passive: !0 }),
              i.player.pause(),
              (i.holdStatus = !1),
              i.nextInteraction());
          },
        }),
        w.set(this, {
          writable: !0,
          value() {
            (i.player.playDirection !== -1 && i.holdStatus !== null && i.holdStatus) ||
              (i.player.setDirection(1), i.player.play(), (i.holdStatus = !0));
          },
        }),
        I.set(this, {
          writable: !0,
          value() {
            i.actions[i.interactionIdx].transition === 'hold' || i.actions[0].type === 'hold'
              ? (i.player.setDirection(-1), i.player.play())
              : (i.actions[i.interactionIdx].transition !== 'pauseHold' && i.actions[0].type !== 'pauseHold') ||
                i.player.pause(),
              (i.holdStatus = !1);
          },
        }),
        x.set(this, {
          writable: !0,
          value() {
            const e = i.actions[i.interactionIdx].state;
            const t = i.actions[i.interactionIdx].transition;

            (e !== 'hover' && e !== 'click') ||
              (i.container.removeEventListener('click', o(i, d)),
              i.container.removeEventListener('mouseenter', o(i, d))),
              (t !== 'hover' && t !== 'click') ||
                (i.container.removeEventListener('click', o(i, y)),
                i.container.removeEventListener('mouseenter', o(i, y)),
                i.container.removeEventListener('touchstart', o(i, y), { passive: !0 }));
          },
        }),
        a(this, 'jumpToInteraction', function (e) {
          o(i, x).call(i),
            (i.interactionIdx = e),
            i.interactionIdx < 0 ? (i.interactionIdx = 0) : i.interactionIdx,
            i.nextInteraction(!1);
        }),
        a(this, 'nextInteraction', function () {
          const e = !(arguments.length > 0 && void 0 !== arguments[0]) || arguments[0];

          (i.oldInterctionIdx = i.interactionIdx), o(i, x).call(i);
          const t = i.actions[i.interactionIdx].jumpTo;

          t
            ? t >= 0 && t < i.actions.length
              ? ((i.interactionIdx = t), o(i, S).call(i, { ignorePath: !1 }))
              : ((i.interactionIdx = 0), i.player.goToAndStop(0, !0), o(i, S).call(i, { ignorePath: !1 }))
            : (e && i.interactionIdx++,
              i.interactionIdx >= i.actions.length
                ? i.actions[i.actions.length - 1].reset
                  ? ((i.interactionIdx = 0),
                    i.player.resetSegments(!0),
                    i.actions[i.interactionIdx].frames
                      ? i.player.goToAndStop(i.actions[i.interactionIdx].frames, !0)
                      : i.player.goToAndStop(0, !0),
                    o(i, S).call(i, { ignorePath: !1 }))
                  : ((i.interactionIdx = i.actions.length - 1), o(i, S).call(i, { ignorePath: !1 }))
                : o(i, S).call(i, { ignorePath: !1 })),
            i.container.dispatchEvent(
              new CustomEvent('transition', {
                bubbles: !0,
                composed: !0,
                detail: { oldIndex: i.oldInterctionIdx, newIndex: i.interactionIdx },
              }),
            );
        }),
        b.set(this, {
          writable: !0,
          value(e) {
            const t = i.actions[i.interactionIdx].frames;

            if (!t) return i.player.resetSegments(!0), void i.player.goToAndPlay(0, !0);
            typeof t == 'string' ? i.player.goToAndPlay(t, e) : i.player.playSegments(t, e);
          },
        }),
        k.set(this, {
          writable: !0,
          value() {
            let e = i.actions[i.interactionIdx].path;

            if (!e)
              if (t(i.enteredPlayer) === 'object' && i.enteredPlayer.constructor.name === 'AnimationItem') {
                if (((e = i.enteredPlayer), i.player === e)) return void o(i, S).call(i, { ignorePath: !0 });
              } else {
                let n = (e = i.loadedAnimation).substr(e.lastIndexOf('/') + 1);

                if (((n = n.substr(0, n.lastIndexOf('.json'))), i.player.fileName === n))
                  return void o(i, S).call(i, { ignorePath: !0 });
              }
            const a = i.container.getBoundingClientRect();
            const r = `width: ${a.width}px !important; height: ${a.height}px !important; background: ${i.container.style.background}`;

            if (
              (i.container.setAttribute('style', r),
              t(i.enteredPlayer) !== 'object' || i.enteredPlayer.constructor.name !== 'AnimationItem')
            ) {
              if (typeof i.enteredPlayer == 'string') {
                const s = document.querySelector(i.enteredPlayer);

                s &&
                  s.nodeName === 'LOTTIE-PLAYER' &&
                  (i.attachedListeners ||
                    (s.addEventListener('ready', function () {
                      (i.container.style.width = ''), (i.container.style.height = '');
                    }),
                    s.addEventListener('load', function () {
                      (i.player = s.getLottie()), o(i, S).call(i, { ignorePath: !0 });
                    }),
                    (i.attachedListeners = !0)),
                  s.load(e));
              } else
                i.enteredPlayer instanceof HTMLElement &&
                  i.enteredPlayer.nodeName === 'LOTTIE-PLAYER' &&
                  (i.attachedListeners ||
                    (i.enteredPlayer.addEventListener('ready', function () {
                      (i.container.style.width = ''), (i.container.style.height = '');
                    }),
                    i.enteredPlayer.addEventListener('load', function () {
                      (i.player = i.enteredPlayer.getLottie()), o(i, S).call(i, { ignorePath: !0 });
                    }),
                    (i.attachedListeners = !0)),
                  i.enteredPlayer.load(e));
              if (!i.player) throw new Error(''.concat(l, ' Specified player is invalid.'), i.enteredPlayer);
            } else {
              if (!window.lottie) throw new Error(''.concat(l, ' A Lottie player is required.'));
              i.stop(),
                i.player.destroy(),
                (i.container.innerHTML = ''),
                t(e) === 'object' && e.constructor.name === 'AnimationItem'
                  ? (i.player = window.lottie.loadAnimation({
                      loop: !1,
                      autoplay: !1,
                      animationData: e.animationData,
                      container: i.container,
                    }))
                  : (i.player = window.lottie.loadAnimation({
                      loop: !1,
                      autoplay: !1,
                      path: e,
                      container: i.container,
                    })),
                i.player.addEventListener('DOMLoaded', function () {
                  (i.container.style.width = ''), (i.container.style.height = ''), o(i, S).call(i, { ignorePath: !0 });
                });
            }
            (i.clickCounter = 0), (i.playCounter = 0);
          },
        }),
        S.set(this, {
          writable: !0,
          value(e) {
            const t = e.ignorePath;
            const n = i.actions[i.interactionIdx].frames;
            const a = i.actions[i.interactionIdx].state;
            const r = i.actions[i.interactionIdx].transition;
            const s = i.actions[i.interactionIdx].path;
            const l = i.stateHandler.get(a);
            const c = i.transitionHandler.get(r);
            const p = i.actions[i.interactionIdx].speed ? i.actions[i.interactionIdx].speed : 1;
            const d = i.actions[i.interactionIdx].delay ? i.actions[i.interactionIdx].delay : 0;

            t || !(s || (i.actions[i.actions.length - 1].reset && i.interactionIdx === 0))
              ? setTimeout(function () {
                  n && ((i.player.autoplay = !1), i.player.resetSegments(!0), i.player.goToAndStop(n[0], !0)),
                    l ? l.call() : a === 'none' && ((i.player.loop = !1), (i.player.autoplay = !1)),
                    c && c.call(),
                    i.player.autoplay && (i.player.resetSegments(!0), o(i, b).call(i, !0)),
                    i.player.setSpeed(p);
                }, d)
              : o(i, k).call(i);
          },
        }),
        P.set(this, {
          writable: !0,
          value(e, t) {
            if (e !== -1 && t !== -1) {
              const n = i.getContainerCursorPosition(e, t);

              (e = n.x), (t = n.y);
            }
            const a = i.actions.find(function (n) {
              const i = n.position;

              if (i) {
                if (Array.isArray(i.x) && Array.isArray(i.y))
                  return e >= i.x[0] && e <= i.x[1] && t >= i.y[0] && t <= i.y[1];
                if (!Number.isNaN(i.x) && !Number.isNaN(i.y)) return e === i.x && t === i.y;
              }

              return !1;
            });

            if (a)
              if (a.type === 'seek' || a.transition === 'seek') {
                const r = (e - a.position.x[0]) / (a.position.x[1] - a.position.x[0]);
                const o = (t - a.position.y[0]) / (a.position.y[1] - a.position.y[0]);

                i.player.playSegments(a.frames, !0),
                  a.position.y[0] < 0 && a.position.y[1] > 1
                    ? i.player.goToAndStop(Math.floor(r * i.player.totalFrames), !0)
                    : i.player.goToAndStop(Math.ceil(((r + o) / 2) * i.player.totalFrames), !0);
              } else
                a.type === 'loop'
                  ? i.player.playSegments(a.frames, !0)
                  : a.type === 'play'
                  ? (!0 === i.player.isPaused && i.player.resetSegments(), i.player.playSegments(a.frames))
                  : a.type === 'stop' && (i.player.resetSegments(!0), i.player.goToAndStop(a.frames[0], !0));
          },
        }),
        M.set(this, {
          writable: !0,
          value() {
            const e = i.getContainerVisibility();
            const t = i.actions.find(function (t) {
              const n = t.visibility;

              return e >= n[0] && e <= n[1];
            });

            if (t)
              if (t.type === 'seek') {
                const n = t.frames[0];
                const a = t.frames.length == 2 ? t.frames[1] : i.player.totalFrames - 1;

                i.assignedSegment !== null && (i.player.resetSegments(!0), (i.assignedSegment = null)),
                  i.player.goToAndStop(
                    n + Math.round(((e - t.visibility[0]) / (t.visibility[1] - t.visibility[0])) * (a - n)),
                    !0,
                  );
              } else
                t.type === 'loop'
                  ? ((i.player.loop = !0),
                    (i.assignedSegment === null || i.assignedSegment !== t.frames || !0 === i.player.isPaused) &&
                      (i.player.playSegments(t.frames, !0), (i.assignedSegment = t.frames)))
                  : t.type === 'play'
                  ? i.scrolledAndPlayed ||
                    ((i.scrolledAndPlayed = !0),
                    i.player.resetSegments(!0),
                    t.frames ? i.player.playSegments(t.frames, !0) : i.player.play())
                  : t.type === 'stop' && i.player.goToAndStop(t.frames[0], !0);
          },
        }),
        (this.enteredPlayer = H),
        t(H) !== 'object' || H.constructor.name !== 'AnimationItem')
      ) {
        if (typeof H == 'string') {
          const W = document.querySelector(H);

          W && W.nodeName === 'LOTTIE-PLAYER' && (H = W.getLottie());
        } else H instanceof HTMLElement && H.nodeName === 'LOTTIE-PLAYER' && (H = H.getLottie());
        if (!H) {
          const j = `${l}Specified player:${H} is invalid.`;

          throw new Error(j);
        }
      }
      typeof C == 'string' && (C = document.querySelector(C)),
        C || (C = H.wrapper),
        (this.player = H),
        (this.loadedAnimation = `${this.player.path + this.player.fileName}.json`),
        (this.attachedListeners = !1),
        (this.container = C),
        (this.mode = T),
        (this.actions = A),
        (this.options = O),
        (this.assignedSegment = null),
        (this.scrolledAndPlayed = !1),
        (this.interactionIdx = 0),
        (this.oldInterctionIdx = 0),
        (this.clickCounter = 0),
        (this.playCounter = 0),
        (this.stateHandler = new Map()),
        (this.transitionHandler = new Map());
    }
    let c;
    let A;
    let C;

    return (
      (c = e),
      (A = [
        {
          key: 'getContainerVisibility',
          value() {
            const e = this.container.getBoundingClientRect();
            const t = e.top;
            const n = e.height;

            return (window.innerHeight - t) / (window.innerHeight + n);
          },
        },
        {
          key: 'getContainerCursorPosition',
          value(e, t) {
            const n = this.container.getBoundingClientRect();
            const i = n.top;

            return { x: (e - n.left) / n.width, y: (t - i) / n.height };
          },
        },
        {
          key: 'initScrollMode',
          value() {
            this.player.stop(), window.addEventListener('scroll', o(this, M), !0);
          },
        },
        {
          key: 'initCursorMode',
          value() {
            this.actions && this.actions.length === 1
              ? this.actions[0].type === 'click'
                ? ((this.player.loop = !1), this.player.stop(), this.container.addEventListener('click', o(this, y)))
                : this.actions[0].type === 'hover'
                ? ((this.player.loop = !1),
                  this.player.stop(),
                  this.container.addEventListener('mouseenter', o(this, y)),
                  this.container.addEventListener('touchstart', o(this, y), { passive: !0 }))
                : this.actions[0].type === 'toggle'
                ? ((this.player.loop = !1), this.player.stop(), this.container.addEventListener('click', o(this, h)))
                : this.actions[0].type === 'hold' || this.actions[0].type === 'pauseHold'
                ? (this.container.addEventListener('mouseenter', o(this, w)),
                  this.container.addEventListener('mouseleave', o(this, I)),
                  this.container.addEventListener('touchstart', o(this, w), { passive: !0 }),
                  this.container.addEventListener('touchend', o(this, I), { passive: !0 }))
                : this.actions[0].type === 'seek' &&
                  ((this.player.loop = !0),
                  this.player.stop(),
                  this.container.addEventListener('mousemove', o(this, u)),
                  this.container.addEventListener('touchmove', o(this, v), { passive: !1 }),
                  this.container.addEventListener('mouseout', o(this, m)))
              : ((this.player.loop = !0),
                this.player.stop(),
                this.container.addEventListener('mousemove', o(this, u)),
                this.container.addEventListener('mouseleave', o(this, m)),
                o(this, P).call(this, -1, -1));
          },
        },
        {
          key: 'initChainMode',
          value() {
            o(this, p).call(this),
              (this.player.loop = !1),
              this.player.stop(),
              o(this, S).call(this, { ignorePath: !1 });
          },
        },
        {
          key: 'start',
          value() {
            const e = this;

            this.mode === 'scroll'
              ? this.player.isLoaded
                ? this.initScrollMode()
                : this.player.addEventListener('DOMLoaded', function () {
                    e.initScrollMode();
                  })
              : this.mode === 'cursor'
              ? this.player.isLoaded
                ? this.initCursorMode()
                : this.player.addEventListener('DOMLoaded', function () {
                    e.initCursorMode();
                  })
              : this.mode === 'chain' &&
                (this.player.isLoaded
                  ? this.initChainMode()
                  : this.player.addEventListener('DOMLoaded', function () {
                      e.initChainMode();
                    }));
          },
        },
        {
          key: 'redefineOptions',
          value(e) {
            const n = e.actions;
            let i = e.container;
            const a = e.mode;
            let o = e.player;
            const s = r(e, ['actions', 'container', 'mode', 'player']);

            if ((this.stop(), (this.enteredPlayer = o), t(o) !== 'object' || o.constructor.name !== 'AnimationItem')) {
              if (typeof o == 'string') {
                const c = document.querySelector(o);

                c && c.nodeName === 'LOTTIE-PLAYER' && (o = c.getLottie());
              } else o instanceof HTMLElement && o.nodeName === 'LOTTIE-PLAYER' && (o = o.getLottie());
              if (!o) throw new Error(`${l}Specified player:${o} is invalid.`, o);
            }
            typeof i == 'string' && (i = document.querySelector(i)),
              i || (i = o.wrapper),
              (this.player = o),
              (this.loadedAnimation = `${this.player.path + this.player.fileName}.json`),
              (this.attachedListeners = !1),
              (this.container = i),
              (this.mode = a),
              (this.actions = n),
              (this.options = s),
              (this.assignedSegment = null),
              (this.scrolledAndPlayed = !1),
              (this.interactionIdx = 0),
              (this.clickCounter = 0),
              (this.playCounter = 0),
              (this.holdStatus = null),
              (this.stateHandler = new Map()),
              (this.transitionHandler = new Map()),
              this.start();
          },
        },
        {
          key: 'stop',
          value() {
            if (
              (this.mode === 'scroll' && window.removeEventListener('scroll', o(this, M), !0),
              this.mode === 'cursor' &&
                (this.container.removeEventListener('click', o(this, y)),
                this.container.removeEventListener('click', o(this, h)),
                this.container.removeEventListener('mouseenter', o(this, y)),
                this.container.removeEventListener('touchstart', o(this, y)),
                this.container.removeEventListener('touchmove', o(this, v)),
                this.container.removeEventListener('mousemove', o(this, u)),
                this.container.removeEventListener('mouseleave', o(this, m)),
                this.container.removeEventListener('touchstart', o(this, w)),
                this.container.removeEventListener('touchend', o(this, I))),
              this.mode === 'chain' &&
                (this.container.removeEventListener('click', o(this, y)),
                this.container.removeEventListener('click', o(this, d)),
                this.container.removeEventListener('mouseenter', o(this, y)),
                this.container.removeEventListener('touchstart', o(this, y)),
                this.container.removeEventListener('touchmove', o(this, v)),
                this.container.removeEventListener('mouseenter', o(this, d)),
                this.container.removeEventListener('touchstart', o(this, d)),
                this.container.removeEventListener('mouseenter', o(this, w)),
                this.container.removeEventListener('touchstart', o(this, w)),
                this.container.removeEventListener('mouseleave', o(this, I)),
                this.container.removeEventListener('mousemove', o(this, u)),
                this.container.removeEventListener('mouseout', o(this, m)),
                this.container.removeEventListener('touchend', o(this, I)),
                this.player))
            )
              try {
                this.player.removeEventListener('loopComplete', o(this, f)),
                  this.player.removeEventListener('complete', o(this, f)),
                  this.player.removeEventListener('enterFrame', o(this, g)),
                  this.player.removeEventListener('enterFrame', o(this, E));
              } catch (e) {}
            this.player = null;
          },
        },
      ]) && i(c.prototype, A),
      C && i(c, C),
      e
    );
  })();
  var p = new WeakMap();
  var d = new WeakMap();
  var h = new WeakMap();
  var y = new WeakMap();
  var u = new WeakMap();
  var v = new WeakMap();
  var m = new WeakMap();
  var f = new WeakMap();
  var L = new WeakMap();
  var g = new WeakMap();
  var E = new WeakMap();
  var w = new WeakMap();
  var I = new WeakMap();
  var x = new WeakMap();
  var b = new WeakMap();
  var k = new WeakMap();
  var S = new WeakMap();
  var P = new WeakMap();
  var M = new WeakMap();
  const A = function (e) {
    const t = new c(e);

    return t.start(), t;
  };

  (e.LottieInteractivity = c), (e.create = A), (e.default = A), Object.defineProperty(e, '__esModule', { value: !0 });
});

