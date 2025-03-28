var k = "top",
    V = "bottom",
    W = "right",
    F = "left",
    Sn = "auto",
    ue = [k, V, W, F],
    Ht = "start",
    te = "end",
    no = "clippingParents",
    Yr = "viewport",
    Xt = "popper",
    ro = "reference",
    Er = ue.reduce(function (e, t) {
        return e.concat([t + "-" + Ht, t + "-" + te]);
    }, []),
    Gr = [].concat(ue, [Sn]).reduce(function (e, t) {
        return e.concat([t, t + "-" + Ht, t + "-" + te]);
    }, []),
    io = "beforeRead",
    so = "read",
    oo = "afterRead",
    ao = "beforeMain",
    co = "main",
    lo = "afterMain",
    uo = "beforeWrite",
    fo = "write",
    ho = "afterWrite",
    po = [io, so, oo, ao, co, lo, uo, fo, ho];
function ot(e) {
    return e ? (e.nodeName || "").toLowerCase() : null;
}
function U(e) {
    if (e == null) return window;
    if (e.toString() !== "[object Window]") {
        var t = e.ownerDocument;
        return (t && t.defaultView) || window;
    }
    return e;
}
function Vt(e) {
    var t = U(e).Element;
    return e instanceof t || e instanceof Element;
}
function K(e) {
    var t = U(e).HTMLElement;
    return e instanceof t || e instanceof HTMLElement;
}
function Xr(e) {
    if (typeof ShadowRoot > "u") return !1;
    var t = U(e).ShadowRoot;
    return e instanceof t || e instanceof ShadowRoot;
}
function Hc(e) {
    var t = e.state;
    Object.keys(t.elements).forEach(function (n) {
        var r = t.styles[n] || {},
            i = t.attributes[n] || {},
            s = t.elements[n];
        !K(s) ||
            !ot(s) ||
            (Object.assign(s.style, r),
            Object.keys(i).forEach(function (o) {
                var a = i[o];
                a === !1
                    ? s.removeAttribute(o)
                    : s.setAttribute(o, a === !0 ? "" : a);
            }));
    });
}
function Vc(e) {
    var t = e.state,
        n = {
            popper: {
                position: t.options.strategy,
                left: "0",
                top: "0",
                margin: "0",
            },
            arrow: { position: "absolute" },
            reference: {},
        };
    return (
        Object.assign(t.elements.popper.style, n.popper),
        (t.styles = n),
        t.elements.arrow && Object.assign(t.elements.arrow.style, n.arrow),
        function () {
            Object.keys(t.elements).forEach(function (r) {
                var i = t.elements[r],
                    s = t.attributes[r] || {},
                    o = Object.keys(
                        t.styles.hasOwnProperty(r) ? t.styles[r] : n[r]
                    ),
                    a = o.reduce(function (c, u) {
                        return (c[u] = ""), c;
                    }, {});
                !K(i) ||
                    !ot(i) ||
                    (Object.assign(i.style, a),
                    Object.keys(s).forEach(function (c) {
                        i.removeAttribute(c);
                    }));
            });
        }
    );
}
const Jr = {
    name: "applyStyles",
    enabled: !0,
    phase: "write",
    fn: Hc,
    effect: Vc,
    requires: ["computeStyles"],
};
function it(e) {
    return e.split("-")[0];
}
var Mt = Math.max,
    gn = Math.min,
    ee = Math.round;
function br() {
    var e = navigator.userAgentData;
    return e != null && e.brands && Array.isArray(e.brands)
        ? e.brands
              .map(function (t) {
                  return t.brand + "/" + t.version;
              })
              .join(" ")
        : navigator.userAgent;
}
function _o() {
    return !/^((?!chrome|android).)*safari/i.test(br());
}
function ne(e, t, n) {
    t === void 0 && (t = !1), n === void 0 && (n = !1);
    var r = e.getBoundingClientRect(),
        i = 1,
        s = 1;
    t &&
        K(e) &&
        ((i = (e.offsetWidth > 0 && ee(r.width) / e.offsetWidth) || 1),
        (s = (e.offsetHeight > 0 && ee(r.height) / e.offsetHeight) || 1));
    var o = Vt(e) ? U(e) : window,
        a = o.visualViewport,
        c = !_o() && n,
        u = (r.left + (c && a ? a.offsetLeft : 0)) / i,
        l = (r.top + (c && a ? a.offsetTop : 0)) / s,
        f = r.width / i,
        m = r.height / s;
    return {
        width: f,
        height: m,
        top: l,
        right: u + f,
        bottom: l + m,
        left: u,
        x: u,
        y: l,
    };
}
function Qr(e) {
    var t = ne(e),
        n = e.offsetWidth,
        r = e.offsetHeight;
    return (
        Math.abs(t.width - n) <= 1 && (n = t.width),
        Math.abs(t.height - r) <= 1 && (r = t.height),
        { x: e.offsetLeft, y: e.offsetTop, width: n, height: r }
    );
}
function mo(e, t) {
    var n = t.getRootNode && t.getRootNode();
    if (e.contains(t)) return !0;
    if (n && Xr(n)) {
        var r = t;
        do {
            if (r && e.isSameNode(r)) return !0;
            r = r.parentNode || r.host;
        } while (r);
    }
    return !1;
}
function ft(e) {
    return U(e).getComputedStyle(e);
}
function Wc(e) {
    return ["table", "td", "th"].indexOf(ot(e)) >= 0;
}
function Tt(e) {
    return ((Vt(e) ? e.ownerDocument : e.document) || window.document)
        .documentElement;
}
function On(e) {
    return ot(e) === "html"
        ? e
        : e.assignedSlot || e.parentNode || (Xr(e) ? e.host : null) || Tt(e);
}
function Yi(e) {
    return !K(e) || ft(e).position === "fixed" ? null : e.offsetParent;
}
function Uc(e) {
    var t = /firefox/i.test(br()),
        n = /Trident/i.test(br());
    if (n && K(e)) {
        var r = ft(e);
        if (r.position === "fixed") return null;
    }
    var i = On(e);
    for (Xr(i) && (i = i.host); K(i) && ["html", "body"].indexOf(ot(i)) < 0; ) {
        var s = ft(i);
        if (
            s.transform !== "none" ||
            s.perspective !== "none" ||
            s.contain === "paint" ||
            ["transform", "perspective"].indexOf(s.willChange) !== -1 ||
            (t && s.willChange === "filter") ||
            (t && s.filter && s.filter !== "none")
        )
            return i;
        i = i.parentNode;
    }
    return null;
}
function Ie(e) {
    for (var t = U(e), n = Yi(e); n && Wc(n) && ft(n).position === "static"; )
        n = Yi(n);
    return n &&
        (ot(n) === "html" || (ot(n) === "body" && ft(n).position === "static"))
        ? t
        : n || Uc(e) || t;
}
function Zr(e) {
    return ["top", "bottom"].indexOf(e) >= 0 ? "x" : "y";
}
function Ce(e, t, n) {
    return Mt(e, gn(t, n));
}
function Kc(e, t, n) {
    var r = Ce(e, t, n);
    return r > n ? n : r;
}
function go() {
    return { top: 0, right: 0, bottom: 0, left: 0 };
}
function Eo(e) {
    return Object.assign({}, go(), e);
}
function bo(e, t) {
    return t.reduce(function (n, r) {
        return (n[r] = e), n;
    }, {});
}
var zc = function (t, n) {
    return (
        (t =
            typeof t == "function"
                ? t(Object.assign({}, n.rects, { placement: n.placement }))
                : t),
        Eo(typeof t != "number" ? t : bo(t, ue))
    );
};
function qc(e) {
    var t,
        n = e.state,
        r = e.name,
        i = e.options,
        s = n.elements.arrow,
        o = n.modifiersData.popperOffsets,
        a = it(n.placement),
        c = Zr(a),
        u = [F, W].indexOf(a) >= 0,
        l = u ? "height" : "width";
    if (!(!s || !o)) {
        var f = zc(i.padding, n),
            m = Qr(s),
            E = c === "y" ? k : F,
            g = c === "y" ? V : W,
            _ =
                n.rects.reference[l] +
                n.rects.reference[c] -
                o[c] -
                n.rects.popper[l],
            p = o[c] - n.rects.reference[c],
            b = Ie(s),
            y = b ? (c === "y" ? b.clientHeight || 0 : b.clientWidth || 0) : 0,
            w = _ / 2 - p / 2,
            A = f[E],
            T = y - m[l] - f[g],
            S = y / 2 - m[l] / 2 + w,
            C = Ce(A, S, T),
            R = c;
        n.modifiersData[r] =
            ((t = {}), (t[R] = C), (t.centerOffset = C - S), t);
    }
}
function Yc(e) {
    var t = e.state,
        n = e.options,
        r = n.element,
        i = r === void 0 ? "[data-popper-arrow]" : r;
    i != null &&
        ((typeof i == "string" &&
            ((i = t.elements.popper.querySelector(i)), !i)) ||
            (mo(t.elements.popper, i) && (t.elements.arrow = i)));
}
const vo = {
    name: "arrow",
    enabled: !0,
    phase: "main",
    fn: qc,
    effect: Yc,
    requires: ["popperOffsets"],
    requiresIfExists: ["preventOverflow"],
};
function re(e) {
    return e.split("-")[1];
}
var Gc = { top: "auto", right: "auto", bottom: "auto", left: "auto" };
function Xc(e, t) {
    var n = e.x,
        r = e.y,
        i = t.devicePixelRatio || 1;
    return { x: ee(n * i) / i || 0, y: ee(r * i) / i || 0 };
}
function Gi(e) {
    var t,
        n = e.popper,
        r = e.popperRect,
        i = e.placement,
        s = e.variation,
        o = e.offsets,
        a = e.position,
        c = e.gpuAcceleration,
        u = e.adaptive,
        l = e.roundOffsets,
        f = e.isFixed,
        m = o.x,
        E = m === void 0 ? 0 : m,
        g = o.y,
        _ = g === void 0 ? 0 : g,
        p = typeof l == "function" ? l({ x: E, y: _ }) : { x: E, y: _ };
    (E = p.x), (_ = p.y);
    var b = o.hasOwnProperty("x"),
        y = o.hasOwnProperty("y"),
        w = F,
        A = k,
        T = window;
    if (u) {
        var S = Ie(n),
            C = "clientHeight",
            R = "clientWidth";
        if (
            (S === U(n) &&
                ((S = Tt(n)),
                ft(S).position !== "static" &&
                    a === "absolute" &&
                    ((C = "scrollHeight"), (R = "scrollWidth"))),
            (S = S),
            i === k || ((i === F || i === W) && s === te))
        ) {
            A = V;
            var D =
                f && S === T && T.visualViewport
                    ? T.visualViewport.height
                    : S[C];
            (_ -= D - r.height), (_ *= c ? 1 : -1);
        }
        if (i === F || ((i === k || i === V) && s === te)) {
            w = W;
            var N =
                f && S === T && T.visualViewport
                    ? T.visualViewport.width
                    : S[R];
            (E -= N - r.width), (E *= c ? 1 : -1);
        }
    }
    var P = Object.assign({ position: a }, u && Gc),
        X = l === !0 ? Xc({ x: E, y: _ }, U(n)) : { x: E, y: _ };
    if (((E = X.x), (_ = X.y), c)) {
        var M;
        return Object.assign(
            {},
            P,
            ((M = {}),
            (M[A] = y ? "0" : ""),
            (M[w] = b ? "0" : ""),
            (M.transform =
                (T.devicePixelRatio || 1) <= 1
                    ? "translate(" + E + "px, " + _ + "px)"
                    : "translate3d(" + E + "px, " + _ + "px, 0)"),
            M)
        );
    }
    return Object.assign(
        {},
        P,
        ((t = {}),
        (t[A] = y ? _ + "px" : ""),
        (t[w] = b ? E + "px" : ""),
        (t.transform = ""),
        t)
    );
}
function Jc(e) {
    var t = e.state,
        n = e.options,
        r = n.gpuAcceleration,
        i = r === void 0 ? !0 : r,
        s = n.adaptive,
        o = s === void 0 ? !0 : s,
        a = n.roundOffsets,
        c = a === void 0 ? !0 : a,
        u = {
            placement: it(t.placement),
            variation: re(t.placement),
            popper: t.elements.popper,
            popperRect: t.rects.popper,
            gpuAcceleration: i,
            isFixed: t.options.strategy === "fixed",
        };
    t.modifiersData.popperOffsets != null &&
        (t.styles.popper = Object.assign(
            {},
            t.styles.popper,
            Gi(
                Object.assign({}, u, {
                    offsets: t.modifiersData.popperOffsets,
                    position: t.options.strategy,
                    adaptive: o,
                    roundOffsets: c,
                })
            )
        )),
        t.modifiersData.arrow != null &&
            (t.styles.arrow = Object.assign(
                {},
                t.styles.arrow,
                Gi(
                    Object.assign({}, u, {
                        offsets: t.modifiersData.arrow,
                        position: "absolute",
                        adaptive: !1,
                        roundOffsets: c,
                    })
                )
            )),
        (t.attributes.popper = Object.assign({}, t.attributes.popper, {
            "data-popper-placement": t.placement,
        }));
}
const ti = {
    name: "computeStyles",
    enabled: !0,
    phase: "beforeWrite",
    fn: Jc,
    data: {},
};
var Xe = { passive: !0 };
function Qc(e) {
    var t = e.state,
        n = e.instance,
        r = e.options,
        i = r.scroll,
        s = i === void 0 ? !0 : i,
        o = r.resize,
        a = o === void 0 ? !0 : o,
        c = U(t.elements.popper),
        u = [].concat(t.scrollParents.reference, t.scrollParents.popper);
    return (
        s &&
            u.forEach(function (l) {
                l.addEventListener("scroll", n.update, Xe);
            }),
        a && c.addEventListener("resize", n.update, Xe),
        function () {
            s &&
                u.forEach(function (l) {
                    l.removeEventListener("scroll", n.update, Xe);
                }),
                a && c.removeEventListener("resize", n.update, Xe);
        }
    );
}
const ei = {
    name: "eventListeners",
    enabled: !0,
    phase: "write",
    fn: function () {},
    effect: Qc,
    data: {},
};
var Zc = { left: "right", right: "left", bottom: "top", top: "bottom" };
function ln(e) {
    return e.replace(/left|right|bottom|top/g, function (t) {
        return Zc[t];
    });
}
var tl = { start: "end", end: "start" };
function Xi(e) {
    return e.replace(/start|end/g, function (t) {
        return tl[t];
    });
}
function ni(e) {
    var t = U(e),
        n = t.pageXOffset,
        r = t.pageYOffset;
    return { scrollLeft: n, scrollTop: r };
}
function ri(e) {
    return ne(Tt(e)).left + ni(e).scrollLeft;
}
function el(e, t) {
    var n = U(e),
        r = Tt(e),
        i = n.visualViewport,
        s = r.clientWidth,
        o = r.clientHeight,
        a = 0,
        c = 0;
    if (i) {
        (s = i.width), (o = i.height);
        var u = _o();
        (u || (!u && t === "fixed")) && ((a = i.offsetLeft), (c = i.offsetTop));
    }
    return { width: s, height: o, x: a + ri(e), y: c };
}
function nl(e) {
    var t,
        n = Tt(e),
        r = ni(e),
        i = (t = e.ownerDocument) == null ? void 0 : t.body,
        s = Mt(
            n.scrollWidth,
            n.clientWidth,
            i ? i.scrollWidth : 0,
            i ? i.clientWidth : 0
        ),
        o = Mt(
            n.scrollHeight,
            n.clientHeight,
            i ? i.scrollHeight : 0,
            i ? i.clientHeight : 0
        ),
        a = -r.scrollLeft + ri(e),
        c = -r.scrollTop;
    return (
        ft(i || n).direction === "rtl" &&
            (a += Mt(n.clientWidth, i ? i.clientWidth : 0) - s),
        { width: s, height: o, x: a, y: c }
    );
}
function ii(e) {
    var t = ft(e),
        n = t.overflow,
        r = t.overflowX,
        i = t.overflowY;
    return /auto|scroll|overlay|hidden/.test(n + i + r);
}
function yo(e) {
    return ["html", "body", "#document"].indexOf(ot(e)) >= 0
        ? e.ownerDocument.body
        : K(e) && ii(e)
        ? e
        : yo(On(e));
}
function xe(e, t) {
    var n;
    t === void 0 && (t = []);
    var r = yo(e),
        i = r === ((n = e.ownerDocument) == null ? void 0 : n.body),
        s = U(r),
        o = i ? [s].concat(s.visualViewport || [], ii(r) ? r : []) : r,
        a = t.concat(o);
    return i ? a : a.concat(xe(On(o)));
}
function vr(e) {
    return Object.assign({}, e, {
        left: e.x,
        top: e.y,
        right: e.x + e.width,
        bottom: e.y + e.height,
    });
}
function rl(e, t) {
    var n = ne(e, !1, t === "fixed");
    return (
        (n.top = n.top + e.clientTop),
        (n.left = n.left + e.clientLeft),
        (n.bottom = n.top + e.clientHeight),
        (n.right = n.left + e.clientWidth),
        (n.width = e.clientWidth),
        (n.height = e.clientHeight),
        (n.x = n.left),
        (n.y = n.top),
        n
    );
}
function Ji(e, t, n) {
    return t === Yr ? vr(el(e, n)) : Vt(t) ? rl(t, n) : vr(nl(Tt(e)));
}
function il(e) {
    var t = xe(On(e)),
        n = ["absolute", "fixed"].indexOf(ft(e).position) >= 0,
        r = n && K(e) ? Ie(e) : e;
    return Vt(r)
        ? t.filter(function (i) {
              return Vt(i) && mo(i, r) && ot(i) !== "body";
          })
        : [];
}
function sl(e, t, n, r) {
    var i = t === "clippingParents" ? il(e) : [].concat(t),
        s = [].concat(i, [n]),
        o = s[0],
        a = s.reduce(function (c, u) {
            var l = Ji(e, u, r);
            return (
                (c.top = Mt(l.top, c.top)),
                (c.right = gn(l.right, c.right)),
                (c.bottom = gn(l.bottom, c.bottom)),
                (c.left = Mt(l.left, c.left)),
                c
            );
        }, Ji(e, o, r));
    return (
        (a.width = a.right - a.left),
        (a.height = a.bottom - a.top),
        (a.x = a.left),
        (a.y = a.top),
        a
    );
}
function Ao(e) {
    var t = e.reference,
        n = e.element,
        r = e.placement,
        i = r ? it(r) : null,
        s = r ? re(r) : null,
        o = t.x + t.width / 2 - n.width / 2,
        a = t.y + t.height / 2 - n.height / 2,
        c;
    switch (i) {
        case k:
            c = { x: o, y: t.y - n.height };
            break;
        case V:
            c = { x: o, y: t.y + t.height };
            break;
        case W:
            c = { x: t.x + t.width, y: a };
            break;
        case F:
            c = { x: t.x - n.width, y: a };
            break;
        default:
            c = { x: t.x, y: t.y };
    }
    var u = i ? Zr(i) : null;
    if (u != null) {
        var l = u === "y" ? "height" : "width";
        switch (s) {
            case Ht:
                c[u] = c[u] - (t[l] / 2 - n[l] / 2);
                break;
            case te:
                c[u] = c[u] + (t[l] / 2 - n[l] / 2);
                break;
        }
    }
    return c;
}
function ie(e, t) {
    t === void 0 && (t = {});
    var n = t,
        r = n.placement,
        i = r === void 0 ? e.placement : r,
        s = n.strategy,
        o = s === void 0 ? e.strategy : s,
        a = n.boundary,
        c = a === void 0 ? no : a,
        u = n.rootBoundary,
        l = u === void 0 ? Yr : u,
        f = n.elementContext,
        m = f === void 0 ? Xt : f,
        E = n.altBoundary,
        g = E === void 0 ? !1 : E,
        _ = n.padding,
        p = _ === void 0 ? 0 : _,
        b = Eo(typeof p != "number" ? p : bo(p, ue)),
        y = m === Xt ? ro : Xt,
        w = e.rects.popper,
        A = e.elements[g ? y : m],
        T = sl(Vt(A) ? A : A.contextElement || Tt(e.elements.popper), c, l, o),
        S = ne(e.elements.reference),
        C = Ao({
            reference: S,
            element: w,
            strategy: "absolute",
            placement: i,
        }),
        R = vr(Object.assign({}, w, C)),
        D = m === Xt ? R : S,
        N = {
            top: T.top - D.top + b.top,
            bottom: D.bottom - T.bottom + b.bottom,
            left: T.left - D.left + b.left,
            right: D.right - T.right + b.right,
        },
        P = e.modifiersData.offset;
    if (m === Xt && P) {
        var X = P[i];
        Object.keys(N).forEach(function (M) {
            var Ct = [W, V].indexOf(M) >= 0 ? 1 : -1,
                xt = [k, V].indexOf(M) >= 0 ? "y" : "x";
            N[M] += X[xt] * Ct;
        });
    }
    return N;
}
function ol(e, t) {
    t === void 0 && (t = {});
    var n = t,
        r = n.placement,
        i = n.boundary,
        s = n.rootBoundary,
        o = n.padding,
        a = n.flipVariations,
        c = n.allowedAutoPlacements,
        u = c === void 0 ? Gr : c,
        l = re(r),
        f = l
            ? a
                ? Er
                : Er.filter(function (g) {
                      return re(g) === l;
                  })
            : ue,
        m = f.filter(function (g) {
            return u.indexOf(g) >= 0;
        });
    m.length === 0 && (m = f);
    var E = m.reduce(function (g, _) {
        return (
            (g[_] = ie(e, {
                placement: _,
                boundary: i,
                rootBoundary: s,
                padding: o,
            })[it(_)]),
            g
        );
    }, {});
    return Object.keys(E).sort(function (g, _) {
        return E[g] - E[_];
    });
}
function al(e) {
    if (it(e) === Sn) return [];
    var t = ln(e);
    return [Xi(e), t, Xi(t)];
}
function cl(e) {
    var t = e.state,
        n = e.options,
        r = e.name;
    if (!t.modifiersData[r]._skip) {
        for (
            var i = n.mainAxis,
                s = i === void 0 ? !0 : i,
                o = n.altAxis,
                a = o === void 0 ? !0 : o,
                c = n.fallbackPlacements,
                u = n.padding,
                l = n.boundary,
                f = n.rootBoundary,
                m = n.altBoundary,
                E = n.flipVariations,
                g = E === void 0 ? !0 : E,
                _ = n.allowedAutoPlacements,
                p = t.options.placement,
                b = it(p),
                y = b === p,
                w = c || (y || !g ? [ln(p)] : al(p)),
                A = [p].concat(w).reduce(function (qt, pt) {
                    return qt.concat(
                        it(pt) === Sn
                            ? ol(t, {
                                  placement: pt,
                                  boundary: l,
                                  rootBoundary: f,
                                  padding: u,
                                  flipVariations: g,
                                  allowedAutoPlacements: _,
                              })
                            : pt
                    );
                }, []),
                T = t.rects.reference,
                S = t.rects.popper,
                C = new Map(),
                R = !0,
                D = A[0],
                N = 0;
            N < A.length;
            N++
        ) {
            var P = A[N],
                X = it(P),
                M = re(P) === Ht,
                Ct = [k, V].indexOf(X) >= 0,
                xt = Ct ? "width" : "height",
                H = ie(t, {
                    placement: P,
                    boundary: l,
                    rootBoundary: f,
                    altBoundary: m,
                    padding: u,
                }),
                J = Ct ? (M ? W : F) : M ? V : k;
            T[xt] > S[xt] && (J = ln(J));
            var Ke = ln(J),
                Nt = [];
            if (
                (s && Nt.push(H[X] <= 0),
                a && Nt.push(H[J] <= 0, H[Ke] <= 0),
                Nt.every(function (qt) {
                    return qt;
                }))
            ) {
                (D = P), (R = !1);
                break;
            }
            C.set(P, Nt);
        }
        if (R)
            for (
                var ze = g ? 3 : 1,
                    Kn = function (pt) {
                        var be = A.find(function (Ye) {
                            var Dt = C.get(Ye);
                            if (Dt)
                                return Dt.slice(0, pt).every(function (zn) {
                                    return zn;
                                });
                        });
                        if (be) return (D = be), "break";
                    },
                    Ee = ze;
                Ee > 0;
                Ee--
            ) {
                var qe = Kn(Ee);
                if (qe === "break") break;
            }
        t.placement !== D &&
            ((t.modifiersData[r]._skip = !0),
            (t.placement = D),
            (t.reset = !0));
    }
}
const wo = {
    name: "flip",
    enabled: !0,
    phase: "main",
    fn: cl,
    requiresIfExists: ["offset"],
    data: { _skip: !1 },
};
function Qi(e, t, n) {
    return (
        n === void 0 && (n = { x: 0, y: 0 }),
        {
            top: e.top - t.height - n.y,
            right: e.right - t.width + n.x,
            bottom: e.bottom - t.height + n.y,
            left: e.left - t.width - n.x,
        }
    );
}
function Zi(e) {
    return [k, W, V, F].some(function (t) {
        return e[t] >= 0;
    });
}
function ll(e) {
    var t = e.state,
        n = e.name,
        r = t.rects.reference,
        i = t.rects.popper,
        s = t.modifiersData.preventOverflow,
        o = ie(t, { elementContext: "reference" }),
        a = ie(t, { altBoundary: !0 }),
        c = Qi(o, r),
        u = Qi(a, i, s),
        l = Zi(c),
        f = Zi(u);
    (t.modifiersData[n] = {
        referenceClippingOffsets: c,
        popperEscapeOffsets: u,
        isReferenceHidden: l,
        hasPopperEscaped: f,
    }),
        (t.attributes.popper = Object.assign({}, t.attributes.popper, {
            "data-popper-reference-hidden": l,
            "data-popper-escaped": f,
        }));
}
const To = {
    name: "hide",
    enabled: !0,
    phase: "main",
    requiresIfExists: ["preventOverflow"],
    fn: ll,
};
function ul(e, t, n) {
    var r = it(e),
        i = [F, k].indexOf(r) >= 0 ? -1 : 1,
        s =
            typeof n == "function"
                ? n(Object.assign({}, t, { placement: e }))
                : n,
        o = s[0],
        a = s[1];
    return (
        (o = o || 0),
        (a = (a || 0) * i),
        [F, W].indexOf(r) >= 0 ? { x: a, y: o } : { x: o, y: a }
    );
}
function fl(e) {
    var t = e.state,
        n = e.options,
        r = e.name,
        i = n.offset,
        s = i === void 0 ? [0, 0] : i,
        o = Gr.reduce(function (l, f) {
            return (l[f] = ul(f, t.rects, s)), l;
        }, {}),
        a = o[t.placement],
        c = a.x,
        u = a.y;
    t.modifiersData.popperOffsets != null &&
        ((t.modifiersData.popperOffsets.x += c),
        (t.modifiersData.popperOffsets.y += u)),
        (t.modifiersData[r] = o);
}
const So = {
    name: "offset",
    enabled: !0,
    phase: "main",
    requires: ["popperOffsets"],
    fn: fl,
};
function dl(e) {
    var t = e.state,
        n = e.name;
    t.modifiersData[n] = Ao({
        reference: t.rects.reference,
        element: t.rects.popper,
        strategy: "absolute",
        placement: t.placement,
    });
}
const si = {
    name: "popperOffsets",
    enabled: !0,
    phase: "read",
    fn: dl,
    data: {},
};
function hl(e) {
    return e === "x" ? "y" : "x";
}
function pl(e) {
    var t = e.state,
        n = e.options,
        r = e.name,
        i = n.mainAxis,
        s = i === void 0 ? !0 : i,
        o = n.altAxis,
        a = o === void 0 ? !1 : o,
        c = n.boundary,
        u = n.rootBoundary,
        l = n.altBoundary,
        f = n.padding,
        m = n.tether,
        E = m === void 0 ? !0 : m,
        g = n.tetherOffset,
        _ = g === void 0 ? 0 : g,
        p = ie(t, { boundary: c, rootBoundary: u, padding: f, altBoundary: l }),
        b = it(t.placement),
        y = re(t.placement),
        w = !y,
        A = Zr(b),
        T = hl(A),
        S = t.modifiersData.popperOffsets,
        C = t.rects.reference,
        R = t.rects.popper,
        D =
            typeof _ == "function"
                ? _(Object.assign({}, t.rects, { placement: t.placement }))
                : _,
        N =
            typeof D == "number"
                ? { mainAxis: D, altAxis: D }
                : Object.assign({ mainAxis: 0, altAxis: 0 }, D),
        P = t.modifiersData.offset ? t.modifiersData.offset[t.placement] : null,
        X = { x: 0, y: 0 };
    if (S) {
        if (s) {
            var M,
                Ct = A === "y" ? k : F,
                xt = A === "y" ? V : W,
                H = A === "y" ? "height" : "width",
                J = S[A],
                Ke = J + p[Ct],
                Nt = J - p[xt],
                ze = E ? -R[H] / 2 : 0,
                Kn = y === Ht ? C[H] : R[H],
                Ee = y === Ht ? -R[H] : -C[H],
                qe = t.elements.arrow,
                qt = E && qe ? Qr(qe) : { width: 0, height: 0 },
                pt = t.modifiersData["arrow#persistent"]
                    ? t.modifiersData["arrow#persistent"].padding
                    : go(),
                be = pt[Ct],
                Ye = pt[xt],
                Dt = Ce(0, C[H], qt[H]),
                zn = w
                    ? C[H] / 2 - ze - Dt - be - N.mainAxis
                    : Kn - Dt - be - N.mainAxis,
                Pc = w
                    ? -C[H] / 2 + ze + Dt + Ye + N.mainAxis
                    : Ee + Dt + Ye + N.mainAxis,
                qn = t.elements.arrow && Ie(t.elements.arrow),
                Mc = qn
                    ? A === "y"
                        ? qn.clientTop || 0
                        : qn.clientLeft || 0
                    : 0,
                ji = (M = P == null ? void 0 : P[A]) != null ? M : 0,
                kc = J + zn - ji - Mc,
                Fc = J + Pc - ji,
                Bi = Ce(E ? gn(Ke, kc) : Ke, J, E ? Mt(Nt, Fc) : Nt);
            (S[A] = Bi), (X[A] = Bi - J);
        }
        if (a) {
            var Hi,
                jc = A === "x" ? k : F,
                Bc = A === "x" ? V : W,
                Lt = S[T],
                Ge = T === "y" ? "height" : "width",
                Vi = Lt + p[jc],
                Wi = Lt - p[Bc],
                Yn = [k, F].indexOf(b) !== -1,
                Ui = (Hi = P == null ? void 0 : P[T]) != null ? Hi : 0,
                Ki = Yn ? Vi : Lt - C[Ge] - R[Ge] - Ui + N.altAxis,
                zi = Yn ? Lt + C[Ge] + R[Ge] - Ui - N.altAxis : Wi,
                qi =
                    E && Yn ? Kc(Ki, Lt, zi) : Ce(E ? Ki : Vi, Lt, E ? zi : Wi);
            (S[T] = qi), (X[T] = qi - Lt);
        }
        t.modifiersData[r] = X;
    }
}
const Oo = {
    name: "preventOverflow",
    enabled: !0,
    phase: "main",
    fn: pl,
    requiresIfExists: ["offset"],
};
function _l(e) {
    return { scrollLeft: e.scrollLeft, scrollTop: e.scrollTop };
}
function ml(e) {
    return e === U(e) || !K(e) ? ni(e) : _l(e);
}
function gl(e) {
    var t = e.getBoundingClientRect(),
        n = ee(t.width) / e.offsetWidth || 1,
        r = ee(t.height) / e.offsetHeight || 1;
    return n !== 1 || r !== 1;
}
function El(e, t, n) {
    n === void 0 && (n = !1);
    var r = K(t),
        i = K(t) && gl(t),
        s = Tt(t),
        o = ne(e, i, n),
        a = { scrollLeft: 0, scrollTop: 0 },
        c = { x: 0, y: 0 };
    return (
        (r || (!r && !n)) &&
            ((ot(t) !== "body" || ii(s)) && (a = ml(t)),
            K(t)
                ? ((c = ne(t, !0)), (c.x += t.clientLeft), (c.y += t.clientTop))
                : s && (c.x = ri(s))),
        {
            x: o.left + a.scrollLeft - c.x,
            y: o.top + a.scrollTop - c.y,
            width: o.width,
            height: o.height,
        }
    );
}
function bl(e) {
    var t = new Map(),
        n = new Set(),
        r = [];
    e.forEach(function (s) {
        t.set(s.name, s);
    });
    function i(s) {
        n.add(s.name);
        var o = [].concat(s.requires || [], s.requiresIfExists || []);
        o.forEach(function (a) {
            if (!n.has(a)) {
                var c = t.get(a);
                c && i(c);
            }
        }),
            r.push(s);
    }
    return (
        e.forEach(function (s) {
            n.has(s.name) || i(s);
        }),
        r
    );
}
function vl(e) {
    var t = bl(e);
    return po.reduce(function (n, r) {
        return n.concat(
            t.filter(function (i) {
                return i.phase === r;
            })
        );
    }, []);
}
function yl(e) {
    var t;
    return function () {
        return (
            t ||
                (t = new Promise(function (n) {
                    Promise.resolve().then(function () {
                        (t = void 0), n(e());
                    });
                })),
            t
        );
    };
}
function Al(e) {
    var t = e.reduce(function (n, r) {
        var i = n[r.name];
        return (
            (n[r.name] = i
                ? Object.assign({}, i, r, {
                      options: Object.assign({}, i.options, r.options),
                      data: Object.assign({}, i.data, r.data),
                  })
                : r),
            n
        );
    }, {});
    return Object.keys(t).map(function (n) {
        return t[n];
    });
}
var ts = { placement: "bottom", modifiers: [], strategy: "absolute" };
function es() {
    for (var e = arguments.length, t = new Array(e), n = 0; n < e; n++)
        t[n] = arguments[n];
    return !t.some(function (r) {
        return !(r && typeof r.getBoundingClientRect == "function");
    });
}
function Cn(e) {
    e === void 0 && (e = {});
    var t = e,
        n = t.defaultModifiers,
        r = n === void 0 ? [] : n,
        i = t.defaultOptions,
        s = i === void 0 ? ts : i;
    return function (a, c, u) {
        u === void 0 && (u = s);
        var l = {
                placement: "bottom",
                orderedModifiers: [],
                options: Object.assign({}, ts, s),
                modifiersData: {},
                elements: { reference: a, popper: c },
                attributes: {},
                styles: {},
            },
            f = [],
            m = !1,
            E = {
                state: l,
                setOptions: function (b) {
                    var y = typeof b == "function" ? b(l.options) : b;
                    _(),
                        (l.options = Object.assign({}, s, l.options, y)),
                        (l.scrollParents = {
                            reference: Vt(a)
                                ? xe(a)
                                : a.contextElement
                                ? xe(a.contextElement)
                                : [],
                            popper: xe(c),
                        });
                    var w = vl(Al([].concat(r, l.options.modifiers)));
                    return (
                        (l.orderedModifiers = w.filter(function (A) {
                            return A.enabled;
                        })),
                        g(),
                        E.update()
                    );
                },
                forceUpdate: function () {
                    if (!m) {
                        var b = l.elements,
                            y = b.reference,
                            w = b.popper;
                        if (es(y, w)) {
                            (l.rects = {
                                reference: El(
                                    y,
                                    Ie(w),
                                    l.options.strategy === "fixed"
                                ),
                                popper: Qr(w),
                            }),
                                (l.reset = !1),
                                (l.placement = l.options.placement),
                                l.orderedModifiers.forEach(function (N) {
                                    return (l.modifiersData[N.name] =
                                        Object.assign({}, N.data));
                                });
                            for (
                                var A = 0;
                                A < l.orderedModifiers.length;
                                A++
                            ) {
                                if (l.reset === !0) {
                                    (l.reset = !1), (A = -1);
                                    continue;
                                }
                                var T = l.orderedModifiers[A],
                                    S = T.fn,
                                    C = T.options,
                                    R = C === void 0 ? {} : C,
                                    D = T.name;
                                typeof S == "function" &&
                                    (l =
                                        S({
                                            state: l,
                                            options: R,
                                            name: D,
                                            instance: E,
                                        }) || l);
                            }
                        }
                    }
                },
                update: yl(function () {
                    return new Promise(function (p) {
                        E.forceUpdate(), p(l);
                    });
                }),
                destroy: function () {
                    _(), (m = !0);
                },
            };
        if (!es(a, c)) return E;
        E.setOptions(u).then(function (p) {
            !m && u.onFirstUpdate && u.onFirstUpdate(p);
        });
        function g() {
            l.orderedModifiers.forEach(function (p) {
                var b = p.name,
                    y = p.options,
                    w = y === void 0 ? {} : y,
                    A = p.effect;
                if (typeof A == "function") {
                    var T = A({ state: l, name: b, instance: E, options: w }),
                        S = function () {};
                    f.push(T || S);
                }
            });
        }
        function _() {
            f.forEach(function (p) {
                return p();
            }),
                (f = []);
        }
        return E;
    };
}
var wl = Cn(),
    Tl = [ei, si, ti, Jr],
    Sl = Cn({ defaultModifiers: Tl }),
    Ol = [ei, si, ti, Jr, So, wo, Oo, vo, To],
    oi = Cn({ defaultModifiers: Ol });
