(function () {
    var l, ba = ba || {},
        z = this;

    function ca(a, b, c) {
        a = a.split(".");
        c = c || z;
        !(a[0] in c) && c.execScript && c.execScript("var " + a[0]);
        for (var d; a.length && (d = a.shift());) if (!a.length && b !== undefined) c[d] = b;
        else c = c[d] ? c[d] : c[d] = {}
    }
    function da(a, b) {
        for (var c = a.split("."), d = b || z, f; f = c.shift();) if (d[f] != null) d = d[f];
        else return null;
        return d
    }
    function ea() {}

    function fa(a) {
        var b = typeof a;
        if (b == "object") if (a) {
            if (a instanceof Array) return "array";
            else if (a instanceof Object) return b;
            var c = Object.prototype.toString.call(a);
            if (c == "[object Window]") return "object";
            if (c == "[object Array]" || typeof a.length == "number" && typeof a.splice != "undefined" && typeof a.propertyIsEnumerable != "undefined" && !a.propertyIsEnumerable("splice")) return "array";
            if (c == "[object Function]" || typeof a.call != "undefined" && typeof a.propertyIsEnumerable != "undefined" && !a.propertyIsEnumerable("call")) return "function"
        } else return "null";
        else if (b == "function" && typeof a.call == "undefined") return "object";
        return b
    }
    function ga(a) {
        return fa(a) == "array"
    }
    function ha(a) {
        var b = fa(a);
        return b == "array" || b == "object" && typeof a.length == "number"
    }
    function C(a) {
        return typeof a == "string"
    }
    function ia(a) {
        return fa(a) == "function"
    }
    function ja(a) {
        a = fa(a);
        return a == "object" || a == "array" || a == "function"
    }
    function D(a) {
        return a[ka] || (a[ka] = ++la)
    }
    var ka = "closure_uid_" + Math.floor(Math.random() * 2147483648).toString(36),
        la = 0;

    function na(a) {
        return a.call.apply(a.Ea, arguments)
    }
    function oa(a, b) {
        var c = b || z;
        if (arguments.length > 2) {
            var d = Array.prototype.slice.call(arguments, 2);
            return function () {
                var f = Array.prototype.slice.call(arguments);
                Array.prototype.unshift.apply(f, d);
                return a.apply(c, f)
            }
        } else return function () {
            return a.apply(c, arguments)
        }
    }
    function pa() {
        pa = Function.prototype.Ea && Function.prototype.Ea.toString().indexOf("native code") != -1 ? na : oa;
        return pa.apply(null, arguments)
    }
    var qa = Date.now ||
    function () {
        return +new Date
    };

    function G(a, b) {
        function c() {}
        c.prototype = b.prototype;
        a.t = b.prototype;
        a.prototype = new c;
        a.prototype.constructor = a
    };

    function ra(a) {
        this.stack = Error().stack || "";
        if (a) this.message = String(a)
    }
    G(ra, Error);
    ra.prototype.name = "CustomError";

    function sa(a) {
        for (var b = 1; b < arguments.length; b++) {
            var c = String(arguments[b]).replace(/\$/g, "$$$$");
            a = a.replace(/\%s/, c)
        }
        return a
    }
    function ta(a) {
        return a.replace(/^[\s\xa0]+|[\s\xa0]+$/g, "")
    }

    function ua(a, b) {
        if (b) return a.replace(va, "&amp;").replace(wa, "&lt;").replace(xa, "&gt;").replace(ya, "&quot;");
        else {
            if (!za.test(a)) return a;
            if (a.indexOf("&") != -1) a = a.replace(va, "&amp;");
            if (a.indexOf("<") != -1) a = a.replace(wa, "&lt;");
            if (a.indexOf(">") != -1) a = a.replace(xa, "&gt;");
            if (a.indexOf('"') != -1) a = a.replace(ya, "&quot;");
            return a
        }
    }
    var va = /&/g,
        wa = /</g,
        xa = />/g,
        ya = /\"/g,
        za = /[&<>\"]/;

    function Aa(a, b) {
        for (var c = 0, d = ta(String(a)).split("."), f = ta(String(b)).split("."), e = Math.max(d.length, f.length), i = 0; c == 0 && i < e; i++) {
            var n = d[i] || "",
                o = f[i] || "",
                p = RegExp("(\\d*)(\\D*)", "g"),
                v = RegExp("(\\d*)(\\D*)", "g");
            do {
                var t = p.exec(n) || ["", "", ""],
                    r = v.exec(o) || ["", "", ""];
                if (t[0].length == 0 && r[0].length == 0) break;
                c = Ba(t[1].length == 0 ? 0 : parseInt(t[1], 10), r[1].length == 0 ? 0 : parseInt(r[1], 10)) || Ba(t[2].length == 0, r[2].length == 0) || Ba(t[2], r[2])
            } while (c == 0)
        }
        return c
    }

    function Ba(a, b) {
        if (a < b) return -1;
        else if (a > b) return 1;
        return 0
    };

    function Ea(a, b) {
        b.unshift(a);
        ra.call(this, sa.apply(null, b));
        b.shift();
        this.Vb = a
    }
    G(Ea, ra);
    Ea.prototype.name = "AssertionError";

    function Fa(a, b) {
        if (!a) {
            var c = Array.prototype.slice.call(arguments, 2),
                d = "Assertion failed";
            if (b) {
                d += ": " + b;
                var f = c
            }
            throw new Ea("" + d, f || []);
        }
        return a
    }
    function Ga(a) {
        throw new Ea("Failure" + (a ? ": " + a : ""), Array.prototype.slice.call(arguments, 1));
    };
    var H = Array.prototype,
        Ha = H.indexOf ?
    function (a, b, c) {
        Fa(a.length != null);
        return H.indexOf.call(a, b, c)
    } : function (a, b, c) {
        c = c == null ? 0 : c < 0 ? Math.max(0, a.length + c) : c;
        if (C(a)) {
            if (!C(b) || b.length != 1) return -1;
            return a.indexOf(b, c)
        }
        for (c = c; c < a.length; c++) if (c in a && a[c] === b) return c;
        return -1
    }, Ia = H.forEach ?
    function (a, b, c) {
        Fa(a.length != null);
        H.forEach.call(a, b, c)
    } : function (a, b, c) {
        for (var d = a.length, f = C(a) ? a.split("") : a, e = 0; e < d; e++) e in f && b.call(c, f[e], e, a)
    };

    function Ja(a, b) {
        var c = Ha(a, b),
            d;
        if (d = c >= 0) {
            Fa(a.length != null);
            H.splice.call(a, c, 1)
        }
        return d
    }
    function Ka() {
        return H.concat.apply(H, arguments)
    }
    function La(a) {
        if (ga(a)) return Ka(a);
        else {
            for (var b = [], c = 0, d = a.length; c < d; c++) b[c] = a[c];
            return b
        }
    }
    function Ma(a, b, c) {
        Fa(a.length != null);
        return arguments.length <= 2 ? H.slice.call(a, b) : H.slice.call(a, b, c)
    };
    var Na, Oa, Pa, Qa;

    function Ra() {
        return z.navigator ? z.navigator.userAgent : null
    }
    Qa = Pa = Oa = Na = false;
    var Sa;
    if (Sa = Ra()) {
        var Ta = z.navigator;
        Na = Sa.indexOf("Opera") == 0;
        Oa = !Na && Sa.indexOf("MSIE") != -1;
        Pa = !Na && Sa.indexOf("WebKit") != -1;
        Qa = !Na && !Pa && Ta.product == "Gecko"
    }
    var Ua = Na,
        I = Oa,
        Va = Qa,
        Wa = Pa,
        Xa = z.navigator,
        Ya = (Xa && Xa.platform || "").indexOf("Mac") != -1,
        Za;
    a: {
        var ab = "",
            bb;
        if (Ua && z.opera) {
            var cb = z.opera.version;
            ab = typeof cb == "function" ? cb() : cb
        } else {
            if (Va) bb = /rv\:([^\);]+)(\)|;)/;
            else if (I) bb = /MSIE\s+([^\);]+)(\)|;)/;
            else if (Wa) bb = /WebKit\/(\S+)/;
            if (bb) {
                var db = bb.exec(Ra());
                ab = db ? db[1] : ""
            }
        }
        if (I) {
            var eb, fb = z.document;
            eb = fb ? fb.documentMode : undefined;
            if (eb > parseFloat(ab)) {
                Za = String(eb);
                break a
            }
        }
        Za = ab
    }
    var gb = {};

    function K(a) {
        return gb[a] || (gb[a] = Aa(Za, a) >= 0)
    };
    var hb, ib = !I || K("9");
    !Va && !I || I && K("9") || Va && K("1.9.1");
    I && K("9");

    function jb(a) {
        var b;
        b = (b = a.className) && typeof b.split == "function" ? b.split(/\s+/) : [];
        var c;
        c = Ma(arguments, 1);
        for (var d = 0, f = 0; f < c.length; f++) if (!(Ha(b, c[f]) >= 0)) {
            b.push(c[f]);
            d++
        }
        c = d == c.length;
        a.className = b.join(" ");
        return c
    };

    function kb(a, b, c) {
        for (var d in a) b.call(c, a[d], d, a)
    }
    function lb(a) {
        var b = [],
            c = 0;
        for (var d in a) b[c++] = a[d];
        return b
    }
    function mb(a) {
        var b = [],
            c = 0;
        for (var d in a) b[c++] = d;
        return b
    }
    function nb(a) {
        for (var b in a) return false;
        return true
    }
    var ob = ["constructor", "hasOwnProperty", "isPrototypeOf", "propertyIsEnumerable", "toLocaleString", "toString", "valueOf"];

    function pb(a) {
        for (var b, c, d = 1; d < arguments.length; d++) {
            c = arguments[d];
            for (b in c) a[b] = c[b];
            for (var f = 0; f < ob.length; f++) {
                b = ob[f];
                if (Object.prototype.hasOwnProperty.call(c, b)) a[b] = c[b]
            }
        }
    };

    function qb(a) {
        return C(a) ? document.getElementById(a) : a
    }

    function rb(a, b, c, d) {
        a = d || a;
        b = b && b != "*" ? b.toUpperCase() : "";
        if (a.querySelectorAll && a.querySelector && (!Wa || document.compatMode == "CSS1Compat" || K("528")) && (b || c)) return a.querySelectorAll(b + (c ? "." + c : ""));
        if (c && a.getElementsByClassName) {
            a = a.getElementsByClassName(c);
            if (b) {
                d = {};
                for (var f = 0, e = 0, i; i = a[e]; e++) if (b == i.nodeName) d[f++] = i;
                d.length = f;
                return d
            } else return a
        }
        a = a.getElementsByTagName(b || "*");
        if (c) {
            d = {};
            for (e = f = 0; i = a[e]; e++) {
                b = i.className;
                if (typeof b.split == "function" && Ha(b.split(/\s+/), c) >= 0) d[f++] = i
            }
            d.length = f;
            return d
        } else return a
    }
    function sb(a, b) {
        kb(b, function (c, d) {
            if (d == "style") a.style.cssText = c;
            else if (d == "class") a.className = c;
            else if (d == "for") a.htmlFor = c;
            else if (d in tb) a.setAttribute(tb[d], c);
            else a[d] = c
        })
    }
    var tb = {
        cellpadding: "cellPadding",
        cellspacing: "cellSpacing",
        colspan: "colSpan",
        rowspan: "rowSpan",
        valign: "vAlign",
        height: "height",
        width: "width",
        usemap: "useMap",
        frameborder: "frameBorder",
        maxlength: "maxLength",
        type: "type"
    };

    function ub() {
        return vb(document, arguments)
    }

    function vb(a, b) {
        var c = b[0],
            d = b[1];
        if (!ib && d && (d.name || d.type)) {
            c = ["<", c];
            d.name && c.push(' name="', ua(d.name), '"');
            if (d.type) {
                c.push(' type="', ua(d.type), '"');
                var f = {};
                pb(f, d);
                d = f;
                delete d.type
            }
            c.push(">");
            c = c.join("")
        }
        c = a.createElement(c);
        if (d) if (C(d)) c.className = d;
        else ga(d) ? jb.apply(null, [c].concat(d)) : sb(c, d);
        b.length > 2 && wb(a, c, b, 2);
        return c
    }

    function wb(a, b, c, d) {
        function f(i) {
            if (i) b.appendChild(C(i) ? a.createTextNode(i) : i)
        }
        for (d = d; d < c.length; d++) {
            var e = c[d];
            ha(e) && !(ja(e) && e.nodeType > 0) ? Ia(xb(e) ? La(e) : e, f) : f(e)
        }
    }
    function xb(a) {
        if (a && typeof a.length == "number") if (ja(a)) return typeof a.item == "function" || typeof a.item == "string";
        else if (ia(a)) return typeof a.item == "function";
        return false
    }
    function L(a) {
        this.p = a || z.document || document
    }
    L.prototype.Ha = function () {
        return vb(this.p, arguments)
    };
    L.prototype.createElement = function (a) {
        return this.p.createElement(a)
    };
    L.prototype.createTextNode = function (a) {
        return this.p.createTextNode(a)
    };
    L.prototype.appendChild = function (a, b) {
        a.appendChild(b)
    };

    function M() {
        if (yb) zb[D(this)] = this
    }
    var yb = false,
        zb = {};
    M.prototype.La = false;
    M.prototype.R = function () {
        if (!this.La) {
            this.La = true;
            this.g();
            if (yb) {
                var a = D(this);
                if (!zb.hasOwnProperty(a)) throw Error(this + " did not call the goog.Disposable base constructor or was disposed of after a clearUndisposedObjects call");
                delete zb[a]
            }
        }
    };
    M.prototype.g = function () {};
    var Ab = [];
    var Bb;
    !I || K("9");
    I && K("8");

    function N(a, b) {
        M.call(this);
        this.type = a;
        this.currentTarget = this.target = b
    }
    G(N, M);
    N.prototype.g = function () {
        delete this.type;
        delete this.target;
        delete this.currentTarget
    };
    N.prototype.z = false;
    N.prototype.ea = true;
    var Eb = new Function("a", "return a");

    function Fb(a, b) {
        a && this.V(a, b)
    }
    G(Fb, N);
    l = Fb.prototype;
    l.target = null;
    l.relatedTarget = null;
    l.offsetX = 0;
    l.offsetY = 0;
    l.clientX = 0;
    l.clientY = 0;
    l.screenX = 0;
    l.screenY = 0;
    l.button = 0;
    l.keyCode = 0;
    l.charCode = 0;
    l.ctrlKey = false;
    l.altKey = false;
    l.shiftKey = false;
    l.metaKey = false;
    l.Ib = false;
    l.Na = null;
    l.V = function (a, b) {
        var c = this.type = a.type;
        N.call(this, c);
        this.target = a.target || a.srcElement;
        this.currentTarget = b;
        var d = a.relatedTarget;
        if (d) {
            if (Va) try {
                Eb(d.nodeName)
            } catch (f) {
                d = null
            }
        } else if (c == "mouseover") d = a.fromElement;
        else if (c == "mouseout") d = a.toElement;
        this.relatedTarget = d;
        this.offsetX = a.offsetX !== undefined ? a.offsetX : a.layerX;
        this.offsetY = a.offsetY !== undefined ? a.offsetY : a.layerY;
        this.clientX = a.clientX !== undefined ? a.clientX : a.pageX;
        this.clientY = a.clientY !== undefined ? a.clientY : a.pageY;
        this.screenX = a.screenX || 0;
        this.screenY = a.screenY || 0;
        this.button = a.button;
        this.keyCode = a.keyCode || 0;
        this.charCode = a.charCode || (c == "keypress" ? a.keyCode : 0);
        this.ctrlKey = a.ctrlKey;
        this.altKey = a.altKey;
        this.shiftKey = a.shiftKey;
        this.metaKey = a.metaKey;
        this.Ib = Ya ? a.metaKey : a.ctrlKey;
        this.eb = a.eb;
        this.Na = a;
        delete this.ea;
        delete this.z
    };
    l.g = function () {
        Fb.t.g.call(this);
        this.relatedTarget = this.currentTarget = this.target = this.Na = null
    };

    function Gb() {}
    var Hb = 0;
    l = Gb.prototype;
    l.key = 0;
    l.B = false;
    l.Fa = false;
    l.V = function (a, b, c, d, f, e) {
        if (ia(a)) this.Va = true;
        else if (a && a.handleEvent && ia(a.handleEvent)) this.Va = false;
        else throw Error("Invalid listener argument");
        this.I = a;
        this.bb = b;
        this.src = c;
        this.type = d;
        this.capture = !! f;
        this.ra = e;
        this.Fa = false;
        this.key = ++Hb;
        this.B = false
    };
    l.handleEvent = function (a) {
        if (this.Va) return this.I.call(this.ra || this.src, a);
        return this.I.handleEvent.call(this.I, a)
    };

    function O(a, b) {
        M.call(this);
        this.Ya = b;
        this.q = [];
        if (a > this.Ya) throw Error("[goog.structs.SimplePool] Initial cannot be greater than max");
        for (var c = 0; c < a; c++) this.q.push(this.m ? this.m() : {})
    }
    G(O, M);
    O.prototype.m = null;
    O.prototype.Ka = null;

    function Ib(a) {
        if (a.q.length) return a.q.pop();
        return a.m ? a.m() : {}
    }
    function Jb(a, b) {
        a.q.length < a.Ya ? a.q.push(b) : Kb(a, b)
    }
    function Kb(a, b) {
        if (a.Ka) a.Ka(b);
        else if (ja(b)) if (ia(b.R)) b.R();
        else for (var c in b) delete b[c]
    }
    O.prototype.g = function () {
        O.t.g.call(this);
        for (var a = this.q; a.length;) Kb(this, a.pop());
        delete this.q
    };
    var Lb;
    var Mb = (Lb = "ScriptEngine" in z && z.ScriptEngine() == "JScript") ? z.ScriptEngineMajorVersion() + "." + z.ScriptEngineMinorVersion() + "." + z.ScriptEngineBuildVersion() : "0";
    var Nb, Ob, Pb, Qb, Rb, Sb, Tb, Ub, Vb, Wb, Xb;
    (function () {
        function a() {
            return {
                c: 0,
                i: 0
            }
        }
        function b() {
            return []
        }
        function c() {
            function r(E) {
                return i.call(r.src, r.key, E)
            }
            return r
        }
        function d() {
            return new Gb
        }
        function f() {
            return new Fb
        }
        var e = Lb && !(Aa(Mb, "5.7") >= 0),
            i;
        Sb = function (r) {
            i = r
        };
        if (e) {
            Nb = function () {
                return Ib(n)
            };
            Ob = function (r) {
                Jb(n, r)
            };
            Pb = function () {
                return Ib(o)
            };
            Qb = function (r) {
                Jb(o, r)
            };
            Rb = function () {
                return Ib(p)
            };
            Tb = function () {
                Jb(p, c())
            };
            Ub = function () {
                return Ib(v)
            };
            Vb = function (r) {
                Jb(v, r)
            };
            Wb = function () {
                return Ib(t)
            };
            Xb = function (r) {
                Jb(t, r)
            };
            var n = new O(0, 600);
            n.m = a;
            var o = new O(0, 600);
            o.m = b;
            var p = new O(0, 600);
            p.m = c;
            var v = new O(0, 600);
            v.m = d;
            var t = new O(0, 600);
            t.m = f
        } else {
            Nb = a;
            Ob = ea;
            Pb = b;
            Qb = ea;
            Rb = c;
            Tb = ea;
            Ub = d;
            Vb = ea;
            Wb = f;
            Xb = ea
        }
    })();
    var Yb = {},
        P = {},
        Q = {},
        Zb = {};

    function $b(a, b, c, d, f) {
        if (b) if (ga(b)) {
            for (var e = 0; e < b.length; e++) $b(a, b[e], c, d, f);
            return null
        } else {
            d = !! d;
            var i = P;
            b in i || (i[b] = Nb());
            i = i[b];
            if (!(d in i)) {
                i[d] = Nb();
                i.c++
            }
            i = i[d];
            var n = D(a),
                o;
            i.i++;
            if (i[n]) {
                o = i[n];
                for (e = 0; e < o.length; e++) {
                    i = o[e];
                    if (i.I == c && i.ra == f) {
                        if (i.B) break;
                        return o[e].key
                    }
                }
            } else {
                o = i[n] = Pb();
                i.c++
            }
            e = Rb();
            e.src = a;
            i = Ub();
            i.V(c, e, a, b, d, f);
            c = i.key;
            e.key = c;
            o.push(i);
            Yb[c] = i;
            Q[n] || (Q[n] = Pb());
            Q[n].push(i);
            if (a.addEventListener) {
                if (a == z || !a.Ia) a.addEventListener(b, e, d)
            } else a.attachEvent(ac(b), e);
            return c
        } else throw Error("Invalid event type");
    }
    function bc(a, b, c, d, f) {
        if (ga(b)) {
            for (var e = 0; e < b.length; e++) bc(a, b[e], c, d, f);
            return null
        }
        d = !! d;
        a: {
            e = P;
            if (b in e) {
                e = e[b];
                if (d in e) {
                    e = e[d];
                    a = D(a);
                    if (e[a]) {
                        a = e[a];
                        break a
                    }
                }
            }
            a = null
        }
        if (!a) return false;
        for (e = 0; e < a.length; e++) if (a[e].I == c && a[e].capture == d && a[e].ra == f) return cc(a[e].key);
        return false
    }

    function cc(a) {
        if (!Yb[a]) return false;
        var b = Yb[a];
        if (b.B) return false;
        var c = b.src,
            d = b.type,
            f = b.bb,
            e = b.capture;
        if (c.removeEventListener) {
            if (c == z || !c.Ia) c.removeEventListener(d, f, e)
        } else c.detachEvent && c.detachEvent(ac(d), f);
        c = D(c);
        f = P[d][e][c];
        if (Q[c]) {
            var i = Q[c];
            Ja(i, b);
            i.length == 0 && delete Q[c]
        }
        b.B = true;
        f.Za = true;
        dc(d, e, c, f);
        delete Yb[a];
        return true
    }

    function dc(a, b, c, d) {
        if (!d.W) if (d.Za) {
            for (var f = 0, e = 0; f < d.length; f++) if (d[f].B) {
                var i = d[f].bb;
                i.src = null;
                Tb(i);
                Vb(d[f])
            } else {
                if (f != e) d[e] = d[f];
                e++
            }
            d.length = e;
            d.Za = false;
            if (e == 0) {
                Qb(d);
                delete P[a][b][c];
                P[a][b].c--;
                if (P[a][b].c == 0) {
                    Ob(P[a][b]);
                    delete P[a][b];
                    P[a].c--
                }
                if (P[a].c == 0) {
                    Ob(P[a]);
                    delete P[a]
                }
            }
        }
    }

    function ec(a, b, c) {
        var d = 0,
            f = a == null,
            e = b == null,
            i = c == null;
        c = !! c;
        if (f) kb(Q, function (o) {
            for (var p = o.length - 1; p >= 0; p--) {
                var v = o[p];
                if ((e || b == v.type) && (i || c == v.capture)) {
                    cc(v.key);
                    d++
                }
            }
        });
        else {
            a = D(a);
            if (Q[a]) {
                a = Q[a];
                for (f = a.length - 1; f >= 0; f--) {
                    var n = a[f];
                    if ((e || b == n.type) && (i || c == n.capture)) {
                        cc(n.key);
                        d++
                    }
                }
            }
        }
        return d
    }
    function ac(a) {
        if (a in Zb) return Zb[a];
        return Zb[a] = "on" + a
    }

    function fc(a, b, c, d, f) {
        var e = 1;
        b = D(b);
        if (a[b]) {
            a.i--;
            a = a[b];
            if (a.W) a.W++;
            else a.W = 1;
            try {
                for (var i = a.length, n = 0; n < i; n++) {
                    var o = a[n];
                    if (o && !o.B) e &= gc(o, f) !== false
                }
            } finally {
                a.W--;
                dc(c, d, b, a)
            }
        }
        return Boolean(e)
    }
    function gc(a, b) {
        var c = a.handleEvent(b);
        a.Fa && cc(a.key);
        return c
    }

    function hc(a, b) {
        if (!Yb[a]) return true;
        var c = Yb[a],
            d = c.type,
            f = P;
        if (!(d in f)) return true;
        f = f[d];
        var e, i;
        if (Bb === undefined) Bb = I && !z.addEventListener;
        if (Bb) {
            e = b || da("window.event");
            var n = true in f,
                o = false in f;
            if (n) {
                if (e.keyCode < 0 || e.returnValue != undefined) return true;
                a: {
                    var p = false;
                    if (e.keyCode == 0) try {
                        e.keyCode = -1;
                        break a
                    } catch (v) {
                        p = true
                    }
                    if (p || e.returnValue == undefined) e.returnValue = true
                }
            }
            p = Wb();
            p.V(e, this);
            e = true;
            try {
                if (n) {
                    for (var t = Pb(), r = p.currentTarget; r; r = r.parentNode) t.push(r);
                    i = f[true];
                    i.i = i.c;
                    for (var E = t.length - 1; !p.z && E >= 0 && i.i; E--) {
                        p.currentTarget = t[E];
                        e &= fc(i, t[E], d, true, p)
                    }
                    if (o) {
                        i = f[false];
                        i.i = i.c;
                        for (E = 0; !p.z && E < t.length && i.i; E++) {
                            p.currentTarget = t[E];
                            e &= fc(i, t[E], d, false, p)
                        }
                    }
                } else e = gc(c, p)
            } finally {
                if (t) {
                    t.length = 0;
                    Qb(t)
                }
                p.R();
                Xb(p)
            }
            return e
        }
        d = new Fb(b, this);
        try {
            e = gc(c, d)
        } finally {
            d.R()
        }
        return e
    }
    Sb(hc);
    Ab[Ab.length] = function (a) {
        hc = a(hc);
        Sb(hc)
    };

    function ic() {
        M.call(this)
    }
    G(ic, M);
    l = ic.prototype;
    l.Ia = true;
    l.za = null;
    l.addEventListener = function (a, b, c, d) {
        $b(this, a, b, c, d)
    };
    l.removeEventListener = function (a, b, c, d) {
        bc(this, a, b, c, d)
    };
    l.dispatchEvent = function (a) {
        a = a;
        var b = a.type || a,
            c = P;
        if (b in c) {
            if (C(a)) a = new N(a, this);
            else if (a instanceof N) a.target = a.target || this;
            else {
                var d = a;
                a = new N(b, this);
                pb(a, d)
            }
            d = 1;
            var f;
            c = c[b];
            b = true in c;
            var e;
            if (b) {
                f = [];
                for (e = this; e; e = e.za) f.push(e);
                e = c[true];
                e.i = e.c;
                for (var i = f.length - 1; !a.z && i >= 0 && e.i; i--) {
                    a.currentTarget = f[i];
                    d &= fc(e, f[i], a.type, true, a) && a.ea != false
                }
            }
            if (false in c) {
                e = c[false];
                e.i = e.c;
                if (b) for (i = 0; !a.z && i < f.length && e.i; i++) {
                    a.currentTarget = f[i];
                    d &= fc(e, f[i], a.type, false, a) && a.ea != false
                } else for (f = this; !a.z && f && e.i; f = f.za) {
                    a.currentTarget = f;
                    d &= fc(e, f, a.type, false, a) && a.ea != false
                }
            }
            a = Boolean(d)
        } else a = true;
        return a
    };
    l.g = function () {
        ic.t.g.call(this);
        ec(this);
        this.za = null
    };
    var R = z.window;

    function jc(a, b, c, d) {
        M.call(this);
        if (!ga(a) || !ga(b)) throw Error("Start and end parameters must be arrays");
        if (a.length != b.length) throw Error("Start and end points must be the same length");
        this.N = a;
        this.Ab = b;
        this.duration = c;
        this.Ca = d;
        this.coords = []
    }
    G(jc, ic);
    var kc = {},
        lc = null;

    function mc() {
        R.clearTimeout(lc);
        var a = qa();
        for (var b in kc) nc(kc[b], a);
        lc = nb(kc) ? null : R.setTimeout(mc, 20)
    }
    function oc(a) {
        a = D(a);
        delete kc[a];
        if (lc && nb(kc)) {
            R.clearTimeout(lc);
            lc = null
        }
    }
    l = jc.prototype;
    l.j = 0;
    l.Qa = 0;
    l.f = 0;
    l.startTime = null;
    l.Ma = null;
    l.ua = null;
    l.play = function (a) {
        if (a || this.j == 0) {
            this.f = 0;
            this.coords = this.N
        } else if (this.j == 1) return false;
        oc(this);
        this.startTime = qa();
        if (this.j == -1) this.startTime -= this.duration * this.f;
        this.Ma = this.startTime + this.duration;
        this.ua = this.startTime;
        this.f || S(this, "begin");
        S(this, "play");
        this.j == -1 && S(this, "resume");
        this.j = 1;
        a = D(this);
        a in kc || (kc[a] = this);
        lc || (lc = R.setTimeout(mc, 20));
        nc(this, this.startTime);
        return true
    };
    l.stop = function (a) {
        oc(this);
        this.j = 0;
        if (a) this.f = 1;
        pc(this, this.f);
        S(this, "stop");
        S(this, "end")
    };
    l.g = function () {
        this.j != 0 && this.stop(false);
        S(this, "destroy");
        jc.t.g.call(this)
    };

    function nc(a, b) {
        a.f = (b - a.startTime) / (a.Ma - a.startTime);
        if (a.f >= 1) a.f = 1;
        a.Qa = 1E3 / (b - a.ua);
        a.ua = b;
        ia(a.Ca) ? pc(a, a.Ca(a.f)) : pc(a, a.f);
        if (a.f == 1) {
            a.j = 0;
            oc(a);
            S(a, "finish");
            S(a, "end")
        } else a.j == 1 && S(a, "animate")
    }
    function pc(a, b) {
        a.coords = Array(a.N.length);
        for (var c = 0; c < a.N.length; c++) a.coords[c] = (a.Ab[c] - a.N[c]) * b + a.N[c]
    }

    function S(a, b) {
        a.dispatchEvent(new qc(b, a))
    }
    function qc(a, b) {
        N.call(this, a);
        this.coords = b.coords;
        this.x = b.coords[0];
        this.y = b.coords[1];
        this.$b = b.coords[2];
        this.duration = b.duration;
        this.f = b.f;
        this.Rb = b.Qa;
        this.eb = b.j;
        this.Ob = b
    }
    G(qc, N);

    function rc(a) {
        return 3 * a * a - 2 * a * a * a
    };
    var sc = function (a) {
            return function () {
                return a
            }
        }(true);