const Co = Object.freeze(
    Object.defineProperty(
        {
            __proto__: null,
            afterMain: lo,
            afterRead: oo,
            afterWrite: ho,
            applyStyles: Jr,
            arrow: vo,
            auto: Sn,
            basePlacements: ue,
            beforeMain: ao,
            beforeRead: io,
            beforeWrite: uo,
            bottom: V,
            clippingParents: no,
            computeStyles: ti,
            createPopper: oi,
            createPopperBase: wl,
            createPopperLite: Sl,
            detectOverflow: ie,
            end: te,
            eventListeners: ei,
            flip: wo,
            hide: To,
            left: F,
            main: co,
            modifierPhases: po,
            offset: So,
            placements: Gr,
            popper: Xt,
            popperGenerator: Cn,
            popperOffsets: si,
            preventOverflow: Oo,
            read: so,
            reference: ro,
            right: W,
            start: Ht,
            top: k,
            variationPlacements: Er,
            viewport: Yr,
            write: fo,
        },
        Symbol.toStringTag,
        { value: "Module" }
    )
);
/*!
 * Bootstrap v5.3.3 (https://getbootstrap.com/)
 * Copyright 2011-2024 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 */ const _t = new Map(),
    Gn = {
        set(e, t, n) {
            _t.has(e) || _t.set(e, new Map());
            const r = _t.get(e);
            if (!r.has(t) && r.size !== 0) {
                console.error(
                    `Bootstrap doesn't allow more than one instance per element. Bound instance: ${
                        Array.from(r.keys())[0]
                    }.`
                );
                return;
            }
            r.set(t, n);
        },
        get(e, t) {
            return (_t.has(e) && _t.get(e).get(t)) || null;
        },
        remove(e, t) {
            if (!_t.has(e)) return;
            const n = _t.get(e);
            n.delete(t), n.size === 0 && _t.delete(e);
        },
    },
    Cl = 1e6,
    xl = 1e3,
    yr = "transitionend",
    xo = (e) => (
        e &&
            window.CSS &&
            window.CSS.escape &&
            (e = e.replace(/#([^\s"#']+)/g, (t, n) => `#${CSS.escape(n)}`)),
        e
    ),
    Nl = (e) =>
        e == null
            ? `${e}`
            : Object.prototype.toString
                  .call(e)
                  .match(/\s([a-z]+)/i)[1]
                  .toLowerCase(),
    Dl = (e) => {
        do e += Math.floor(Math.random() * Cl);
        while (document.getElementById(e));
        return e;
    },
    Ll = (e) => {
        if (!e) return 0;
        let { transitionDuration: t, transitionDelay: n } =
            window.getComputedStyle(e);
        const r = Number.parseFloat(t),
            i = Number.parseFloat(n);
        return !r && !i
            ? 0
            : ((t = t.split(",")[0]),
              (n = n.split(",")[0]),
              (Number.parseFloat(t) + Number.parseFloat(n)) * xl);
    },
    No = (e) => {
        e.dispatchEvent(new Event(yr));
    },
    ct = (e) =>
        !e || typeof e != "object"
            ? !1
            : (typeof e.jquery < "u" && (e = e[0]), typeof e.nodeType < "u"),
    Et = (e) =>
        ct(e)
            ? e.jquery
                ? e[0]
                : e
            : typeof e == "string" && e.length > 0
            ? document.querySelector(xo(e))
            : null,
    fe = (e) => {
        if (!ct(e) || e.getClientRects().length === 0) return !1;
        const t =
                getComputedStyle(e).getPropertyValue("visibility") ===
                "visible",
            n = e.closest("details:not([open])");
        if (!n) return t;
        if (n !== e) {
            const r = e.closest("summary");
            if ((r && r.parentNode !== n) || r === null) return !1;
        }
        return t;
    },
    bt = (e) =>
        !e ||
        e.nodeType !== Node.ELEMENT_NODE ||
        e.classList.contains("disabled")
            ? !0
            : typeof e.disabled < "u"
            ? e.disabled
            : e.hasAttribute("disabled") &&
              e.getAttribute("disabled") !== "false",
    Do = (e) => {
        if (!document.documentElement.attachShadow) return null;
        if (typeof e.getRootNode == "function") {
            const t = e.getRootNode();
            return t instanceof ShadowRoot ? t : null;
        }
        return e instanceof ShadowRoot
            ? e
            : e.parentNode
            ? Do(e.parentNode)
            : null;
    },
    En = () => {},
    Pe = (e) => {
        e.offsetHeight;
    },
    Lo = () =>
        window.jQuery && !document.body.hasAttribute("data-bs-no-jquery")
            ? window.jQuery
            : null,
    Xn = [],
    $l = (e) => {
        document.readyState === "loading"
            ? (Xn.length ||
                  document.addEventListener("DOMContentLoaded", () => {
                      for (const t of Xn) t();
                  }),
              Xn.push(e))
            : e();
    },
    q = () => document.documentElement.dir === "rtl",
    G = (e) => {
        $l(() => {
            const t = Lo();
            if (t) {
                const n = e.NAME,
                    r = t.fn[n];
                (t.fn[n] = e.jQueryInterface),
                    (t.fn[n].Constructor = e),
                    (t.fn[n].noConflict = () => (
                        (t.fn[n] = r), e.jQueryInterface
                    ));
            }
        });
    },
    B = (e, t = [], n = e) => (typeof e == "function" ? e(...t) : n),
    $o = (e, t, n = !0) => {
        if (!n) {
            B(e);
            return;
        }
        const i = Ll(t) + 5;
        let s = !1;
        const o = ({ target: a }) => {
            a === t && ((s = !0), t.removeEventListener(yr, o), B(e));
        };
        t.addEventListener(yr, o),
            setTimeout(() => {
                s || No(t);
            }, i);
    },
    ai = (e, t, n, r) => {
        const i = e.length;
        let s = e.indexOf(t);
        return s === -1
            ? !n && r
                ? e[i - 1]
                : e[0]
            : ((s += n ? 1 : -1),
              r && (s = (s + i) % i),
              e[Math.max(0, Math.min(s, i - 1))]);
    },
    Rl = /[^.]*(?=\..*)\.|.*/,
    Il = /\..*/,
    Pl = /::\d+$/,
    Jn = {};
let ns = 1;
const Ro = { mouseenter: "mouseover", mouseleave: "mouseout" },
    Ml = new Set([
        "click",
        "dblclick",
        "mouseup",
        "mousedown",
        "contextmenu",
        "mousewheel",
        "DOMMouseScroll",
        "mouseover",
        "mouseout",
        "mousemove",
        "selectstart",
        "selectend",
        "keydown",
        "keypress",
        "keyup",
        "orientationchange",
        "touchstart",
        "touchmove",
        "touchend",
        "touchcancel",
        "pointerdown",
        "pointermove",
        "pointerup",
        "pointerleave",
        "pointercancel",
        "gesturestart",
        "gesturechange",
        "gestureend",
        "focus",
        "blur",
        "change",
        "reset",
        "select",
        "submit",
        "focusin",
        "focusout",
        "load",
        "unload",
        "beforeunload",
        "resize",
        "move",
        "DOMContentLoaded",
        "readystatechange",
        "error",
        "abort",
        "scroll",
    ]);
function Io(e, t) {
    return (t && `${t}::${ns++}`) || e.uidEvent || ns++;
}
function Po(e) {
    const t = Io(e);
    return (e.uidEvent = t), (Jn[t] = Jn[t] || {}), Jn[t];
}
function kl(e, t) {
    return function n(r) {
        return (
            ci(r, { delegateTarget: e }),
            n.oneOff && h.off(e, r.type, t),
            t.apply(e, [r])
        );
    };
}
function Fl(e, t, n) {
    return function r(i) {
        const s = e.querySelectorAll(t);
        for (let { target: o } = i; o && o !== this; o = o.parentNode)
            for (const a of s)
                if (a === o)
                    return (
                        ci(i, { delegateTarget: o }),
                        r.oneOff && h.off(e, i.type, t, n),
                        n.apply(o, [i])
                    );
    };
}
function Mo(e, t, n = null) {
    return Object.values(e).find(
        (r) => r.callable === t && r.delegationSelector === n
    );
}
function ko(e, t, n) {
    const r = typeof t == "string",
        i = r ? n : t || n;
    let s = Fo(e);
    return Ml.has(s) || (s = e), [r, i, s];
}
function rs(e, t, n, r, i) {
    if (typeof t != "string" || !e) return;
    let [s, o, a] = ko(t, n, r);
    t in Ro &&
        (o = ((g) =>
            function (_) {
                if (
                    !_.relatedTarget ||
                    (_.relatedTarget !== _.delegateTarget &&
                        !_.delegateTarget.contains(_.relatedTarget))
                )
                    return g.call(this, _);
            })(o));
    const c = Po(e),
        u = c[a] || (c[a] = {}),
        l = Mo(u, o, s ? n : null);
    if (l) {
        l.oneOff = l.oneOff && i;
        return;
    }
    const f = Io(o, t.replace(Rl, "")),
        m = s ? Fl(e, n, o) : kl(e, o);
    (m.delegationSelector = s ? n : null),
        (m.callable = o),
        (m.oneOff = i),
        (m.uidEvent = f),
        (u[f] = m),
        e.addEventListener(a, m, s);
}
function Ar(e, t, n, r, i) {
    const s = Mo(t[n], r, i);
    s && (e.removeEventListener(n, s, !!i), delete t[n][s.uidEvent]);
}
function jl(e, t, n, r) {
    const i = t[n] || {};
    for (const [s, o] of Object.entries(i))
        s.includes(r) && Ar(e, t, n, o.callable, o.delegationSelector);
}
function Fo(e) {
    return (e = e.replace(Il, "")), Ro[e] || e;
}
const h = {
    on(e, t, n, r) {
        rs(e, t, n, r, !1);
    },
    one(e, t, n, r) {
        rs(e, t, n, r, !0);
    },
    off(e, t, n, r) {
        if (typeof t != "string" || !e) return;
        const [i, s, o] = ko(t, n, r),
            a = o !== t,
            c = Po(e),
            u = c[o] || {},
            l = t.startsWith(".");
        if (typeof s < "u") {
            if (!Object.keys(u).length) return;
            Ar(e, c, o, s, i ? n : null);
            return;
        }
        if (l) for (const f of Object.keys(c)) jl(e, c, f, t.slice(1));
        for (const [f, m] of Object.entries(u)) {
            const E = f.replace(Pl, "");
            (!a || t.includes(E)) &&
                Ar(e, c, o, m.callable, m.delegationSelector);
        }
    },
    trigger(e, t, n) {
        if (typeof t != "string" || !e) return null;
        const r = Lo(),
            i = Fo(t),
            s = t !== i;
        let o = null,
            a = !0,
            c = !0,
            u = !1;
        s &&
            r &&
            ((o = r.Event(t, n)),
            r(e).trigger(o),
            (a = !o.isPropagationStopped()),
            (c = !o.isImmediatePropagationStopped()),
            (u = o.isDefaultPrevented()));
        const l = ci(new Event(t, { bubbles: a, cancelable: !0 }), n);
        return (
            u && l.preventDefault(),
            c && e.dispatchEvent(l),
            l.defaultPrevented && o && o.preventDefault(),
            l
        );
    },
};
function ci(e, t = {}) {
    for (const [n, r] of Object.entries(t))
        try {
            e[n] = r;
        } catch {
            Object.defineProperty(e, n, {
                configurable: !0,
                get() {
                    return r;
                },
            });
        }
    return e;
}
function is(e) {
    if (e === "true") return !0;
    if (e === "false") return !1;
    if (e === Number(e).toString()) return Number(e);
    if (e === "" || e === "null") return null;
    if (typeof e != "string") return e;
    try {
        return JSON.parse(decodeURIComponent(e));
    } catch {
        return e;
    }
}
function Qn(e) {
    return e.replace(/[A-Z]/g, (t) => `-${t.toLowerCase()}`);
}
const lt = {
    setDataAttribute(e, t, n) {
        e.setAttribute(`data-bs-${Qn(t)}`, n);
    },
    removeDataAttribute(e, t) {
        e.removeAttribute(`data-bs-${Qn(t)}`);
    },
    getDataAttributes(e) {
        if (!e) return {};
        const t = {},
            n = Object.keys(e.dataset).filter(
                (r) => r.startsWith("bs") && !r.startsWith("bsConfig")
            );
        for (const r of n) {
            let i = r.replace(/^bs/, "");
            (i = i.charAt(0).toLowerCase() + i.slice(1, i.length)),
                (t[i] = is(e.dataset[r]));
        }
        return t;
    },
    getDataAttribute(e, t) {
        return is(e.getAttribute(`data-bs-${Qn(t)}`));
    },
};
class Me {
    static get Default() {
        return {};
    }
    static get DefaultType() {
        return {};
    }
    static get NAME() {
        throw new Error(
            'You have to implement the static method "NAME", for each component!'
        );
    }
    _getConfig(t) {
        return (
            (t = this._mergeConfigObj(t)),
            (t = this._configAfterMerge(t)),
            this._typeCheckConfig(t),
            t
        );
    }
    _configAfterMerge(t) {
        return t;
    }
    _mergeConfigObj(t, n) {
        const r = ct(n) ? lt.getDataAttribute(n, "config") : {};
        return {
            ...this.constructor.Default,
            ...(typeof r == "object" ? r : {}),
            ...(ct(n) ? lt.getDataAttributes(n) : {}),
            ...(typeof t == "object" ? t : {}),
        };
    }
    _typeCheckConfig(t, n = this.constructor.DefaultType) {
        for (const [r, i] of Object.entries(n)) {
            const s = t[r],
                o = ct(s) ? "element" : Nl(s);
            if (!new RegExp(i).test(o))
                throw new TypeError(
                    `${this.constructor.NAME.toUpperCase()}: Option "${r}" provided type "${o}" but expected type "${i}".`
                );
        }
    }
}
const Bl = "5.3.3";
class tt extends Me {
    constructor(t, n) {
        super(),
            (t = Et(t)),
            t &&
                ((this._element = t),
                (this._config = this._getConfig(n)),
                Gn.set(this._element, this.constructor.DATA_KEY, this));
    }
    dispose() {
        Gn.remove(this._element, this.constructor.DATA_KEY),
            h.off(this._element, this.constructor.EVENT_KEY);
        for (const t of Object.getOwnPropertyNames(this)) this[t] = null;
    }
    _queueCallback(t, n, r = !0) {
        $o(t, n, r);
    }
    _getConfig(t) {
        return (
            (t = this._mergeConfigObj(t, this._element)),
            (t = this._configAfterMerge(t)),
            this._typeCheckConfig(t),
            t
        );
    }
    static getInstance(t) {
        return Gn.get(Et(t), this.DATA_KEY);
    }
    static getOrCreateInstance(t, n = {}) {
        return (
            this.getInstance(t) || new this(t, typeof n == "object" ? n : null)
        );
    }
    static get VERSION() {
        return Bl;
    }
    static get DATA_KEY() {
        return `bs.${this.NAME}`;
    }
    static get EVENT_KEY() {
        return `.${this.DATA_KEY}`;
    }
    static eventName(t) {
        return `${t}${this.EVENT_KEY}`;
    }
}
const Zn = (e) => {
        let t = e.getAttribute("data-bs-target");
        if (!t || t === "#") {
            let n = e.getAttribute("href");
            if (!n || (!n.includes("#") && !n.startsWith("."))) return null;
            n.includes("#") &&
                !n.startsWith("#") &&
                (n = `#${n.split("#")[1]}`),
                (t = n && n !== "#" ? n.trim() : null);
        }
        return t
            ? t
                  .split(",")
                  .map((n) => xo(n))
                  .join(",")
            : null;
    },
    v = {
        find(e, t = document.documentElement) {
            return [].concat(...Element.prototype.querySelectorAll.call(t, e));
        },
        findOne(e, t = document.documentElement) {
            return Element.prototype.querySelector.call(t, e);
        },
        children(e, t) {
            return [].concat(...e.children).filter((n) => n.matches(t));
        },
        parents(e, t) {
            const n = [];
            let r = e.parentNode.closest(t);
            for (; r; ) n.push(r), (r = r.parentNode.closest(t));
            return n;
        },
        prev(e, t) {
            let n = e.previousElementSibling;
            for (; n; ) {
                if (n.matches(t)) return [n];
                n = n.previousElementSibling;
            }
            return [];
        },
        next(e, t) {
            let n = e.nextElementSibling;
            for (; n; ) {
                if (n.matches(t)) return [n];
                n = n.nextElementSibling;
            }
            return [];
        },
        focusableChildren(e) {
            const t = [
                "a",
                "button",
                "input",
                "textarea",
                "select",
                "details",
                "[tabindex]",
                '[contenteditable="true"]',
            ]
                .map((n) => `${n}:not([tabindex^="-"])`)
                .join(",");
            return this.find(t, e).filter((n) => !bt(n) && fe(n));
        },
        getSelectorFromElement(e) {
            const t = Zn(e);
            return t && v.findOne(t) ? t : null;
        },
        getElementFromSelector(e) {
            const t = Zn(e);
            return t ? v.findOne(t) : null;
        },
        getMultipleElementsFromSelector(e) {
            const t = Zn(e);
            return t ? v.find(t) : [];
        },
    },
    xn = (e, t = "hide") => {
        const n = `click.dismiss${e.EVENT_KEY}`,
            r = e.NAME;
        h.on(document, n, `[data-bs-dismiss="${r}"]`, function (i) {
            if (
                (["A", "AREA"].includes(this.tagName) && i.preventDefault(),
                bt(this))
            )
                return;
            const s = v.getElementFromSelector(this) || this.closest(`.${r}`);
            e.getOrCreateInstance(s)[t]();
        });
    },
    Hl = "alert",
    Vl = "bs.alert",
    jo = `.${Vl}`,
    Wl = `close${jo}`,
    Ul = `closed${jo}`,
    Kl = "fade",
    zl = "show";
class Nn extends tt {
    static get NAME() {
        return Hl;
    }
    close() {
        if (h.trigger(this._element, Wl).defaultPrevented) return;
        this._element.classList.remove(zl);
        const n = this._element.classList.contains(Kl);
        this._queueCallback(() => this._destroyElement(), this._element, n);
    }
    _destroyElement() {
        this._element.remove(), h.trigger(this._element, Ul), this.dispose();
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = Nn.getOrCreateInstance(this);
            if (typeof t == "string") {
                if (n[t] === void 0 || t.startsWith("_") || t === "constructor")
                    throw new TypeError(`No method named "${t}"`);
                n[t](this);
            }
        });
    }
}
xn(Nn, "close");
G(Nn);
const ql = "button",
    Yl = "bs.button",
    Gl = `.${Yl}`,
    Xl = ".data-api",
    Jl = "active",
    ss = '[data-bs-toggle="button"]',
    Ql = `click${Gl}${Xl}`;
class Dn extends tt {
    static get NAME() {
        return ql;
    }
    toggle() {
        this._element.setAttribute(
            "aria-pressed",
            this._element.classList.toggle(Jl)
        );
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = Dn.getOrCreateInstance(this);
            t === "toggle" && n[t]();
        });
    }
}
h.on(document, Ql, ss, (e) => {
    e.preventDefault();
    const t = e.target.closest(ss);
    Dn.getOrCreateInstance(t).toggle();
});
G(Dn);
const Zl = "swipe",
    de = ".bs.swipe",
    tu = `touchstart${de}`,
    eu = `touchmove${de}`,
    nu = `touchend${de}`,
    ru = `pointerdown${de}`,
    iu = `pointerup${de}`,
    su = "touch",
    ou = "pen",
    au = "pointer-event",
    cu = 40,
    lu = { endCallback: null, leftCallback: null, rightCallback: null },
    uu = {
        endCallback: "(function|null)",
        leftCallback: "(function|null)",
        rightCallback: "(function|null)",
    };
class bn extends Me {
    constructor(t, n) {
        super(),
            (this._element = t),
            !(!t || !bn.isSupported()) &&
                ((this._config = this._getConfig(n)),
                (this._deltaX = 0),
                (this._supportPointerEvents = !!window.PointerEvent),
                this._initEvents());
    }
    static get Default() {
        return lu;
    }
    static get DefaultType() {
        return uu;
    }
    static get NAME() {
        return Zl;
    }
    dispose() {
        h.off(this._element, de);
    }
    _start(t) {
        if (!this._supportPointerEvents) {
            this._deltaX = t.touches[0].clientX;
            return;
        }
        this._eventIsPointerPenTouch(t) && (this._deltaX = t.clientX);
    }
    _end(t) {
        this._eventIsPointerPenTouch(t) &&
            (this._deltaX = t.clientX - this._deltaX),
            this._handleSwipe(),
            B(this._config.endCallback);
    }
    _move(t) {
        this._deltaX =
            t.touches && t.touches.length > 1
                ? 0
                : t.touches[0].clientX - this._deltaX;
    }
    _handleSwipe() {
        const t = Math.abs(this._deltaX);
        if (t <= cu) return;
        const n = t / this._deltaX;
        (this._deltaX = 0),
            n &&
                B(
                    n > 0
                        ? this._config.rightCallback
                        : this._config.leftCallback
                );
    }
    _initEvents() {
        this._supportPointerEvents
            ? (h.on(this._element, ru, (t) => this._start(t)),
              h.on(this._element, iu, (t) => this._end(t)),
              this._element.classList.add(au))
            : (h.on(this._element, tu, (t) => this._start(t)),
              h.on(this._element, eu, (t) => this._move(t)),
              h.on(this._element, nu, (t) => this._end(t)));
    }
    _eventIsPointerPenTouch(t) {
        return (
            this._supportPointerEvents &&
            (t.pointerType === ou || t.pointerType === su)
        );
    }
    static isSupported() {
        return (
            "ontouchstart" in document.documentElement ||
            navigator.maxTouchPoints > 0
        );
    }
}
const fu = "carousel",
    du = "bs.carousel",
    St = `.${du}`,
    Bo = ".data-api",
    hu = "ArrowLeft",
    pu = "ArrowRight",
    _u = 500,
    ve = "next",
    Yt = "prev",
    Jt = "left",
    un = "right",
    mu = `slide${St}`,
    tr = `slid${St}`,
    gu = `keydown${St}`,
    Eu = `mouseenter${St}`,
    bu = `mouseleave${St}`,
    vu = `dragstart${St}`,
    yu = `load${St}${Bo}`,
    Au = `click${St}${Bo}`,
    Ho = "carousel",
    Je = "active",
    wu = "slide",
    Tu = "carousel-item-end",
    Su = "carousel-item-start",
    Ou = "carousel-item-next",
    Cu = "carousel-item-prev",
    Vo = ".active",
    Wo = ".carousel-item",
    xu = Vo + Wo,
    Nu = ".carousel-item img",
    Du = ".carousel-indicators",
    Lu = "[data-bs-slide], [data-bs-slide-to]",
    $u = '[data-bs-ride="carousel"]',
    Ru = { [hu]: un, [pu]: Jt },
    Iu = {
        interval: 5e3,
        keyboard: !0,
        pause: "hover",
        ride: !1,
        touch: !0,
        wrap: !0,
    },
    Pu = {
        interval: "(number|boolean)",
        keyboard: "boolean",
        pause: "(string|boolean)",
        ride: "(boolean|string)",
        touch: "boolean",
        wrap: "boolean",
    };
class ke extends tt {
    constructor(t, n) {
        super(t, n),
            (this._interval = null),
            (this._activeElement = null),
            (this._isSliding = !1),
            (this.touchTimeout = null),
            (this._swipeHelper = null),
            (this._indicatorsElement = v.findOne(Du, this._element)),
            this._addEventListeners(),
            this._config.ride === Ho && this.cycle();
    }
    static get Default() {
        return Iu;
    }
    static get DefaultType() {
        return Pu;
    }
    static get NAME() {
        return fu;
    }
    next() {
        this._slide(ve);
    }
    nextWhenVisible() {
        !document.hidden && fe(this._element) && this.next();
    }
    prev() {
        this._slide(Yt);
    }
    pause() {
        this._isSliding && No(this._element), this._clearInterval();
    }
    cycle() {
        this._clearInterval(),
            this._updateInterval(),
            (this._interval = setInterval(
                () => this.nextWhenVisible(),
                this._config.interval
            ));
    }
    _maybeEnableCycle() {
        if (this._config.ride) {
            if (this._isSliding) {
                h.one(this._element, tr, () => this.cycle());
                return;
            }
            this.cycle();
        }
    }
    to(t) {
        const n = this._getItems();
        if (t > n.length - 1 || t < 0) return;
        if (this._isSliding) {
            h.one(this._element, tr, () => this.to(t));
            return;
        }
        const r = this._getItemIndex(this._getActive());
        if (r === t) return;
        const i = t > r ? ve : Yt;
        this._slide(i, n[t]);
    }
    dispose() {
        this._swipeHelper && this._swipeHelper.dispose(), super.dispose();
    }
    _configAfterMerge(t) {
        return (t.defaultInterval = t.interval), t;
    }
    _addEventListeners() {
        this._config.keyboard &&
            h.on(this._element, gu, (t) => this._keydown(t)),
            this._config.pause === "hover" &&
                (h.on(this._element, Eu, () => this.pause()),
                h.on(this._element, bu, () => this._maybeEnableCycle())),
            this._config.touch &&
                bn.isSupported() &&
                this._addTouchEventListeners();
    }
    _addTouchEventListeners() {
        for (const r of v.find(Nu, this._element))
            h.on(r, vu, (i) => i.preventDefault());
        const n = {
            leftCallback: () => this._slide(this._directionToOrder(Jt)),
            rightCallback: () => this._slide(this._directionToOrder(un)),
            endCallback: () => {
                this._config.pause === "hover" &&
                    (this.pause(),
                    this.touchTimeout && clearTimeout(this.touchTimeout),
                    (this.touchTimeout = setTimeout(
                        () => this._maybeEnableCycle(),
                        _u + this._config.interval
                    )));
            },
        };
        this._swipeHelper = new bn(this._element, n);
    }
    _keydown(t) {
        if (/input|textarea/i.test(t.target.tagName)) return;
        const n = Ru[t.key];
        n && (t.preventDefault(), this._slide(this._directionToOrder(n)));
    }
    _getItemIndex(t) {
        return this._getItems().indexOf(t);
    }
    _setActiveIndicatorElement(t) {
        if (!this._indicatorsElement) return;
        const n = v.findOne(Vo, this._indicatorsElement);
        n.classList.remove(Je), n.removeAttribute("aria-current");
        const r = v.findOne(
            `[data-bs-slide-to="${t}"]`,
            this._indicatorsElement
        );
        r && (r.classList.add(Je), r.setAttribute("aria-current", "true"));
    }
    _updateInterval() {
        const t = this._activeElement || this._getActive();
        if (!t) return;
        const n = Number.parseInt(t.getAttribute("data-bs-interval"), 10);
        this._config.interval = n || this._config.defaultInterval;
    }
    _slide(t, n = null) {
        if (this._isSliding) return;
        const r = this._getActive(),
            i = t === ve,
            s = n || ai(this._getItems(), r, i, this._config.wrap);
        if (s === r) return;
        const o = this._getItemIndex(s),
            a = (E) =>
                h.trigger(this._element, E, {
                    relatedTarget: s,
                    direction: this._orderToDirection(t),
                    from: this._getItemIndex(r),
                    to: o,
                });
        if (a(mu).defaultPrevented || !r || !s) return;
        const u = !!this._interval;
        this.pause(),
            (this._isSliding = !0),
            this._setActiveIndicatorElement(o),
            (this._activeElement = s);
        const l = i ? Su : Tu,
            f = i ? Ou : Cu;
        s.classList.add(f), Pe(s), r.classList.add(l), s.classList.add(l);
        const m = () => {
            s.classList.remove(l, f),
                s.classList.add(Je),
                r.classList.remove(Je, f, l),
                (this._isSliding = !1),
                a(tr);
        };
        this._queueCallback(m, r, this._isAnimated()), u && this.cycle();
    }
    _isAnimated() {
        return this._element.classList.contains(wu);
    }
    _getActive() {
        return v.findOne(xu, this._element);
    }
    _getItems() {
        return v.find(Wo, this._element);
    }
    _clearInterval() {
        this._interval &&
            (clearInterval(this._interval), (this._interval = null));
    }
    _directionToOrder(t) {
        return q() ? (t === Jt ? Yt : ve) : t === Jt ? ve : Yt;
    }
    _orderToDirection(t) {
        return q() ? (t === Yt ? Jt : un) : t === Yt ? un : Jt;
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = ke.getOrCreateInstance(this, t);
            if (typeof t == "number") {
                n.to(t);
                return;
            }
            if (typeof t == "string") {
                if (n[t] === void 0 || t.startsWith("_") || t === "constructor")
                    throw new TypeError(`No method named "${t}"`);
                n[t]();
            }
        });
    }
}
h.on(document, Au, Lu, function (e) {
    const t = v.getElementFromSelector(this);
    if (!t || !t.classList.contains(Ho)) return;
    e.preventDefault();
    const n = ke.getOrCreateInstance(t),
        r = this.getAttribute("data-bs-slide-to");
    if (r) {
        n.to(r), n._maybeEnableCycle();
        return;
    }
    if (lt.getDataAttribute(this, "slide") === "next") {
        n.next(), n._maybeEnableCycle();
        return;
    }
    n.prev(), n._maybeEnableCycle();
});
h.on(window, yu, () => {
    const e = v.find($u);
    for (const t of e) ke.getOrCreateInstance(t);
});
G(ke);
const Mu = "collapse",
    ku = "bs.collapse",
    Fe = `.${ku}`,
    Fu = ".data-api",
    ju = `show${Fe}`,
    Bu = `shown${Fe}`,
    Hu = `hide${Fe}`,
    Vu = `hidden${Fe}`,
    Wu = `click${Fe}${Fu}`,
    er = "show",
    Zt = "collapse",
    Qe = "collapsing",
    Uu = "collapsed",
    Ku = `:scope .${Zt} .${Zt}`,
    zu = "collapse-horizontal",
    qu = "width",
    Yu = "height",
    Gu = ".collapse.show, .collapse.collapsing",
    wr = '[data-bs-toggle="collapse"]',
    Xu = { parent: null, toggle: !0 },
    Ju = { parent: "(null|element)", toggle: "boolean" };
class Le extends tt {
    constructor(t, n) {
        super(t, n), (this._isTransitioning = !1), (this._triggerArray = []);
        const r = v.find(wr);
        for (const i of r) {
            const s = v.getSelectorFromElement(i),
                o = v.find(s).filter((a) => a === this._element);
            s !== null && o.length && this._triggerArray.push(i);
        }
        this._initializeChildren(),
            this._config.parent ||
                this._addAriaAndCollapsedClass(
                    this._triggerArray,
                    this._isShown()
                ),
            this._config.toggle && this.toggle();
    }
    static get Default() {
        return Xu;
    }
    static get DefaultType() {
        return Ju;
    }
    static get NAME() {
        return Mu;
    }
    toggle() {
        this._isShown() ? this.hide() : this.show();
    }
    show() {
        if (this._isTransitioning || this._isShown()) return;
        let t = [];
        if (
            (this._config.parent &&
                (t = this._getFirstLevelChildren(Gu)
                    .filter((a) => a !== this._element)
                    .map((a) => Le.getOrCreateInstance(a, { toggle: !1 }))),
            (t.length && t[0]._isTransitioning) ||
                h.trigger(this._element, ju).defaultPrevented)
        )
            return;
        for (const a of t) a.hide();
        const r = this._getDimension();
        this._element.classList.remove(Zt),
            this._element.classList.add(Qe),
            (this._element.style[r] = 0),
            this._addAriaAndCollapsedClass(this._triggerArray, !0),
            (this._isTransitioning = !0);
        const i = () => {
                (this._isTransitioning = !1),
                    this._element.classList.remove(Qe),
                    this._element.classList.add(Zt, er),
                    (this._element.style[r] = ""),
                    h.trigger(this._element, Bu);
            },
            o = `scroll${r[0].toUpperCase() + r.slice(1)}`;
        this._queueCallback(i, this._element, !0),
            (this._element.style[r] = `${this._element[o]}px`);
    }
    hide() {
        if (
            this._isTransitioning ||
            !this._isShown() ||
            h.trigger(this._element, Hu).defaultPrevented
        )
            return;
        const n = this._getDimension();
        (this._element.style[n] = `${
            this._element.getBoundingClientRect()[n]
        }px`),
            Pe(this._element),
            this._element.classList.add(Qe),
            this._element.classList.remove(Zt, er);
        for (const i of this._triggerArray) {
            const s = v.getElementFromSelector(i);
            s && !this._isShown(s) && this._addAriaAndCollapsedClass([i], !1);
        }
        this._isTransitioning = !0;
        const r = () => {
            (this._isTransitioning = !1),
                this._element.classList.remove(Qe),
                this._element.classList.add(Zt),
                h.trigger(this._element, Vu);
        };
        (this._element.style[n] = ""),
            this._queueCallback(r, this._element, !0);
    }
    _isShown(t = this._element) {
        return t.classList.contains(er);
    }
    _configAfterMerge(t) {
        return (t.toggle = !!t.toggle), (t.parent = Et(t.parent)), t;
    }
    _getDimension() {
        return this._element.classList.contains(zu) ? qu : Yu;
    }
    _initializeChildren() {
        if (!this._config.parent) return;
        const t = this._getFirstLevelChildren(wr);
        for (const n of t) {
            const r = v.getElementFromSelector(n);
            r && this._addAriaAndCollapsedClass([n], this._isShown(r));
        }
    }
    _getFirstLevelChildren(t) {
        const n = v.find(Ku, this._config.parent);
        return v.find(t, this._config.parent).filter((r) => !n.includes(r));
    }
    _addAriaAndCollapsedClass(t, n) {
        if (t.length)
            for (const r of t)
                r.classList.toggle(Uu, !n), r.setAttribute("aria-expanded", n);
    }
    static jQueryInterface(t) {
        const n = {};
        return (
            typeof t == "string" && /show|hide/.test(t) && (n.toggle = !1),
            this.each(function () {
                const r = Le.getOrCreateInstance(this, n);
                if (typeof t == "string") {
                    if (typeof r[t] > "u")
                        throw new TypeError(`No method named "${t}"`);
                    r[t]();
                }
            })
        );
    }
}
h.on(document, Wu, wr, function (e) {
    (e.target.tagName === "A" ||
        (e.delegateTarget && e.delegateTarget.tagName === "A")) &&
        e.preventDefault();
    for (const t of v.getMultipleElementsFromSelector(this))
        Le.getOrCreateInstance(t, { toggle: !1 }).toggle();
});
G(Le);
const os = "dropdown",
    Qu = "bs.dropdown",
    Wt = `.${Qu}`,
    li = ".data-api",
    Zu = "Escape",
    as = "Tab",
    tf = "ArrowUp",
    cs = "ArrowDown",
    ef = 2,
    nf = `hide${Wt}`,
    rf = `hidden${Wt}`,
    sf = `show${Wt}`,
    of = `shown${Wt}`,
    Uo = `click${Wt}${li}`,
    Ko = `keydown${Wt}${li}`,
    af = `keyup${Wt}${li}`,
    Qt = "show",
    cf = "dropup",
    lf = "dropend",
    uf = "dropstart",
    ff = "dropup-center",
    df = "dropdown-center",
    Rt = '[data-bs-toggle="dropdown"]:not(.disabled):not(:disabled)',
    hf = `${Rt}.${Qt}`,
    fn = ".dropdown-menu",
    pf = ".navbar",
    _f = ".navbar-nav",
    mf = ".dropdown-menu .dropdown-item:not(.disabled):not(:disabled)",
    gf = q() ? "top-end" : "top-start",
    Ef = q() ? "top-start" : "top-end",
    bf = q() ? "bottom-end" : "bottom-start",
    vf = q() ? "bottom-start" : "bottom-end",
    yf = q() ? "left-start" : "right-start",
    Af = q() ? "right-start" : "left-start",
    wf = "top",
    Tf = "bottom",
    Sf = {
        autoClose: !0,
        boundary: "clippingParents",
        display: "dynamic",
        offset: [0, 2],
        popperConfig: null,
        reference: "toggle",
    },
    Of = {
        autoClose: "(boolean|string)",
        boundary: "(string|element)",
        display: "string",
        offset: "(array|string|function)",
        popperConfig: "(null|object|function)",
        reference: "(string|element|object)",
    };
class st extends tt {
    constructor(t, n) {
        super(t, n),
            (this._popper = null),
            (this._parent = this._element.parentNode),
            (this._menu =
                v.next(this._element, fn)[0] ||
                v.prev(this._element, fn)[0] ||
                v.findOne(fn, this._parent)),
            (this._inNavbar = this._detectNavbar());
    }
    static get Default() {
        return Sf;
    }
    static get DefaultType() {
        return Of;
    }
    static get NAME() {
        return os;
    }
    toggle() {
        return this._isShown() ? this.hide() : this.show();
    }
    show() {
        if (bt(this._element) || this._isShown()) return;
        const t = { relatedTarget: this._element };
        if (!h.trigger(this._element, sf, t).defaultPrevented) {
            if (
                (this._createPopper(),
                "ontouchstart" in document.documentElement &&
                    !this._parent.closest(_f))
            )
                for (const r of [].concat(...document.body.children))
                    h.on(r, "mouseover", En);
            this._element.focus(),
                this._element.setAttribute("aria-expanded", !0),
                this._menu.classList.add(Qt),
                this._element.classList.add(Qt),
                h.trigger(this._element, of, t);
        }
    }
    hide() {
        if (bt(this._element) || !this._isShown()) return;
        const t = { relatedTarget: this._element };
        this._completeHide(t);
    }
    dispose() {
        this._popper && this._popper.destroy(), super.dispose();
    }
    update() {
        (this._inNavbar = this._detectNavbar()),
            this._popper && this._popper.update();
    }
    _completeHide(t) {
        if (!h.trigger(this._element, nf, t).defaultPrevented) {
            if ("ontouchstart" in document.documentElement)
                for (const r of [].concat(...document.body.children))
                    h.off(r, "mouseover", En);
            this._popper && this._popper.destroy(),
                this._menu.classList.remove(Qt),
                this._element.classList.remove(Qt),
                this._element.setAttribute("aria-expanded", "false"),
                lt.removeDataAttribute(this._menu, "popper"),
                h.trigger(this._element, rf, t);
        }
    }
    _getConfig(t) {
        if (
            ((t = super._getConfig(t)),
            typeof t.reference == "object" &&
                !ct(t.reference) &&
                typeof t.reference.getBoundingClientRect != "function")
        )
            throw new TypeError(
                `${os.toUpperCase()}: Option "reference" provided type "object" without a required "getBoundingClientRect" method.`
            );
        return t;
    }
    _createPopper() {
        if (typeof Co > "u")
            throw new TypeError(
                "Bootstrap's dropdowns require Popper (https://popper.js.org)"
            );
        let t = this._element;
        this._config.reference === "parent"
            ? (t = this._parent)
            : ct(this._config.reference)
            ? (t = Et(this._config.reference))
            : typeof this._config.reference == "object" &&
              (t = this._config.reference);
        const n = this._getPopperConfig();
        this._popper = oi(t, this._menu, n);
    }
    _isShown() {
        return this._menu.classList.contains(Qt);
    }
    _getPlacement() {
        const t = this._parent;
        if (t.classList.contains(lf)) return yf;
        if (t.classList.contains(uf)) return Af;
        if (t.classList.contains(ff)) return wf;
        if (t.classList.contains(df)) return Tf;
        const n =
            getComputedStyle(this._menu)
                .getPropertyValue("--bs-position")
                .trim() === "end";
        return t.classList.contains(cf) ? (n ? Ef : gf) : n ? vf : bf;
    }
    _detectNavbar() {
        return this._element.closest(pf) !== null;
    }
    _getOffset() {
        const { offset: t } = this._config;
        return typeof t == "string"
            ? t.split(",").map((n) => Number.parseInt(n, 10))
            : typeof t == "function"
            ? (n) => t(n, this._element)
            : t;
    }
    _getPopperConfig() {
        const t = {
            placement: this._getPlacement(),
            modifiers: [
                {
                    name: "preventOverflow",
                    options: { boundary: this._config.boundary },
                },
                { name: "offset", options: { offset: this._getOffset() } },
            ],
        };
        return (
            (this._inNavbar || this._config.display === "static") &&
                (lt.setDataAttribute(this._menu, "popper", "static"),
                (t.modifiers = [{ name: "applyStyles", enabled: !1 }])),
            { ...t, ...B(this._config.popperConfig, [t]) }
        );
    }
    _selectMenuItem({ key: t, target: n }) {
        const r = v.find(mf, this._menu).filter((i) => fe(i));
        r.length && ai(r, n, t === cs, !r.includes(n)).focus();
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = st.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (typeof n[t] > "u")
                    throw new TypeError(`No method named "${t}"`);
                n[t]();
            }
        });
    }
    static clearMenus(t) {
        if (t.button === ef || (t.type === "keyup" && t.key !== as)) return;
        const n = v.find(hf);
        for (const r of n) {
            const i = st.getInstance(r);
            if (!i || i._config.autoClose === !1) continue;
            const s = t.composedPath(),
                o = s.includes(i._menu);
            if (
                s.includes(i._element) ||
                (i._config.autoClose === "inside" && !o) ||
                (i._config.autoClose === "outside" && o) ||
                (i._menu.contains(t.target) &&
                    ((t.type === "keyup" && t.key === as) ||
                        /input|select|option|textarea|form/i.test(
                            t.target.tagName
                        )))
            )
                continue;
            const a = { relatedTarget: i._element };
            t.type === "click" && (a.clickEvent = t), i._completeHide(a);
        }
    }
    static dataApiKeydownHandler(t) {
        const n = /input|textarea/i.test(t.target.tagName),
            r = t.key === Zu,
            i = [tf, cs].includes(t.key);
        if ((!i && !r) || (n && !r)) return;
        t.preventDefault();
        const s = this.matches(Rt)
                ? this
                : v.prev(this, Rt)[0] ||
                  v.next(this, Rt)[0] ||
                  v.findOne(Rt, t.delegateTarget.parentNode),
            o = st.getOrCreateInstance(s);
        if (i) {
            t.stopPropagation(), o.show(), o._selectMenuItem(t);
            return;
        }
        o._isShown() && (t.stopPropagation(), o.hide(), s.focus());
    }
}
h.on(document, Ko, Rt, st.dataApiKeydownHandler);
h.on(document, Ko, fn, st.dataApiKeydownHandler);
h.on(document, Uo, st.clearMenus);
h.on(document, af, st.clearMenus);
h.on(document, Uo, Rt, function (e) {
    e.preventDefault(), st.getOrCreateInstance(this).toggle();
});
G(st);
const zo = "backdrop",
    Cf = "fade",
    ls = "show",
    us = `mousedown.bs.${zo}`,
    xf = {
        className: "modal-backdrop",
        clickCallback: null,
        isAnimated: !1,
        isVisible: !0,
        rootElement: "body",
    },
    Nf = {
        className: "string",
        clickCallback: "(function|null)",
        isAnimated: "boolean",
        isVisible: "boolean",
        rootElement: "(element|string)",
    };
class qo extends Me {
    constructor(t) {
        super(),
            (this._config = this._getConfig(t)),
            (this._isAppended = !1),
            (this._element = null);
    }
    static get Default() {
        return xf;
    }
    static get DefaultType() {
        return Nf;
    }
    static get NAME() {
        return zo;
    }
    show(t) {
        if (!this._config.isVisible) {
            B(t);
            return;
        }
        this._append();
        const n = this._getElement();
        this._config.isAnimated && Pe(n),
            n.classList.add(ls),
            this._emulateAnimation(() => {
                B(t);
            });
    }
    hide(t) {
        if (!this._config.isVisible) {
            B(t);
            return;
        }
        this._getElement().classList.remove(ls),
            this._emulateAnimation(() => {
                this.dispose(), B(t);
            });
    }
    dispose() {
        this._isAppended &&
            (h.off(this._element, us),
            this._element.remove(),
            (this._isAppended = !1));
    }
    _getElement() {
        if (!this._element) {
            const t = document.createElement("div");
            (t.className = this._config.className),
                this._config.isAnimated && t.classList.add(Cf),
                (this._element = t);
        }
        return this._element;
    }
    _configAfterMerge(t) {
        return (t.rootElement = Et(t.rootElement)), t;
    }
    _append() {
        if (this._isAppended) return;
        const t = this._getElement();
        this._config.rootElement.append(t),
            h.on(t, us, () => {
                B(this._config.clickCallback);
            }),
            (this._isAppended = !0);
    }
    _emulateAnimation(t) {
        $o(t, this._getElement(), this._config.isAnimated);
    }
}
const Df = "focustrap",
    Lf = "bs.focustrap",
    vn = `.${Lf}`,
    $f = `focusin${vn}`,
    Rf = `keydown.tab${vn}`,
    If = "Tab",
    Pf = "forward",
    fs = "backward",
    Mf = { autofocus: !0, trapElement: null },
    kf = { autofocus: "boolean", trapElement: "element" };
class Yo extends Me {
    constructor(t) {
        super(),
            (this._config = this._getConfig(t)),
            (this._isActive = !1),
            (this._lastTabNavDirection = null);
    }
    static get Default() {
        return Mf;
    }
    static get DefaultType() {
        return kf;
    }
    static get NAME() {
        return Df;
    }
    activate() {
        this._isActive ||
            (this._config.autofocus && this._config.trapElement.focus(),
            h.off(document, vn),
            h.on(document, $f, (t) => this._handleFocusin(t)),
            h.on(document, Rf, (t) => this._handleKeydown(t)),
            (this._isActive = !0));
    }
    deactivate() {
        this._isActive && ((this._isActive = !1), h.off(document, vn));
    }
    _handleFocusin(t) {
        const { trapElement: n } = this._config;
        if (t.target === document || t.target === n || n.contains(t.target))
            return;
        const r = v.focusableChildren(n);
        r.length === 0
            ? n.focus()
            : this._lastTabNavDirection === fs
            ? r[r.length - 1].focus()
            : r[0].focus();
    }
    _handleKeydown(t) {
        t.key === If && (this._lastTabNavDirection = t.shiftKey ? fs : Pf);
    }
}
const ds = ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top",
    hs = ".sticky-top",
    Ze = "padding-right",
    ps = "margin-right";
class Tr {
    constructor() {
        this._element = document.body;
    }
    getWidth() {
        const t = document.documentElement.clientWidth;
        return Math.abs(window.innerWidth - t);
    }
    hide() {
        const t = this.getWidth();
        this._disableOverFlow(),
            this._setElementAttributes(this._element, Ze, (n) => n + t),
            this._setElementAttributes(ds, Ze, (n) => n + t),
            this._setElementAttributes(hs, ps, (n) => n - t);
    }
    reset() {
        this._resetElementAttributes(this._element, "overflow"),
            this._resetElementAttributes(this._element, Ze),
            this._resetElementAttributes(ds, Ze),
            this._resetElementAttributes(hs, ps);
    }
    isOverflowing() {
        return this.getWidth() > 0;
    }
    _disableOverFlow() {
        this._saveInitialAttribute(this._element, "overflow"),
            (this._element.style.overflow = "hidden");
    }
    _setElementAttributes(t, n, r) {
        const i = this.getWidth(),
            s = (o) => {
                if (
                    o !== this._element &&
                    window.innerWidth > o.clientWidth + i
                )
                    return;
                this._saveInitialAttribute(o, n);
                const a = window.getComputedStyle(o).getPropertyValue(n);
                o.style.setProperty(n, `${r(Number.parseFloat(a))}px`);
            };
        this._applyManipulationCallback(t, s);
    }
    _saveInitialAttribute(t, n) {
        const r = t.style.getPropertyValue(n);
        r && lt.setDataAttribute(t, n, r);
    }
    _resetElementAttributes(t, n) {
        const r = (i) => {
            const s = lt.getDataAttribute(i, n);
            if (s === null) {
                i.style.removeProperty(n);
                return;
            }
            lt.removeDataAttribute(i, n), i.style.setProperty(n, s);
        };
        this._applyManipulationCallback(t, r);
    }
    _applyManipulationCallback(t, n) {
        if (ct(t)) {
            n(t);
            return;
        }
        for (const r of v.find(t, this._element)) n(r);
    }
}
const Ff = "modal",
    jf = "bs.modal",
    Y = `.${jf}`,
    Bf = ".data-api",
    Hf = "Escape",
    Vf = `hide${Y}`,
    Wf = `hidePrevented${Y}`,
    Go = `hidden${Y}`,
    Xo = `show${Y}`,
    Uf = `shown${Y}`,
    Kf = `resize${Y}`,
    zf = `click.dismiss${Y}`,
    qf = `mousedown.dismiss${Y}`,
    Yf = `keydown.dismiss${Y}`,
    Gf = `click${Y}${Bf}`,
    _s = "modal-open",
    Xf = "fade",
    ms = "show",
    nr = "modal-static",
    Jf = ".modal.show",
    Qf = ".modal-dialog",
    Zf = ".modal-body",
    td = '[data-bs-toggle="modal"]',
    ed = { backdrop: !0, focus: !0, keyboard: !0 },
    nd = {
        backdrop: "(boolean|string)",
        focus: "boolean",
        keyboard: "boolean",
    };
class se extends tt {
    constructor(t, n) {
        super(t, n),
            (this._dialog = v.findOne(Qf, this._element)),
            (this._backdrop = this._initializeBackDrop()),
            (this._focustrap = this._initializeFocusTrap()),
            (this._isShown = !1),
            (this._isTransitioning = !1),
            (this._scrollBar = new Tr()),
            this._addEventListeners();
    }
    static get Default() {
        return ed;
    }
    static get DefaultType() {
        return nd;
    }
    static get NAME() {
        return Ff;
    }
    toggle(t) {
        return this._isShown ? this.hide() : this.show(t);
    }
    show(t) {
        this._isShown ||
            this._isTransitioning ||
            h.trigger(this._element, Xo, { relatedTarget: t })
                .defaultPrevented ||
            ((this._isShown = !0),
            (this._isTransitioning = !0),
            this._scrollBar.hide(),
            document.body.classList.add(_s),
            this._adjustDialog(),
            this._backdrop.show(() => this._showElement(t)));
    }
    hide() {
        !this._isShown ||
            this._isTransitioning ||
            h.trigger(this._element, Vf).defaultPrevented ||
            ((this._isShown = !1),
            (this._isTransitioning = !0),
            this._focustrap.deactivate(),
            this._element.classList.remove(ms),
            this._queueCallback(
                () => this._hideModal(),
                this._element,
                this._isAnimated()
            ));
    }
    dispose() {
        h.off(window, Y),
            h.off(this._dialog, Y),
            this._backdrop.dispose(),
            this._focustrap.deactivate(),
            super.dispose();
    }
    handleUpdate() {
        this._adjustDialog();
    }
    _initializeBackDrop() {
        return new qo({
            isVisible: !!this._config.backdrop,
            isAnimated: this._isAnimated(),
        });
    }
    _initializeFocusTrap() {
        return new Yo({ trapElement: this._element });
    }
    _showElement(t) {
        document.body.contains(this._element) ||
            document.body.append(this._element),
            (this._element.style.display = "block"),
            this._element.removeAttribute("aria-hidden"),
            this._element.setAttribute("aria-modal", !0),
            this._element.setAttribute("role", "dialog"),
            (this._element.scrollTop = 0);
        const n = v.findOne(Zf, this._dialog);
        n && (n.scrollTop = 0),
            Pe(this._element),
            this._element.classList.add(ms);
        const r = () => {
            this._config.focus && this._focustrap.activate(),
                (this._isTransitioning = !1),
                h.trigger(this._element, Uf, { relatedTarget: t });
        };
        this._queueCallback(r, this._dialog, this._isAnimated());
    }
    _addEventListeners() {
        h.on(this._element, Yf, (t) => {
            if (t.key === Hf) {
                if (this._config.keyboard) {
                    this.hide();
                    return;
                }
                this._triggerBackdropTransition();
            }
        }),
            h.on(window, Kf, () => {
                this._isShown && !this._isTransitioning && this._adjustDialog();
            }),
            h.on(this._element, qf, (t) => {
                h.one(this._element, zf, (n) => {
                    if (
                        !(
                            this._element !== t.target ||
                            this._element !== n.target
                        )
                    ) {
                        if (this._config.backdrop === "static") {
                            this._triggerBackdropTransition();
                            return;
                        }
                        this._config.backdrop && this.hide();
                    }
                });
            });
    }
    _hideModal() {
        (this._element.style.display = "none"),
            this._element.setAttribute("aria-hidden", !0),
            this._element.removeAttribute("aria-modal"),
            this._element.removeAttribute("role"),
            (this._isTransitioning = !1),
            this._backdrop.hide(() => {
                document.body.classList.remove(_s),
                    this._resetAdjustments(),
                    this._scrollBar.reset(),
                    h.trigger(this._element, Go);
            });
    }
    _isAnimated() {
        return this._element.classList.contains(Xf);
    }
    _triggerBackdropTransition() {
        if (h.trigger(this._element, Wf).defaultPrevented) return;
        const n =
                this._element.scrollHeight >
                document.documentElement.clientHeight,
            r = this._element.style.overflowY;
        r === "hidden" ||
            this._element.classList.contains(nr) ||
            (n || (this._element.style.overflowY = "hidden"),
            this._element.classList.add(nr),
            this._queueCallback(() => {
                this._element.classList.remove(nr),
                    this._queueCallback(() => {
                        this._element.style.overflowY = r;
                    }, this._dialog);
            }, this._dialog),
            this._element.focus());
    }
    _adjustDialog() {
        const t =
                this._element.scrollHeight >
                document.documentElement.clientHeight,
            n = this._scrollBar.getWidth(),
            r = n > 0;
        if (r && !t) {
            const i = q() ? "paddingLeft" : "paddingRight";
            this._element.style[i] = `${n}px`;
        }
        if (!r && t) {
            const i = q() ? "paddingRight" : "paddingLeft";
            this._element.style[i] = `${n}px`;
        }
    }
    _resetAdjustments() {
        (this._element.style.paddingLeft = ""),
            (this._element.style.paddingRight = "");
    }
    static jQueryInterface(t, n) {
        return this.each(function () {
            const r = se.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (typeof r[t] > "u")
                    throw new TypeError(`No method named "${t}"`);
                r[t](n);
            }
        });
    }
}
h.on(document, Gf, td, function (e) {
    const t = v.getElementFromSelector(this);
    ["A", "AREA"].includes(this.tagName) && e.preventDefault(),
        h.one(t, Xo, (i) => {
            i.defaultPrevented ||
                h.one(t, Go, () => {
                    fe(this) && this.focus();
                });
        });
    const n = v.findOne(Jf);
    n && se.getInstance(n).hide(), se.getOrCreateInstance(t).toggle(this);
});
xn(se);
G(se);
const rd = "offcanvas",
    id = "bs.offcanvas",
    ht = `.${id}`,
    Jo = ".data-api",
    sd = `load${ht}${Jo}`,
    od = "Escape",
    gs = "show",
    Es = "showing",
    bs = "hiding",
    ad = "offcanvas-backdrop",
    Qo = ".offcanvas.show",
    cd = `show${ht}`,
    ld = `shown${ht}`,
    ud = `hide${ht}`,
    vs = `hidePrevented${ht}`,
    Zo = `hidden${ht}`,
    fd = `resize${ht}`,
    dd = `click${ht}${Jo}`,
    hd = `keydown.dismiss${ht}`,
    pd = '[data-bs-toggle="offcanvas"]',
    _d = { backdrop: !0, keyboard: !0, scroll: !1 },
    md = {
        backdrop: "(boolean|string)",
        keyboard: "boolean",
        scroll: "boolean",
    };