/*
 Portions of this code are from the Dojo Toolkit, received by
 The Closure Library Authors under the BSD license. All other code is
 Copyright 2005-2009 The Closure Library Authors. All Rights Reserved.

 The "New" BSD License:

 Copyright (c) 2005-2009, The Dojo Foundation
 All rights reserved.

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions are met:

 Redistributions of source code must retain the above copyright notice, this
 list of conditions and the following disclaimer.
 Redistributions in binary form must reproduce the above copyright notice,
 this list of conditions and the following disclaimer in the documentation
 and/or other materials provided with the distribution.
 Neither the name of the Dojo Foundation nor the names of its contributors
 may be used to endorse or promote products derived from this software
 without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 DISCLAIMED.  IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
    var tc = function () {
            function a(g, h) {
                var j = h || [];
                g && j.push(g);
                return j
            }
            var b = Wa && document.compatMode == "BackCompat",
                c = document.firstChild.children ? "children" : "childNodes",
                d = false;

            function f(g) {
                g += ">~+".indexOf(g.slice(-1)) >= 0 ? " * " : " ";

                function h(Ca, td) {
                    return ta(g.slice(Ca, td))
                }
                var j = [],
                    k = -1,
                    m = -1,
                    q = -1,
                    A = -1,
                    u = -1,
                    s = -1,
                    w = -1,
                    x = "",
                    F = "",
                    $, B = 0,
                    ud = g.length,
                    y = null,
                    J = null;

                function Da() {
                    if (s >= 0) {
                        y.id = h(s, B).replace(/\\/g, "");
                        s = -1
                    }
                    if (w >= 0) {
                        var Ca = w == B ? null : h(w, B);
                        if (">~+".indexOf(Ca) < 0) y.e = Ca;
                        else y.ca = Ca;
                        w = -1
                    }
                    if (u >= 0) {
                        y.k.push(h(u + 1, B).replace(/\\/g, ""));
                        u = -1
                    }
                }
                for (; x = F, F = g.charAt(B), B < ud; B++) if (x != "\\") {
                    if (!y) {
                        $ = B;
                        y = {
                            A: null,
                            s: [],
                            P: [],
                            k: [],
                            e: null,
                            ca: null,
                            id: null,
                            pa: function () {
                                return d ? this.Hb : this.e
                            }
                        };
                        w = B
                    }
                    if (k >= 0) if (F == "]") {
                        if (J.la) J.va = h(q || k + 1, B);
                        else J.la = h(k + 1, B);
                        if (k = J.va) if (k.charAt(0) == '"' || k.charAt(0) == "'") J.va = k.slice(1, -1);
                        y.P.push(J);
                        J = null;
                        k = q = -1
                    } else {
                        if (F == "=") {
                            q = "|~^$*".indexOf(x) >= 0 ? x : "";
                            J.type = q + F;
                            J.la = h(k + 1, B - q.length);
                            q = B + 1
                        }
                    } else if (m >= 0) {
                        if (F == ")") {
                            if (A >= 0) J.value = h(m + 1, B);
                            A = m = -1
                        }
                    } else if (F == "#") {
                        Da();
                        s = B + 1
                    } else if (F == ".") {
                        Da();
                        u = B
                    } else if (F == ":") {
                        Da();
                        A = B
                    } else if (F == "[") {
                        Da();
                        k = B;
                        J = {}
                    } else if (F == "(") {
                        if (A >= 0) {
                            J = {
                                name: h(A + 1, B),
                                value: null
                            };
                            y.s.push(J)
                        }
                        m = B
                    } else if (F == " " && x != F) {
                        Da();
                        A >= 0 && y.s.push({
                            name: h(A + 1, B)
                        });
                        y.Xa = y.s.length || y.P.length || y.k.length;
                        y.Wb = y.A = h($, B);
                        y.Hb = y.e = y.ca ? null : y.e || "*";
                        if (y.e) y.e = y.e.toUpperCase();
                        if (j.length && j[j.length - 1].ca) {
                            y.Ta = j.pop();
                            y.A = y.Ta.A + " " + y.A
                        }
                        j.push(y);
                        y = null
                    }
                }
                return j
            }
            function e(g, h) {
                if (!g) return h;
                if (!h) return g;
                return function () {
                    return g.apply(window, arguments) && h.apply(window, arguments)
                }
            }
            function i(g) {
                return 1 == g.nodeType
            }
            function n(g, h) {
                if (!g) return "";
                if (h == "class") return g.className || "";
                if (h == "for") return g.htmlFor || "";
                if (h == "style") return g.style.cssText || "";
                return (d ? g.getAttribute(h) : g.getAttribute(h, 2)) || ""
            }
            var o = {
                "*=": function (g, h) {
                    return function (j) {
                        return n(j, g).indexOf(h) >= 0
                    }
                },
                "^=": function (g, h) {
                    return function (j) {
                        return n(j, g).indexOf(h) == 0
                    }
                },
                "$=": function (g, h) {
                    return function (j) {
                        j = " " + n(j, g);
                        return j.lastIndexOf(h) == j.length - h.length
                    }
                },
                "~=": function (g, h) {
                    var j = " " + h + " ";
                    return function (k) {
                        return (" " + n(k, g) + " ").indexOf(j) >= 0
                    }
                },
                "|=": function (g, h) {
                    h = " " + h;
                    return function (j) {
                        j = " " + n(j, g);
                        return j == h || j.indexOf(h + "-") == 0
                    }
                },
                "=": function (g, h) {
                    return function (j) {
                        return n(j, g) == h
                    }
                }
            },
                p = typeof document.firstChild.nextElementSibling == "undefined",
                v = !p ? "nextElementSibling" : "nextSibling",
                t = !p ? "previousElementSibling" : "previousSibling",
                r = p ? i : sc;

            function E(g) {
                for (; g = g[t];) if (r(g)) return false;
                return true
            }
            function vc(g) {
                for (; g = g[v];) if (r(g)) return false;
                return true
            }
            function $a(g) {
                var h = g.parentNode,
                    j = 0,
                    k = h[c],
                    m = g._i || -1,
                    q = h._l || -1;
                if (!k) return -1;
                k = k.length;
                if (q == k && m >= 0 && q >= 0) return m;
                h._l = k;
                m = -1;
                for (h = h.firstElementChild || h.firstChild; h; h = h[v]) if (r(h)) {
                    h._i = ++j;
                    if (g === h) m = j
                }
                return m
            }
            function vd(g) {
                return !($a(g) % 2)
            }
            function wd(g) {
                return $a(g) % 2
            }
            var Cb = {
                checked: function () {
                    return function (g) {
                        return g.checked || g.attributes.checked
                    }
                },
                "first-child": function () {
                    return E
                },
                "last-child": function () {
                    return vc
                },
                "only-child": function () {
                    return function (g) {
                        if (!E(g)) return false;
                        if (!vc(g)) return false;
                        return true
                    }
                },
                empty: function () {
                    return function (g) {
                        var h = g.childNodes;
                        for (g = g.childNodes.length - 1; g >= 0; g--) {
                            var j = h[g].nodeType;
                            if (j === 1 || j == 3) return false
                        }
                        return true
                    }
                },
                contains: function (g, h) {
                    var j = h.charAt(0);
                    if (j == '"' || j == "'") h = h.slice(1, -1);
                    return function (k) {
                        return k.innerHTML.indexOf(h) >= 0
                    }
                },
                not: function (g, h) {
                    var j = f(h)[0],
                        k = {
                            v: 1
                        };
                    if (j.e != "*") k.e = 1;
                    if (!j.k.length) k.k = 1;
                    var m = ma(j, k);
                    return function (q) {
                        return !m(q)
                    }
                },
                "nth-child": function (g, h) {
                    if (h == "odd") return wd;
                    else if (h == "even") return vd;
                    if (h.indexOf("n") != -1) {
                        var j = h.split("n", 2),
                            k = j[0] ? j[0] == "-" ? -1 : parseInt(j[0], 10) : 1,
                            m = j[1] ? parseInt(j[1], 10) : 0,
                            q = 0,
                            A = -1;
                        if (k > 0) if (m < 0) m = m % k && k + m % k;
                        else {
                            if (m > 0) {
                                if (m >= k) q = m - m % k;
                                m %= k
                            }
                        } else if (k < 0) {
                            k *= -1;
                            if (m > 0) {
                                A = m;
                                m %= k
                            }
                        }
                        if (k > 0) return function (s) {
                            s = $a(s);
                            return s >= q && (A < 0 || s <= A) && s % k == m
                        };
                        else h = m
                    }
                    var u = parseInt(h, 10);
                    return function (s) {
                        return $a(s) == u
                    }
                }
            },
                xd = I ?
            function (g) {
                var h = g.toLowerCase();
                if (h == "class") g = "className";
                return function (j) {
                    return d ? j.getAttribute(g) : j[g] || j[h]
                }
            } : function (g) {
                return function (h) {
                    return h && h.getAttribute && h.hasAttribute(g)
                }
            };

            function ma(g, h) {
                if (!g) return sc;
                h = h || {};
                var j = null;
                h.v || (j = e(j, i));
                if (!h.e) if (g.e != "*") j = e(j, function (k) {
                    return k && k.tagName == g.pa()
                });
                h.k || Ia(g.k, function (k, m) {
                    var q = RegExp("(?:^|\\s)" + k + "(?:\\s|$)");
                    j = e(j, function (A) {
                        return q.test(A.className)
                    });
                    j.Qb = m
                });
                h.s || Ia(g.s, function (k) {
                    var m = k.name;
                    if (Cb[m]) j = e(j, Cb[m](m, k.value))
                });
                h.P || Ia(g.P, function (k) {
                    var m, q = k.la;
                    if (k.type && o[k.type]) m = o[k.type](q, k.va);
                    else if (q.length) m = xd(q);
                    if (m) j = e(j, m)
                });
                if (!h.id) if (g.id) j = e(j, function (k) {
                    return !!k && k.id == g.id
                });
                j || "default" in h || (j = sc);
                return j
            }
            function yd(g) {
                return function (h, j, k) {
                    for (; h = h[v];) if (!(p && !i(h))) {
                        if ((!k || Db(h, k)) && g(h)) j.push(h);
                        break
                    }
                    return j
                }
            }
            function zd(g) {
                return function (h, j, k) {
                    for (h = h[v]; h;) {
                        if (r(h)) {
                            if (k && !Db(h, k)) break;
                            g(h) && j.push(h)
                        }
                        h = h[v]
                    }
                    return j
                }
            }
            function Ad(g) {
                g = g || sc;
                return function (h, j, k) {
                    for (var m = 0, q = h[c]; h = q[m++];) if (r(h) && (!k || Db(h, k)) && g(h, m)) j.push(h);
                    return j
                }
            }
            var wc = {};

            function xc(g) {
                var h = wc[g.A];
                if (h) return h;
                var j = g.Ta;
                j = j ? j.ca : "";
                var k = ma(g, {
                    v: 1
                }),
                    m = "*" == g.e,
                    q = document.getElementsByClassName;
                if (j) {
                    q = {
                        v: 1
                    };
                    if (m) q.e = 1;
                    k = ma(g, q);
                    if ("+" == j) h = yd(k);
                    else if ("~" == j) h = zd(k);
                    else if (">" == j) h = Ad(k)
                } else if (g.id) {
                    k = !g.Xa && m ? sc : ma(g, {
                        v: 1,
                        id: 1
                    });
                    h = function (u, s) {
                        var w;
                        w = u ? new L(u.nodeType == 9 ? u : u.ownerDocument || u.document) : hb || (hb = new L);
                        var x = g.id;
                        if ((w = C(x) ? w.p.getElementById(x) : x) && k(w)) if (9 == u.nodeType) return a(w, s);
                        else {
                            for (x = w.parentNode; x;) {
                                if (x == u) break;
                                x = x.parentNode
                            }
                            if (x) return a(w, s)
                        }
                    }
                } else if (q && /\{\s*\[native code\]\s*\}/.test(String(q)) && g.k.length && !b) {
                    k = ma(g, {
                        v: 1,
                        k: 1,
                        id: 1
                    });
                    var A = g.k.join(" ");
                    h = function (u, s) {
                        for (var w = a(0, s), x, F = 0, $ = u.getElementsByClassName(A); x = $[F++];) k(x, u) && w.push(x);
                        return w
                    }
                } else if (!m && !g.Xa) h = function (u, s) {
                    for (var w = a(0, s), x, F = 0, $ = u.getElementsByTagName(g.pa()); x = $[F++];) w.push(x);
                    return w
                };
                else {
                    k = ma(g, {
                        v: 1,
                        e: 1,
                        id: 1
                    });
                    h = function (u, s) {
                        for (var w = a(0, s), x, F = 0, $ = u.getElementsByTagName(g.pa()); x = $[F++];) k(x, u) && w.push(x);
                        return w
                    }
                }
                return wc[g.A] = h
            }
            var yc = {},
                zc = {};

            function Ac(g) {
                var h = f(ta(g));
                if (h.length == 1) {
                    var j = xc(h[0]);
                    return function (k) {
                        if (k = j(k, [])) k.aa = true;
                        return k
                    }
                }
                return function (k) {
                    k = a(k);
                    for (var m, q, A = h.length, u, s, w = 0; w < A; w++) {
                        s = [];
                        m = h[w];
                        q = k.length - 1;
                        if (q > 0) {
                            u = {};
                            s.aa = true
                        }
                        q = xc(m);
                        for (var x = 0; m = k[x]; x++) q(m, s, u);
                        if (!s.length) break;
                        k = s
                    }
                    return s
                }
            }
            var Bc = !! document.querySelectorAll && (!Wa || K("526"));

            function Cc(g, h) {
                if (Bc) {
                    var j = zc[g];
                    if (j && !h) return j
                }
                if (j = yc[g]) return j;
                j = g.charAt(0);
                var k = -1 == g.indexOf(" ");
                if (g.indexOf("#") >= 0 && k) h = true;
                if (Bc && !h && ">~+".indexOf(j) == -1 && (!I || g.indexOf(":") == -1) && !(b && g.indexOf(".") >= 0) && g.indexOf(":contains") == -1 && g.indexOf("|=") == -1) {
                    var m = ">~+".indexOf(g.charAt(g.length - 1)) >= 0 ? g + " *" : g;
                    return zc[g] = function (A) {
                        try {
                            if (!(9 == A.nodeType || k)) throw "";
                            var u = A.querySelectorAll(m);
                            if (I) u.wb = true;
                            else u.aa = true;
                            return u
                        } catch (s) {
                            return Cc(g, true)(A)
                        }
                    }
                } else {
                    var q = g.split(/\s*,\s*/);
                    return yc[g] = q.length < 2 ? Ac(g) : function (A) {
                        for (var u = 0, s = [], w; w = q[u++];) s = s.concat(Ac(w)(A));
                        return s
                    }
                }
            }
            var aa = 0,
                Bd = I ?
            function (g) {
                return d ? g.getAttribute("_uid") || g.setAttribute("_uid", ++aa) || aa : g.uniqueID
            } : function (g) {
                return g._uid || (g._uid = ++aa)
            };

            function Db(g, h) {
                if (!h) return 1;
                var j = Bd(g);
                if (!h[j]) return h[j] = 1;
                return 0
            }
            function Cd(g) {
                if (g && g.aa) return g;
                var h = [];
                if (!g || !g.length) return h;
                g[0] && h.push(g[0]);
                if (g.length < 2) return h;
                aa++;
                if (I && d) {
                    var j = aa + "";
                    g[0].setAttribute("_zipIdx", j);
                    for (var k = 1, m; m = g[k]; k++) {
                        g[k].getAttribute("_zipIdx") != j && h.push(m);
                        m.setAttribute("_zipIdx", j)
                    }
                } else if (I && g.wb) try {
                    for (k = 1; m = g[k]; k++) i(m) && h.push(m)
                } catch (q) {} else {
                    if (g[0]) g[0]._zipIdx = aa;
                    for (k = 1; m = g[k]; k++) {
                        g[k]._zipIdx != aa && h.push(m);
                        m._zipIdx = aa
                    }
                }
                return h
            }
            function Dc(g, h) {
                if (!g) return [];
                if (g.constructor == Array) return g;
                if (!C(g)) return [g];
                if (C(h)) {
                    h = qb(h);
                    if (!h) return []
                }
                h = h || document;
                var j = h.ownerDocument || h.documentElement;
                d = h.contentType && h.contentType == "application/xml" || Ua && (h.doctype || j.toString() == "[object XMLDocument]") || !! j && (I ? j.xml : h.xmlVersion || j.xmlVersion);
                if ((j = Cc(g)(h)) && j.aa) return j;
                return Cd(j)
            }
            Dc.s = Cb;
            return Dc
        }();
    ca("goog.dom.query", tc, void 0);
    ca("goog.dom.query.pseudos", tc.s, void 0);

    function uc(a, b, c, d, f) {
        var e = new jc(a, b, c, f || rc);
        $b(e, "animate", d.ba, false, d);
        $b(e, "finish", function (i) {
            ec(e);
            d.$a(i)
        }, false, d);
        e.play();
        return e
    }
    function Ec(a) {
        var b = 2;
        b = b[0] || 1.618;
        return Math.pow(a, 2) * ((b + 1) * a - b)
    }
    window.openSafely = function (a) {
        var b = window.open("", "link" + (new Date).getTime(), "");
        b.document.write('<meta http-equiv="refresh" content="0;url=' + a + '">');
        b.document.close()
    };
    var Fc = /(https?:\/\/)?(www\.)?/;

    function Gc(a, b, c, d) {
        this.width = b;
        this.height = c;
        this.w = -1;
        this.Ga = [d];
        this.canvas = ub("canvas", {
            width: b,
            height: c
        });
        qb(a).appendChild(this.canvas)
    }
    Gc.prototype.ba = function (a) {
        var b = this.Bb;
        a = a.coords[0];
        var c = this.canvas.getContext("2d");
        c.clearRect(0, 0, this.width, this.height);
        for (var d = this.width / 2, f = this.height, e = d + 5, i = e * 0.4, n = Math.PI * 1.15, o = Math.PI * 1.85, p = this.Ga.length, v = o - n, t = v / p, r = 0; r < p; r++) {
            var E = n + t * r;
            c.fillStyle = this.Ga[r];
            c.beginPath();
            c.moveTo(d, f);
            c.arc(d, f, e, E, E + t, false);
            c.lineTo(d, f);
            c.closePath();
            c.fill()
        }
        c.globalCompositeOperation = "destination-out";
        c.fillStyle = "white";
        c.strokeStyle = "white";
        c.beginPath();
        c.moveTo(d, f);
        c.arc(d, f, i, n, o, false);
        c.lineTo(d, f);
        c.closePath();
        c.fill();
        c.stroke();
        c.globalCompositeOperation = "source-over";
        c.save();
        c.strokeStyle = "black";
        c.fillStyle = "black";
        i = Math.round(e / 30) + 1;
        c.translate(d, f - 6);
        c.rotate(a / b * v - v / 2);
        c.beginPath();
        c.moveTo(i, i);
        c.lineTo(0, -1 * e * 1.1);
        c.lineTo(-1 * i, i);
        c.quadraticCurveTo(0, 10, i, i);
        c.closePath();
        c.fill();
        c.restore()
    };
    Gc.prototype.$a = function (a) {
        this.ba(a);
        this.Da = false;
        this.w = a.coords[0]
    };
    var Hc = {
        jb: ".",
        mb: ",",
        qb: "%",
        O: "0",
        tb: "+",
        ob: "-",
        lb: "E",
        sb: "\u2030",
        nb: "\u221e",
        pb: "NaN",
        ib: "#,##0.###",
        ub: "#E0",
        rb: "#,##0%",
        hb: "\u00a4#,##0.00;(\u00a4#,##0.00)",
        kb: "USD"
    },
        T = Hc;
    T = Hc;
    var Ic = {
        AED: "\u062f.\u0625",
        ARS: "$",
        AUD: "$",
        BDT: "\u09f3",
        BRL: "R$",
        CAD: "$",
        CHF: "Fr.",
        CLP: "$",
        CNY: "\u00a5",
        COP: "$",
        CRC: "\u20a1",
        CUP: "$",
        CZK: "K\u010d",
        DKK: "kr",
        DOP: "$",
        EGP: "\u00a3",
        EUR: "\u20ac",
        GBP: "\u00a3",
        HKD: "$",
        HRK: "kn",
        HUF: "Ft",
        IDR: "Rp",
        ILS: "\u20aa",
        INR: "Rs",
        IQD: "\u0639\u062f",
        ISK: "kr",
        JMD: "$",
        JPY: "\u00a5",
        KRW: "\u20a9",
        KWD: "\u062f.\u0643",
        LKR: "Rs",
        LVL: "Ls",
        MNT: "\u20ae",
        MXN: "$",
        MYR: "RM",
        NOK: "kr",
        NZD: "$",
        PAB: "B/.",
        PEN: "S/.",
        PHP: "P",
        PKR: "Rs.",
        PLN: "z\u0142",
        RON: "L",
        RUB: "\u0440\u0443\u0431",
        SAR: "\u0633.\u0631",
        SEK: "kr",
        SGD: "$",
        SKK: "Sk",
        SYP: "SYP",
        THB: "\u0e3f",
        TRY: "TL",
        TWD: "NT$",
        USD: "$",
        UYU: "$",
        VEF: "Bs.F",
        VND: "\u20ab",
        XAF: "FCFA",
        XCD: "$",
        YER: "YER",
        ZAR: "R"
    };

    function Jc(a, b) {
        a.Xb = b.replace(/ /g, "\u00a0");
        var c = [0];
        a.Aa = Kc(a, b, c);
        for (var d = c[0], f = -1, e = 0, i = 0, n = 0, o = -1, p = b.length, v = true; c[0] < p && v; c[0]++) switch (b.charAt(c[0])) {
        case "#":
            if (i > 0) n++;
            else e++;
            o >= 0 && f < 0 && o++;
            break;
        case "0":
            if (n > 0) throw Error('Unexpected "0" in pattern "' + b + '"');
            i++;
            o >= 0 && f < 0 && o++;
            break;
        case ",":
            o = 0;
            break;
        case ".":
            if (f >= 0) throw Error('Multiple decimal separators in pattern "' + b + '"');
            f = e + i + n;
            break;
        case "E":
            if (a.ha) throw Error('Multiple exponential symbols in pattern "' + b + '"');
            a.ha = true;
            a.Y = 0;
            if (c[0] + 1 < p && b.charAt(c[0] + 1) == "+") {
                c[0]++;
                a.gb = true
            }
            for (; c[0] + 1 < p && b.charAt(c[0] + 1) == "0";) {
                c[0]++;
                a.Y++
            }
            if (e + i < 1 || a.Y < 1) throw Error('Malformed exponential pattern "' + b + '"');
            v = false;
            break;
        default:
            c[0]--;
            v = false;
            break
        }
        if (i == 0 && e > 0 && f >= 0) {
            i = f;
            i == 0 && i++;
            n = e - i;
            e = i - 1;
            i = 1
        }
        if (f < 0 && n > 0 || f >= 0 && (f < e || f > e + i) || o == 0) throw Error('Malformed pattern "' + b + '"');
        n = e + i + n;
        a.wa = f >= 0 ? n - f : 0;
        if (f >= 0) {
            a.L = e + i - f;
            if (a.L < 0) a.L = 0
        }
        a.h = (f >= 0 ? f : n) - e;
        if (a.ha) {
            a.X = e + a.h;
            if (a.wa == 0 && a.h == 0) a.h = 1
        }
        a.qa = Math.max(0, o);
        a.Ja = f == 0 || f == n;
        d = c[0] - d;
        a.Ba = Kc(a, b, c);
        if (c[0] < b.length && b.charAt(c[0]) == Lc) {
            c[0]++;
            a.$ = Kc(a, b, c);
            c[0] += d;
            a.xa = Kc(a, b, c)
        } else {
            a.$ = a.Aa + a.$;
            a.xa += a.Ba
        }
    }

    function Mc(a, b, c, d) {
        var f = Math.pow(10, a.wa);
        b = Math.round(b * f);
        var e = Math.floor(b / f),
            i = Math.floor(b - e * f),
            n = a.L > 0 || i > 0,
            o = "";
        for (b = e; b > 1.0E20;) {
            o = "0" + o;
            b = Math.round(b / 10)
        }
        o = b + o;
        var p = T.jb,
            v = T.mb;
        b = T.O.charCodeAt(0);
        var t = o.length;
        if (e > 0 || c > 0) {
            for (e = t; e < c; e++) d.push(T.O);
            for (e = 0; e < t; e++) {
                d.push(String.fromCharCode(b + o.charAt(e) * 1));
                t - e > 1 && a.qa > 0 && (t - e) % a.qa == 1 && d.push(v)
            }
        } else n || d.push(T.O);
        if (a.Ja || n) d.push(p);
        c = "" + (i + f);
        for (f = c.length; c.charAt(f - 1) == "0" && f > a.L + 1;) f--;
        for (e = 1; e < f; e++) d.push(String.fromCharCode(b + c.charAt(e) * 1))
    }
    function Nc(a, b, c) {
        c.push(T.lb);
        if (b < 0) {
            b = -b;
            c.push(T.ob)
        } else a.gb && c.push(T.tb);
        b = "" + b;
        for (var d = b.length; d < a.Y; d++) c.push(T.O);
        c.push(b)
    }
    var Lc = ";";

    function Kc(a, b, c) {
        for (var d = "", f = false, e = b.length; c[0] < e; c[0]++) {
            var i = b.charAt(c[0]);
            if (i == "'") if (c[0] + 1 < e && b.charAt(c[0] + 1) == "'") {
                c[0]++;
                d += "'"
            } else f = !f;
            else if (f) d += i;
            else switch (i) {
            case "#":
            case "0":
            case ",":
            case ".":
            case Lc:
                return d;
            case "\u00a4":
                if (c[0] + 1 < e && b.charAt(c[0] + 1) == "\u00a4") {
                    c[0]++;
                    d += a.Ua
                } else d += a.xb;
                break;
            case "%":
                if (a.M != 1) throw Error("Too many percent/permill");
                a.M = 100;
                d += T.qb;
                break;
            case "\u2030":
                if (a.M != 1) throw Error("Too many percent/permill");
                a.M = 1E3;
                d += T.sb;
                break;
            default:
                d += i
            }
        }
        return d
    };

    function Oc(a) {
        this.element = qb(a);
        this.w = 0
    }
    var U = new(function (a, b) {
        this.Ua = b || T.kb;
        this.xb = Ic[this.Ua];
        this.X = 40;
        this.h = 1;
        this.wa = 3;
        this.Y = this.L = 0;
        this.gb = false;
        this.Ba = this.Aa = "";
        this.$ = "-";
        this.xa = "";
        this.M = 1;
        this.qa = 3;
        this.ha = this.Ja = false;
        if (typeof a == "number") switch (a) {
        case 1:
            Jc(this, T.ib);
            break;
        case 2:
            Jc(this, T.ub);
            break;
        case 3:
            Jc(this, T.rb);
            break;
        case 4:
            Jc(this, T.hb);
            break;
        default:
            throw Error("Unsupported pattern type.");
        } else Jc(this, a)
    })("#,##0");
    Oc.prototype.ba = function (a) {
        this.w = a.coords[0];
        var b = this.element;
        var c = a.coords[0];
        if (isNaN(c)) a = T.pb;
        else {
            a = [];
            var d = c < 0 || c == 0 && 1 / c < 0;
            a.push(d ? U.$ : U.Aa);
            if (isFinite(c)) {
                c *= d ? -1 : 1;
                c *= U.M;
                if (U.ha) {
                    c = c;
                    if (c == 0) {
                        Mc(U, c, U.h, a);
                        Nc(U, 0, a)
                    } else {
                        var f = Math.floor(Math.log(c) / Math.log(10));
                        c /= Math.pow(10, f);
                        var e = U.h;
                        if (U.X > 1 && U.X > U.h) {
                            for (; f % U.X != 0;) {
                                c *= 10;
                                f--
                            }
                            e = 1
                        } else if (U.h < 1) {
                            f++;
                            c /= 10
                        } else {
                            f -= U.h - 1;
                            c *= Math.pow(10, U.h - 1)
                        }
                        Mc(U, c, e, a);
                        Nc(U, f, a)
                    }
                } else Mc(U, c, U.h, a)
            } else a.push(T.nb);
            a.push(d ? U.xa : U.Ba);
            a = a.join("")
        }
        b.innerHTML = a
    };
    Oc.prototype.$a = function (a) {
        this.ba(a)
    };

    function Pc(a) {
        if (typeof a.T == "function") return a.T();
        if (C(a)) return a.split("");
        if (ha(a)) {
            for (var b = [], c = a.length, d = 0; d < c; d++) b.push(a[d]);
            return b
        }
        return lb(a)
    }
    function Qc(a, b, c) {
        if (typeof a.forEach == "function") a.forEach(b, c);
        else if (ha(a) || C(a)) Ia(a, b, c);
        else {
            var d;
            if (typeof a.oa == "function") d = a.oa();
            else if (typeof a.T != "function") if (ha(a) || C(a)) {
                d = [];
                for (var f = a.length, e = 0; e < f; e++) d.push(e);
                d = d
            } else d = mb(a);
            else d = void 0;
            f = Pc(a);
            e = f.length;
            for (var i = 0; i < e; i++) b.call(c, f[i], d && d[i], a)
        }
    };

    function Rc(a) {
        this.J = {};
        this.d = [];
        var b = arguments.length;
        if (b > 1) {
            if (b % 2) throw Error("Uneven number of arguments");
            for (var c = 0; c < b; c += 2) Sc(this, arguments[c], arguments[c + 1])
        } else if (a) {
            if (a instanceof Rc) {
                b = a.oa();
                c = a.T()
            } else {
                b = mb(a);
                c = lb(a)
            }
            for (var d = 0; d < b.length; d++) Sc(this, b[d], c[d])
        }
    }
    Rc.prototype.c = 0;
    Rc.prototype.Mb = 0;
    Rc.prototype.T = function () {
        Tc(this);
        for (var a = [], b = 0; b < this.d.length; b++) a.push(this.J[this.d[b]]);
        return a
    };
    Rc.prototype.oa = function () {
        Tc(this);
        return this.d.concat()
    };

    function Tc(a) {
        if (a.c != a.d.length) {
            for (var b = 0, c = 0; b < a.d.length;) {
                var d = a.d[b];
                if (Object.prototype.hasOwnProperty.call(a.J, d)) a.d[c++] = d;
                b++
            }
            a.d.length = c
        }
        if (a.c != a.d.length) {
            var f = {};
            for (c = b = 0; b < a.d.length;) {
                d = a.d[b];
                if (!Object.prototype.hasOwnProperty.call(f, d)) {
                    a.d[c++] = d;
                    f[d] = 1
                }
                b++
            }
            a.d.length = c
        }
    }
    function Sc(a, b, c) {
        if (!Object.prototype.hasOwnProperty.call(a.J, b)) {
            a.c++;
            a.d.push(b);
            a.Mb++
        }
        a.J[b] = c
    };

    function Uc(a) {
        return Vc(a || arguments.callee.caller, [])
    }

    function Vc(a, b) {
        var c = [];
        if (Ha(b, a) >= 0) c.push("[...circular reference...]");
        else if (a && b.length < 50) {
            c.push(Wc(a) + "(");
            for (var d = a.arguments, f = 0; f < d.length; f++) {
                f > 0 && c.push(", ");
                var e;
                e = d[f];
                switch (typeof e) {
                case "object":
                    e = e ? "object" : "null";
                    break;
                case "string":
                    e = e;
                    break;
                case "number":
                    e = String(e);
                    break;
                case "boolean":
                    e = e ? "true" : "false";
                    break;
                case "function":
                    e = (e = Wc(e)) ? e : "[fn]";
                    break;
                case "undefined":
                default:
                    e = typeof e;
                    break
                }
                if (e.length > 40) e = e.substr(0, 40) + "...";
                c.push(e)
            }
            b.push(a);
            c.push(")\n");
            try {
                c.push(Vc(a.caller, b))
            } catch (i) {
                c.push("[exception trying to get caller]\n")
            }
        } else a ? c.push("[...long stack...]") : c.push("[end]");
        return c.join("")
    }
    function Wc(a) {
        a = String(a);
        if (!Xc[a]) {
            var b = /function ([^\(]+)/.exec(a);
            Xc[a] = b ? b[1] : "[Anonymous]"
        }
        return Xc[a]
    }
    var Xc = {};

    function Yc(a, b, c, d, f) {
        this.reset(a, b, c, d, f)
    }
    Yc.prototype.Kb = 0;
    Yc.prototype.Pa = null;
    Yc.prototype.Oa = null;
    var Zc = 0;
    Yc.prototype.reset = function (a, b, c, d, f) {
        this.Kb = typeof f == "number" ? f : Zc++;
        this.Zb = d || qa();
        this.H = a;
        this.Db = b;
        this.Ub = c;
        delete this.Pa;
        delete this.Oa
    };
    Yc.prototype.db = function (a) {
        this.H = a
    };

    function V(a) {
        this.Eb = a
    }
    V.prototype.da = null;
    V.prototype.H = null;
    V.prototype.ma = null;
    V.prototype.Sa = null;

    function $c(a, b) {
        this.name = a;
        this.value = b
    }
    $c.prototype.toString = function () {
        return this.name
    };
    var ad = new $c("SEVERE", 1E3),
        bd = new $c("WARNING", 900),
        cd = new $c("CONFIG", 700),
        dd = new $c("FINE", 500),
        ed = new $c("FINEST", 300);
    V.prototype.db = function (a) {
        this.H = a
    };

    function fd(a) {
        if (a.H) return a.H;
        if (a.da) return fd(a.da);
        Ga("Root logger has no level set.");
        return null
    }
    V.prototype.log = function (a, b, c) {
        if (a.value >= fd(this).value) {
            a = this.Cb(a, b, c);
            z.console && z.console.markTimeline && z.console.markTimeline("log:" + a.Db);
            for (b = this; b;) {
                c = b;
                var d = a;
                if (c.Sa) for (var f = 0, e = void 0; e = c.Sa[f]; f++) e(d);
                b = b.da
            }
        }
    };
    V.prototype.Cb = function (a, b, c) {
        var d = new Yc(a, String(b), this.Eb);
        if (c) {
            d.Pa = c;
            var f;
            var e = arguments.callee.caller;
            try {
                var i;
                var n = da("window.location.href");
                if (C(c)) i = {
                    message: c,
                    name: "Unknown error",
                    lineNumber: "Not available",
                    fileName: n,
                    stack: "Not available"
                };
                else {
                    var o, p, v = false;
                    try {
                        o = c.lineNumber || c.Tb || "Not available"
                    } catch (t) {
                        o = "Not available";
                        v = true
                    }
                    try {
                        p = c.fileName || c.filename || c.sourceURL || n
                    } catch (r) {
                        p = "Not available";
                        v = true
                    }
                    i = v || !c.lineNumber || !c.fileName || !c.stack ? {
                        message: c.message,
                        name: c.name,
                        lineNumber: o,
                        fileName: p,
                        stack: c.stack || "Not available"
                    } : c
                }
                f = "Message: " + ua(i.message) + '\nUrl: <a href="view-source:' + i.fileName + '" target="_new">' + i.fileName + "</a>\nLine: " + i.lineNumber + "\n\nBrowser stack:\n" + ua(i.stack + "-> ") + "[end]\n\nJS stack traversal:\n" + ua(Uc(e) + "-> ")
            } catch (E) {
                f = "Exception trying to expose exception! You win, we lose. " + E
            }
            d.Oa = f
        }
        return d
    };

    function W(a, b, c) {
        a.log(dd, b, c)
    }
    var gd = {},
        hd = null;

    function id(a) {
        if (!hd) {
            hd = new V("");
            gd[""] = hd;
            hd.db(cd)
        }
        var b;
        if (!(b = gd[a])) {
            b = new V(a);
            var c = a.lastIndexOf("."),
                d = a.substr(0, c);
            c = a.substr(c + 1);
            d = id(d);
            if (!d.ma) d.ma = {};
            d.ma[c] = b;
            b.da = d;
            b = gd[a] = b
        }
        return b
    };

    function jd() {}
    jd.prototype.Q = null;

    function kd() {
        return ld(md)
    }
    var md;

    function nd() {}
    G(nd, jd);

    function ld(a) {
        return (a = od(a)) ? new ActiveXObject(a) : new XMLHttpRequest
    }
    function pd(a) {
        var b = {};
        if (od(a)) {
            b[0] = true;
            b[1] = true
        }
        return b
    }
    nd.prototype.sa = null;

    function od(a) {
        if (!a.sa && typeof XMLHttpRequest == "undefined" && typeof ActiveXObject != "undefined") {
            for (var b = ["MSXML2.XMLHTTP.6.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP"], c = 0; c < b.length; c++) {
                var d = b[c];
                try {
                    new ActiveXObject(d);
                    return a.sa = d
                } catch (f) {}
            }
            throw Error("Could not create ActiveXObject. ActiveX might be disabled, or MSXML might not be installed");
        }
        return a.sa
    }
    md = new nd;

    function qd() {
        if (Va) {
            this.o = {};
            this.ja = {};
            this.fa = []
        }
    }
    qd.prototype.b = id("goog.net.xhrMonitor");
    qd.prototype.S = Va;

    function rd(a, b) {
        if (a.S) {
            var c = C(b) ? b : ja(b) ? D(b) : "";
            a.b.log(ed, "Pushing context: " + b + " (" + c + ")", void 0);
            a.fa.push(c)
        }
    }
    function sd(a) {
        if (a.S) {
            var b = a.fa.pop();
            a.b.log(ed, "Popping context: " + b, void 0);
            Dd(a, b)
        }
    }
    function Ed(a, b) {
        if (a.S) {
            var c = D(b);
            W(a.b, "Opening XHR : " + c);
            for (var d = 0; d < a.fa.length; d++) {
                var f = a.fa[d];
                Fd(a, a.o, f, c);
                Fd(a, a.ja, c, f)
            }
        }
    }

    function Dd(a, b) {
        var c = a.ja[b],
            d = a.o[b];
        if (c && d) {
            a.b.log(ed, "Updating dependent contexts", void 0);
            Ia(c, function (f) {
                Ia(d, function (e) {
                    Fd(this, this.o, f, e);
                    Fd(this, this.ja, e, f)
                }, this)
            }, a)
        }
    }
    function Fd(a, b, c, d) {
        b[c] || (b[c] = []);
        Ha(b[c], d) >= 0 || b[c].push(d)
    }
    var X = new qd;
    var Gd = RegExp("^(?:([^:/?#.]+):)?(?://(?:([^/?#]*)@)?([\\w\\d\\-\\u0100-\\uffff.%]*)(?::([0-9]+))?)?([^?#]+)?(?:\\?([^#]*))?(?:#(.*))?$");

    function Y(a) {
        M.call(this);
        this.headers = new Rc;
        this.C = a || null
    }
    G(Y, ic);
    Y.prototype.b = id("goog.net.XhrIo");
    var Hd = /^https?:?$/i;
    l = Y.prototype;
    l.n = false;
    l.a = null;
    l.ia = null;
    l.G = "";
    l.Wa = "";
    l.D = 0;
    l.F = "";
    l.na = false;
    l.U = false;
    l.ta = false;
    l.r = false;
    l.ga = 0;
    l.u = null;
    l.cb = "";
    l.Nb = false;
    l.send = function (a, b, c, d) {
        if (this.a) throw Error("[goog.net.XhrIo] Object is active with another request");
        b = b ? b.toUpperCase() : "GET";
        this.G = a;
        this.F = "";
        this.D = 0;
        this.Wa = b;
        this.na = false;
        this.n = true;
        this.a = this.C ? ld(this.C) : new kd;
        this.ia = this.C ? this.C.Q || (this.C.Q = pd(this.C)) : md.Q || (md.Q = pd(md));
        Ed(X, this.a);
        this.a.onreadystatechange = pa(this.ab, this);
        try {
            W(this.b, Z(this, "Opening Xhr"));
            this.ta = true;
            this.a.open(b, a, true);
            this.ta = false
        } catch (f) {
            W(this.b, Z(this, "Error opening Xhr: " + f.message));
            Id(this, 5, f);
            return
        }
        a = c || "";
        var e = new Rc(this.headers);
        d && Qc(d, function (n, o) {
            Sc(e, o, n)
        });
        b == "POST" && !Object.prototype.hasOwnProperty.call(e.J, "Content-Type") && Sc(e, "Content-Type", "application/x-www-form-urlencoded;charset=utf-8");
        Qc(e, function (n, o) {
            this.a.setRequestHeader(o, n)
        }, this);
        if (this.cb) this.a.Yb = this.cb;
        if ("withCredentials" in this.a) this.a.withCredentials = this.Nb;
        try {
            if (this.u) {
                R.clearTimeout(this.u);
                this.u = null
            }
            if (this.ga > 0) {
                W(this.b, Z(this, "Will abort after " + this.ga + "ms if incomplete"));
                this.u = R.setTimeout(pa(this.Lb, this), this.ga)
            }
            W(this.b, Z(this, "Sending request"));
            this.U = true;
            this.a.send(a);
            this.U = false
        } catch (i) {
            W(this.b, Z(this, "Send error: " + i.message));
            Id(this, 5, i)
        }
    };
    l.dispatchEvent = function (a) {
        if (this.a) {
            rd(X, this.a);
            try {
                return Y.t.dispatchEvent.call(this, a)
            } finally {
                sd(X)
            }
        } else return Y.t.dispatchEvent.call(this, a)
    };
    l.Lb = function () {
        if (typeof ba != "undefined") if (this.a) {
            this.F = "Timed out after " + this.ga + "ms, aborting";
            this.D = 8;
            W(this.b, Z(this, this.F));
            this.dispatchEvent("timeout");
            this.abort(8)
        }
    };

    function Id(a, b, c) {
        a.n = false;
        if (a.a) {
            a.r = true;
            a.a.abort();
            a.r = false
        }
        a.F = c;
        a.D = b;
        Jd(a);
        Kd(a)
    }
    function Jd(a) {
        if (!a.na) {
            a.na = true;
            a.dispatchEvent("complete");
            a.dispatchEvent("error")
        }
    }
    l.abort = function (a) {
        if (this.a && this.n) {
            W(this.b, Z(this, "Aborting"));
            this.n = false;
            this.r = true;
            this.a.abort();
            this.r = false;
            this.D = a || 7;
            this.dispatchEvent("complete");
            this.dispatchEvent("abort");
            Kd(this)
        }
    };
    l.g = function () {
        if (this.a) {
            if (this.n) {
                this.n = false;
                this.r = true;
                this.a.abort();
                this.r = false
            }
            Kd(this, true)
        }
        Y.t.g.call(this)
    };
    l.ab = function () {
        !this.ta && !this.U && !this.r ? this.ya() : Ld(this)
    };
    l.ya = function () {
        Ld(this)
    };

    function Ld(a) {
        if (a.n) if (typeof ba != "undefined") if (a.ia[1] && Md(a) == 4 && Nd(a) == 2) W(a.b, Z(a, "Local request error detected and ignored"));
        else if (a.U && Md(a) == 4) R.setTimeout(pa(a.ab, a), 0);
        else {
            a.dispatchEvent("readystatechange");
            if (Md(a) == 4) {
                W(a.b, Z(a, "Request complete"));
                a.n = false;
                var b;
                a: switch (Nd(a)) {
                case 0:
                    b = (b = C(a.G) ? a.G.match(Gd)[1] || null : a.G.Sb()) ? Hd.test(b) : self.location ? Hd.test(self.location.protocol) : true;
                    b = !b;
                    break a;
                case 200:
                case 204:
                case 304:
                    b = true;
                    break a;
                default:
                    b = false;
                    break a
                }
                if (b) {
                    a.dispatchEvent("complete");
                    a.dispatchEvent("success")
                } else {
                    a.D = 6;
                    var c;
                    try {
                        c = Md(a) > 2 ? a.a.statusText : ""
                    } catch (d) {
                        W(a.b, "Can not get status: " + d.message);
                        c = ""
                    }
                    a.F = c + " [" + Nd(a) + "]";
                    Jd(a)
                }
                Kd(a)
            }
        }
    }

    function Kd(a, b) {
        if (a.a) {
            var c = a.a,
                d = a.ia[0] ? ea : null;
            a.a = null;
            a.ia = null;
            if (a.u) {
                R.clearTimeout(a.u);
                a.u = null
            }
            if (!b) {
                rd(X, c);
                a.dispatchEvent("ready");
                sd(X)
            }
            if (X.S) {
                var f = D(c);
                W(X.b, "Closing XHR : " + f);
                delete X.ja[f];
                for (var e in X.o) {
                    Ja(X.o[e], f);
                    X.o[e].length == 0 && delete X.o[e]
                }
            }
            try {
                c.onreadystatechange = d
            } catch (i) {
                a.b.log(ad, "Problem encountered resetting onreadystatechange: " + i.message, void 0)
            }
        }
    }
    function Md(a) {
        return a.a ? a.a.readyState : 0
    }

    function Nd(a) {
        try {
            return Md(a) > 2 ? a.a.status : -1
        } catch (b) {
            a.b.log(bd, "Can not get status: " + b.message, void 0);
            return -1
        }
    }
    function Z(a, b) {
        return b + " [" + a.Wa + " " + a.G + " " + Nd(a) + "]"
    }
    Ab[Ab.length] = function (a) {
        Y.prototype.ya = a(Y.prototype.ya)
    };

    function Od(a, b) {
        var c = b ? new L(b.nodeType == 9 ? b : b.ownerDocument || b.document) : hb || (hb = new L),
            d = null;
        if (I) {
            d = c.p.createStyleSheet();
            Pd(d, a)
        } else {
            var f = rb(c.p, "head", void 0, void 0)[0];
            if (!f) {
                d = rb(c.p, "body", void 0, void 0)[0];
                f = c.Ha("head");
                d.parentNode.insertBefore(f, d)
            }
            d = c.Ha("style");
            Pd(d, a);
            c.appendChild(f, d)
        }
        return d
    }
    function Pd(a, b) {
        if (I) a.cssText = b;
        else a[Wa ? "innerText" : "innerHTML"] = b
    };

    function Qd(a) {
        this.l = a;
        this.yb = this.l.disableStyles || false;
        this.zb = qb(this.l.element);
        this.Z = this.K = 0;
        this.vb = this.l.api || "/some_api";
        this.ka = new Y;
        $b(this.ka, "success", this.Gb, false, this);
        this.Jb = this.l.refreshInterval || 3E3;
        a = this.l.width || 240;
        var b = this.l.color || "#9ECAE1",
            c = this.l.label || "Total visitors",
            d = a / 240;
        if (window.location.hostname && !this.l.label) c = "Visitors on ";
        var f = ub("div", {
            "class": "chartbeatWidget"
        }),
            e = ub("div", {
                "class": "chartbeatNumber"
            }),
            i = ub("div", {
                "class": "chartbeatGauge"
            }),
            n = ub("a", {
                "class": "branding",
                href: "http://www.chartbeat.com",
                target: "_blank"
            }, ["Powered by chartbeat"]);
        c = ub("div", {
            "class": "chartbeatLabel"
        }, [c]);
        if (window.location.hostname && !this.l.label) {
            var o = ub("span", {
                "class": "host"
            }, [window.location.hostname.replace(Fc, "")]);
            c.appendChild(o)
        }
        Od('.chartbeatWidget { position: relative; } .chartbeatWidget a.branding { display: none; }');
        if (!this.yb) {
            o = Math.round(42 * d);
            Od('.chartbeatWidget { color: #333; font-family: Helvetica, "Helvetica Neue", Arial, sans-serif; padding: 10px; text-align: center; width: ' + a + "px; } .chartbeatWidget .chartbeatNumber { font-weight: bold; font-size:" + o + "px; line-height: 1.25; } .chartbeatWidget .chartbeatLabel { color: #777; font-size:" + o * 0.35 + "px; line-height: 1.5; } .chartbeatWidget .chartbeatLabel .host { color: #555; } .chartbeatWidget a.branding { bottom: -25px; font-size:" + o * 0.3 + "px; left: auto; right: 0; }")
        }
//        f.appendChild(c);
//        f.appendChild(e);
        this.zb.appendChild(f);
//        f.appendChild(n);
        this.Fb = new Oc(e);
        if (!I || K("9")) {
            f.appendChild(i);
            this.Ra = new Gc(i, Math.round(150 * d), Math.round(90 * d), b)
        }
        this.fb();
        z.setTimeout(pa(this.fb, this), this.Jb)
    }
    Qd.prototype.fb = function () {

        this.ka.send(this.vb);
    	if (this.vb.indexOf('init') < 0) this.vb = this.vb + '/init/1';
    };
    Qd.prototype.Gb = function () {
        var a;
        var b = this.ka;
        if (b.a) b: {
            b = String(b.a.responseText);
            if (/^\s*$/.test(b) ? false : /^[\],:{}\s\u2028\u2029]*$/.test(b.replace(/\\["\\\/bfnrtu]/g, "@").replace(/"[^"\\\n\r\u2028\u2029\x00-\x08\x10-\x1f\x80-\x9f]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:[\s\u2028\u2029]*\[)+/g, ""))) try {
                a = eval("(" + b + ")");
                break b
            } catch (c) {}
            throw Error("Invalid JSON string: " + b);
        } else a = void 0;
        a = a.people || a.total || 0;
        b = this.Fb;
        uc([b.w], [a], 1E3, b);
        if (this.Ra) {
            if (!this.K || this.K < a) this.K = a * 1.2;
            if (!this.Z || this.Z > a) this.Z = a * 0.6;
            b = this.Ra;
            var d = this.Z,
                f = this.K,
                e = this.K;
            if (a != b.w && !b.Da) {
                b.Da = true;
                b.min = d;
                b.max = f;
                b.Pb = 0;
                b.Bb = e;
                uc([b.w], [a], 1500, b, Ec)
            }
        }
    };
    ca("SiteTotal", Qd, void 0);
})();