class vt extends tt {
    constructor(t, n) {
        super(t, n),
            (this._isShown = !1),
            (this._backdrop = this._initializeBackDrop()),
            (this._focustrap = this._initializeFocusTrap()),
            this._addEventListeners();
    }
    static get Default() {
        return _d;
    }
    static get DefaultType() {
        return md;
    }
    static get NAME() {
        return rd;
    }
    toggle(t) {
        return this._isShown ? this.hide() : this.show(t);
    }
    show(t) {
        if (
            this._isShown ||
            h.trigger(this._element, cd, { relatedTarget: t }).defaultPrevented
        )
            return;
        (this._isShown = !0),
            this._backdrop.show(),
            this._config.scroll || new Tr().hide(),
            this._element.setAttribute("aria-modal", !0),
            this._element.setAttribute("role", "dialog"),
            this._element.classList.add(Es);
        const r = () => {
            (!this._config.scroll || this._config.backdrop) &&
                this._focustrap.activate(),
                this._element.classList.add(gs),
                this._element.classList.remove(Es),
                h.trigger(this._element, ld, { relatedTarget: t });
        };
        this._queueCallback(r, this._element, !0);
    }
    hide() {
        if (!this._isShown || h.trigger(this._element, ud).defaultPrevented)
            return;
        this._focustrap.deactivate(),
            this._element.blur(),
            (this._isShown = !1),
            this._element.classList.add(bs),
            this._backdrop.hide();
        const n = () => {
            this._element.classList.remove(gs, bs),
                this._element.removeAttribute("aria-modal"),
                this._element.removeAttribute("role"),
                this._config.scroll || new Tr().reset(),
                h.trigger(this._element, Zo);
        };
        this._queueCallback(n, this._element, !0);
    }
    dispose() {
        this._backdrop.dispose(), this._focustrap.deactivate(), super.dispose();
    }
    _initializeBackDrop() {
        const t = () => {
                if (this._config.backdrop === "static") {
                    h.trigger(this._element, vs);
                    return;
                }
                this.hide();
            },
            n = !!this._config.backdrop;
        return new qo({
            className: ad,
            isVisible: n,
            isAnimated: !0,
            rootElement: this._element.parentNode,
            clickCallback: n ? t : null,
        });
    }
    _initializeFocusTrap() {
        return new Yo({ trapElement: this._element });
    }
    _addEventListeners() {
        h.on(this._element, hd, (t) => {
            if (t.key === od) {
                if (this._config.keyboard) {
                    this.hide();
                    return;
                }
                h.trigger(this._element, vs);
            }
        });
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = vt.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (n[t] === void 0 || t.startsWith("_") || t === "constructor")
                    throw new TypeError(`No method named "${t}"`);
                n[t](this);
            }
        });
    }
}
h.on(document, dd, pd, function (e) {
    const t = v.getElementFromSelector(this);
    if ((["A", "AREA"].includes(this.tagName) && e.preventDefault(), bt(this)))
        return;
    h.one(t, Zo, () => {
        fe(this) && this.focus();
    });
    const n = v.findOne(Qo);
    n && n !== t && vt.getInstance(n).hide(),
        vt.getOrCreateInstance(t).toggle(this);
});
h.on(window, sd, () => {
    for (const e of v.find(Qo)) vt.getOrCreateInstance(e).show();
});
h.on(window, fd, () => {
    for (const e of v.find("[aria-modal][class*=show][class*=offcanvas-]"))
        getComputedStyle(e).position !== "fixed" &&
            vt.getOrCreateInstance(e).hide();
});
xn(vt);
G(vt);
const gd = /^aria-[\w-]*$/i,
    ta = {
        "*": ["class", "dir", "id", "lang", "role", gd],
        a: ["target", "href", "title", "rel"],
        area: [],
        b: [],
        br: [],
        col: [],
        code: [],
        dd: [],
        div: [],
        dl: [],
        dt: [],
        em: [],
        hr: [],
        h1: [],
        h2: [],
        h3: [],
        h4: [],
        h5: [],
        h6: [],
        i: [],
        img: ["src", "srcset", "alt", "title", "width", "height"],
        li: [],
        ol: [],
        p: [],
        pre: [],
        s: [],
        small: [],
        span: [],
        sub: [],
        sup: [],
        strong: [],
        u: [],
        ul: [],
    },
    Ed = new Set([
        "background",
        "cite",
        "href",
        "itemtype",
        "longdesc",
        "poster",
        "src",
        "xlink:href",
    ]),
    bd = /^(?!javascript:)(?:[a-z0-9+.-]+:|[^&:/?#]*(?:[/?#]|$))/i,
    vd = (e, t) => {
        const n = e.nodeName.toLowerCase();
        return t.includes(n)
            ? Ed.has(n)
                ? !!bd.test(e.nodeValue)
                : !0
            : t.filter((r) => r instanceof RegExp).some((r) => r.test(n));
    };
function yd(e, t, n) {
    if (!e.length) return e;
    if (n && typeof n == "function") return n(e);
    const i = new window.DOMParser().parseFromString(e, "text/html"),
        s = [].concat(...i.body.querySelectorAll("*"));
    for (const o of s) {
        const a = o.nodeName.toLowerCase();
        if (!Object.keys(t).includes(a)) {
            o.remove();
            continue;
        }
        const c = [].concat(...o.attributes),
            u = [].concat(t["*"] || [], t[a] || []);
        for (const l of c) vd(l, u) || o.removeAttribute(l.nodeName);
    }
    return i.body.innerHTML;
}
const Ad = "TemplateFactory",
    wd = {
        allowList: ta,
        content: {},
        extraClass: "",
        html: !1,
        sanitize: !0,
        sanitizeFn: null,
        template: "<div></div>",
    },
    Td = {
        allowList: "object",
        content: "object",
        extraClass: "(string|function)",
        html: "boolean",
        sanitize: "boolean",
        sanitizeFn: "(null|function)",
        template: "string",
    },
    Sd = {
        entry: "(string|element|function|null)",
        selector: "(string|element)",
    };
class Od extends Me {
    constructor(t) {
        super(), (this._config = this._getConfig(t));
    }
    static get Default() {
        return wd;
    }
    static get DefaultType() {
        return Td;
    }
    static get NAME() {
        return Ad;
    }
    getContent() {
        return Object.values(this._config.content)
            .map((t) => this._resolvePossibleFunction(t))
            .filter(Boolean);
    }
    hasContent() {
        return this.getContent().length > 0;
    }
    changeContent(t) {
        return (
            this._checkContent(t),
            (this._config.content = { ...this._config.content, ...t }),
            this
        );
    }
    toHtml() {
        const t = document.createElement("div");
        t.innerHTML = this._maybeSanitize(this._config.template);
        for (const [i, s] of Object.entries(this._config.content))
            this._setContent(t, s, i);
        const n = t.children[0],
            r = this._resolvePossibleFunction(this._config.extraClass);
        return r && n.classList.add(...r.split(" ")), n;
    }
    _typeCheckConfig(t) {
        super._typeCheckConfig(t), this._checkContent(t.content);
    }
    _checkContent(t) {
        for (const [n, r] of Object.entries(t))
            super._typeCheckConfig({ selector: n, entry: r }, Sd);
    }
    _setContent(t, n, r) {
        const i = v.findOne(r, t);
        if (i) {
            if (((n = this._resolvePossibleFunction(n)), !n)) {
                i.remove();
                return;
            }
            if (ct(n)) {
                this._putElementInTemplate(Et(n), i);
                return;
            }
            if (this._config.html) {
                i.innerHTML = this._maybeSanitize(n);
                return;
            }
            i.textContent = n;
        }
    }
    _maybeSanitize(t) {
        return this._config.sanitize
            ? yd(t, this._config.allowList, this._config.sanitizeFn)
            : t;
    }
    _resolvePossibleFunction(t) {
        return B(t, [this]);
    }
    _putElementInTemplate(t, n) {
        if (this._config.html) {
            (n.innerHTML = ""), n.append(t);
            return;
        }
        n.textContent = t.textContent;
    }
}
const Cd = "tooltip",
    xd = new Set(["sanitize", "allowList", "sanitizeFn"]),
    rr = "fade",
    Nd = "modal",
    tn = "show",
    Dd = ".tooltip-inner",
    ys = `.${Nd}`,
    As = "hide.bs.modal",
    ye = "hover",
    ir = "focus",
    Ld = "click",
    $d = "manual",
    Rd = "hide",
    Id = "hidden",
    Pd = "show",
    Md = "shown",
    kd = "inserted",
    Fd = "click",
    jd = "focusin",
    Bd = "focusout",
    Hd = "mouseenter",
    Vd = "mouseleave",
    Wd = {
        AUTO: "auto",
        TOP: "top",
        RIGHT: q() ? "left" : "right",
        BOTTOM: "bottom",
        LEFT: q() ? "right" : "left",
    },
    Ud = {
        allowList: ta,
        animation: !0,
        boundary: "clippingParents",
        container: !1,
        customClass: "",
        delay: 0,
        fallbackPlacements: ["top", "right", "bottom", "left"],
        html: !1,
        offset: [0, 6],
        placement: "top",
        popperConfig: null,
        sanitize: !0,
        sanitizeFn: null,
        selector: !1,
        template:
            '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
        title: "",
        trigger: "hover focus",
    },
    Kd = {
        allowList: "object",
        animation: "boolean",
        boundary: "(string|element)",
        container: "(string|element|boolean)",
        customClass: "(string|function)",
        delay: "(number|object)",
        fallbackPlacements: "array",
        html: "boolean",
        offset: "(array|string|function)",
        placement: "(string|function)",
        popperConfig: "(null|object|function)",
        sanitize: "boolean",
        sanitizeFn: "(null|function)",
        selector: "(string|boolean)",
        template: "string",
        title: "(string|element|function)",
        trigger: "string",
    };
class he extends tt {
    constructor(t, n) {
        if (typeof Co > "u")
            throw new TypeError(
                "Bootstrap's tooltips require Popper (https://popper.js.org)"
            );
        super(t, n),
            (this._isEnabled = !0),
            (this._timeout = 0),
            (this._isHovered = null),
            (this._activeTrigger = {}),
            (this._popper = null),
            (this._templateFactory = null),
            (this._newContent = null),
            (this.tip = null),
            this._setListeners(),
            this._config.selector || this._fixTitle();
    }
    static get Default() {
        return Ud;
    }
    static get DefaultType() {
        return Kd;
    }
    static get NAME() {
        return Cd;
    }
    enable() {
        this._isEnabled = !0;
    }
    disable() {
        this._isEnabled = !1;
    }
    toggleEnabled() {
        this._isEnabled = !this._isEnabled;
    }
    toggle() {
        if (this._isEnabled) {
            if (
                ((this._activeTrigger.click = !this._activeTrigger.click),
                this._isShown())
            ) {
                this._leave();
                return;
            }
            this._enter();
        }
    }
    dispose() {
        clearTimeout(this._timeout),
            h.off(this._element.closest(ys), As, this._hideModalHandler),
            this._element.getAttribute("data-bs-original-title") &&
                this._element.setAttribute(
                    "title",
                    this._element.getAttribute("data-bs-original-title")
                ),
            this._disposePopper(),
            super.dispose();
    }
    show() {
        if (this._element.style.display === "none")
            throw new Error("Please use show on visible elements");
        if (!(this._isWithContent() && this._isEnabled)) return;
        const t = h.trigger(this._element, this.constructor.eventName(Pd)),
            r = (
                Do(this._element) || this._element.ownerDocument.documentElement
            ).contains(this._element);
        if (t.defaultPrevented || !r) return;
        this._disposePopper();
        const i = this._getTipElement();
        this._element.setAttribute("aria-describedby", i.getAttribute("id"));
        const { container: s } = this._config;
        if (
            (this._element.ownerDocument.documentElement.contains(this.tip) ||
                (s.append(i),
                h.trigger(this._element, this.constructor.eventName(kd))),
            (this._popper = this._createPopper(i)),
            i.classList.add(tn),
            "ontouchstart" in document.documentElement)
        )
            for (const a of [].concat(...document.body.children))
                h.on(a, "mouseover", En);
        const o = () => {
            h.trigger(this._element, this.constructor.eventName(Md)),
                this._isHovered === !1 && this._leave(),
                (this._isHovered = !1);
        };
        this._queueCallback(o, this.tip, this._isAnimated());
    }
    hide() {
        if (
            !this._isShown() ||
            h.trigger(this._element, this.constructor.eventName(Rd))
                .defaultPrevented
        )
            return;
        if (
            (this._getTipElement().classList.remove(tn),
            "ontouchstart" in document.documentElement)
        )
            for (const i of [].concat(...document.body.children))
                h.off(i, "mouseover", En);
        (this._activeTrigger[Ld] = !1),
            (this._activeTrigger[ir] = !1),
            (this._activeTrigger[ye] = !1),
            (this._isHovered = null);
        const r = () => {
            this._isWithActiveTrigger() ||
                (this._isHovered || this._disposePopper(),
                this._element.removeAttribute("aria-describedby"),
                h.trigger(this._element, this.constructor.eventName(Id)));
        };
        this._queueCallback(r, this.tip, this._isAnimated());
    }
    update() {
        this._popper && this._popper.update();
    }
    _isWithContent() {
        return !!this._getTitle();
    }
    _getTipElement() {
        return (
            this.tip ||
                (this.tip = this._createTipElement(
                    this._newContent || this._getContentForTemplate()
                )),
            this.tip
        );
    }
    _createTipElement(t) {
        const n = this._getTemplateFactory(t).toHtml();
        if (!n) return null;
        n.classList.remove(rr, tn),
            n.classList.add(`bs-${this.constructor.NAME}-auto`);
        const r = Dl(this.constructor.NAME).toString();
        return (
            n.setAttribute("id", r),
            this._isAnimated() && n.classList.add(rr),
            n
        );
    }
    setContent(t) {
        (this._newContent = t),
            this._isShown() && (this._disposePopper(), this.show());
    }
    _getTemplateFactory(t) {
        return (
            this._templateFactory
                ? this._templateFactory.changeContent(t)
                : (this._templateFactory = new Od({
                      ...this._config,
                      content: t,
                      extraClass: this._resolvePossibleFunction(
                          this._config.customClass
                      ),
                  })),
            this._templateFactory
        );
    }
    _getContentForTemplate() {
        return { [Dd]: this._getTitle() };
    }
    _getTitle() {
        return (
            this._resolvePossibleFunction(this._config.title) ||
            this._element.getAttribute("data-bs-original-title")
        );
    }
    _initializeOnDelegatedTarget(t) {
        return this.constructor.getOrCreateInstance(
            t.delegateTarget,
            this._getDelegateConfig()
        );
    }
    _isAnimated() {
        return (
            this._config.animation ||
            (this.tip && this.tip.classList.contains(rr))
        );
    }
    _isShown() {
        return this.tip && this.tip.classList.contains(tn);
    }
    _createPopper(t) {
        const n = B(this._config.placement, [this, t, this._element]),
            r = Wd[n.toUpperCase()];
        return oi(this._element, t, this._getPopperConfig(r));
    }
    _getOffset() {
        const { offset: t } = this._config;
        return typeof t == "string"
            ? t.split(",").map((n) => Number.parseInt(n, 10))
            : typeof t == "function"
            ? (n) => t(n, this._element)
            : t;
    }
    _resolvePossibleFunction(t) {
        return B(t, [this._element]);
    }
    _getPopperConfig(t) {
        const n = {
            placement: t,
            modifiers: [
                {
                    name: "flip",
                    options: {
                        fallbackPlacements: this._config.fallbackPlacements,
                    },
                },
                { name: "offset", options: { offset: this._getOffset() } },
                {
                    name: "preventOverflow",
                    options: { boundary: this._config.boundary },
                },
                {
                    name: "arrow",
                    options: { element: `.${this.constructor.NAME}-arrow` },
                },
                {
                    name: "preSetPlacement",
                    enabled: !0,
                    phase: "beforeMain",
                    fn: (r) => {
                        this._getTipElement().setAttribute(
                            "data-popper-placement",
                            r.state.placement
                        );
                    },
                },
            ],
        };
        return { ...n, ...B(this._config.popperConfig, [n]) };
    }
    _setListeners() {
        const t = this._config.trigger.split(" ");
        for (const n of t)
            if (n === "click")
                h.on(
                    this._element,
                    this.constructor.eventName(Fd),
                    this._config.selector,
                    (r) => {
                        this._initializeOnDelegatedTarget(r).toggle();
                    }
                );
            else if (n !== $d) {
                const r =
                        n === ye
                            ? this.constructor.eventName(Hd)
                            : this.constructor.eventName(jd),
                    i =
                        n === ye
                            ? this.constructor.eventName(Vd)
                            : this.constructor.eventName(Bd);
                h.on(this._element, r, this._config.selector, (s) => {
                    const o = this._initializeOnDelegatedTarget(s);
                    (o._activeTrigger[s.type === "focusin" ? ir : ye] = !0),
                        o._enter();
                }),
                    h.on(this._element, i, this._config.selector, (s) => {
                        const o = this._initializeOnDelegatedTarget(s);
                        (o._activeTrigger[s.type === "focusout" ? ir : ye] =
                            o._element.contains(s.relatedTarget)),
                            o._leave();
                    });
            }
        (this._hideModalHandler = () => {
            this._element && this.hide();
        }),
            h.on(this._element.closest(ys), As, this._hideModalHandler);
    }
    _fixTitle() {
        const t = this._element.getAttribute("title");
        t &&
            (!this._element.getAttribute("aria-label") &&
                !this._element.textContent.trim() &&
                this._element.setAttribute("aria-label", t),
            this._element.setAttribute("data-bs-original-title", t),
            this._element.removeAttribute("title"));
    }
    _enter() {
        if (this._isShown() || this._isHovered) {
            this._isHovered = !0;
            return;
        }
        (this._isHovered = !0),
            this._setTimeout(() => {
                this._isHovered && this.show();
            }, this._config.delay.show);
    }
    _leave() {
        this._isWithActiveTrigger() ||
            ((this._isHovered = !1),
            this._setTimeout(() => {
                this._isHovered || this.hide();
            }, this._config.delay.hide));
    }
    _setTimeout(t, n) {
        clearTimeout(this._timeout), (this._timeout = setTimeout(t, n));
    }
    _isWithActiveTrigger() {
        return Object.values(this._activeTrigger).includes(!0);
    }
    _getConfig(t) {
        const n = lt.getDataAttributes(this._element);
        for (const r of Object.keys(n)) xd.has(r) && delete n[r];
        return (
            (t = { ...n, ...(typeof t == "object" && t ? t : {}) }),
            (t = this._mergeConfigObj(t)),
            (t = this._configAfterMerge(t)),
            this._typeCheckConfig(t),
            t
        );
    }
    _configAfterMerge(t) {
        return (
            (t.container =
                t.container === !1 ? document.body : Et(t.container)),
            typeof t.delay == "number" &&
                (t.delay = { show: t.delay, hide: t.delay }),
            typeof t.title == "number" && (t.title = t.title.toString()),
            typeof t.content == "number" && (t.content = t.content.toString()),
            t
        );
    }
    _getDelegateConfig() {
        const t = {};
        for (const [n, r] of Object.entries(this._config))
            this.constructor.Default[n] !== r && (t[n] = r);
        return (t.selector = !1), (t.trigger = "manual"), t;
    }
    _disposePopper() {
        this._popper && (this._popper.destroy(), (this._popper = null)),
            this.tip && (this.tip.remove(), (this.tip = null));
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = he.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (typeof n[t] > "u")
                    throw new TypeError(`No method named "${t}"`);
                n[t]();
            }
        });
    }
}
G(he);
const zd = "popover",
    qd = ".popover-header",
    Yd = ".popover-body",
    Gd = {
        ...he.Default,
        content: "",
        offset: [0, 8],
        placement: "right",
        template:
            '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
        trigger: "click",
    },
    Xd = { ...he.DefaultType, content: "(null|string|element|function)" };
class ui extends he {
    static get Default() {
        return Gd;
    }
    static get DefaultType() {
        return Xd;
    }
    static get NAME() {
        return zd;
    }
    _isWithContent() {
        return this._getTitle() || this._getContent();
    }
    _getContentForTemplate() {
        return { [qd]: this._getTitle(), [Yd]: this._getContent() };
    }
    _getContent() {
        return this._resolvePossibleFunction(this._config.content);
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = ui.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (typeof n[t] > "u")
                    throw new TypeError(`No method named "${t}"`);
                n[t]();
            }
        });
    }
}
G(ui);
const Jd = "scrollspy",
    Qd = "bs.scrollspy",
    fi = `.${Qd}`,
    Zd = ".data-api",
    th = `activate${fi}`,
    ws = `click${fi}`,
    eh = `load${fi}${Zd}`,
    nh = "dropdown-item",
    Gt = "active",
    rh = '[data-bs-spy="scroll"]',
    sr = "[href]",
    ih = ".nav, .list-group",
    Ts = ".nav-link",
    sh = ".nav-item",
    oh = ".list-group-item",
    ah = `${Ts}, ${sh} > ${Ts}, ${oh}`,
    ch = ".dropdown",
    lh = ".dropdown-toggle",
    uh = {
        offset: null,
        rootMargin: "0px 0px -25%",
        smoothScroll: !1,
        target: null,
        threshold: [0.1, 0.5, 1],
    },
    fh = {
        offset: "(number|null)",
        rootMargin: "string",
        smoothScroll: "boolean",
        target: "element",
        threshold: "array",
    };
class Ln extends tt {
    constructor(t, n) {
        super(t, n),
            (this._targetLinks = new Map()),
            (this._observableSections = new Map()),
            (this._rootElement =
                getComputedStyle(this._element).overflowY === "visible"
                    ? null
                    : this._element),
            (this._activeTarget = null),
            (this._observer = null),
            (this._previousScrollData = {
                visibleEntryTop: 0,
                parentScrollTop: 0,
            }),
            this.refresh();
    }
    static get Default() {
        return uh;
    }
    static get DefaultType() {
        return fh;
    }
    static get NAME() {
        return Jd;
    }
    refresh() {
        this._initializeTargetsAndObservables(),
            this._maybeEnableSmoothScroll(),
            this._observer
                ? this._observer.disconnect()
                : (this._observer = this._getNewObserver());
        for (const t of this._observableSections.values())
            this._observer.observe(t);
    }
    dispose() {
        this._observer.disconnect(), super.dispose();
    }
    _configAfterMerge(t) {
        return (
            (t.target = Et(t.target) || document.body),
            (t.rootMargin = t.offset ? `${t.offset}px 0px -30%` : t.rootMargin),
            typeof t.threshold == "string" &&
                (t.threshold = t.threshold
                    .split(",")
                    .map((n) => Number.parseFloat(n))),
            t
        );
    }
    _maybeEnableSmoothScroll() {
        this._config.smoothScroll &&
            (h.off(this._config.target, ws),
            h.on(this._config.target, ws, sr, (t) => {
                const n = this._observableSections.get(t.target.hash);
                if (n) {
                    t.preventDefault();
                    const r = this._rootElement || window,
                        i = n.offsetTop - this._element.offsetTop;
                    if (r.scrollTo) {
                        r.scrollTo({ top: i, behavior: "smooth" });
                        return;
                    }
                    r.scrollTop = i;
                }
            }));
    }
    _getNewObserver() {
        const t = {
            root: this._rootElement,
            threshold: this._config.threshold,
            rootMargin: this._config.rootMargin,
        };
        return new IntersectionObserver((n) => this._observerCallback(n), t);
    }
    _observerCallback(t) {
        const n = (o) => this._targetLinks.get(`#${o.target.id}`),
            r = (o) => {
                (this._previousScrollData.visibleEntryTop = o.target.offsetTop),
                    this._process(n(o));
            },
            i = (this._rootElement || document.documentElement).scrollTop,
            s = i >= this._previousScrollData.parentScrollTop;
        this._previousScrollData.parentScrollTop = i;
        for (const o of t) {
            if (!o.isIntersecting) {
                (this._activeTarget = null), this._clearActiveClass(n(o));
                continue;
            }
            const a =
                o.target.offsetTop >= this._previousScrollData.visibleEntryTop;
            if (s && a) {
                if ((r(o), !i)) return;
                continue;
            }
            !s && !a && r(o);
        }
    }
    _initializeTargetsAndObservables() {
        (this._targetLinks = new Map()), (this._observableSections = new Map());
        const t = v.find(sr, this._config.target);
        for (const n of t) {
            if (!n.hash || bt(n)) continue;
            const r = v.findOne(decodeURI(n.hash), this._element);
            fe(r) &&
                (this._targetLinks.set(decodeURI(n.hash), n),
                this._observableSections.set(n.hash, r));
        }
    }
    _process(t) {
        this._activeTarget !== t &&
            (this._clearActiveClass(this._config.target),
            (this._activeTarget = t),
            t.classList.add(Gt),
            this._activateParents(t),
            h.trigger(this._element, th, { relatedTarget: t }));
    }
    _activateParents(t) {
        if (t.classList.contains(nh)) {
            v.findOne(lh, t.closest(ch)).classList.add(Gt);
            return;
        }
        for (const n of v.parents(t, ih))
            for (const r of v.prev(n, ah)) r.classList.add(Gt);
    }
    _clearActiveClass(t) {
        t.classList.remove(Gt);
        const n = v.find(`${sr}.${Gt}`, t);
        for (const r of n) r.classList.remove(Gt);
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = Ln.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (n[t] === void 0 || t.startsWith("_") || t === "constructor")
                    throw new TypeError(`No method named "${t}"`);
                n[t]();
            }
        });
    }
}
h.on(window, eh, () => {
    for (const e of v.find(rh)) Ln.getOrCreateInstance(e);
});
G(Ln);
const dh = "tab",
    hh = "bs.tab",
    Ut = `.${hh}`,
    ph = `hide${Ut}`,
    _h = `hidden${Ut}`,
    mh = `show${Ut}`,
    gh = `shown${Ut}`,
    Eh = `click${Ut}`,
    bh = `keydown${Ut}`,
    vh = `load${Ut}`,
    yh = "ArrowLeft",
    Ss = "ArrowRight",
    Ah = "ArrowUp",
    Os = "ArrowDown",
    or = "Home",
    Cs = "End",
    It = "active",
    xs = "fade",
    ar = "show",
    wh = "dropdown",
    ea = ".dropdown-toggle",
    Th = ".dropdown-menu",
    cr = `:not(${ea})`,
    Sh = '.list-group, .nav, [role="tablist"]',
    Oh = ".nav-item, .list-group-item",
    Ch = `.nav-link${cr}, .list-group-item${cr}, [role="tab"]${cr}`,
    na =
        '[data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="list"]',
    lr = `${Ch}, ${na}`,
    xh = `.${It}[data-bs-toggle="tab"], .${It}[data-bs-toggle="pill"], .${It}[data-bs-toggle="list"]`;
class oe extends tt {
    constructor(t) {
        super(t),
            (this._parent = this._element.closest(Sh)),
            this._parent &&
                (this._setInitialAttributes(this._parent, this._getChildren()),
                h.on(this._element, bh, (n) => this._keydown(n)));
    }
    static get NAME() {
        return dh;
    }
    show() {
        const t = this._element;
        if (this._elemIsActive(t)) return;
        const n = this._getActiveElem(),
            r = n ? h.trigger(n, ph, { relatedTarget: t }) : null;
        h.trigger(t, mh, { relatedTarget: n }).defaultPrevented ||
            (r && r.defaultPrevented) ||
            (this._deactivate(n, t), this._activate(t, n));
    }
    _activate(t, n) {
        if (!t) return;
        t.classList.add(It), this._activate(v.getElementFromSelector(t));
        const r = () => {
            if (t.getAttribute("role") !== "tab") {
                t.classList.add(ar);
                return;
            }
            t.removeAttribute("tabindex"),
                t.setAttribute("aria-selected", !0),
                this._toggleDropDown(t, !0),
                h.trigger(t, gh, { relatedTarget: n });
        };
        this._queueCallback(r, t, t.classList.contains(xs));
    }
    _deactivate(t, n) {
        if (!t) return;
        t.classList.remove(It),
            t.blur(),
            this._deactivate(v.getElementFromSelector(t));
        const r = () => {
            if (t.getAttribute("role") !== "tab") {
                t.classList.remove(ar);
                return;
            }
            t.setAttribute("aria-selected", !1),
                t.setAttribute("tabindex", "-1"),
                this._toggleDropDown(t, !1),
                h.trigger(t, _h, { relatedTarget: n });
        };
        this._queueCallback(r, t, t.classList.contains(xs));
    }
    _keydown(t) {
        if (![yh, Ss, Ah, Os, or, Cs].includes(t.key)) return;
        t.stopPropagation(), t.preventDefault();
        const n = this._getChildren().filter((i) => !bt(i));
        let r;
        if ([or, Cs].includes(t.key)) r = n[t.key === or ? 0 : n.length - 1];
        else {
            const i = [Ss, Os].includes(t.key);
            r = ai(n, t.target, i, !0);
        }
        r && (r.focus({ preventScroll: !0 }), oe.getOrCreateInstance(r).show());
    }
    _getChildren() {
        return v.find(lr, this._parent);
    }
    _getActiveElem() {
        return this._getChildren().find((t) => this._elemIsActive(t)) || null;
    }
    _setInitialAttributes(t, n) {
        this._setAttributeIfNotExists(t, "role", "tablist");
        for (const r of n) this._setInitialAttributesOnChild(r);
    }
    _setInitialAttributesOnChild(t) {
        t = this._getInnerElement(t);
        const n = this._elemIsActive(t),
            r = this._getOuterElement(t);
        t.setAttribute("aria-selected", n),
            r !== t && this._setAttributeIfNotExists(r, "role", "presentation"),
            n || t.setAttribute("tabindex", "-1"),
            this._setAttributeIfNotExists(t, "role", "tab"),
            this._setInitialAttributesOnTargetPanel(t);
    }
    _setInitialAttributesOnTargetPanel(t) {
        const n = v.getElementFromSelector(t);
        n &&
            (this._setAttributeIfNotExists(n, "role", "tabpanel"),
            t.id &&
                this._setAttributeIfNotExists(n, "aria-labelledby", `${t.id}`));
    }
    _toggleDropDown(t, n) {
        const r = this._getOuterElement(t);
        if (!r.classList.contains(wh)) return;
        const i = (s, o) => {
            const a = v.findOne(s, r);
            a && a.classList.toggle(o, n);
        };
        i(ea, It), i(Th, ar), r.setAttribute("aria-expanded", n);
    }
    _setAttributeIfNotExists(t, n, r) {
        t.hasAttribute(n) || t.setAttribute(n, r);
    }
    _elemIsActive(t) {
        return t.classList.contains(It);
    }
    _getInnerElement(t) {
        return t.matches(lr) ? t : v.findOne(lr, t);
    }
    _getOuterElement(t) {
        return t.closest(Oh) || t;
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = oe.getOrCreateInstance(this);
            if (typeof t == "string") {
                if (n[t] === void 0 || t.startsWith("_") || t === "constructor")
                    throw new TypeError(`No method named "${t}"`);
                n[t]();
            }
        });
    }
}
h.on(document, Eh, na, function (e) {
    ["A", "AREA"].includes(this.tagName) && e.preventDefault(),
        !bt(this) && oe.getOrCreateInstance(this).show();
});
h.on(window, vh, () => {
    for (const e of v.find(xh)) oe.getOrCreateInstance(e);
});
G(oe);
const Nh = "toast",
    Dh = "bs.toast",
    Ot = `.${Dh}`,
    Lh = `mouseover${Ot}`,
    $h = `mouseout${Ot}`,
    Rh = `focusin${Ot}`,
    Ih = `focusout${Ot}`,
    Ph = `hide${Ot}`,
    Mh = `hidden${Ot}`,
    kh = `show${Ot}`,
    Fh = `shown${Ot}`,
    jh = "fade",
    Ns = "hide",
    en = "show",
    nn = "showing",
    Bh = { animation: "boolean", autohide: "boolean", delay: "number" },
    Hh = { animation: !0, autohide: !0, delay: 5e3 };
class $n extends tt {
    constructor(t, n) {
        super(t, n),
            (this._timeout = null),
            (this._hasMouseInteraction = !1),
            (this._hasKeyboardInteraction = !1),
            this._setListeners();
    }
    static get Default() {
        return Hh;
    }
    static get DefaultType() {
        return Bh;
    }
    static get NAME() {
        return Nh;
    }
    show() {
        if (h.trigger(this._element, kh).defaultPrevented) return;
        this._clearTimeout(),
            this._config.animation && this._element.classList.add(jh);
        const n = () => {
            this._element.classList.remove(nn),
                h.trigger(this._element, Fh),
                this._maybeScheduleHide();
        };
        this._element.classList.remove(Ns),
            Pe(this._element),
            this._element.classList.add(en, nn),
            this._queueCallback(n, this._element, this._config.animation);
    }
    hide() {
        if (!this.isShown() || h.trigger(this._element, Ph).defaultPrevented)
            return;
        const n = () => {
            this._element.classList.add(Ns),
                this._element.classList.remove(nn, en),
                h.trigger(this._element, Mh);
        };
        this._element.classList.add(nn),
            this._queueCallback(n, this._element, this._config.animation);
    }
    dispose() {
        this._clearTimeout(),
            this.isShown() && this._element.classList.remove(en),
            super.dispose();
    }
    isShown() {
        return this._element.classList.contains(en);
    }
    _maybeScheduleHide() {
        this._config.autohide &&
            (this._hasMouseInteraction ||
                this._hasKeyboardInteraction ||
                (this._timeout = setTimeout(() => {
                    this.hide();
                }, this._config.delay)));
    }
    _onInteraction(t, n) {
        switch (t.type) {
            case "mouseover":
            case "mouseout": {
                this._hasMouseInteraction = n;
                break;
            }
            case "focusin":
            case "focusout": {
                this._hasKeyboardInteraction = n;
                break;
            }
        }
        if (n) {
            this._clearTimeout();
            return;
        }
        const r = t.relatedTarget;
        this._element === r ||
            this._element.contains(r) ||
            this._maybeScheduleHide();
    }
    _setListeners() {
        h.on(this._element, Lh, (t) => this._onInteraction(t, !0)),
            h.on(this._element, $h, (t) => this._onInteraction(t, !1)),
            h.on(this._element, Rh, (t) => this._onInteraction(t, !0)),
            h.on(this._element, Ih, (t) => this._onInteraction(t, !1));
    }
    _clearTimeout() {
        clearTimeout(this._timeout), (this._timeout = null);
    }
    static jQueryInterface(t) {
        return this.each(function () {
            const n = $n.getOrCreateInstance(this, t);
            if (typeof t == "string") {
                if (typeof n[t] > "u")
                    throw new TypeError(`No method named "${t}"`);
                n[t](this);
            }
        });
    }
}
xn($n);
G($n);
function ra(e, t) {
    return function () {
        return e.apply(t, arguments);
    };
}
const { toString: Vh } = Object.prototype,
    { getPrototypeOf: di } = Object,
    Rn = ((e) => (t) => {
        const n = Vh.call(t);
        return e[n] || (e[n] = n.slice(8, -1).toLowerCase());
    })(Object.create(null)),
    at = (e) => ((e = e.toLowerCase()), (t) => Rn(t) === e),
    In = (e) => (t) => typeof t === e,
    { isArray: pe } = Array,
    $e = In("undefined");
function Wh(e) {
    return (
        e !== null &&
        !$e(e) &&
        e.constructor !== null &&
        !$e(e.constructor) &&
        z(e.constructor.isBuffer) &&
        e.constructor.isBuffer(e)
    );
}
const ia = at("ArrayBuffer");
function Uh(e) {
    let t;
    return (
        typeof ArrayBuffer < "u" && ArrayBuffer.isView
            ? (t = ArrayBuffer.isView(e))
            : (t = e && e.buffer && ia(e.buffer)),
        t
    );
}
const Kh = In("string"),
    z = In("function"),
    sa = In("number"),
    Pn = (e) => e !== null && typeof e == "object",
    zh = (e) => e === !0 || e === !1,
    dn = (e) => {
        if (Rn(e) !== "object") return !1;
        const t = di(e);
        return (
            (t === null ||
                t === Object.prototype ||
                Object.getPrototypeOf(t) === null) &&
            !(Symbol.toStringTag in e) &&
            !(Symbol.iterator in e)
        );
    },
    qh = at("Date"),
    Yh = at("File"),
    Gh = at("Blob"),
    Xh = at("FileList"),
    Jh = (e) => Pn(e) && z(e.pipe),
    Qh = (e) => {
        let t;
        return (
            e &&
            ((typeof FormData == "function" && e instanceof FormData) ||
                (z(e.append) &&
                    ((t = Rn(e)) === "formdata" ||
                        (t === "object" &&
                            z(e.toString) &&
                            e.toString() === "[object FormData]"))))
        );
    },
    Zh = at("URLSearchParams"),
    tp = (e) =>
        e.trim ? e.trim() : e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
function je(e, t, { allOwnKeys: n = !1 } = {}) {
    if (e === null || typeof e > "u") return;
    let r, i;
    if ((typeof e != "object" && (e = [e]), pe(e)))
        for (r = 0, i = e.length; r < i; r++) t.call(null, e[r], r, e);
    else {
        const s = n ? Object.getOwnPropertyNames(e) : Object.keys(e),
            o = s.length;
        let a;
        for (r = 0; r < o; r++) (a = s[r]), t.call(null, e[a], a, e);
    }
}
function oa(e, t) {
    t = t.toLowerCase();
    const n = Object.keys(e);
    let r = n.length,
        i;
    for (; r-- > 0; ) if (((i = n[r]), t === i.toLowerCase())) return i;
    return null;
}
const aa =
        typeof globalThis < "u"
            ? globalThis
            : typeof self < "u"
            ? self
            : typeof window < "u"
            ? window
            : global,
    ca = (e) => !$e(e) && e !== aa;
function Sr() {
    const { caseless: e } = (ca(this) && this) || {},
        t = {},
        n = (r, i) => {
            const s = (e && oa(t, i)) || i;
            dn(t[s]) && dn(r)
                ? (t[s] = Sr(t[s], r))
                : dn(r)
                ? (t[s] = Sr({}, r))
                : pe(r)
                ? (t[s] = r.slice())
                : (t[s] = r);
        };
    for (let r = 0, i = arguments.length; r < i; r++)
        arguments[r] && je(arguments[r], n);
    return t;
}
const ep = (e, t, n, { allOwnKeys: r } = {}) => (
        je(
            t,
            (i, s) => {
                n && z(i) ? (e[s] = ra(i, n)) : (e[s] = i);
            },
            { allOwnKeys: r }
        ),
        e
    ),
    np = (e) => (e.charCodeAt(0) === 65279 && (e = e.slice(1)), e),
    rp = (e, t, n, r) => {
        (e.prototype = Object.create(t.prototype, r)),
            (e.prototype.constructor = e),
            Object.defineProperty(e, "super", { value: t.prototype }),
            n && Object.assign(e.prototype, n);
    },
    ip = (e, t, n, r) => {
        let i, s, o;
        const a = {};
        if (((t = t || {}), e == null)) return t;
        do {
            for (i = Object.getOwnPropertyNames(e), s = i.length; s-- > 0; )
                (o = i[s]),
                    (!r || r(o, e, t)) && !a[o] && ((t[o] = e[o]), (a[o] = !0));
            e = n !== !1 && di(e);
        } while (e && (!n || n(e, t)) && e !== Object.prototype);
        return t;
    },
    sp = (e, t, n) => {
        (e = String(e)),
            (n === void 0 || n > e.length) && (n = e.length),
            (n -= t.length);
        const r = e.indexOf(t, n);
        return r !== -1 && r === n;
    },
    op = (e) => {
        if (!e) return null;
        if (pe(e)) return e;
        let t = e.length;
        if (!sa(t)) return null;
        const n = new Array(t);
        for (; t-- > 0; ) n[t] = e[t];
        return n;
    },
    ap = (
        (e) => (t) =>
            e && t instanceof e
    )(typeof Uint8Array < "u" && di(Uint8Array)),
    cp = (e, t) => {
        const r = (e && e[Symbol.iterator]).call(e);
        let i;
        for (; (i = r.next()) && !i.done; ) {
            const s = i.value;
            t.call(e, s[0], s[1]);
        }
    },
    lp = (e, t) => {
        let n;
        const r = [];
        for (; (n = e.exec(t)) !== null; ) r.push(n);
        return r;
    },
    up = at("HTMLFormElement"),
    fp = (e) =>
        e.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g, function (n, r, i) {
            return r.toUpperCase() + i;
        }),
    Ds = (
        ({ hasOwnProperty: e }) =>
        (t, n) =>
            e.call(t, n)
    )(Object.prototype),
    dp = at("RegExp"),
    la = (e, t) => {
        const n = Object.getOwnPropertyDescriptors(e),
            r = {};
        je(n, (i, s) => {
            let o;
            (o = t(i, s, e)) !== !1 && (r[s] = o || i);
        }),
            Object.defineProperties(e, r);
    },
    hp = (e) => {
        la(e, (t, n) => {
            if (z(e) && ["arguments", "caller", "callee"].indexOf(n) !== -1)
                return !1;
            const r = e[n];
            if (z(r)) {
                if (((t.enumerable = !1), "writable" in t)) {
                    t.writable = !1;
                    return;
                }
                t.set ||
                    (t.set = () => {
                        throw Error(
                            "Can not rewrite read-only method '" + n + "'"
                        );
                    });
            }
        });
    },
    pp = (e, t) => {
        const n = {},
            r = (i) => {
                i.forEach((s) => {
                    n[s] = !0;
                });
            };
        return pe(e) ? r(e) : r(String(e).split(t)), n;
    },
    _p = () => {},
    mp = (e, t) => ((e = +e), Number.isFinite(e) ? e : t),
    ur = "abcdefghijklmnopqrstuvwxyz",
    Ls = "0123456789",
    ua = { DIGIT: Ls, ALPHA: ur, ALPHA_DIGIT: ur + ur.toUpperCase() + Ls },
    gp = (e = 16, t = ua.ALPHA_DIGIT) => {
        let n = "";
        const { length: r } = t;
        for (; e--; ) n += t[(Math.random() * r) | 0];
        return n;
    };
function Ep(e) {
    return !!(
        e &&
        z(e.append) &&
        e[Symbol.toStringTag] === "FormData" &&
        e[Symbol.iterator]
    );
}
const bp = (e) => {
        const t = new Array(10),
            n = (r, i) => {
                if (Pn(r)) {
                    if (t.indexOf(r) >= 0) return;
                    if (!("toJSON" in r)) {
                        t[i] = r;
                        const s = pe(r) ? [] : {};
                        return (
                            je(r, (o, a) => {
                                const c = n(o, i + 1);
                                !$e(c) && (s[a] = c);
                            }),
                            (t[i] = void 0),
                            s
                        );
                    }
                }
                return r;
            };
        return n(e, 0);
    },
    vp = at("AsyncFunction"),
    yp = (e) => e && (Pn(e) || z(e)) && z(e.then) && z(e.catch),
    d = {
        isArray: pe,
        isArrayBuffer: ia,
        isBuffer: Wh,
        isFormData: Qh,
        isArrayBufferView: Uh,
        isString: Kh,
        isNumber: sa,
        isBoolean: zh,
        isObject: Pn,
        isPlainObject: dn,
        isUndefined: $e,
        isDate: qh,
        isFile: Yh,
        isBlob: Gh,
        isRegExp: dp,
        isFunction: z,
        isStream: Jh,
        isURLSearchParams: Zh,
        isTypedArray: ap,
        isFileList: Xh,
        forEach: je,
        merge: Sr,
        extend: ep,
        trim: tp,
        stripBOM: np,
        inherits: rp,
        toFlatObject: ip,
        kindOf: Rn,
        kindOfTest: at,
        endsWith: sp,
        toArray: op,
        forEachEntry: cp,
        matchAll: lp,
        isHTMLForm: up,
        hasOwnProperty: Ds,
        hasOwnProp: Ds,
        reduceDescriptors: la,
        freezeMethods: hp,
        toObjectSet: pp,
        toCamelCase: fp,
        noop: _p,
        toFiniteNumber: mp,
        findKey: oa,
        global: aa,
        isContextDefined: ca,
        ALPHABET: ua,
        generateString: gp,
        isSpecCompliantForm: Ep,
        toJSONObject: bp,
        isAsyncFn: vp,
        isThenable: yp,
    };
function O(e, t, n, r, i) {
    Error.call(this),
        Error.captureStackTrace
            ? Error.captureStackTrace(this, this.constructor)
            : (this.stack = new Error().stack),
        (this.message = e),
        (this.name = "AxiosError"),
        t && (this.code = t),
        n && (this.config = n),
        r && (this.request = r),
        i && (this.response = i);
}
d.inherits(O, Error, {
    toJSON: function () {
        return {
            message: this.message,
            name: this.name,
            description: this.description,
            number: this.number,
            fileName: this.fileName,
            lineNumber: this.lineNumber,
            columnNumber: this.columnNumber,
            stack: this.stack,
            config: d.toJSONObject(this.config),
            code: this.code,
            status:
                this.response && this.response.status
                    ? this.response.status
                    : null,
        };
    },
});
const fa = O.prototype,
    da = {};
[
    "ERR_BAD_OPTION_VALUE",
    "ERR_BAD_OPTION",
    "ECONNABORTED",
    "ETIMEDOUT",
    "ERR_NETWORK",
    "ERR_FR_TOO_MANY_REDIRECTS",
    "ERR_DEPRECATED",
    "ERR_BAD_RESPONSE",
    "ERR_BAD_REQUEST",
    "ERR_CANCELED",
    "ERR_NOT_SUPPORT",
    "ERR_INVALID_URL",
].forEach((e) => {
    da[e] = { value: e };
});
Object.defineProperties(O, da);
Object.defineProperty(fa, "isAxiosError", { value: !0 });
O.from = (e, t, n, r, i, s) => {
    const o = Object.create(fa);
    return (
        d.toFlatObject(
            e,
            o,
            function (c) {
                return c !== Error.prototype;
            },
            (a) => a !== "isAxiosError"
        ),
        O.call(o, e.message, t, n, r, i),
        (o.cause = e),
        (o.name = e.name),
        s && Object.assign(o, s),
        o
    );
};
const Ap = null;
function Or(e) {
    return d.isPlainObject(e) || d.isArray(e);
}
function ha(e) {
    return d.endsWith(e, "[]") ? e.slice(0, -2) : e;
}
function $s(e, t, n) {
    return e
        ? e
              .concat(t)
              .map(function (i, s) {
                  return (i = ha(i)), !n && s ? "[" + i + "]" : i;
              })
              .join(n ? "." : "")
        : t;
}
function wp(e) {
    return d.isArray(e) && !e.some(Or);
}
const Tp = d.toFlatObject(d, {}, null, function (t) {
    return /^is[A-Z]/.test(t);
});
function Mn(e, t, n) {
    if (!d.isObject(e)) throw new TypeError("target must be an object");
    (t = t || new FormData()),
        (n = d.toFlatObject(
            n,
            { metaTokens: !0, dots: !1, indexes: !1 },
            !1,
            function (_, p) {
                return !d.isUndefined(p[_]);
            }
        ));
    const r = n.metaTokens,
        i = n.visitor || l,
        s = n.dots,
        o = n.indexes,
        c = (n.Blob || (typeof Blob < "u" && Blob)) && d.isSpecCompliantForm(t);
    if (!d.isFunction(i)) throw new TypeError("visitor must be a function");
    function u(g) {
        if (g === null) return "";
        if (d.isDate(g)) return g.toISOString();
        if (!c && d.isBlob(g))
            throw new O("Blob is not supported. Use a Buffer instead.");
        return d.isArrayBuffer(g) || d.isTypedArray(g)
            ? c && typeof Blob == "function"
                ? new Blob([g])
                : Buffer.from(g)
            : g;
    }
    function l(g, _, p) {
        let b = g;
        if (g && !p && typeof g == "object") {
            if (d.endsWith(_, "{}"))
                (_ = r ? _ : _.slice(0, -2)), (g = JSON.stringify(g));
            else if (
                (d.isArray(g) && wp(g)) ||
                ((d.isFileList(g) || d.endsWith(_, "[]")) && (b = d.toArray(g)))
            )
                return (
                    (_ = ha(_)),
                    b.forEach(function (w, A) {
                        !(d.isUndefined(w) || w === null) &&
                            t.append(
                                o === !0
                                    ? $s([_], A, s)
                                    : o === null
                                    ? _
                                    : _ + "[]",
                                u(w)
                            );
                    }),
                    !1
                );
        }
        return Or(g) ? !0 : (t.append($s(p, _, s), u(g)), !1);
    }
    const f = [],
        m = Object.assign(Tp, {
            defaultVisitor: l,
            convertValue: u,
            isVisitable: Or,
        });
    function E(g, _) {
        if (!d.isUndefined(g)) {
            if (f.indexOf(g) !== -1)
                throw Error("Circular reference detected in " + _.join("."));
            f.push(g),
                d.forEach(g, function (b, y) {
                    (!(d.isUndefined(b) || b === null) &&
                        i.call(t, b, d.isString(y) ? y.trim() : y, _, m)) ===
                        !0 && E(b, _ ? _.concat(y) : [y]);
                }),
                f.pop();
        }
    }
    if (!d.isObject(e)) throw new TypeError("data must be an object");
    return E(e), t;
}
function Rs(e) {
    const t = {
        "!": "%21",
        "'": "%27",
        "(": "%28",
        ")": "%29",
        "~": "%7E",
        "%20": "+",
        "%00": "\0",
    };
    return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g, function (r) {
        return t[r];
    });
}
function hi(e, t) {
    (this._pairs = []), e && Mn(e, this, t);
}
const pa = hi.prototype;
pa.append = function (t, n) {
    this._pairs.push([t, n]);
};
pa.toString = function (t) {
    const n = t
        ? function (r) {
              return t.call(this, r, Rs);
          }
        : Rs;
    return this._pairs
        .map(function (i) {
            return n(i[0]) + "=" + n(i[1]);
        }, "")
        .join("&");
};
function Sp(e) {
    return encodeURIComponent(e)
        .replace(/%3A/gi, ":")
        .replace(/%24/g, "$")
        .replace(/%2C/gi, ",")
        .replace(/%20/g, "+")
        .replace(/%5B/gi, "[")
        .replace(/%5D/gi, "]");
}
function _a(e, t, n) {
    if (!t) return e;
    const r = (n && n.encode) || Sp,
        i = n && n.serialize;
    let s;
    if (
        (i
            ? (s = i(t, n))
            : (s = d.isURLSearchParams(t)
                  ? t.toString()
                  : new hi(t, n).toString(r)),
        s)
    ) {
        const o = e.indexOf("#");
        o !== -1 && (e = e.slice(0, o)),
            (e += (e.indexOf("?") === -1 ? "?" : "&") + s);
    }
    return e;
}
class Is {
    constructor() {
        this.handlers = [];
    }
    use(t, n, r) {
        return (
            this.handlers.push({
                fulfilled: t,
                rejected: n,
                synchronous: r ? r.synchronous : !1,
                runWhen: r ? r.runWhen : null,
            }),
            this.handlers.length - 1
        );
    }
    eject(t) {
        this.handlers[t] && (this.handlers[t] = null);
    }
    clear() {
        this.handlers && (this.handlers = []);
    }
    forEach(t) {
        d.forEach(this.handlers, function (r) {
            r !== null && t(r);
        });
    }
}
const ma = {
        silentJSONParsing: !0,
        forcedJSONParsing: !0,
        clarifyTimeoutError: !1,
    },
    Op = typeof URLSearchParams < "u" ? URLSearchParams : hi,
    Cp = typeof FormData < "u" ? FormData : null,
    xp = typeof Blob < "u" ? Blob : null,
    Np = {
        isBrowser: !0,
        classes: { URLSearchParams: Op, FormData: Cp, Blob: xp },
        protocols: ["http", "https", "file", "blob", "url", "data"],
    },
    ga = typeof window < "u" && typeof document < "u",
    Dp = ((e) => ga && ["ReactNative", "NativeScript", "NS"].indexOf(e) < 0)(
        typeof navigator < "u" && navigator.product
    ),
    Lp =
        typeof WorkerGlobalScope < "u" &&
        self instanceof WorkerGlobalScope &&
        typeof self.importScripts == "function",
    $p = Object.freeze(
        Object.defineProperty(
            {
                __proto__: null,
                hasBrowserEnv: ga,
                hasStandardBrowserEnv: Dp,
                hasStandardBrowserWebWorkerEnv: Lp,
            },
            Symbol.toStringTag,
            { value: "Module" }
        )
    ),
    rt = { ...$p, ...Np };
function Rp(e, t) {
    return Mn(
        e,
        new rt.classes.URLSearchParams(),
        Object.assign(
            {
                visitor: function (n, r, i, s) {
                    return rt.isNode && d.isBuffer(n)
                        ? (this.append(r, n.toString("base64")), !1)
                        : s.defaultVisitor.apply(this, arguments);
                },
            },
            t
        )
    );
}
function Ip(e) {
    return d
        .matchAll(/\w+|\[(\w*)]/g, e)
        .map((t) => (t[0] === "[]" ? "" : t[1] || t[0]));
}
function Pp(e) {
    const t = {},
        n = Object.keys(e);
    let r;
    const i = n.length;
    let s;
    for (r = 0; r < i; r++) (s = n[r]), (t[s] = e[s]);
    return t;
}
function Ea(e) {
    function t(n, r, i, s) {
        let o = n[s++];
        if (o === "__proto__") return !0;
        const a = Number.isFinite(+o),
            c = s >= n.length;
        return (
            (o = !o && d.isArray(i) ? i.length : o),
            c
                ? (d.hasOwnProp(i, o) ? (i[o] = [i[o], r]) : (i[o] = r), !a)
                : ((!i[o] || !d.isObject(i[o])) && (i[o] = []),
                  t(n, r, i[o], s) && d.isArray(i[o]) && (i[o] = Pp(i[o])),
                  !a)
        );
    }
    if (d.isFormData(e) && d.isFunction(e.entries)) {
        const n = {};
        return (
            d.forEachEntry(e, (r, i) => {
                t(Ip(r), i, n, 0);
            }),
            n
        );
    }
    return null;
}
function Mp(e, t, n) {
    if (d.isString(e))
        try {
            return (t || JSON.parse)(e), d.trim(e);
        } catch (r) {
            if (r.name !== "SyntaxError") throw r;
        }
    return (n || JSON.stringify)(e);
}
const pi = {
    transitional: ma,
    adapter: ["xhr", "http"],
    transformRequest: [
        function (t, n) {
            const r = n.getContentType() || "",
                i = r.indexOf("application/json") > -1,
                s = d.isObject(t);
            if (
                (s && d.isHTMLForm(t) && (t = new FormData(t)), d.isFormData(t))
            )
                return i ? JSON.stringify(Ea(t)) : t;
            if (
                d.isArrayBuffer(t) ||
                d.isBuffer(t) ||
                d.isStream(t) ||
                d.isFile(t) ||
                d.isBlob(t)
            )
                return t;
            if (d.isArrayBufferView(t)) return t.buffer;
            if (d.isURLSearchParams(t))
                return (
                    n.setContentType(
                        "application/x-www-form-urlencoded;charset=utf-8",
                        !1
                    ),
                    t.toString()
                );
            let a;
            if (s) {
                if (r.indexOf("application/x-www-form-urlencoded") > -1)
                    return Rp(t, this.formSerializer).toString();
                if (
                    (a = d.isFileList(t)) ||
                    r.indexOf("multipart/form-data") > -1
                ) {
                    const c = this.env && this.env.FormData;
                    return Mn(
                        a ? { "files[]": t } : t,
                        c && new c(),
                        this.formSerializer
                    );
                }
            }
            return s || i
                ? (n.setContentType("application/json", !1), Mp(t))
                : t;
        },
    ],
    transformResponse: [
        function (t) {
            const n = this.transitional || pi.transitional,
                r = n && n.forcedJSONParsing,
                i = this.responseType === "json";
            if (t && d.isString(t) && ((r && !this.responseType) || i)) {
                const o = !(n && n.silentJSONParsing) && i;
                try {
                    return JSON.parse(t);
                } catch (a) {
                    if (o)
                        throw a.name === "SyntaxError"
                            ? O.from(
                                  a,
                                  O.ERR_BAD_RESPONSE,
                                  this,
                                  null,
                                  this.response
                              )
                            : a;
                }
            }
            return t;
        },
    ],
    timeout: 0,
    xsrfCookieName: "XSRF-TOKEN",
    xsrfHeaderName: "X-XSRF-TOKEN",
    maxContentLength: -1,
    maxBodyLength: -1,
    env: { FormData: rt.classes.FormData, Blob: rt.classes.Blob },
    validateStatus: function (t) {
        return t >= 200 && t < 300;
    },
    headers: {
        common: {
            Accept: "application/json, text/plain, */*",
            "Content-Type": void 0,
        },
    },
};
d.forEach(["delete", "get", "head", "post", "put", "patch"], (e) => {
    pi.headers[e] = {};
});
const _i = pi,
    kp = d.toObjectSet([
        "age",
        "authorization",
        "content-length",
        "content-type",
        "etag",
        "expires",
        "from",
        "host",
        "if-modified-since",
        "if-unmodified-since",
        "last-modified",
        "location",
        "max-forwards",
        "proxy-authorization",
        "referer",
        "retry-after",
        "user-agent",
    ]),
    Fp = (e) => {
        const t = {};
        let n, r, i;
        return (
            e &&
                e
                    .split(
                        `
`
                    )
                    .forEach(function (o) {
                        (i = o.indexOf(":")),
                            (n = o.substring(0, i).trim().toLowerCase()),
                            (r = o.substring(i + 1).trim()),
                            !(!n || (t[n] && kp[n])) &&
                                (n === "set-cookie"
                                    ? t[n]
                                        ? t[n].push(r)
                                        : (t[n] = [r])
                                    : (t[n] = t[n] ? t[n] + ", " + r : r));
                    }),
            t
        );
    },
    Ps = Symbol("internals");
function Ae(e) {
    return e && String(e).trim().toLowerCase();
}
function hn(e) {
    return e === !1 || e == null ? e : d.isArray(e) ? e.map(hn) : String(e);
}
function jp(e) {
    const t = Object.create(null),
        n = /([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;
    let r;
    for (; (r = n.exec(e)); ) t[r[1]] = r[2];
    return t;
}
const Bp = (e) => /^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());
function fr(e, t, n, r, i) {
    if (d.isFunction(r)) return r.call(this, t, n);
    if ((i && (t = n), !!d.isString(t))) {
        if (d.isString(r)) return t.indexOf(r) !== -1;
        if (d.isRegExp(r)) return r.test(t);
    }
}
function Hp(e) {
    return e
        .trim()
        .toLowerCase()
        .replace(/([a-z\d])(\w*)/g, (t, n, r) => n.toUpperCase() + r);
}
function Vp(e, t) {
    const n = d.toCamelCase(" " + t);
    ["get", "set", "has"].forEach((r) => {
        Object.defineProperty(e, r + n, {
            value: function (i, s, o) {
                return this[r].call(this, t, i, s, o);
            },
            configurable: !0,
        });
    });
}
class kn {
    constructor(t) {
        t && this.set(t);
    }
    set(t, n, r) {
        const i = this;
        function s(a, c, u) {
            const l = Ae(c);
            if (!l) throw new Error("header name must be a non-empty string");
            const f = d.findKey(i, l);
            (!f ||
                i[f] === void 0 ||
                u === !0 ||
                (u === void 0 && i[f] !== !1)) &&
                (i[f || c] = hn(a));
        }
        const o = (a, c) => d.forEach(a, (u, l) => s(u, l, c));
        return (
            d.isPlainObject(t) || t instanceof this.constructor
                ? o(t, n)
                : d.isString(t) && (t = t.trim()) && !Bp(t)
                ? o(Fp(t), n)
                : t != null && s(n, t, r),
            this
        );
    }
    get(t, n) {
        if (((t = Ae(t)), t)) {
            const r = d.findKey(this, t);
            if (r) {
                const i = this[r];
                if (!n) return i;
                if (n === !0) return jp(i);
                if (d.isFunction(n)) return n.call(this, i, r);
                if (d.isRegExp(n)) return n.exec(i);
                throw new TypeError("parser must be boolean|regexp|function");
            }
        }
    }
    has(t, n) {
        if (((t = Ae(t)), t)) {
            const r = d.findKey(this, t);
            return !!(
                r &&
                this[r] !== void 0 &&
                (!n || fr(this, this[r], r, n))
            );
        }
        return !1;
    }
    delete(t, n) {
        const r = this;
        let i = !1;
        function s(o) {
            if (((o = Ae(o)), o)) {
                const a = d.findKey(r, o);
                a && (!n || fr(r, r[a], a, n)) && (delete r[a], (i = !0));
            }
        }
        return d.isArray(t) ? t.forEach(s) : s(t), i;
    }
    clear(t) {
        const n = Object.keys(this);
        let r = n.length,
            i = !1;
        for (; r--; ) {
            const s = n[r];
            (!t || fr(this, this[s], s, t, !0)) && (delete this[s], (i = !0));
        }
        return i;
    }
    normalize(t) {
        const n = this,
            r = {};
        return (
            d.forEach(this, (i, s) => {
                const o = d.findKey(r, s);
                if (o) {
                    (n[o] = hn(i)), delete n[s];
                    return;
                }
                const a = t ? Hp(s) : String(s).trim();
                a !== s && delete n[s], (n[a] = hn(i)), (r[a] = !0);
            }),
            this
        );
    }
    concat(...t) {
        return this.constructor.concat(this, ...t);
    }
    toJSON(t) {
        const n = Object.create(null);
        return (
            d.forEach(this, (r, i) => {
                r != null &&
                    r !== !1 &&
                    (n[i] = t && d.isArray(r) ? r.join(", ") : r);
            }),
            n
        );
    }
    [Symbol.iterator]() {
        return Object.entries(this.toJSON())[Symbol.iterator]();
    }
    toString() {
        return Object.entries(this.toJSON()).map(([t, n]) => t + ": " + n)
            .join(`
`);
    }
    get [Symbol.toStringTag]() {
        return "AxiosHeaders";
    }
    static from(t) {
        return t instanceof this ? t : new this(t);
    }
    static concat(t, ...n) {
        const r = new this(t);
        return n.forEach((i) => r.set(i)), r;
    }
    static accessor(t) {
        const r = (this[Ps] = this[Ps] = { accessors: {} }).accessors,
            i = this.prototype;
        function s(o) {
            const a = Ae(o);
            r[a] || (Vp(i, o), (r[a] = !0));
        }
        return d.isArray(t) ? t.forEach(s) : s(t), this;
    }
}
kn.accessor([
    "Content-Type",
    "Content-Length",
    "Accept",
    "Accept-Encoding",
    "User-Agent",
    "Authorization",
]);
d.reduceDescriptors(kn.prototype, ({ value: e }, t) => {
    let n = t[0].toUpperCase() + t.slice(1);
    return {
        get: () => e,
        set(r) {
            this[n] = r;
        },
    };
});
d.freezeMethods(kn);
const ut = kn;
function dr(e, t) {
    const n = this || _i,
        r = t || n,
        i = ut.from(r.headers);
    let s = r.data;
    return (
        d.forEach(e, function (a) {
            s = a.call(n, s, i.normalize(), t ? t.status : void 0);
        }),
        i.normalize(),
        s
    );
}
function ba(e) {
    return !!(e && e.__CANCEL__);
}
function Be(e, t, n) {
    O.call(this, e ?? "canceled", O.ERR_CANCELED, t, n),
        (this.name = "CanceledError");
}
d.inherits(Be, O, { __CANCEL__: !0 });
function Wp(e, t, n) {
    const r = n.config.validateStatus;
    !n.status || !r || r(n.status)
        ? e(n)
        : t(
              new O(
                  "Request failed with status code " + n.status,
                  [O.ERR_BAD_REQUEST, O.ERR_BAD_RESPONSE][
                      Math.floor(n.status / 100) - 4
                  ],
                  n.config,
                  n.request,
                  n
              )
          );
}
const Up = rt.hasStandardBrowserEnv
    ? {
          write(e, t, n, r, i, s) {
              const o = [e + "=" + encodeURIComponent(t)];
              d.isNumber(n) && o.push("expires=" + new Date(n).toGMTString()),
                  d.isString(r) && o.push("path=" + r),
                  d.isString(i) && o.push("domain=" + i),
                  s === !0 && o.push("secure"),
                  (document.cookie = o.join("; "));
          },
          read(e) {
              const t = document.cookie.match(
                  new RegExp("(^|;\\s*)(" + e + ")=([^;]*)")
              );
              return t ? decodeURIComponent(t[3]) : null;
          },
          remove(e) {
              this.write(e, "", Date.now() - 864e5);
          },
      }
    : {
          write() {},
          read() {
              return null;
          },
          remove() {},
      };
function Kp(e) {
    return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(e);
}
function zp(e, t) {
    return t ? e.replace(/\/?\/$/, "") + "/" + t.replace(/^\/+/, "") : e;
}
function va(e, t) {
    return e && !Kp(t) ? zp(e, t) : t;
}
const qp = rt.hasStandardBrowserEnv
    ? (function () {
          const t = /(msie|trident)/i.test(navigator.userAgent),
              n = document.createElement("a");
          let r;
          function i(s) {
              let o = s;
              return (
                  t && (n.setAttribute("href", o), (o = n.href)),
                  n.setAttribute("href", o),
                  {
                      href: n.href,
                      protocol: n.protocol ? n.protocol.replace(/:$/, "") : "",
                      host: n.host,
                      search: n.search ? n.search.replace(/^\?/, "") : "",
                      hash: n.hash ? n.hash.replace(/^#/, "") : "",
                      hostname: n.hostname,
                      port: n.port,
                      pathname:
                          n.pathname.charAt(0) === "/"
                              ? n.pathname
                              : "/" + n.pathname,
                  }
              );
          }
          return (
              (r = i(window.location.href)),
              function (o) {
                  const a = d.isString(o) ? i(o) : o;
                  return a.protocol === r.protocol && a.host === r.host;
              }
          );
      })()
    : (function () {
          return function () {
              return !0;
          };
      })();
function Yp(e) {
    const t = /^([-+\w]{1,25})(:?\/\/|:)/.exec(e);
    return (t && t[1]) || "";
}
function Gp(e, t) {
    e = e || 10;
    const n = new Array(e),
        r = new Array(e);
    let i = 0,
        s = 0,
        o;
    return (
        (t = t !== void 0 ? t : 1e3),
        function (c) {
            const u = Date.now(),
                l = r[s];
            o || (o = u), (n[i] = c), (r[i] = u);
            let f = s,
                m = 0;
            for (; f !== i; ) (m += n[f++]), (f = f % e);
            if (((i = (i + 1) % e), i === s && (s = (s + 1) % e), u - o < t))
                return;
            const E = l && u - l;
            return E ? Math.round((m * 1e3) / E) : void 0;
        }
    );
}
function Ms(e, t) {
    let n = 0;
    const r = Gp(50, 250);
    return (i) => {
        const s = i.loaded,
            o = i.lengthComputable ? i.total : void 0,
            a = s - n,
            c = r(a),
            u = s <= o;
        n = s;
        const l = {
            loaded: s,
            total: o,
            progress: o ? s / o : void 0,
            bytes: a,
            rate: c || void 0,
            estimated: c && o && u ? (o - s) / c : void 0,
            event: i,
        };
        (l[t ? "download" : "upload"] = !0), e(l);
    };
}
const Xp = typeof XMLHttpRequest < "u",
    Jp =
        Xp &&
        function (e) {
            return new Promise(function (n, r) {
                let i = e.data;
                const s = ut.from(e.headers).normalize();
                let { responseType: o, withXSRFToken: a } = e,
                    c;
                function u() {
                    e.cancelToken && e.cancelToken.unsubscribe(c),
                        e.signal && e.signal.removeEventListener("abort", c);
                }
                let l;
                if (d.isFormData(i)) {
                    if (
                        rt.hasStandardBrowserEnv ||
                        rt.hasStandardBrowserWebWorkerEnv
                    )
                        s.setContentType(!1);
                    else if ((l = s.getContentType()) !== !1) {
                        const [_, ...p] = l
                            ? l
                                  .split(";")
                                  .map((b) => b.trim())
                                  .filter(Boolean)
                            : [];
                        s.setContentType(
                            [_ || "multipart/form-data", ...p].join("; ")
                        );
                    }
                }
                let f = new XMLHttpRequest();
                if (e.auth) {
                    const _ = e.auth.username || "",
                        p = e.auth.password
                            ? unescape(encodeURIComponent(e.auth.password))
                            : "";
                    s.set("Authorization", "Basic " + btoa(_ + ":" + p));
                }
                const m = va(e.baseURL, e.url);
                f.open(
                    e.method.toUpperCase(),
                    _a(m, e.params, e.paramsSerializer),
                    !0
                ),
                    (f.timeout = e.timeout);
                function E() {
                    if (!f) return;
                    const _ = ut.from(
                            "getAllResponseHeaders" in f &&
                                f.getAllResponseHeaders()
                        ),
                        b = {
                            data:
                                !o || o === "text" || o === "json"
                                    ? f.responseText
                                    : f.response,
                            status: f.status,
                            statusText: f.statusText,
                            headers: _,
                            config: e,
                            request: f,
                        };
                    Wp(
                        function (w) {
                            n(w), u();
                        },
                        function (w) {
                            r(w), u();
                        },
                        b
                    ),
                        (f = null);
                }
                if (
                    ("onloadend" in f
                        ? (f.onloadend = E)
                        : (f.onreadystatechange = function () {
                              !f ||
                                  f.readyState !== 4 ||
                                  (f.status === 0 &&
                                      !(
                                          f.responseURL &&
                                          f.responseURL.indexOf("file:") === 0
                                      )) ||
                                  setTimeout(E);
                          }),
                    (f.onabort = function () {
                        f &&
                            (r(new O("Request aborted", O.ECONNABORTED, e, f)),
                            (f = null));
                    }),
                    (f.onerror = function () {
                        r(new O("Network Error", O.ERR_NETWORK, e, f)),
                            (f = null);
                    }),
                    (f.ontimeout = function () {
                        let p = e.timeout
                            ? "timeout of " + e.timeout + "ms exceeded"
                            : "timeout exceeded";
                        const b = e.transitional || ma;
                        e.timeoutErrorMessage && (p = e.timeoutErrorMessage),
                            r(
                                new O(
                                    p,
                                    b.clarifyTimeoutError
                                        ? O.ETIMEDOUT
                                        : O.ECONNABORTED,
                                    e,
                                    f
                                )
                            ),
                            (f = null);
                    }),
                    rt.hasStandardBrowserEnv &&
                        (a && d.isFunction(a) && (a = a(e)),
                        a || (a !== !1 && qp(m))))
                ) {
                    const _ =
                        e.xsrfHeaderName &&
                        e.xsrfCookieName &&
                        Up.read(e.xsrfCookieName);
                    _ && s.set(e.xsrfHeaderName, _);
                }
                i === void 0 && s.setContentType(null),
                    "setRequestHeader" in f &&
                        d.forEach(s.toJSON(), function (p, b) {
                            f.setRequestHeader(b, p);
                        }),
                    d.isUndefined(e.withCredentials) ||
                        (f.withCredentials = !!e.withCredentials),
                    o && o !== "json" && (f.responseType = e.responseType),
                    typeof e.onDownloadProgress == "function" &&
                        f.addEventListener(
                            "progress",
                            Ms(e.onDownloadProgress, !0)
                        ),
                    typeof e.onUploadProgress == "function" &&
                        f.upload &&
                        f.upload.addEventListener(
                            "progress",
                            Ms(e.onUploadProgress)
                        ),
                    (e.cancelToken || e.signal) &&
                        ((c = (_) => {
                            f &&
                                (r(!_ || _.type ? new Be(null, e, f) : _),
                                f.abort(),
                                (f = null));
                        }),
                        e.cancelToken && e.cancelToken.subscribe(c),
                        e.signal &&
                            (e.signal.aborted
                                ? c()
                                : e.signal.addEventListener("abort", c)));
                const g = Yp(m);
                if (g && rt.protocols.indexOf(g) === -1) {
                    r(
                        new O(
                            "Unsupported protocol " + g + ":",
                            O.ERR_BAD_REQUEST,
                            e
                        )
                    );
                    return;
                }
                f.send(i || null);
            });
        },
    Cr = { http: Ap, xhr: Jp };
d.forEach(Cr, (e, t) => {
    if (e) {
        try {
            Object.defineProperty(e, "name", { value: t });
        } catch {}
        Object.defineProperty(e, "adapterName", { value: t });
    }
});
const ks = (e) => `- ${e}`,
    Qp = (e) => d.isFunction(e) || e === null || e === !1,
    ya = {
        getAdapter: (e) => {
            e = d.isArray(e) ? e : [e];
            const { length: t } = e;
            let n, r;
            const i = {};
            for (let s = 0; s < t; s++) {
                n = e[s];
                let o;
                if (
                    ((r = n),
                    !Qp(n) &&
                        ((r = Cr[(o = String(n)).toLowerCase()]), r === void 0))
                )
                    throw new O(`Unknown adapter '${o}'`);
                if (r) break;
                i[o || "#" + s] = r;
            }
            if (!r) {
                const s = Object.entries(i).map(
                    ([a, c]) =>
                        `adapter ${a} ` +
                        (c === !1
                            ? "is not supported by the environment"
                            : "is not available in the build")
                );
                let o = t
                    ? s.length > 1
                        ? `since :
` +
                          s.map(ks).join(`
`)
                        : " " + ks(s[0])
                    : "as no adapter specified";
                throw new O(
                    "There is no suitable adapter to dispatch the request " + o,
                    "ERR_NOT_SUPPORT"
                );
            }
            return r;
        },
        adapters: Cr,
    };
function hr(e) {
    if (
        (e.cancelToken && e.cancelToken.throwIfRequested(),
        e.signal && e.signal.aborted)
    )
        throw new Be(null, e);
}
function Fs(e) {
    return (
        hr(e),
        (e.headers = ut.from(e.headers)),
        (e.data = dr.call(e, e.transformRequest)),
        ["post", "put", "patch"].indexOf(e.method) !== -1 &&
            e.headers.setContentType("application/x-www-form-urlencoded", !1),
        ya
            .getAdapter(e.adapter || _i.adapter)(e)
            .then(
                function (r) {
                    return (
                        hr(e),
                        (r.data = dr.call(e, e.transformResponse, r)),
                        (r.headers = ut.from(r.headers)),
                        r
                    );
                },
                function (r) {
                    return (
                        ba(r) ||
                            (hr(e),
                            r &&
                                r.response &&
                                ((r.response.data = dr.call(
                                    e,
                                    e.transformResponse,
                                    r.response
                                )),
                                (r.response.headers = ut.from(
                                    r.response.headers
                                )))),
                        Promise.reject(r)
                    );
                }
            )
    );
}
const js = (e) => (e instanceof ut ? { ...e } : e);
function ae(e, t) {
    t = t || {};
    const n = {};
    function r(u, l, f) {
        return d.isPlainObject(u) && d.isPlainObject(l)
            ? d.merge.call({ caseless: f }, u, l)
            : d.isPlainObject(l)
            ? d.merge({}, l)
            : d.isArray(l)
            ? l.slice()
            : l;
    }
    function i(u, l, f) {
        if (d.isUndefined(l)) {
            if (!d.isUndefined(u)) return r(void 0, u, f);
        } else return r(u, l, f);
    }
    function s(u, l) {
        if (!d.isUndefined(l)) return r(void 0, l);
    }
    function o(u, l) {
        if (d.isUndefined(l)) {
            if (!d.isUndefined(u)) return r(void 0, u);
        } else return r(void 0, l);
    }
    function a(u, l, f) {
        if (f in t) return r(u, l);
        if (f in e) return r(void 0, u);
    }
    const c = {
        url: s,
        method: s,
        data: s,
        baseURL: o,
        transformRequest: o,
        transformResponse: o,
        paramsSerializer: o,
        timeout: o,
        timeoutMessage: o,
        withCredentials: o,
        withXSRFToken: o,
        adapter: o,
        responseType: o,
        xsrfCookieName: o,
        xsrfHeaderName: o,
        onUploadProgress: o,
        onDownloadProgress: o,
        decompress: o,
        maxContentLength: o,
        maxBodyLength: o,
        beforeRedirect: o,
        transport: o,
        httpAgent: o,
        httpsAgent: o,
        cancelToken: o,
        socketPath: o,
        responseEncoding: o,
        validateStatus: a,
        headers: (u, l) => i(js(u), js(l), !0),
    };
    return (
        d.forEach(Object.keys(Object.assign({}, e, t)), function (l) {
            const f = c[l] || i,
                m = f(e[l], t[l], l);
            (d.isUndefined(m) && f !== a) || (n[l] = m);
        }),
        n
    );
}
const Aa = "1.6.8",
    mi = {};
["object", "boolean", "number", "function", "string", "symbol"].forEach(
    (e, t) => {
        mi[e] = function (r) {
            return typeof r === e || "a" + (t < 1 ? "n " : " ") + e;
        };
    }
);
const Bs = {};
mi.transitional = function (t, n, r) {
    function i(s, o) {
        return (
            "[Axios v" +
            Aa +
            "] Transitional option '" +
            s +
            "'" +
            o +
            (r ? ". " + r : "")
        );
    }
    return (s, o, a) => {
        if (t === !1)
            throw new O(
                i(o, " has been removed" + (n ? " in " + n : "")),
                O.ERR_DEPRECATED
            );
        return (
            n &&
                !Bs[o] &&
                ((Bs[o] = !0),
                console.warn(
                    i(
                        o,
                        " has been deprecated since v" +
                            n +
                            " and will be removed in the near future"
                    )
                )),
            t ? t(s, o, a) : !0
        );
    };
};
function Zp(e, t, n) {
    if (typeof e != "object")
        throw new O("options must be an object", O.ERR_BAD_OPTION_VALUE);
    const r = Object.keys(e);
    let i = r.length;
    for (; i-- > 0; ) {
        const s = r[i],
            o = t[s];
        if (o) {
            const a = e[s],
                c = a === void 0 || o(a, s, e);
            if (c !== !0)
                throw new O(
                    "option " + s + " must be " + c,
                    O.ERR_BAD_OPTION_VALUE
                );
            continue;
        }
        if (n !== !0) throw new O("Unknown option " + s, O.ERR_BAD_OPTION);
    }
}
const xr = { assertOptions: Zp, validators: mi },
    mt = xr.validators;
class yn {
    constructor(t) {
        (this.defaults = t),
            (this.interceptors = { request: new Is(), response: new Is() });
    }
    async request(t, n) {
        try {
            return await this._request(t, n);
        } catch (r) {
            if (r instanceof Error) {
                let i;
                Error.captureStackTrace
                    ? Error.captureStackTrace((i = {}))
                    : (i = new Error());
                const s = i.stack ? i.stack.replace(/^.+\n/, "") : "";
                r.stack
                    ? s &&
                      !String(r.stack).endsWith(s.replace(/^.+\n.+\n/, "")) &&
                      (r.stack +=
                          `
` + s)
                    : (r.stack = s);
            }
            throw r;
        }
    }
    _request(t, n) {
        typeof t == "string" ? ((n = n || {}), (n.url = t)) : (n = t || {}),
            (n = ae(this.defaults, n));
        const { transitional: r, paramsSerializer: i, headers: s } = n;
        r !== void 0 &&
            xr.assertOptions(
                r,
                {
                    silentJSONParsing: mt.transitional(mt.boolean),
                    forcedJSONParsing: mt.transitional(mt.boolean),
                    clarifyTimeoutError: mt.transitional(mt.boolean),
                },
                !1
            ),
            i != null &&
                (d.isFunction(i)
                    ? (n.paramsSerializer = { serialize: i })
                    : xr.assertOptions(
                          i,
                          { encode: mt.function, serialize: mt.function },
                          !0
                      )),
            (n.method = (
                n.method ||
                this.defaults.method ||
                "get"
            ).toLowerCase());
        let o = s && d.merge(s.common, s[n.method]);
        s &&
            d.forEach(
                ["delete", "get", "head", "post", "put", "patch", "common"],
                (g) => {
                    delete s[g];
                }
            ),
            (n.headers = ut.concat(o, s));
        const a = [];
        let c = !0;
        this.interceptors.request.forEach(function (_) {
            (typeof _.runWhen == "function" && _.runWhen(n) === !1) ||
                ((c = c && _.synchronous), a.unshift(_.fulfilled, _.rejected));
        });
        const u = [];
        this.interceptors.response.forEach(function (_) {
            u.push(_.fulfilled, _.rejected);
        });
        let l,
            f = 0,
            m;
        if (!c) {
            const g = [Fs.bind(this), void 0];
            for (
                g.unshift.apply(g, a),
                    g.push.apply(g, u),
                    m = g.length,
                    l = Promise.resolve(n);
                f < m;

            )
                l = l.then(g[f++], g[f++]);
            return l;
        }
        m = a.length;
        let E = n;
        for (f = 0; f < m; ) {
            const g = a[f++],
                _ = a[f++];
            try {
                E = g(E);
            } catch (p) {
                _.call(this, p);
                break;
            }
        }
        try {
            l = Fs.call(this, E);
        } catch (g) {
            return Promise.reject(g);
        }
        for (f = 0, m = u.length; f < m; ) l = l.then(u[f++], u[f++]);
        return l;
    }
    getUri(t) {
        t = ae(this.defaults, t);
        const n = va(t.baseURL, t.url);
        return _a(n, t.params, t.paramsSerializer);
    }
}
d.forEach(["delete", "get", "head", "options"], function (t) {
    yn.prototype[t] = function (n, r) {
        return this.request(
            ae(r || {}, { method: t, url: n, data: (r || {}).data })
        );
    };
});
d.forEach(["post", "put", "patch"], function (t) {
    function n(r) {
        return function (s, o, a) {
            return this.request(
                ae(a || {}, {
                    method: t,
                    headers: r ? { "Content-Type": "multipart/form-data" } : {},
                    url: s,
                    data: o,
                })
            );
        };
    }
    (yn.prototype[t] = n()), (yn.prototype[t + "Form"] = n(!0));
});
const pn = yn;
class gi {
    constructor(t) {
        if (typeof t != "function")
            throw new TypeError("executor must be a function.");
        let n;
        this.promise = new Promise(function (s) {
            n = s;
        });
        const r = this;
        this.promise.then((i) => {
            if (!r._listeners) return;
            let s = r._listeners.length;
            for (; s-- > 0; ) r._listeners[s](i);
            r._listeners = null;
        }),
            (this.promise.then = (i) => {
                let s;
                const o = new Promise((a) => {
                    r.subscribe(a), (s = a);
                }).then(i);
                return (
                    (o.cancel = function () {
                        r.unsubscribe(s);
                    }),
                    o
                );
            }),
            t(function (s, o, a) {
                r.reason || ((r.reason = new Be(s, o, a)), n(r.reason));
            });
    }
    throwIfRequested() {
        if (this.reason) throw this.reason;
    }
    subscribe(t) {
        if (this.reason) {
            t(this.reason);
            return;
        }
        this._listeners ? this._listeners.push(t) : (this._listeners = [t]);
    }
    unsubscribe(t) {
        if (!this._listeners) return;
        const n = this._listeners.indexOf(t);
        n !== -1 && this._listeners.splice(n, 1);
    }
    static source() {
        let t;
        return {
            token: new gi(function (i) {
                t = i;
            }),
            cancel: t,
        };
    }
}
const t_ = gi;
function e_(e) {
    return function (n) {
        return e.apply(null, n);
    };
}
function n_(e) {
    return d.isObject(e) && e.isAxiosError === !0;
}
const Nr = {
    Continue: 100,
    SwitchingProtocols: 101,
    Processing: 102,
    EarlyHints: 103,
    Ok: 200,
    Created: 201,
    Accepted: 202,
    NonAuthoritativeInformation: 203,
    NoContent: 204,
    ResetContent: 205,
    PartialContent: 206,
    MultiStatus: 207,
    AlreadyReported: 208,
    ImUsed: 226,
    MultipleChoices: 300,
    MovedPermanently: 301,
    Found: 302,
    SeeOther: 303,
    NotModified: 304,
    UseProxy: 305,
    Unused: 306,
    TemporaryRedirect: 307,
    PermanentRedirect: 308,
    BadRequest: 400,
    Unauthorized: 401,
    PaymentRequired: 402,
    Forbidden: 403,
    NotFound: 404,
    MethodNotAllowed: 405,
    NotAcceptable: 406,
    ProxyAuthenticationRequired: 407,
    RequestTimeout: 408,
    Conflict: 409,
    Gone: 410,
    LengthRequired: 411,
    PreconditionFailed: 412,
    PayloadTooLarge: 413,
    UriTooLong: 414,
    UnsupportedMediaType: 415,
    RangeNotSatisfiable: 416,
    ExpectationFailed: 417,
    ImATeapot: 418,
    MisdirectedRequest: 421,
    UnprocessableEntity: 422,
    Locked: 423,
    FailedDependency: 424,
    TooEarly: 425,
    UpgradeRequired: 426,
    PreconditionRequired: 428,
    TooManyRequests: 429,
    RequestHeaderFieldsTooLarge: 431,
    UnavailableForLegalReasons: 451,
    InternalServerError: 500,
    NotImplemented: 501,
    BadGateway: 502,
    ServiceUnavailable: 503,
    GatewayTimeout: 504,
    HttpVersionNotSupported: 505,
    VariantAlsoNegotiates: 506,
    InsufficientStorage: 507,
    LoopDetected: 508,
    NotExtended: 510,
    NetworkAuthenticationRequired: 511,
};
Object.entries(Nr).forEach(([e, t]) => {
    Nr[t] = e;
});
const r_ = Nr;
function wa(e) {
    const t = new pn(e),
        n = ra(pn.prototype.request, t);
    return (
        d.extend(n, pn.prototype, t, { allOwnKeys: !0 }),
        d.extend(n, t, null, { allOwnKeys: !0 }),
        (n.create = function (i) {
            return wa(ae(e, i));
        }),
        n
    );
}
const L = wa(_i);
L.Axios = pn;
L.CanceledError = Be;
L.CancelToken = t_;
L.isCancel = ba;
L.VERSION = Aa;
L.toFormData = Mn;
L.AxiosError = O;
L.Cancel = L.CanceledError;
L.all = function (t) {
    return Promise.all(t);
};
L.spread = e_;
L.isAxiosError = n_;
L.mergeConfig = ae;
L.AxiosHeaders = ut;
L.formToJSON = (e) => Ea(d.isHTMLForm(e) ? new FormData(e) : e);
L.getAdapter = ya.getAdapter;
L.HttpStatusCode = r_;
L.default = L;
const i_ = L;
window.axios = i_;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
var Dr = !1,
    Lr = !1,
    kt = [],
    $r = -1;
function s_(e) {
    o_(e);
}
function o_(e) {
    kt.includes(e) || kt.push(e), a_();
}
function Ta(e) {
    let t = kt.indexOf(e);
    t !== -1 && t > $r && kt.splice(t, 1);
}
function a_() {
    !Lr && !Dr && ((Dr = !0), queueMicrotask(c_));
}
function c_() {
    (Dr = !1), (Lr = !0);
    for (let e = 0; e < kt.length; e++) kt[e](), ($r = e);
    (kt.length = 0), ($r = -1), (Lr = !1);
}
var _e,
    Kt,
    me,
    Sa,
    Rr = !0;
function l_(e) {
    (Rr = !1), e(), (Rr = !0);
}
function u_(e) {
    (_e = e.reactive),
        (me = e.release),
        (Kt = (t) =>
            e.effect(t, {
                scheduler: (n) => {
                    Rr ? s_(n) : n();
                },
            })),
        (Sa = e.raw);
}
function Hs(e) {
    Kt = e;
}
function f_(e) {
    let t = () => {};
    return [
        (r) => {
            let i = Kt(r);
            return (
                e._x_effects ||
                    ((e._x_effects = new Set()),
                    (e._x_runEffects = () => {
                        e._x_effects.forEach((s) => s());
                    })),
                e._x_effects.add(i),
                (t = () => {
                    i !== void 0 && (e._x_effects.delete(i), me(i));
                }),
                i
            );
        },
        () => {
            t();
        },
    ];
}
function Oa(e, t) {
    let n = !0,
        r,
        i = Kt(() => {
            let s = e();
            JSON.stringify(s),
                n
                    ? (r = s)
                    : queueMicrotask(() => {
                          t(s, r), (r = s);
                      }),
                (n = !1);
        });
    return () => me(i);
}
function Ne(e, t, n = {}) {
    e.dispatchEvent(
        new CustomEvent(t, {
            detail: n,
            bubbles: !0,
            composed: !0,
            cancelable: !0,
        })
    );
}
function yt(e, t) {
    if (typeof ShadowRoot == "function" && e instanceof ShadowRoot) {
        Array.from(e.children).forEach((i) => yt(i, t));
        return;
    }
    let n = !1;
    if ((t(e, () => (n = !0)), n)) return;
    let r = e.firstElementChild;
    for (; r; ) yt(r, t), (r = r.nextElementSibling);
}
function Q(e, ...t) {
    console.warn(`Alpine Warning: ${e}`, ...t);
}
var Vs = !1;
function d_() {
    Vs &&
        Q(
            "Alpine has already been initialized on this page. Calling Alpine.start() more than once can cause problems."
        ),
        (Vs = !0),
        document.body ||
            Q(
                "Unable to initialize. Trying to load Alpine before `<body>` is available. Did you forget to add `defer` in Alpine's `<script>` tag?"
            ),
        Ne(document, "alpine:init"),
        Ne(document, "alpine:initializing"),
        wi(),
        __((t) => dt(t, yt)),
        vi((t) => bi(t)),
        Ma((t, n) => {
            Ci(t, n).forEach((r) => r());
        });
    let e = (t) => !Fn(t.parentElement, !0);
    Array.from(document.querySelectorAll(Na().join(",")))
        .filter(e)
        .forEach((t) => {
            dt(t);
        }),
        Ne(document, "alpine:initialized");
}
var Ei = [],
    Ca = [];
function xa() {
    return Ei.map((e) => e());
}
function Na() {
    return Ei.concat(Ca).map((e) => e());
}
function Da(e) {
    Ei.push(e);
}
function La(e) {
    Ca.push(e);
}
function Fn(e, t = !1) {
    return He(e, (n) => {
        if ((t ? Na() : xa()).some((i) => n.matches(i))) return !0;
    });
}
function He(e, t) {
    if (e) {
        if (t(e)) return e;
        if ((e._x_teleportBack && (e = e._x_teleportBack), !!e.parentElement))
            return He(e.parentElement, t);
    }
}
function h_(e) {
    return xa().some((t) => e.matches(t));
}
var $a = [];
function p_(e) {
    $a.push(e);
}
function dt(e, t = yt, n = () => {}) {
    N_(() => {
        t(e, (r, i) => {
            n(r, i),
                $a.forEach((s) => s(r, i)),
                Ci(r, r.attributes).forEach((s) => s()),
                r._x_ignore && i();
        });
    });
}
function bi(e, t = yt) {
    t(e, (n) => {
        Fa(n), m_(n);
    });
}
var Ra = [],
    Ia = [],
    Pa = [];
function __(e) {
    Pa.push(e);
}
function vi(e, t) {
    typeof t == "function"
        ? (e._x_cleanups || (e._x_cleanups = []), e._x_cleanups.push(t))
        : ((t = e), Ia.push(t));
}
function Ma(e) {
    Ra.push(e);
}
function ka(e, t, n) {
    e._x_attributeCleanups || (e._x_attributeCleanups = {}),
        e._x_attributeCleanups[t] || (e._x_attributeCleanups[t] = []),
        e._x_attributeCleanups[t].push(n);
}
function Fa(e, t) {
    e._x_attributeCleanups &&
        Object.entries(e._x_attributeCleanups).forEach(([n, r]) => {
            (t === void 0 || t.includes(n)) &&
                (r.forEach((i) => i()), delete e._x_attributeCleanups[n]);
        });
}
function m_(e) {
    if (e._x_cleanups) for (; e._x_cleanups.length; ) e._x_cleanups.pop()();
}
var yi = new MutationObserver(Si),
    Ai = !1;
function wi() {
    yi.observe(document, {
        subtree: !0,
        childList: !0,
        attributes: !0,
        attributeOldValue: !0,
    }),
        (Ai = !0);
}
function ja() {
    g_(), yi.disconnect(), (Ai = !1);
}
var we = [];
function g_() {
    let e = yi.takeRecords();
    we.push(() => e.length > 0 && Si(e));
    let t = we.length;
    queueMicrotask(() => {
        if (we.length === t) for (; we.length > 0; ) we.shift()();
    });
}
function I(e) {
    if (!Ai) return e();
    ja();
    let t = e();
    return wi(), t;
}
var Ti = !1,
    An = [];
function E_() {
    Ti = !0;
}
function b_() {
    (Ti = !1), Si(An), (An = []);
}
function Si(e) {
    if (Ti) {
        An = An.concat(e);
        return;
    }
    let t = new Set(),
        n = new Set(),
        r = new Map(),
        i = new Map();
    for (let s = 0; s < e.length; s++)
        if (
            !e[s].target._x_ignoreMutationObserver &&
            (e[s].type === "childList" &&
                (e[s].addedNodes.forEach((o) => o.nodeType === 1 && t.add(o)),
                e[s].removedNodes.forEach((o) => o.nodeType === 1 && n.add(o))),
            e[s].type === "attributes")
        ) {
            let o = e[s].target,
                a = e[s].attributeName,
                c = e[s].oldValue,
                u = () => {
                    r.has(o) || r.set(o, []),
                        r.get(o).push({ name: a, value: o.getAttribute(a) });
                },
                l = () => {
                    i.has(o) || i.set(o, []), i.get(o).push(a);
                };
            o.hasAttribute(a) && c === null
                ? u()
                : o.hasAttribute(a)
                ? (l(), u())
                : l();
        }
    i.forEach((s, o) => {
        Fa(o, s);
    }),
        r.forEach((s, o) => {
            Ra.forEach((a) => a(o, s));
        });
    for (let s of n) t.has(s) || (Ia.forEach((o) => o(s)), bi(s));
    t.forEach((s) => {
        (s._x_ignoreSelf = !0), (s._x_ignore = !0);
    });
    for (let s of t)
        n.has(s) ||
            (s.isConnected &&
                (delete s._x_ignoreSelf,
                delete s._x_ignore,
                Pa.forEach((o) => o(s)),
                (s._x_ignore = !0),
                (s._x_ignoreSelf = !0)));
    t.forEach((s) => {
        delete s._x_ignoreSelf, delete s._x_ignore;
    }),
        (t = null),
        (n = null),
        (r = null),
        (i = null);
}
function Ba(e) {
    return We(ce(e));
}
function Ve(e, t, n) {
    return (
        (e._x_dataStack = [t, ...ce(n || e)]),
        () => {
            e._x_dataStack = e._x_dataStack.filter((r) => r !== t);
        }
    );
}
function ce(e) {
    return e._x_dataStack
        ? e._x_dataStack
        : typeof ShadowRoot == "function" && e instanceof ShadowRoot
        ? ce(e.host)
        : e.parentNode
        ? ce(e.parentNode)
        : [];
}
function We(e) {
    return new Proxy({ objects: e }, v_);
}
var v_ = {
    ownKeys({ objects: e }) {
        return Array.from(new Set(e.flatMap((t) => Object.keys(t))));
    },
    has({ objects: e }, t) {
        return t == Symbol.unscopables
            ? !1
            : e.some(
                  (n) =>
                      Object.prototype.hasOwnProperty.call(n, t) ||
                      Reflect.has(n, t)
              );
    },
    get({ objects: e }, t, n) {
        return t == "toJSON"
            ? y_
            : Reflect.get(e.find((r) => Reflect.has(r, t)) || {}, t, n);
    },
    set({ objects: e }, t, n, r) {
        const i =
                e.find((o) => Object.prototype.hasOwnProperty.call(o, t)) ||
                e[e.length - 1],
            s = Object.getOwnPropertyDescriptor(i, t);
        return s != null && s.set && s != null && s.get
            ? Reflect.set(i, t, n, r)
            : Reflect.set(i, t, n);
    },
};
function y_() {
    return Reflect.ownKeys(this).reduce(
        (t, n) => ((t[n] = Reflect.get(this, n)), t),
        {}
    );
}
function Ha(e) {
    let t = (r) => typeof r == "object" && !Array.isArray(r) && r !== null,
        n = (r, i = "") => {
            Object.entries(Object.getOwnPropertyDescriptors(r)).forEach(
                ([s, { value: o, enumerable: a }]) => {
                    if (
                        a === !1 ||
                        o === void 0 ||
                        (typeof o == "object" && o !== null && o.__v_skip)
                    )
                        return;
                    let c = i === "" ? s : `${i}.${s}`;
                    typeof o == "object" && o !== null && o._x_interceptor
                        ? (r[s] = o.initialize(e, c, s))
                        : t(o) && o !== r && !(o instanceof Element) && n(o, c);
                }
            );
        };
    return n(e);
}
function Va(e, t = () => {}) {
    let n = {
        initialValue: void 0,
        _x_interceptor: !0,
        initialize(r, i, s) {
            return e(
                this.initialValue,
                () => A_(r, i),
                (o) => Ir(r, i, o),
                i,
                s
            );
        },
    };
    return (
        t(n),
        (r) => {
            if (typeof r == "object" && r !== null && r._x_interceptor) {
                let i = n.initialize.bind(n);
                n.initialize = (s, o, a) => {
                    let c = r.initialize(s, o, a);
                    return (n.initialValue = c), i(s, o, a);
                };
            } else n.initialValue = r;
            return n;
        }
    );
}
function A_(e, t) {
    return t.split(".").reduce((n, r) => n[r], e);
}
function Ir(e, t, n) {
    if ((typeof t == "string" && (t = t.split(".")), t.length === 1))
        e[t[0]] = n;
    else {
        if (t.length === 0) throw error;
        return e[t[0]] || (e[t[0]] = {}), Ir(e[t[0]], t.slice(1), n);
    }
}
var Wa = {};
function et(e, t) {
    Wa[e] = t;
}
function Pr(e, t) {
    return (
        Object.entries(Wa).forEach(([n, r]) => {
            let i = null;
            function s() {
                if (i) return i;
                {
                    let [o, a] = Ga(t);
                    return (i = { interceptor: Va, ...o }), vi(t, a), i;
                }
            }
            Object.defineProperty(e, `$${n}`, {
                get() {
                    return r(t, s());
                },
                enumerable: !1,
            });
        }),
        e
    );
}
function w_(e, t, n, ...r) {
    try {
        return n(...r);
    } catch (i) {
        Re(i, e, t);
    }
}
function Re(e, t, n = void 0) {
    (e = Object.assign(e ?? { message: "No error message given." }, {
        el: t,
        expression: n,
    })),
        console.warn(
            `Alpine Expression Error: ${e.message}

${
    n
        ? 'Expression: "' +
          n +
          `"

`
        : ""
}`,
            t
        ),
        setTimeout(() => {
            throw e;
        }, 0);
}
var _n = !0;
function Ua(e) {
    let t = _n;
    _n = !1;
    let n = e();
    return (_n = t), n;
}
function Ft(e, t, n = {}) {
    let r;
    return j(e, t)((i) => (r = i), n), r;
}
function j(...e) {
    return Ka(...e);
}
var Ka = za;
function T_(e) {
    Ka = e;
}
function za(e, t) {
    let n = {};
    Pr(n, e);
    let r = [n, ...ce(e)],
        i = typeof t == "function" ? S_(r, t) : C_(r, t, e);
    return w_.bind(null, e, t, i);
}
function S_(e, t) {
    return (n = () => {}, { scope: r = {}, params: i = [] } = {}) => {
        let s = t.apply(We([r, ...e]), i);
        wn(n, s);
    };
}
var pr = {};
function O_(e, t) {
    if (pr[e]) return pr[e];
    let n = Object.getPrototypeOf(async function () {}).constructor,
        r =
            /^[\n\s]*if.*\(.*\)/.test(e.trim()) ||
            /^(let|const)\s/.test(e.trim())
                ? `(async()=>{ ${e} })()`
                : e,
        s = (() => {
            try {
                let o = new n(
                    ["__self", "scope"],
                    `with (scope) { __self.result = ${r} }; __self.finished = true; return __self.result;`
                );
                return (
                    Object.defineProperty(o, "name", {
                        value: `[Alpine] ${e}`,
                    }),
                    o
                );
            } catch (o) {
                return Re(o, t, e), Promise.resolve();
            }
        })();
    return (pr[e] = s), s;
}
function C_(e, t, n) {
    let r = O_(t, n);
    return (i = () => {}, { scope: s = {}, params: o = [] } = {}) => {
        (r.result = void 0), (r.finished = !1);
        let a = We([s, ...e]);
        if (typeof r == "function") {
            let c = r(r, a).catch((u) => Re(u, n, t));
            r.finished
                ? (wn(i, r.result, a, o, n), (r.result = void 0))
                : c
                      .then((u) => {
                          wn(i, u, a, o, n);
                      })
                      .catch((u) => Re(u, n, t))
                      .finally(() => (r.result = void 0));
        }
    };
}
function wn(e, t, n, r, i) {
    if (_n && typeof t == "function") {
        let s = t.apply(n, r);
        s instanceof Promise
            ? s.then((o) => wn(e, o, n, r)).catch((o) => Re(o, i, t))
            : e(s);
    } else
        typeof t == "object" && t instanceof Promise
            ? t.then((s) => e(s))
            : e(t);
}
var Oi = "x-";
function ge(e = "") {
    return Oi + e;
}
function x_(e) {
    Oi = e;
}
var Mr = {};
function $(e, t) {
    return (
        (Mr[e] = t),
        {
            before(n) {
                if (!Mr[n]) {
                    console.warn(
                        String.raw`Cannot find directive \`${n}\`. \`${e}\` will use the default order of execution`
                    );
                    return;
                }
                const r = Pt.indexOf(n);
                Pt.splice(r >= 0 ? r : Pt.indexOf("DEFAULT"), 0, e);
            },
        }
    );
}
function Ci(e, t, n) {
    if (((t = Array.from(t)), e._x_virtualDirectives)) {
        let s = Object.entries(e._x_virtualDirectives).map(([a, c]) => ({
                name: a,
                value: c,
            })),
            o = qa(s);
        (s = s.map((a) =>
            o.find((c) => c.name === a.name)
                ? { name: `x-bind:${a.name}`, value: `"${a.value}"` }
                : a
        )),
            (t = t.concat(s));
    }
    let r = {};
    return t
        .map(Qa((s, o) => (r[s] = o)))
        .filter(tc)
        .map(L_(r, n))
        .sort($_)
        .map((s) => D_(e, s));
}
function qa(e) {
    return Array.from(e)
        .map(Qa())
        .filter((t) => !tc(t));
}
var kr = !1,
    Oe = new Map(),
    Ya = Symbol();
function N_(e) {
    kr = !0;
    let t = Symbol();
    (Ya = t), Oe.set(t, []);
    let n = () => {
            for (; Oe.get(t).length; ) Oe.get(t).shift()();
            Oe.delete(t);
        },
        r = () => {
            (kr = !1), n();
        };
    e(n), r();
}
function Ga(e) {
    let t = [],
        n = (a) => t.push(a),
        [r, i] = f_(e);
    return (
        t.push(i),
        [
            {
                Alpine: Ue,
                effect: r,
                cleanup: n,
                evaluateLater: j.bind(j, e),
                evaluate: Ft.bind(Ft, e),
            },
            () => t.forEach((a) => a()),
        ]
    );
}
function D_(e, t) {
    let n = () => {},
        r = Mr[t.type] || n,
        [i, s] = Ga(e);
    ka(e, t.original, s);
    let o = () => {
        e._x_ignore ||
            e._x_ignoreSelf ||
            (r.inline && r.inline(e, t, i),
            (r = r.bind(r, e, t, i)),
            kr ? Oe.get(Ya).push(r) : r());
    };
    return (o.runCleanups = s), o;
}
var Xa =
        (e, t) =>
        ({ name: n, value: r }) => (
            n.startsWith(e) && (n = n.replace(e, t)), { name: n, value: r }
        ),
    Ja = (e) => e;
function Qa(e = () => {}) {
    return ({ name: t, value: n }) => {
        let { name: r, value: i } = Za.reduce((s, o) => o(s), {
            name: t,
            value: n,
        });
        return r !== t && e(r, t), { name: r, value: i };
    };
}
var Za = [];
function xi(e) {
    Za.push(e);
}
function tc({ name: e }) {
    return ec().test(e);
}
var ec = () => new RegExp(`^${Oi}([^:^.]+)\\b`);
function L_(e, t) {
    return ({ name: n, value: r }) => {
        let i = n.match(ec()),
            s = n.match(/:([a-zA-Z0-9\-_:]+)/),
            o = n.match(/\.[^.\]]+(?=[^\]]*$)/g) || [],
            a = t || e[n] || n;
        return {
            type: i ? i[1] : null,
            value: s ? s[1] : null,
            modifiers: o.map((c) => c.replace(".", "")),
            expression: r,
            original: a,
        };
    };
}
var Fr = "DEFAULT",
    Pt = [
        "ignore",
        "ref",
        "data",
        "id",
        "anchor",
        "bind",
        "init",
        "for",
        "model",
        "modelable",
        "transition",
        "show",
        "if",
        Fr,
        "teleport",
    ];
function $_(e, t) {
    let n = Pt.indexOf(e.type) === -1 ? Fr : e.type,
        r = Pt.indexOf(t.type) === -1 ? Fr : t.type;
    return Pt.indexOf(n) - Pt.indexOf(r);
}
var jr = [],
    Ni = !1;
function Di(e = () => {}) {
    return (
        queueMicrotask(() => {
            Ni ||
                setTimeout(() => {
                    Br();
                });
        }),
        new Promise((t) => {
            jr.push(() => {
                e(), t();
            });
        })
    );
}
function Br() {
    for (Ni = !1; jr.length; ) jr.shift()();
}
function R_() {
    Ni = !0;
}
function Li(e, t) {
    return Array.isArray(t)
        ? Ws(e, t.join(" "))
        : typeof t == "object" && t !== null
        ? I_(e, t)
        : typeof t == "function"
        ? Li(e, t())
        : Ws(e, t);
}
function Ws(e, t) {
    let n = (i) =>
            i
                .split(" ")
                .filter((s) => !e.classList.contains(s))
                .filter(Boolean),
        r = (i) => (
            e.classList.add(...i),
            () => {
                e.classList.remove(...i);
            }
        );
    return (t = t === !0 ? (t = "") : t || ""), r(n(t));
}
function I_(e, t) {
    let n = (a) => a.split(" ").filter(Boolean),
        r = Object.entries(t)
            .flatMap(([a, c]) => (c ? n(a) : !1))
            .filter(Boolean),
        i = Object.entries(t)
            .flatMap(([a, c]) => (c ? !1 : n(a)))
            .filter(Boolean),
        s = [],
        o = [];
    return (
        i.forEach((a) => {
            e.classList.contains(a) && (e.classList.remove(a), o.push(a));
        }),
        r.forEach((a) => {
            e.classList.contains(a) || (e.classList.add(a), s.push(a));
        }),
        () => {
            o.forEach((a) => e.classList.add(a)),
                s.forEach((a) => e.classList.remove(a));
        }
    );
}
function jn(e, t) {
    return typeof t == "object" && t !== null ? P_(e, t) : M_(e, t);
}
function P_(e, t) {
    let n = {};
    return (
        Object.entries(t).forEach(([r, i]) => {
            (n[r] = e.style[r]),
                r.startsWith("--") || (r = k_(r)),
                e.style.setProperty(r, i);
        }),
        setTimeout(() => {
            e.style.length === 0 && e.removeAttribute("style");
        }),
        () => {
            jn(e, n);
        }
    );
}
function M_(e, t) {
    let n = e.getAttribute("style", t);
    return (
        e.setAttribute("style", t),
        () => {
            e.setAttribute("style", n || "");
        }
    );
}
function k_(e) {
    return e.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
}
function Hr(e, t = () => {}) {
    let n = !1;
    return function () {
        n ? t.apply(this, arguments) : ((n = !0), e.apply(this, arguments));
    };
}
$(
    "transition",
    (e, { value: t, modifiers: n, expression: r }, { evaluate: i }) => {
        typeof r == "function" && (r = i(r)),
            r !== !1 &&
                (!r || typeof r == "boolean" ? j_(e, n, t) : F_(e, r, t));
    }
);
function F_(e, t, n) {
    nc(e, Li, ""),
        {
            enter: (i) => {
                e._x_transition.enter.during = i;
            },
            "enter-start": (i) => {
                e._x_transition.enter.start = i;
            },
            "enter-end": (i) => {
                e._x_transition.enter.end = i;
            },
            leave: (i) => {
                e._x_transition.leave.during = i;
            },
            "leave-start": (i) => {
                e._x_transition.leave.start = i;
            },
            "leave-end": (i) => {
                e._x_transition.leave.end = i;
            },
        }[n](t);
}
function j_(e, t, n) {
    nc(e, jn);
    let r = !t.includes("in") && !t.includes("out") && !n,
        i = r || t.includes("in") || ["enter"].includes(n),
        s = r || t.includes("out") || ["leave"].includes(n);
    t.includes("in") && !r && (t = t.filter((b, y) => y < t.indexOf("out"))),
        t.includes("out") &&
            !r &&
            (t = t.filter((b, y) => y > t.indexOf("out")));
    let o = !t.includes("opacity") && !t.includes("scale"),
        a = o || t.includes("opacity"),
        c = o || t.includes("scale"),
        u = a ? 0 : 1,
        l = c ? Te(t, "scale", 95) / 100 : 1,
        f = Te(t, "delay", 0) / 1e3,
        m = Te(t, "origin", "center"),
        E = "opacity, transform",
        g = Te(t, "duration", 150) / 1e3,
        _ = Te(t, "duration", 75) / 1e3,
        p = "cubic-bezier(0.4, 0.0, 0.2, 1)";
    i &&
        ((e._x_transition.enter.during = {
            transformOrigin: m,
            transitionDelay: `${f}s`,
            transitionProperty: E,
            transitionDuration: `${g}s`,
            transitionTimingFunction: p,
        }),
        (e._x_transition.enter.start = {
            opacity: u,
            transform: `scale(${l})`,
        }),
        (e._x_transition.enter.end = { opacity: 1, transform: "scale(1)" })),
        s &&
            ((e._x_transition.leave.during = {
                transformOrigin: m,
                transitionDelay: `${f}s`,
                transitionProperty: E,
                transitionDuration: `${_}s`,
                transitionTimingFunction: p,
            }),
            (e._x_transition.leave.start = {
                opacity: 1,
                transform: "scale(1)",
            }),
            (e._x_transition.leave.end = {
                opacity: u,
                transform: `scale(${l})`,
            }));
}
function nc(e, t, n = {}) {
    e._x_transition ||
        (e._x_transition = {
            enter: { during: n, start: n, end: n },
            leave: { during: n, start: n, end: n },
            in(r = () => {}, i = () => {}) {
                Vr(
                    e,
                    t,
                    {
                        during: this.enter.during,
                        start: this.enter.start,
                        end: this.enter.end,
                    },
                    r,
                    i
                );
            },
            out(r = () => {}, i = () => {}) {
                Vr(
                    e,
                    t,
                    {
                        during: this.leave.during,
                        start: this.leave.start,
                        end: this.leave.end,
                    },
                    r,
                    i
                );
            },
        });
}
window.Element.prototype._x_toggleAndCascadeWithTransitions = function (
    e,
    t,
    n,
    r
) {
    const i =
        document.visibilityState === "visible"
            ? requestAnimationFrame
            : setTimeout;
    let s = () => i(n);
    if (t) {
        e._x_transition && (e._x_transition.enter || e._x_transition.leave)
            ? e._x_transition.enter &&
              (Object.entries(e._x_transition.enter.during).length ||
                  Object.entries(e._x_transition.enter.start).length ||
                  Object.entries(e._x_transition.enter.end).length)
                ? e._x_transition.in(n)
                : s()
            : e._x_transition
            ? e._x_transition.in(n)
            : s();
        return;
    }
    (e._x_hidePromise = e._x_transition
        ? new Promise((o, a) => {
              e._x_transition.out(
                  () => {},
                  () => o(r)
              ),
                  e._x_transitioning &&
                      e._x_transitioning.beforeCancel(() =>
                          a({ isFromCancelledTransition: !0 })
                      );
          })
        : Promise.resolve(r)),
        queueMicrotask(() => {
            let o = rc(e);
            o
                ? (o._x_hideChildren || (o._x_hideChildren = []),
                  o._x_hideChildren.push(e))
                : i(() => {
                      let a = (c) => {
                          let u = Promise.all([
                              c._x_hidePromise,
                              ...(c._x_hideChildren || []).map(a),
                          ]).then(([l]) => l());
                          return (
                              delete c._x_hidePromise,
                              delete c._x_hideChildren,
                              u
                          );
                      };
                      a(e).catch((c) => {
                          if (!c.isFromCancelledTransition) throw c;
                      });
                  });
        });
};
function rc(e) {
    let t = e.parentNode;
    if (t) return t._x_hidePromise ? t : rc(t);
}
function Vr(
    e,
    t,
    { during: n, start: r, end: i } = {},
    s = () => {},
    o = () => {}
) {
    if (
        (e._x_transitioning && e._x_transitioning.cancel(),
        Object.keys(n).length === 0 &&
            Object.keys(r).length === 0 &&
            Object.keys(i).length === 0)
    ) {
        s(), o();
        return;
    }
    let a, c, u;
    B_(e, {
        start() {
            a = t(e, r);
        },
        during() {
            c = t(e, n);
        },
        before: s,
        end() {
            a(), (u = t(e, i));
        },
        after: o,
        cleanup() {
            c(), u();
        },
    });
}
function B_(e, t) {
    let n,
        r,
        i,
        s = Hr(() => {
            I(() => {
                (n = !0),
                    r || t.before(),
                    i || (t.end(), Br()),
                    t.after(),
                    e.isConnected && t.cleanup(),
                    delete e._x_transitioning;
            });
        });
    (e._x_transitioning = {
        beforeCancels: [],
        beforeCancel(o) {
            this.beforeCancels.push(o);
        },
        cancel: Hr(function () {
            for (; this.beforeCancels.length; ) this.beforeCancels.shift()();
            s();
        }),
        finish: s,
    }),
        I(() => {
            t.start(), t.during();
        }),
        R_(),
        requestAnimationFrame(() => {
            if (n) return;
            let o =
                    Number(
                        getComputedStyle(e)
                            .transitionDuration.replace(/,.*/, "")
                            .replace("s", "")
                    ) * 1e3,
                a =
                    Number(
                        getComputedStyle(e)
                            .transitionDelay.replace(/,.*/, "")
                            .replace("s", "")
                    ) * 1e3;
            o === 0 &&
                (o =
                    Number(
                        getComputedStyle(e).animationDuration.replace("s", "")
                    ) * 1e3),
                I(() => {
                    t.before();
                }),
                (r = !0),
                requestAnimationFrame(() => {
                    n ||
                        (I(() => {
                            t.end();
                        }),
                        Br(),
                        setTimeout(e._x_transitioning.finish, o + a),
                        (i = !0));
                });
        });
}
function Te(e, t, n) {
    if (e.indexOf(t) === -1) return n;
    const r = e[e.indexOf(t) + 1];
    if (!r || (t === "scale" && isNaN(r))) return n;
    if (t === "duration" || t === "delay") {
        let i = r.match(/([0-9]+)ms/);
        if (i) return i[1];
    }
    return t === "origin" &&
        ["top", "right", "left", "center", "bottom"].includes(
            e[e.indexOf(t) + 2]
        )
        ? [r, e[e.indexOf(t) + 2]].join(" ")
        : r;
}
var At = !1;
function zt(e, t = () => {}) {
    return (...n) => (At ? t(...n) : e(...n));
}
function H_(e) {
    return (...t) => At && e(...t);
}
var ic = [];
function Bn(e) {
    ic.push(e);
}
function V_(e, t) {
    ic.forEach((n) => n(e, t)),
        (At = !0),
        sc(() => {
            dt(t, (n, r) => {
                r(n, () => {});
            });
        }),
        (At = !1);
}
var Wr = !1;
function W_(e, t) {
    t._x_dataStack || (t._x_dataStack = e._x_dataStack),
        (At = !0),
        (Wr = !0),
        sc(() => {
            U_(t);
        }),
        (At = !1),
        (Wr = !1);
}
function U_(e) {
    let t = !1;
    dt(e, (r, i) => {
        yt(r, (s, o) => {
            if (t && h_(s)) return o();
            (t = !0), i(s, o);
        });
    });
}
function sc(e) {
    let t = Kt;
    Hs((n, r) => {
        let i = t(n);
        return me(i), () => {};
    }),
        e(),
        Hs(t);
}
function oc(e, t, n, r = []) {
    switch (
        (e._x_bindings || (e._x_bindings = _e({})),
        (e._x_bindings[t] = n),
        (t = r.includes("camel") ? Q_(t) : t),
        t)
    ) {
        case "value":
            K_(e, n);
            break;
        case "style":
            q_(e, n);
            break;
        case "class":
            z_(e, n);
            break;
        case "selected":
        case "checked":
            Y_(e, t, n);
            break;
        default:
            ac(e, t, n);
            break;
    }
}
function K_(e, t) {
    if (e.type === "radio")
        e.attributes.value === void 0 && (e.value = t),
            window.fromModel &&
                (typeof t == "boolean"
                    ? (e.checked = mn(e.value) === t)
                    : (e.checked = Us(e.value, t)));
    else if (e.type === "checkbox")
        Number.isInteger(t)
            ? (e.value = t)
            : !Array.isArray(t) &&
              typeof t != "boolean" &&
              ![null, void 0].includes(t)
            ? (e.value = String(t))
            : Array.isArray(t)
            ? (e.checked = t.some((n) => Us(n, e.value)))
            : (e.checked = !!t);
    else if (e.tagName === "SELECT") J_(e, t);
    else {
        if (e.value === t) return;
        e.value = t === void 0 ? "" : t;
    }
}
function z_(e, t) {
    e._x_undoAddedClasses && e._x_undoAddedClasses(),
        (e._x_undoAddedClasses = Li(e, t));
}
function q_(e, t) {
    e._x_undoAddedStyles && e._x_undoAddedStyles(),
        (e._x_undoAddedStyles = jn(e, t));
}
function Y_(e, t, n) {
    ac(e, t, n), X_(e, t, n);
}
function ac(e, t, n) {
    [null, void 0, !1].includes(n) && Z_(t)
        ? e.removeAttribute(t)
        : (cc(t) && (n = t), G_(e, t, n));
}
function G_(e, t, n) {
    e.getAttribute(t) != n && e.setAttribute(t, n);
}
function X_(e, t, n) {
    e[t] !== n && (e[t] = n);
}
function J_(e, t) {
    const n = [].concat(t).map((r) => r + "");
    Array.from(e.options).forEach((r) => {
        r.selected = n.includes(r.value);
    });
}
function Q_(e) {
    return e.toLowerCase().replace(/-(\w)/g, (t, n) => n.toUpperCase());
}
function Us(e, t) {
    return e == t;
}
function mn(e) {
    return [1, "1", "true", "on", "yes", !0].includes(e)
        ? !0
        : [0, "0", "false", "off", "no", !1].includes(e)
        ? !1
        : e
        ? !!e
        : null;
}
function cc(e) {
    return [
        "disabled",
        "checked",
        "required",
        "readonly",
        "open",
        "selected",
        "autofocus",
        "itemscope",
        "multiple",
        "novalidate",
        "allowfullscreen",
        "allowpaymentrequest",
        "formnovalidate",
        "autoplay",
        "controls",
        "loop",
        "muted",
        "playsinline",
        "default",
        "ismap",
        "reversed",
        "async",
        "defer",
        "nomodule",
    ].includes(e);
}
function Z_(e) {
    return ![
        "aria-pressed",
        "aria-checked",
        "aria-expanded",
        "aria-selected",
    ].includes(e);
}
function tm(e, t, n) {
    return e._x_bindings && e._x_bindings[t] !== void 0
        ? e._x_bindings[t]
        : lc(e, t, n);
}
function em(e, t, n, r = !0) {
    if (e._x_bindings && e._x_bindings[t] !== void 0) return e._x_bindings[t];
    if (e._x_inlineBindings && e._x_inlineBindings[t] !== void 0) {
        let i = e._x_inlineBindings[t];
        return (i.extract = r), Ua(() => Ft(e, i.expression));
    }
    return lc(e, t, n);
}
function lc(e, t, n) {
    let r = e.getAttribute(t);
    return r === null
        ? typeof n == "function"
            ? n()
            : n
        : r === ""
        ? !0
        : cc(t)
        ? !![t, "true"].includes(r)
        : r;
}
function uc(e, t) {
    var n;
    return function () {
        var r = this,
            i = arguments,
            s = function () {
                (n = null), e.apply(r, i);
            };
        clearTimeout(n), (n = setTimeout(s, t));
    };
}
function fc(e, t) {
    let n;
    return function () {
        let r = this,
            i = arguments;
        n || (e.apply(r, i), (n = !0), setTimeout(() => (n = !1), t));
    };
}
function dc({ get: e, set: t }, { get: n, set: r }) {
    let i = !0,
        s,
        o = Kt(() => {
            let a = e(),
                c = n();
            if (i) r(_r(a)), (i = !1);
            else {
                let u = JSON.stringify(a),
                    l = JSON.stringify(c);
                u !== s ? r(_r(a)) : u !== l && t(_r(c));
            }
            (s = JSON.stringify(e())), JSON.stringify(n());
        });
    return () => {
        me(o);
    };
}
function _r(e) {
    return typeof e == "object" ? JSON.parse(JSON.stringify(e)) : e;
}
function nm(e) {
    (Array.isArray(e) ? e : [e]).forEach((n) => n(Ue));
}
var $t = {},
    Ks = !1;
function rm(e, t) {
    if ((Ks || (($t = _e($t)), (Ks = !0)), t === void 0)) return $t[e];
    ($t[e] = t),
        typeof t == "object" &&
            t !== null &&
            t.hasOwnProperty("init") &&
            typeof t.init == "function" &&
            $t[e].init(),
        Ha($t[e]);
}
function im() {
    return $t;
}
var hc = {};
function sm(e, t) {
    let n = typeof t != "function" ? () => t : t;
    return e instanceof Element ? pc(e, n()) : ((hc[e] = n), () => {});
}
function om(e) {
    return (
        Object.entries(hc).forEach(([t, n]) => {
            Object.defineProperty(e, t, {
                get() {
                    return (...r) => n(...r);
                },
            });
        }),
        e
    );
}
function pc(e, t, n) {
    let r = [];
    for (; r.length; ) r.pop()();
    let i = Object.entries(t).map(([o, a]) => ({ name: o, value: a })),
        s = qa(i);
    return (
        (i = i.map((o) =>
            s.find((a) => a.name === o.name)
                ? { name: `x-bind:${o.name}`, value: `"${o.value}"` }
                : o
        )),
        Ci(e, i, n).map((o) => {
            r.push(o.runCleanups), o();
        }),
        () => {
            for (; r.length; ) r.pop()();
        }
    );
}
var _c = {};
function am(e, t) {
    _c[e] = t;
}
function cm(e, t) {
    return (
        Object.entries(_c).forEach(([n, r]) => {
            Object.defineProperty(e, n, {
                get() {
                    return (...i) => r.bind(t)(...i);
                },
                enumerable: !1,
            });
        }),
        e
    );
}
var lm = {
        get reactive() {
            return _e;
        },
        get release() {
            return me;
        },
        get effect() {
            return Kt;
        },
        get raw() {
            return Sa;
        },
        version: "3.13.8",
        flushAndStopDeferringMutations: b_,
        dontAutoEvaluateFunctions: Ua,
        disableEffectScheduling: l_,
        startObservingMutations: wi,
        stopObservingMutations: ja,
        setReactivityEngine: u_,
        onAttributeRemoved: ka,
        onAttributesAdded: Ma,
        closestDataStack: ce,
        skipDuringClone: zt,
        onlyDuringClone: H_,
        addRootSelector: Da,
        addInitSelector: La,
        interceptClone: Bn,
        addScopeToNode: Ve,
        deferMutations: E_,
        mapAttributes: xi,
        evaluateLater: j,
        interceptInit: p_,
        setEvaluator: T_,
        mergeProxies: We,
        extractProp: em,
        findClosest: He,
        onElRemoved: vi,
        closestRoot: Fn,
        destroyTree: bi,
        interceptor: Va,
        transition: Vr,
        setStyles: jn,
        mutateDom: I,
        directive: $,
        entangle: dc,
        throttle: fc,
        debounce: uc,
        evaluate: Ft,
        initTree: dt,
        nextTick: Di,
        prefixed: ge,
        prefix: x_,
        plugin: nm,
        magic: et,
        store: rm,
        start: d_,
        clone: W_,
        cloneNode: V_,
        bound: tm,
        $data: Ba,
        watch: Oa,
        walk: yt,
        data: am,
        bind: sm,
    },
    Ue = lm;
function um(e, t) {
    const n = Object.create(null),
        r = e.split(",");
    for (let i = 0; i < r.length; i++) n[r[i]] = !0;
    return t ? (i) => !!n[i.toLowerCase()] : (i) => !!n[i];
}
var fm = Object.freeze({}),
    dm = Object.prototype.hasOwnProperty,
    Hn = (e, t) => dm.call(e, t),
    jt = Array.isArray,
    De = (e) => mc(e) === "[object Map]",
    hm = (e) => typeof e == "string",
    $i = (e) => typeof e == "symbol",
    Vn = (e) => e !== null && typeof e == "object",
    pm = Object.prototype.toString,
    mc = (e) => pm.call(e),
    gc = (e) => mc(e).slice(8, -1),
    Ri = (e) =>
        hm(e) && e !== "NaN" && e[0] !== "-" && "" + parseInt(e, 10) === e,
    _m = (e) => {
        const t = Object.create(null);
        return (n) => t[n] || (t[n] = e(n));
    },
    mm = _m((e) => e.charAt(0).toUpperCase() + e.slice(1)),
    Ec = (e, t) => e !== t && (e === e || t === t),
    Ur = new WeakMap(),
    Se = [],
    nt,
    Bt = Symbol("iterate"),
    Kr = Symbol("Map key iterate");
function gm(e) {
    return e && e._isEffect === !0;
}
function Em(e, t = fm) {
    gm(e) && (e = e.raw);
    const n = ym(e, t);
    return t.lazy || n(), n;
}
function bm(e) {
    e.active &&
        (bc(e), e.options.onStop && e.options.onStop(), (e.active = !1));
}
var vm = 0;
function ym(e, t) {
    const n = function () {
        if (!n.active) return e();
        if (!Se.includes(n)) {
            bc(n);
            try {
                return wm(), Se.push(n), (nt = n), e();
            } finally {
                Se.pop(), vc(), (nt = Se[Se.length - 1]);
            }
        }
    };
    return (
        (n.id = vm++),
        (n.allowRecurse = !!t.allowRecurse),
        (n._isEffect = !0),
        (n.active = !0),
        (n.raw = e),
        (n.deps = []),
        (n.options = t),
        n
    );
}
function bc(e) {
    const { deps: t } = e;
    if (t.length) {
        for (let n = 0; n < t.length; n++) t[n].delete(e);
        t.length = 0;
    }
}
var le = !0,
    Ii = [];
function Am() {
    Ii.push(le), (le = !1);
}
function wm() {
    Ii.push(le), (le = !0);
}
function vc() {
    const e = Ii.pop();
    le = e === void 0 ? !0 : e;
}
function Z(e, t, n) {
    if (!le || nt === void 0) return;
    let r = Ur.get(e);
    r || Ur.set(e, (r = new Map()));
    let i = r.get(n);
    i || r.set(n, (i = new Set())),
        i.has(nt) ||
            (i.add(nt),
            nt.deps.push(i),
            nt.options.onTrack &&
                nt.options.onTrack({ effect: nt, target: e, type: t, key: n }));
}
function wt(e, t, n, r, i, s) {
    const o = Ur.get(e);
    if (!o) return;
    const a = new Set(),
        c = (l) => {
            l &&
                l.forEach((f) => {
                    (f !== nt || f.allowRecurse) && a.add(f);
                });
        };
    if (t === "clear") o.forEach(c);
    else if (n === "length" && jt(e))
        o.forEach((l, f) => {
            (f === "length" || f >= r) && c(l);
        });
    else
        switch ((n !== void 0 && c(o.get(n)), t)) {
            case "add":
                jt(e)
                    ? Ri(n) && c(o.get("length"))
                    : (c(o.get(Bt)), De(e) && c(o.get(Kr)));
                break;
            case "delete":
                jt(e) || (c(o.get(Bt)), De(e) && c(o.get(Kr)));
                break;
            case "set":
                De(e) && c(o.get(Bt));
                break;
        }
    const u = (l) => {
        l.options.onTrigger &&
            l.options.onTrigger({
                effect: l,
                target: e,
                key: n,
                type: t,
                newValue: r,
                oldValue: i,
                oldTarget: s,
            }),
            l.options.scheduler ? l.options.scheduler(l) : l();
    };
    a.forEach(u);
}
var Tm = um("__proto__,__v_isRef,__isVue"),
    yc = new Set(
        Object.getOwnPropertyNames(Symbol)
            .map((e) => Symbol[e])
            .filter($i)
    ),
    Sm = Ac(),
    Om = Ac(!0),
    zs = Cm();
function Cm() {
    const e = {};
    return (
        ["includes", "indexOf", "lastIndexOf"].forEach((t) => {
            e[t] = function (...n) {
                const r = x(this);
                for (let s = 0, o = this.length; s < o; s++)
                    Z(r, "get", s + "");
                const i = r[t](...n);
                return i === -1 || i === !1 ? r[t](...n.map(x)) : i;
            };
        }),
        ["push", "pop", "shift", "unshift", "splice"].forEach((t) => {
            e[t] = function (...n) {
                Am();
                const r = x(this)[t].apply(this, n);
                return vc(), r;
            };
        }),
        e
    );
}
function Ac(e = !1, t = !1) {
    return function (r, i, s) {
        if (i === "__v_isReactive") return !e;
        if (i === "__v_isReadonly") return e;
        if (i === "__v_raw" && s === (e ? (t ? Wm : Oc) : t ? Vm : Sc).get(r))
            return r;
        const o = jt(r);
        if (!e && o && Hn(zs, i)) return Reflect.get(zs, i, s);
        const a = Reflect.get(r, i, s);
        return ($i(i) ? yc.has(i) : Tm(i)) || (e || Z(r, "get", i), t)
            ? a
            : zr(a)
            ? !o || !Ri(i)
                ? a.value
                : a
            : Vn(a)
            ? e
                ? Cc(a)
                : Fi(a)
            : a;
    };
}
var xm = Nm();
function Nm(e = !1) {
    return function (n, r, i, s) {
        let o = n[r];
        if (!e && ((i = x(i)), (o = x(o)), !jt(n) && zr(o) && !zr(i)))
            return (o.value = i), !0;
        const a = jt(n) && Ri(r) ? Number(r) < n.length : Hn(n, r),
            c = Reflect.set(n, r, i, s);
        return (
            n === x(s) &&
                (a ? Ec(i, o) && wt(n, "set", r, i, o) : wt(n, "add", r, i)),
            c
        );
    };
}
function Dm(e, t) {
    const n = Hn(e, t),
        r = e[t],
        i = Reflect.deleteProperty(e, t);
    return i && n && wt(e, "delete", t, void 0, r), i;
}
function Lm(e, t) {
    const n = Reflect.has(e, t);
    return (!$i(t) || !yc.has(t)) && Z(e, "has", t), n;
}
function $m(e) {
    return Z(e, "iterate", jt(e) ? "length" : Bt), Reflect.ownKeys(e);
}
var Rm = { get: Sm, set: xm, deleteProperty: Dm, has: Lm, ownKeys: $m },
    Im = {
        get: Om,
        set(e, t) {
            return (
                console.warn(
                    `Set operation on key "${String(
                        t
                    )}" failed: target is readonly.`,
                    e
                ),
                !0
            );
        },
        deleteProperty(e, t) {
            return (
                console.warn(
                    `Delete operation on key "${String(
                        t
                    )}" failed: target is readonly.`,
                    e
                ),
                !0
            );
        },
    },
    Pi = (e) => (Vn(e) ? Fi(e) : e),
    Mi = (e) => (Vn(e) ? Cc(e) : e),
    ki = (e) => e,
    Wn = (e) => Reflect.getPrototypeOf(e);
function rn(e, t, n = !1, r = !1) {
    e = e.__v_raw;
    const i = x(e),
        s = x(t);
    t !== s && !n && Z(i, "get", t), !n && Z(i, "get", s);
    const { has: o } = Wn(i),
        a = r ? ki : n ? Mi : Pi;
    if (o.call(i, t)) return a(e.get(t));
    if (o.call(i, s)) return a(e.get(s));
    e !== i && e.get(t);
}
function sn(e, t = !1) {
    const n = this.__v_raw,
        r = x(n),
        i = x(e);
    return (
        e !== i && !t && Z(r, "has", e),
        !t && Z(r, "has", i),
        e === i ? n.has(e) : n.has(e) || n.has(i)
    );
}
function on(e, t = !1) {
    return (
        (e = e.__v_raw), !t && Z(x(e), "iterate", Bt), Reflect.get(e, "size", e)
    );
}
function qs(e) {
    e = x(e);
    const t = x(this);
    return Wn(t).has.call(t, e) || (t.add(e), wt(t, "add", e, e)), this;
}
function Ys(e, t) {
    t = x(t);
    const n = x(this),
        { has: r, get: i } = Wn(n);
    let s = r.call(n, e);
    s ? Tc(n, r, e) : ((e = x(e)), (s = r.call(n, e)));
    const o = i.call(n, e);
    return (
        n.set(e, t),
        s ? Ec(t, o) && wt(n, "set", e, t, o) : wt(n, "add", e, t),
        this
    );
}
function Gs(e) {
    const t = x(this),
        { has: n, get: r } = Wn(t);
    let i = n.call(t, e);
    i ? Tc(t, n, e) : ((e = x(e)), (i = n.call(t, e)));
    const s = r ? r.call(t, e) : void 0,
        o = t.delete(e);
    return i && wt(t, "delete", e, void 0, s), o;
}
function Xs() {
    const e = x(this),
        t = e.size !== 0,
        n = De(e) ? new Map(e) : new Set(e),
        r = e.clear();
    return t && wt(e, "clear", void 0, void 0, n), r;
}
function an(e, t) {
    return function (r, i) {
        const s = this,
            o = s.__v_raw,
            a = x(o),
            c = t ? ki : e ? Mi : Pi;
        return (
            !e && Z(a, "iterate", Bt),
            o.forEach((u, l) => r.call(i, c(u), c(l), s))
        );
    };
}
function cn(e, t, n) {
    return function (...r) {
        const i = this.__v_raw,
            s = x(i),
            o = De(s),
            a = e === "entries" || (e === Symbol.iterator && o),
            c = e === "keys" && o,
            u = i[e](...r),
            l = n ? ki : t ? Mi : Pi;
        return (
            !t && Z(s, "iterate", c ? Kr : Bt),
            {
                next() {
                    const { value: f, done: m } = u.next();
                    return m
                        ? { value: f, done: m }
                        : { value: a ? [l(f[0]), l(f[1])] : l(f), done: m };
                },
                [Symbol.iterator]() {
                    return this;
                },
            }
        );
    };
}
function gt(e) {
    return function (...t) {
        {
            const n = t[0] ? `on key "${t[0]}" ` : "";
            console.warn(
                `${mm(e)} operation ${n}failed: target is readonly.`,
                x(this)
            );
        }
        return e === "delete" ? !1 : this;
    };
}
function Pm() {
    const e = {
            get(s) {
                return rn(this, s);
            },
            get size() {
                return on(this);
            },
            has: sn,
            add: qs,
            set: Ys,
            delete: Gs,
            clear: Xs,
            forEach: an(!1, !1),
        },
        t = {
            get(s) {
                return rn(this, s, !1, !0);
            },
            get size() {
                return on(this);
            },
            has: sn,
            add: qs,
            set: Ys,
            delete: Gs,
            clear: Xs,
            forEach: an(!1, !0),
        },
        n = {
            get(s) {
                return rn(this, s, !0);
            },
            get size() {
                return on(this, !0);
            },
            has(s) {
                return sn.call(this, s, !0);
            },
            add: gt("add"),
            set: gt("set"),
            delete: gt("delete"),
            clear: gt("clear"),
            forEach: an(!0, !1),
        },
        r = {
            get(s) {
                return rn(this, s, !0, !0);
            },
            get size() {
                return on(this, !0);
            },
            has(s) {
                return sn.call(this, s, !0);
            },
            add: gt("add"),
            set: gt("set"),
            delete: gt("delete"),
            clear: gt("clear"),
            forEach: an(!0, !0),
        };
    return (
        ["keys", "values", "entries", Symbol.iterator].forEach((s) => {
            (e[s] = cn(s, !1, !1)),
                (n[s] = cn(s, !0, !1)),
                (t[s] = cn(s, !1, !0)),
                (r[s] = cn(s, !0, !0));
        }),
        [e, n, t, r]
    );
}
var [Mm, km, Fm, jm] = Pm();
function wc(e, t) {
    const n = t ? (e ? jm : Fm) : e ? km : Mm;
    return (r, i, s) =>
        i === "__v_isReactive"
            ? !e
            : i === "__v_isReadonly"
            ? e
            : i === "__v_raw"
            ? r
            : Reflect.get(Hn(n, i) && i in r ? n : r, i, s);
}
var Bm = { get: wc(!1, !1) },
    Hm = { get: wc(!0, !1) };
function Tc(e, t, n) {
    const r = x(n);
    if (r !== n && t.call(e, r)) {
        const i = gc(e);
        console.warn(
            `Reactive ${i} contains both the raw and reactive versions of the same object${
                i === "Map" ? " as keys" : ""
            }, which can lead to inconsistencies. Avoid differentiating between the raw and reactive versions of an object and only use the reactive version if possible.`
        );
    }
}
var Sc = new WeakMap(),
    Vm = new WeakMap(),
    Oc = new WeakMap(),
    Wm = new WeakMap();
function Um(e) {
    switch (e) {
        case "Object":
        case "Array":
            return 1;
        case "Map":
        case "Set":
        case "WeakMap":
        case "WeakSet":
            return 2;
        default:
            return 0;
    }
}
function Km(e) {
    return e.__v_skip || !Object.isExtensible(e) ? 0 : Um(gc(e));
}
function Fi(e) {
    return e && e.__v_isReadonly ? e : xc(e, !1, Rm, Bm, Sc);
}
function Cc(e) {
    return xc(e, !0, Im, Hm, Oc);
}
function xc(e, t, n, r, i) {
    if (!Vn(e))
        return console.warn(`value cannot be made reactive: ${String(e)}`), e;
    if (e.__v_raw && !(t && e.__v_isReactive)) return e;
    const s = i.get(e);
    if (s) return s;
    const o = Km(e);
    if (o === 0) return e;
    const a = new Proxy(e, o === 2 ? r : n);
    return i.set(e, a), a;
}
function x(e) {
    return (e && x(e.__v_raw)) || e;
}
function zr(e) {
    return !!(e && e.__v_isRef === !0);
}
et("nextTick", () => Di);
et("dispatch", (e) => Ne.bind(Ne, e));
et("watch", (e, { evaluateLater: t, cleanup: n }) => (r, i) => {
    let s = t(r),
        a = Oa(() => {
            let c;
            return s((u) => (c = u)), c;
        }, i);
    n(a);
});
et("store", im);
et("data", (e) => Ba(e));
et("root", (e) => Fn(e));
et(
    "refs",
    (e) => (e._x_refs_proxy || (e._x_refs_proxy = We(zm(e))), e._x_refs_proxy)
);
function zm(e) {
    let t = [];
    return (
        He(e, (n) => {
            n._x_refs && t.push(n._x_refs);
        }),
        t
    );
}
var mr = {};
function Nc(e) {
    return mr[e] || (mr[e] = 0), ++mr[e];
}
function qm(e, t) {
    return He(e, (n) => {
        if (n._x_ids && n._x_ids[t]) return !0;
    });
}
function Ym(e, t) {
    e._x_ids || (e._x_ids = {}), e._x_ids[t] || (e._x_ids[t] = Nc(t));
}
et("id", (e, { cleanup: t }) => (n, r = null) => {
    let i = `${n}${r ? `-${r}` : ""}`;
    return Gm(e, i, t, () => {
        let s = qm(e, n),
            o = s ? s._x_ids[n] : Nc(n);
        return r ? `${n}-${o}-${r}` : `${n}-${o}`;
    });
});
Bn((e, t) => {
    e._x_id && (t._x_id = e._x_id);
});
function Gm(e, t, n, r) {
    if ((e._x_id || (e._x_id = {}), e._x_id[t])) return e._x_id[t];
    let i = r();
    return (
        (e._x_id[t] = i),
        n(() => {
            delete e._x_id[t];
        }),
        i
    );
}
et("el", (e) => e);
Dc("Focus", "focus", "focus");
Dc("Persist", "persist", "persist");
function Dc(e, t, n) {
    et(t, (r) =>
        Q(
            `You can't use [$${t}] without first installing the "${e}" plugin here: https://alpinejs.dev/plugins/${n}`,
            r
        )
    );
}
$(
    "modelable",
    (e, { expression: t }, { effect: n, evaluateLater: r, cleanup: i }) => {
        let s = r(t),
            o = () => {
                let l;
                return s((f) => (l = f)), l;
            },
            a = r(`${t} = __placeholder`),
            c = (l) => a(() => {}, { scope: { __placeholder: l } }),
            u = o();
        c(u),
            queueMicrotask(() => {
                if (!e._x_model) return;
                e._x_removeModelListeners.default();
                let l = e._x_model.get,
                    f = e._x_model.set,
                    m = dc(
                        {
                            get() {
                                return l();
                            },
                            set(E) {
                                f(E);
                            },
                        },
                        {
                            get() {
                                return o();
                            },
                            set(E) {
                                c(E);
                            },
                        }
                    );
                i(m);
            });
    }
);
$("teleport", (e, { modifiers: t, expression: n }, { cleanup: r }) => {
    e.tagName.toLowerCase() !== "template" &&
        Q("x-teleport can only be used on a <template> tag", e);
    let i = Js(n),
        s = e.content.cloneNode(!0).firstElementChild;
    (e._x_teleport = s),
        (s._x_teleportBack = e),
        e.setAttribute("data-teleport-template", !0),
        s.setAttribute("data-teleport-target", !0),
        e._x_forwardEvents &&
            e._x_forwardEvents.forEach((a) => {
                s.addEventListener(a, (c) => {
                    c.stopPropagation(),
                        e.dispatchEvent(new c.constructor(c.type, c));
                });
            }),
        Ve(s, {}, e);
    let o = (a, c, u) => {
        u.includes("prepend")
            ? c.parentNode.insertBefore(a, c)
            : u.includes("append")
            ? c.parentNode.insertBefore(a, c.nextSibling)
            : c.appendChild(a);
    };
    I(() => {
        o(s, i, t), dt(s), (s._x_ignore = !0);
    }),
        (e._x_teleportPutBack = () => {
            let a = Js(n);
            I(() => {
                o(e._x_teleport, a, t);
            });
        }),
        r(() => s.remove());
});
var Xm = document.createElement("div");
function Js(e) {
    let t = zt(
        () => document.querySelector(e),
        () => Xm
    )();
    return t || Q(`Cannot find x-teleport element for selector: "${e}"`), t;
}
var Lc = () => {};
Lc.inline = (e, { modifiers: t }, { cleanup: n }) => {
    t.includes("self") ? (e._x_ignoreSelf = !0) : (e._x_ignore = !0),
        n(() => {
            t.includes("self") ? delete e._x_ignoreSelf : delete e._x_ignore;
        });
};
$("ignore", Lc);
$(
    "effect",
    zt((e, { expression: t }, { effect: n }) => {
        n(j(e, t));
    })
);
function qr(e, t, n, r) {
    let i = e,
        s = (c) => r(c),
        o = {},
        a = (c, u) => (l) => u(c, l);
    if (
        (n.includes("dot") && (t = Jm(t)),
        n.includes("camel") && (t = Qm(t)),
        n.includes("passive") && (o.passive = !0),
        n.includes("capture") && (o.capture = !0),
        n.includes("window") && (i = window),
        n.includes("document") && (i = document),
        n.includes("debounce"))
    ) {
        let c = n[n.indexOf("debounce") + 1] || "invalid-wait",
            u = Tn(c.split("ms")[0]) ? Number(c.split("ms")[0]) : 250;
        s = uc(s, u);
    }
    if (n.includes("throttle")) {
        let c = n[n.indexOf("throttle") + 1] || "invalid-wait",
            u = Tn(c.split("ms")[0]) ? Number(c.split("ms")[0]) : 250;
        s = fc(s, u);
    }
    return (
        n.includes("prevent") &&
            (s = a(s, (c, u) => {
                u.preventDefault(), c(u);
            })),
        n.includes("stop") &&
            (s = a(s, (c, u) => {
                u.stopPropagation(), c(u);
            })),
        n.includes("self") &&
            (s = a(s, (c, u) => {
                u.target === e && c(u);
            })),
        (n.includes("away") || n.includes("outside")) &&
            ((i = document),
            (s = a(s, (c, u) => {
                e.contains(u.target) ||
                    (u.target.isConnected !== !1 &&
                        ((e.offsetWidth < 1 && e.offsetHeight < 1) ||
                            (e._x_isShown !== !1 && c(u))));
            }))),
        n.includes("once") &&
            (s = a(s, (c, u) => {
                c(u), i.removeEventListener(t, s, o);
            })),
        (s = a(s, (c, u) => {
            (tg(t) && eg(u, n)) || c(u);
        })),
        i.addEventListener(t, s, o),
        () => {
            i.removeEventListener(t, s, o);
        }
    );
}
function Jm(e) {
    return e.replace(/-/g, ".");
}
function Qm(e) {
    return e.toLowerCase().replace(/-(\w)/g, (t, n) => n.toUpperCase());
}
function Tn(e) {
    return !Array.isArray(e) && !isNaN(e);
}
function Zm(e) {
    return [" ", "_"].includes(e)
        ? e
        : e
              .replace(/([a-z])([A-Z])/g, "$1-$2")
              .replace(/[_\s]/, "-")
              .toLowerCase();
}
function tg(e) {
    return ["keydown", "keyup"].includes(e);
}
function eg(e, t) {
    let n = t.filter(
        (s) =>
            ![
                "window",
                "document",
                "prevent",
                "stop",
                "once",
                "capture",
            ].includes(s)
    );
    if (n.includes("debounce")) {
        let s = n.indexOf("debounce");
        n.splice(s, Tn((n[s + 1] || "invalid-wait").split("ms")[0]) ? 2 : 1);
    }
    if (n.includes("throttle")) {
        let s = n.indexOf("throttle");
        n.splice(s, Tn((n[s + 1] || "invalid-wait").split("ms")[0]) ? 2 : 1);
    }
    if (n.length === 0 || (n.length === 1 && Qs(e.key).includes(n[0])))
        return !1;
    const i = ["ctrl", "shift", "alt", "meta", "cmd", "super"].filter((s) =>
        n.includes(s)
    );
    return (
        (n = n.filter((s) => !i.includes(s))),
        !(
            i.length > 0 &&
            i.filter(
                (o) => (
                    (o === "cmd" || o === "super") && (o = "meta"), e[`${o}Key`]
                )
            ).length === i.length &&
            Qs(e.key).includes(n[0])
        )
    );
}
function Qs(e) {
    if (!e) return [];
    e = Zm(e);
    let t = {
        ctrl: "control",
        slash: "/",
        space: " ",
        spacebar: " ",
        cmd: "meta",
        esc: "escape",
        up: "arrow-up",
        down: "arrow-down",
        left: "arrow-left",
        right: "arrow-right",
        period: ".",
        equal: "=",
        minus: "-",
        underscore: "_",
    };
    return (
        (t[e] = e),
        Object.keys(t)
            .map((n) => {
                if (t[n] === e) return n;
            })
            .filter((n) => n)
    );
}
$("model", (e, { modifiers: t, expression: n }, { effect: r, cleanup: i }) => {
    let s = e;
    t.includes("parent") && (s = e.parentNode);
    let o = j(s, n),
        a;
    typeof n == "string"
        ? (a = j(s, `${n} = __placeholder`))
        : typeof n == "function" && typeof n() == "string"
        ? (a = j(s, `${n()} = __placeholder`))
        : (a = () => {});
    let c = () => {
            let m;
            return o((E) => (m = E)), to(m) ? m.get() : m;
        },
        u = (m) => {
            let E;
            o((g) => (E = g)),
                to(E) ? E.set(m) : a(() => {}, { scope: { __placeholder: m } });
        };
    typeof n == "string" &&
        e.type === "radio" &&
        I(() => {
            e.hasAttribute("name") || e.setAttribute("name", n);
        });
    var l =
        e.tagName.toLowerCase() === "select" ||
        ["checkbox", "radio"].includes(e.type) ||
        t.includes("lazy")
            ? "change"
            : "input";
    let f = At
        ? () => {}
        : qr(e, l, t, (m) => {
              u(Zs(e, t, m, c()));
          });
    if (
        (t.includes("fill") &&
            ([void 0, null, ""].includes(c()) ||
                (e.type === "checkbox" && Array.isArray(c()))) &&
            u(Zs(e, t, { target: e }, c())),
        e._x_removeModelListeners || (e._x_removeModelListeners = {}),
        (e._x_removeModelListeners.default = f),
        i(() => e._x_removeModelListeners.default()),
        e.form)
    ) {
        let m = qr(e.form, "reset", [], (E) => {
            Di(() => e._x_model && e._x_model.set(e.value));
        });
        i(() => m());
    }
    (e._x_model = {
        get() {
            return c();
        },
        set(m) {
            u(m);
        },
    }),
        (e._x_forceModelUpdate = (m) => {
            m === void 0 && typeof n == "string" && n.match(/\./) && (m = ""),
                (window.fromModel = !0),
                I(() => oc(e, "value", m)),
                delete window.fromModel;
        }),
        r(() => {
            let m = c();
            (t.includes("unintrusive") &&
                document.activeElement.isSameNode(e)) ||
                e._x_forceModelUpdate(m);
        });
});
function Zs(e, t, n, r) {
    return I(() => {
        if (n instanceof CustomEvent && n.detail !== void 0)
            return n.detail !== null && n.detail !== void 0
                ? n.detail
                : n.target.value;
        if (e.type === "checkbox")
            if (Array.isArray(r)) {
                let i = null;
                return (
                    t.includes("number")
                        ? (i = gr(n.target.value))
                        : t.includes("boolean")
                        ? (i = mn(n.target.value))
                        : (i = n.target.value),
                    n.target.checked
                        ? r.concat([i])
                        : r.filter((s) => !ng(s, i))
                );
            } else return n.target.checked;
        else {
            if (e.tagName.toLowerCase() === "select" && e.multiple)
                return t.includes("number")
                    ? Array.from(n.target.selectedOptions).map((i) => {
                          let s = i.value || i.text;
                          return gr(s);
                      })
                    : t.includes("boolean")
                    ? Array.from(n.target.selectedOptions).map((i) => {
                          let s = i.value || i.text;
                          return mn(s);
                      })
                    : Array.from(n.target.selectedOptions).map(
                          (i) => i.value || i.text
                      );
            {
                let i;
                return (
                    e.type === "radio"
                        ? n.target.checked
                            ? (i = n.target.value)
                            : (i = r)
                        : (i = n.target.value),
                    t.includes("number")
                        ? gr(i)
                        : t.includes("boolean")
                        ? mn(i)
                        : t.includes("trim")
                        ? i.trim()
                        : i
                );
            }
        }
    });
}
function gr(e) {
    let t = e ? parseFloat(e) : null;
    return rg(t) ? t : e;
}
function ng(e, t) {
    return e == t;
}
function rg(e) {
    return !Array.isArray(e) && !isNaN(e);
}
function to(e) {
    return (
        e !== null &&
        typeof e == "object" &&
        typeof e.get == "function" &&
        typeof e.set == "function"
    );
}
$("cloak", (e) =>
    queueMicrotask(() => I(() => e.removeAttribute(ge("cloak"))))
);
La(() => `[${ge("init")}]`);
$(
    "init",
    zt((e, { expression: t }, { evaluate: n }) =>
        typeof t == "string" ? !!t.trim() && n(t, {}, !1) : n(t, {}, !1)
    )
);
$("text", (e, { expression: t }, { effect: n, evaluateLater: r }) => {
    let i = r(t);
    n(() => {
        i((s) => {
            I(() => {
                e.textContent = s;
            });
        });
    });
});
$("html", (e, { expression: t }, { effect: n, evaluateLater: r }) => {
    let i = r(t);
    n(() => {
        i((s) => {
            I(() => {
                (e.innerHTML = s),
                    (e._x_ignoreSelf = !0),
                    dt(e),
                    delete e._x_ignoreSelf;
            });
        });
    });
});
xi(Xa(":", Ja(ge("bind:"))));
var $c = (
    e,
    { value: t, modifiers: n, expression: r, original: i },
    { effect: s, cleanup: o }
) => {
    if (!t) {
        let c = {};
        om(c),
            j(e, r)(
                (l) => {
                    pc(e, l, i);
                },
                { scope: c }
            );
        return;
    }
    if (t === "key") return ig(e, r);
    if (
        e._x_inlineBindings &&
        e._x_inlineBindings[t] &&
        e._x_inlineBindings[t].extract
    )
        return;
    let a = j(e, r);
    s(() =>
        a((c) => {
            c === void 0 && typeof r == "string" && r.match(/\./) && (c = ""),
                I(() => oc(e, t, c, n));
        })
    ),
        o(() => {
            e._x_undoAddedClasses && e._x_undoAddedClasses(),
                e._x_undoAddedStyles && e._x_undoAddedStyles();
        });
};
$c.inline = (e, { value: t, modifiers: n, expression: r }) => {
    t &&
        (e._x_inlineBindings || (e._x_inlineBindings = {}),
        (e._x_inlineBindings[t] = { expression: r, extract: !1 }));
};
$("bind", $c);
function ig(e, t) {
    e._x_keyExpression = t;
}
Da(() => `[${ge("data")}]`);
$("data", (e, { expression: t }, { cleanup: n }) => {
    if (sg(e)) return;
    t = t === "" ? "{}" : t;
    let r = {};
    Pr(r, e);
    let i = {};
    cm(i, r);
    let s = Ft(e, t, { scope: i });
    (s === void 0 || s === !0) && (s = {}), Pr(s, e);
    let o = _e(s);
    Ha(o);
    let a = Ve(e, o);
    o.init && Ft(e, o.init),
        n(() => {
            o.destroy && Ft(e, o.destroy), a();
        });
});
Bn((e, t) => {
    e._x_dataStack &&
        ((t._x_dataStack = e._x_dataStack),
        t.setAttribute("data-has-alpine-state", !0));
});
function sg(e) {
    return At ? (Wr ? !0 : e.hasAttribute("data-has-alpine-state")) : !1;
}
$("show", (e, { modifiers: t, expression: n }, { effect: r }) => {
    let i = j(e, n);
    e._x_doHide ||
        (e._x_doHide = () => {
            I(() => {
                e.style.setProperty(
                    "display",
                    "none",
                    t.includes("important") ? "important" : void 0
                );
            });
        }),
        e._x_doShow ||
            (e._x_doShow = () => {
                I(() => {
                    e.style.length === 1 && e.style.display === "none"
                        ? e.removeAttribute("style")
                        : e.style.removeProperty("display");
                });
            });
    let s = () => {
            e._x_doHide(), (e._x_isShown = !1);
        },
        o = () => {
            e._x_doShow(), (e._x_isShown = !0);
        },
        a = () => setTimeout(o),
        c = Hr(
            (f) => (f ? o() : s()),
            (f) => {
                typeof e._x_toggleAndCascadeWithTransitions == "function"
                    ? e._x_toggleAndCascadeWithTransitions(e, f, o, s)
                    : f
                    ? a()
                    : s();
            }
        ),
        u,
        l = !0;
    r(() =>
        i((f) => {
            (!l && f === u) ||
                (t.includes("immediate") && (f ? a() : s()),
                c(f),
                (u = f),
                (l = !1));
        })
    );
});
$("for", (e, { expression: t }, { effect: n, cleanup: r }) => {
    let i = ag(t),
        s = j(e, i.items),
        o = j(e, e._x_keyExpression || "index");
    (e._x_prevKeys = []),
        (e._x_lookup = {}),
        n(() => og(e, i, s, o)),
        r(() => {
            Object.values(e._x_lookup).forEach((a) => a.remove()),
                delete e._x_prevKeys,
                delete e._x_lookup;
        });
});
function og(e, t, n, r) {
    let i = (o) => typeof o == "object" && !Array.isArray(o),
        s = e;
    n((o) => {
        cg(o) && o >= 0 && (o = Array.from(Array(o).keys(), (p) => p + 1)),
            o === void 0 && (o = []);
        let a = e._x_lookup,
            c = e._x_prevKeys,
            u = [],
            l = [];
        if (i(o))
            o = Object.entries(o).map(([p, b]) => {
                let y = eo(t, b, p, o);
                r(
                    (w) => {
                        l.includes(w) && Q("Duplicate key on x-for", e),
                            l.push(w);
                    },
                    { scope: { index: p, ...y } }
                ),
                    u.push(y);
            });
        else
            for (let p = 0; p < o.length; p++) {
                let b = eo(t, o[p], p, o);
                r(
                    (y) => {
                        l.includes(y) && Q("Duplicate key on x-for", e),
                            l.push(y);
                    },
                    { scope: { index: p, ...b } }
                ),
                    u.push(b);
            }
        let f = [],
            m = [],
            E = [],
            g = [];
        for (let p = 0; p < c.length; p++) {
            let b = c[p];
            l.indexOf(b) === -1 && E.push(b);
        }
        c = c.filter((p) => !E.includes(p));
        let _ = "template";
        for (let p = 0; p < l.length; p++) {
            let b = l[p],
                y = c.indexOf(b);
            if (y === -1) c.splice(p, 0, b), f.push([_, p]);
            else if (y !== p) {
                let w = c.splice(p, 1)[0],
                    A = c.splice(y - 1, 1)[0];
                c.splice(p, 0, A), c.splice(y, 0, w), m.push([w, A]);
            } else g.push(b);
            _ = b;
        }
        for (let p = 0; p < E.length; p++) {
            let b = E[p];
            a[b]._x_effects && a[b]._x_effects.forEach(Ta),
                a[b].remove(),
                (a[b] = null),
                delete a[b];
        }
        for (let p = 0; p < m.length; p++) {
            let [b, y] = m[p],
                w = a[b],
                A = a[y],
                T = document.createElement("div");
            I(() => {
                A || Q('x-for ":key" is undefined or invalid', s, y, a),
                    A.after(T),
                    w.after(A),
                    A._x_currentIfEl && A.after(A._x_currentIfEl),
                    T.before(w),
                    w._x_currentIfEl && w.after(w._x_currentIfEl),
                    T.remove();
            }),
                A._x_refreshXForScope(u[l.indexOf(y)]);
        }
        for (let p = 0; p < f.length; p++) {
            let [b, y] = f[p],
                w = b === "template" ? s : a[b];
            w._x_currentIfEl && (w = w._x_currentIfEl);
            let A = u[y],
                T = l[y],
                S = document.importNode(s.content, !0).firstElementChild,
                C = _e(A);
            Ve(S, C, s),
                (S._x_refreshXForScope = (R) => {
                    Object.entries(R).forEach(([D, N]) => {
                        C[D] = N;
                    });
                }),
                I(() => {
                    w.after(S), zt(() => dt(S))();
                }),
                typeof T == "object" &&
                    Q(
                        "x-for key cannot be an object, it must be a string or an integer",
                        s
                    ),
                (a[T] = S);
        }
        for (let p = 0; p < g.length; p++)
            a[g[p]]._x_refreshXForScope(u[l.indexOf(g[p])]);
        s._x_prevKeys = l;
    });
}
function ag(e) {
    let t = /,([^,\}\]]*)(?:,([^,\}\]]*))?$/,
        n = /^\s*\(|\)\s*$/g,
        r = /([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/,
        i = e.match(r);
    if (!i) return;
    let s = {};
    s.items = i[2].trim();
    let o = i[1].replace(n, "").trim(),
        a = o.match(t);
    return (
        a
            ? ((s.item = o.replace(t, "").trim()),
              (s.index = a[1].trim()),
              a[2] && (s.collection = a[2].trim()))
            : (s.item = o),
        s
    );
}
function eo(e, t, n, r) {
    let i = {};
    return (
        /^\[.*\]$/.test(e.item) && Array.isArray(t)
            ? e.item
                  .replace("[", "")
                  .replace("]", "")
                  .split(",")
                  .map((o) => o.trim())
                  .forEach((o, a) => {
                      i[o] = t[a];
                  })
            : /^\{.*\}$/.test(e.item) &&
              !Array.isArray(t) &&
              typeof t == "object"
            ? e.item
                  .replace("{", "")
                  .replace("}", "")
                  .split(",")
                  .map((o) => o.trim())
                  .forEach((o) => {
                      i[o] = t[o];
                  })
            : (i[e.item] = t),
        e.index && (i[e.index] = n),
        e.collection && (i[e.collection] = r),
        i
    );
}
function cg(e) {
    return !Array.isArray(e) && !isNaN(e);
}
function Rc() {}
Rc.inline = (e, { expression: t }, { cleanup: n }) => {
    let r = Fn(e);
    r._x_refs || (r._x_refs = {}),
        (r._x_refs[t] = e),
        n(() => delete r._x_refs[t]);
};
$("ref", Rc);
$("if", (e, { expression: t }, { effect: n, cleanup: r }) => {
    e.tagName.toLowerCase() !== "template" &&
        Q("x-if can only be used on a <template> tag", e);
    let i = j(e, t),
        s = () => {
            if (e._x_currentIfEl) return e._x_currentIfEl;
            let a = e.content.cloneNode(!0).firstElementChild;
            return (
                Ve(a, {}, e),
                I(() => {
                    e.after(a), zt(() => dt(a))();
                }),
                (e._x_currentIfEl = a),
                (e._x_undoIf = () => {
                    yt(a, (c) => {
                        c._x_effects && c._x_effects.forEach(Ta);
                    }),
                        a.remove(),
                        delete e._x_currentIfEl;
                }),
                a
            );
        },
        o = () => {
            e._x_undoIf && (e._x_undoIf(), delete e._x_undoIf);
        };
    n(() =>
        i((a) => {
            a ? s() : o();
        })
    ),
        r(() => e._x_undoIf && e._x_undoIf());
});
$("id", (e, { expression: t }, { evaluate: n }) => {
    n(t).forEach((i) => Ym(e, i));
});
Bn((e, t) => {
    e._x_ids && (t._x_ids = e._x_ids);
});
xi(Xa("@", Ja(ge("on:"))));
$(
    "on",
    zt((e, { value: t, modifiers: n, expression: r }, { cleanup: i }) => {
        let s = r ? j(e, r) : () => {};
        e.tagName.toLowerCase() === "template" &&
            (e._x_forwardEvents || (e._x_forwardEvents = []),
            e._x_forwardEvents.includes(t) || e._x_forwardEvents.push(t));
        let o = qr(e, t, n, (a) => {
            s(() => {}, { scope: { $event: a }, params: [a] });
        });
        i(() => o());
    })
);
Un("Collapse", "collapse", "collapse");
Un("Intersect", "intersect", "intersect");
Un("Focus", "trap", "focus");
Un("Mask", "mask", "mask");
function Un(e, t, n) {
    $(t, (r) =>
        Q(
            `You can't use [x-${t}] without first installing the "${e}" plugin here: https://alpinejs.dev/plugins/${n}`,
            r
        )
    );
}
Ue.setEvaluator(za);
Ue.setReactivityEngine({ reactive: Fi, effect: Em, release: bm, raw: x });
var lg = Ue,
    Ic = lg;
window.Alpine = Ic;
Ic.start();
