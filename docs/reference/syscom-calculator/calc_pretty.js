!(function () {
  try {
    var e =
        "undefined" != typeof globalThis
          ? globalThis
          : "undefined" != typeof global
            ? global
            : "undefined" != typeof window
              ? window
              : "undefined" != typeof self
                ? self
                : {},
      n = new e.Error().stack;
    n && ((e._debugIds || (e._debugIds = {}))[n] = "48478e4d-65d7-ba6f-1944-9dd3cff8bebc");
  } catch (e) {}
})();
(globalThis.TURBOPACK || (globalThis.TURBOPACK = [])).push([
  "object" == typeof document ? document.currentScript : void 0,
  887364,
  (e) => {
    "use strict";
    e.s(
      [
        "CARGAS_CRITICAS_DEFAULT_W",
        () => eA,
        "cargasCriticasActualW",
        () => eR,
        "consumoAnualKwh",
        () => eW,
        "consumoDiario",
        () => eD,
        "default",
        () => eT,
        "hspActual",
        () => eF,
        "resumenHistorico",
        () => e$,
        "vSistemaActual",
        () => eq,
      ],
      887364,
    );
    var r = e.i(843476),
      o = e.i(500932),
      a = e.i(271645);
    let t = { lifepo4: 0.8, agm: 0.5, gel: 0.5, plomo: 0.5 };
    function i(e) {
      return e.reduce((e, r) => e + (r.simultaneo ? r.watts * r.cantidad : 0), 0);
    }
    function s(e) {
      return e.reduce((e, r) => e + (r.critico ? r.watts * r.cantidad : 0), 0);
    }
    function l(e) {
      return e <= 2 ? 12 : e <= 6 ? 24 : 48;
    }
    function n(e, r) {
      return r <= 0 ? 0 : Math.ceil(e / r);
    }
    function c(e, r, o, a) {
      if (o <= 0 || a <= 0) return null;
      let t = Math.round(r / o);
      if (t < 1 || Math.abs(t * o - r) > 0.08 * r) return null;
      let i = Math.max(1, Math.ceil(e / a));
      return { serie: t, paralelo: i, total: t * i };
    }
    let d = {
        Aguascalientes: 5.9,
        "Baja California": 5.8,
        "Baja California Sur": 6,
        Campeche: 5.2,
        Chiapas: 5,
        Chihuahua: 6.1,
        "Ciudad de México": 5.4,
        Coahuila: 5.8,
        Colima: 5.4,
        Durango: 6,
        "Estado de México": 5.4,
        Guanajuato: 5.8,
        Guerrero: 5.4,
        Hidalgo: 5.2,
        Jalisco: 5.6,
        Michoacán: 5.5,
        Morelos: 5.6,
        Nayarit: 5.4,
        "Nuevo León": 5.2,
        Oaxaca: 5.3,
        Puebla: 5.4,
        Querétaro: 5.7,
        "Quintana Roo": 5,
        "San Luis Potosí": 5.5,
        Sinaloa: 5.6,
        Sonora: 6.2,
        Tabasco: 4.8,
        Tamaulipas: 5,
        Tlaxcala: 5.4,
        Veracruz: 4.8,
        Yucatán: 5.2,
        Zacatecas: 6,
      },
      u = Object.keys(d);
    e.i(413406);
    var m = e.i(713310),
      p = e.i(991056);
    let x = ({ size: e = 24, color: r = "currentColor", ...o }) =>
      a.createElement(
        "svg",
        { viewBox: "0 0 24 24", fill: "none", width: e, height: e, color: r, "aria-hidden": "true", ...o },
        a.createElement("path", {
          fillRule: "evenodd",
          clipRule: "evenodd",
          d: "M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11 11-4.925 11-11S18.075 1 12 1ZM7.362 12.247 12 5.5v5h3.98c.492 0 .738 0 .873.102a.5.5 0 0 1 .196.373c.008.169-.131.372-.41.778L12 18.5v-5H8.02c-.492 0-.739 0-.873-.102a.5.5 0 0 1-.196-.373c-.008-.169.131-.372.41-.778Z",
          fill: "currentColor",
        }),
      );
    x.displayName = "ZapCircle";
    let b = [
      {
        tipo: "interconexion",
        titulo: "Interconexión a CFE",
        descripcion: "Sigues conectado a CFE y bajas tu recibo: los paneles inyectan a la red. Sin baterías.",
        Icono: x,
      },
      {
        tipo: "aislado",
        titulo: "Sistema aislado",
        descripcion:
          "Sin CFE: los paneles cargan baterías y de ahí se alimenta todo. Ideal para ranchos y sitios remotos.",
        Icono: m.BatteryCharging01,
      },
      {
        tipo: "hibrido",
        titulo: "Híbrido",
        descripcion: "Paneles + baterías + CFE: bajas tu recibo y tienes respaldo cuando se va la luz.",
        Icono: p.SwitchHorizontal01,
      },
    ];
    function h(e) {
      let a,
        t,
        i,
        s,
        l = (0, o.c)(7),
        { seleccionado: n, onSeleccionar: c } = e;
      return (
        l[0] === Symbol.for("react.memo_cache_sentinel")
          ? ((a = (0, r.jsx)("h2", {
              className: "text-xl font-semibold text-[var(--color-text-primary)]",
              children: "¿Qué tipo de sistema necesitas?",
            })),
            (t = (0, r.jsx)("p", {
              className: "mt-1 text-sm text-[var(--color-text-tertiary)]",
              children: "No te preocupes por los tecnicismos: elige el que describe tu caso.",
            })),
            (l[0] = a),
            (l[1] = t))
          : ((a = l[0]), (t = l[1])),
        l[2] !== c || l[3] !== n
          ? ((i = b.map((e) => {
              let { tipo: o, titulo: a, descripcion: t, Icono: i } = e,
                s = n === o;
              return (0, r.jsxs)(
                "button",
                {
                  type: "button",
                  onClick: () => c(o),
                  className: `flex flex-col items-start gap-3 rounded-2xl border bg-[var(--color-bg-primary)] p-5 text-left transition-shadow duration-200 hover:shadow-[0_12px_24px_rgba(10,13,18,0.12)] ${s ? "border-[var(--color-border-brand)] ring-1 ring-inset ring-[var(--color-border-brand)]" : "border-[var(--color-border-secondary)]"}`,
                  children: [
                    (0, r.jsx)(i, { size: 24, className: "text-[var(--color-fg-quaternary)]", "aria-hidden": !0 }),
                    (0, r.jsx)("span", {
                      className: "text-base font-semibold text-[var(--color-text-primary)]",
                      children: a,
                    }),
                    (0, r.jsx)("span", { className: "text-sm text-[var(--color-text-tertiary)]", children: t }),
                  ],
                },
                o,
              );
            })),
            (l[2] = c),
            (l[3] = n),
            (l[4] = i))
          : (i = l[4]),
        l[5] !== i
          ? ((s = (0, r.jsxs)("div", {
              children: [a, t, (0, r.jsx)("div", { className: "mt-6 grid gap-4 sm:grid-cols-3", children: i })],
            })),
            (l[5] = i),
            (l[6] = s))
          : (s = l[6]),
        s
      );
    }
    var v = e.i(686912),
      g = e.i(87117);
    let f = ({ size: e = 24, color: r = "currentColor", ...o }) =>
      a.createElement(
        "svg",
        { viewBox: "0 0 24 24", fill: "none", width: e, height: e, color: r, "aria-hidden": "true", ...o },
        a.createElement("path", {
          fillRule: "evenodd",
          clipRule: "evenodd",
          d: "M12.838 7H5.162c-.527 0-.981 0-1.356.03-.395.033-.789.104-1.167.297a3 3 0 0 0-1.311 1.311c-.193.378-.264.772-.296 1.167C1 10.18 1 10.635 1 11.161v7.678c0 .527 0 .982.03 1.356.033.395.104.789.297 1.167a3 3 0 0 0 1.311 1.311c.378.193.772.264 1.167.296.375.031.83.031 1.356.031h7.678c.527 0 .981 0 1.356-.03.395-.033.789-.104 1.167-.297a3 3 0 0 0 1.311-1.311c.193-.378.264-.772.296-1.167.031-.375.031-.83.031-1.357v-7.677c0-.527 0-.981-.03-1.356-.033-.395-.104-.789-.297-1.167a3 3 0 0 0-1.311-1.311c-.378-.193-.772-.264-1.167-.296A17.9 17.9 0 0 0 12.838 7Zm.37 6.207a1 1 0 0 0-1.415-1.414L8 15.586l-1.293-1.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4.5-4.5Z",
          fill: "currentColor",
        }),
        a.createElement("path", {
          d: "M18.838 1h-7.677c-.527 0-.981 0-1.356.03-.395.033-.789.104-1.167.297a3 3 0 0 0-1.311 1.311c-.193.378-.264.772-.296 1.167C7.003 4.141 7 4.541 7 5h5.88c.49 0 1.02 0 1.478.037.519.043 1.205.148 1.912.508a5 5 0 0 1 2.185 2.185c.36.707.465 1.393.508 1.912.037.459.037.989.037 1.477V17c.459 0 .86-.003 1.195-.03.395-.033.789-.104 1.167-.297a3 3 0 0 0 1.311-1.311c.193-.378.264-.772.296-1.167.031-.375.031-.83.031-1.356V5.16c0-.527 0-.981-.03-1.356-.033-.395-.104-.789-.297-1.167a3 3 0 0 0-1.311-1.311c-.378-.193-.772-.264-1.167-.296C19.82 1 19.365 1 18.838 1Z",
          fill: "currentColor",
        }),
      );
    f.displayName = "CheckDone01";
    var y = e.i(142017),
      j = e.i(694525),
      N = e.i(183094);
    let _ = [
        { nombre: "Foco LED", watts: 10, horasDia: 5 },
        { nombre: "Refrigerador", watts: 250, horasDia: 8 },
        { nombre: "Congelador", watts: 300, horasDia: 8 },
        { nombre: "Televisión", watts: 100, horasDia: 4 },
        { nombre: "Minisplit 1 ton", watts: 1100, horasDia: 6 },
        { nombre: "A/A de ventana", watts: 900, horasDia: 6 },
        { nombre: "Ventilador", watts: 60, horasDia: 8 },
        { nombre: "Lavadora", watts: 500, horasDia: 1 },
        { nombre: "Microondas", watts: 1200, horasDia: 0.5 },
        { nombre: "Licuadora", watts: 400, horasDia: 0.2 },
        { nombre: "Cafetera", watts: 800, horasDia: 0.5 },
        { nombre: "Plancha", watts: 1200, horasDia: 0.5 },
        { nombre: "Horno eléctrico", watts: 1500, horasDia: 0.5 },
        { nombre: "Computadora de escritorio", watts: 200, horasDia: 4 },
        { nombre: "Laptop", watts: 65, horasDia: 6 },
        { nombre: "Módem / router", watts: 15, horasDia: 24 },
        { nombre: "Cámara de seguridad", watts: 10, horasDia: 24 },
        { nombre: "Bomba de agua ½ HP", watts: 400, horasDia: 1 },
        { nombre: "Bomba de agua 1 HP", watts: 750, horasDia: 1 },
        { nombre: "Cargador de celular", watts: 10, horasDia: 3 },
      ],
      w = [
        { id: "recibo", label: "Subir recibo CFE", Icono: y.FilePlus01 },
        { id: "manual", label: "Capturar kWh", Icono: N.Pencil01 },
        { id: "aparatos", label: "Lista de aparatos", Icono: f },
      ];
    function k(e) {
      let a,
        t = (0, o.c)(14),
        { estado: i, dispatch: s } = e;
      if (t[0] !== s || t[1] !== i) {
        let e,
          o,
          l,
          n,
          c,
          d = eD(i);
        (t[3] === Symbol.for("react.memo_cache_sentinel")
          ? ((e = (0, r.jsx)("h2", {
              className: "text-xl font-semibold text-[var(--color-text-primary)]",
              children: "¿Cuánta energía consumes?",
            })),
            (o = (0, r.jsx)("p", {
              className: "mt-1 text-sm text-[var(--color-text-tertiary)]",
              children: "La forma más fácil: sube tu recibo de CFE y lo leemos por ti.",
            })),
            (t[3] = e),
            (t[4] = o))
          : ((e = t[3]), (o = t[4])),
          t[5] !== s || t[6] !== i.metodoConsumo
            ? ((l = w.map((e) => {
                let { id: o, label: a, Icono: t } = e;
                return (0, r.jsxs)(
                  "button",
                  {
                    type: "button",
                    onClick: () => s({ type: "metodoConsumo", valor: o }),
                    className: `flex flex-1 items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold sm:text-sm ${i.metodoConsumo === o ? "bg-[var(--color-bg-primary)] text-[var(--color-text-primary)] shadow-xs" : "text-[var(--color-text-tertiary)] hover:text-[var(--color-text-secondary)]"}`,
                    children: [(0, r.jsx)(t, { size: 16, "aria-hidden": !0, className: "shrink-0" }), a],
                  },
                  o,
                );
              })),
              (t[5] = s),
              (t[6] = i.metodoConsumo),
              (t[7] = l))
            : (l = t[7]),
          t[8] !== l
            ? ((n = (0, r.jsx)("div", {
                className: "mt-5 flex gap-1 rounded-lg bg-[var(--color-bg-tertiary)] p-1",
                children: l,
              })),
              (t[8] = l),
              (t[9] = n))
            : (n = t[9]));
        let u = "recibo" === i.metodoConsumo && (0, r.jsx)(C, { estado: i, dispatch: s }),
          m = "manual" === i.metodoConsumo && (0, r.jsx)(M, { estado: i, dispatch: s }),
          p = "aparatos" === i.metodoConsumo && (0, r.jsx)(P, { estado: i, dispatch: s });
        (t[10] !== u || t[11] !== m || t[12] !== p
          ? ((c = (0, r.jsxs)("div", {
              className:
                "mt-5 rounded-2xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] p-5",
              children: [u, m, p],
            })),
            (t[10] = u),
            (t[11] = m),
            (t[12] = p),
            (t[13] = c))
          : (c = t[13]),
          (a = (0, r.jsxs)("div", {
            children: [
              e,
              o,
              n,
              c,
              d > 0 &&
                (0, r.jsxs)("p", {
                  className: "mt-4 text-sm text-[var(--color-text-secondary)]",
                  children: [
                    "Tu consumo estimado:",
                    " ",
                    (0, r.jsxs)("strong", {
                      className: "text-[var(--color-text-primary)]",
                      children: [d.toFixed(1), " kWh al día"],
                    }),
                    i.tarifa ? ` \xb7 tarifa ${i.tarifa}` : "",
                  ],
                }),
            ],
          })),
          (t[0] = s),
          (t[1] = i),
          (t[2] = a));
      } else a = t[2];
      return a;
    }
    function C({ estado: e, dispatch: o }) {
      let t = (0, a.useRef)(null),
        [i, s] = (0, a.useState)(!1),
        [l, n] = (0, a.useState)(null),
        [c, d] = (0, a.useState)(!1),
        [u, m] = (0, a.useState)(!1),
        [p, x] = (0, a.useState)(null),
        [b, h] = (0, a.useState)(null),
        [f, N] = (0, a.useState)([]);
      async function _(e) {
        (s(!0), n(null), d(!1));
        try {
          let r = new FormData();
          r.append("file", e);
          let a = await fetch("/api/energia/recibo-cfe", { method: "POST", body: r }),
            t = await a.json();
          if (!a.ok || !t.success) return void n(t.error || "No se pudo leer el recibo; captura los kWh a mano");
          let i = Number(t.dias_periodo) || 60;
          (d(!t.dias_periodo), x(e.name), h(null != t.total_pagado ? Number(t.total_pagado) : null));
          let s = Array.isArray(t.historico) ? t.historico : [];
          (N(s),
            o({ type: "consumoRecibo", kwh: Number(t.kwh_periodo), dias: i, tarifa: t.tarifa ?? null, historico: s }));
        } catch {
          n("No se pudo leer el recibo; captura los kWh a mano");
        } finally {
          s(!1);
        }
      }
      let w = null != p && null != e.kwhPeriodo;
      return (0, r.jsxs)("div", {
        children: [
          (0, r.jsx)("input", {
            ref: t,
            type: "file",
            accept: ".pdf,image/jpeg,image/png,image/webp",
            className: "hidden",
            onChange: (e) => {
              let r = e.target.files?.[0];
              (r && _(r), (e.target.value = ""));
            },
          }),
          i
            ? (0, r.jsxs)("div", {
                className:
                  "flex flex-col items-center gap-2 rounded-lg border border-dashed border-[var(--color-border-primary)] p-8 text-center",
                children: [
                  (0, r.jsx)(j.Loading01, {
                    size: 24,
                    className: "animate-spin text-[var(--color-fg-quaternary)]",
                    "aria-hidden": !0,
                  }),
                  (0, r.jsx)("p", {
                    className: "text-sm text-[var(--color-text-tertiary)]",
                    children: "Leyendo tu recibo…",
                  }),
                ],
              })
            : w
              ? (0, r.jsxs)("div", {
                  className: "rounded-xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-secondary)]",
                  children: [
                    (0, r.jsxs)("div", {
                      className: "flex items-center gap-2.5 border-b border-[var(--color-border-secondary)] px-4 py-3",
                      children: [
                        (0, r.jsx)(g.CheckCircle, {
                          size: 20,
                          className: "shrink-0 text-[var(--color-text-success-primary,#079455)]",
                          "aria-hidden": !0,
                        }),
                        (0, r.jsxs)("div", {
                          className: "min-w-0 flex-1",
                          children: [
                            (0, r.jsx)("p", {
                              className: "text-sm font-semibold text-[var(--color-text-primary)]",
                              children: "Leímos tu recibo",
                            }),
                            (0, r.jsx)("p", {
                              className: "truncate text-xs text-[var(--color-text-tertiary)]",
                              children: p,
                            }),
                          ],
                        }),
                        (0, r.jsx)("button", {
                          type: "button",
                          onClick: () => t.current?.click(),
                          className:
                            "shrink-0 text-xs font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline",
                          children: "Cambiar archivo",
                        }),
                      ],
                    }),
                    (0, r.jsxs)("dl", {
                      className: "grid grid-cols-2 gap-x-4 gap-y-3 px-4 py-4 sm:grid-cols-4",
                      children: [
                        (0, r.jsx)(S, { etiqueta: "Energía del periodo", valor: `${e.kwhPeriodo} kWh`, destacado: !0 }),
                        (0, r.jsx)(S, { etiqueta: "Tarifa", valor: e.tarifa || "—" }),
                        (0, r.jsx)(S, { etiqueta: "Periodo", valor: `${e.diasPeriodo} d\xedas`, alerta: c }),
                        null != b &&
                          (0, r.jsx)(S, {
                            etiqueta: "Total del recibo",
                            valor: `$${b.toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
                          }),
                        e.historico.length > 0 &&
                          (0, r.jsx)(S, {
                            etiqueta: "Historial",
                            valor: `${e.historico.length} periodo${e.historico.length > 1 ? "s" : ""}`,
                          }),
                      ],
                    }),
                    c &&
                      (0, r.jsxs)("p", {
                        className:
                          "flex items-start gap-1.5 border-t border-[var(--color-border-secondary)] px-4 py-2.5 text-xs text-[var(--color-text-secondary)]",
                        children: [
                          (0, r.jsx)(v.AlertTriangle, {
                            size: 16,
                            className: "mt-px shrink-0 text-[#b54708]",
                            "aria-hidden": !0,
                          }),
                          "No pudimos leer el periodo, asumimos 60 días (bimestral). Si tu recibo es mensual, corrige los días abajo.",
                        ],
                      }),
                    (0, r.jsxs)("div", {
                      className: "border-t border-[var(--color-border-secondary)] px-4 py-4",
                      children: [
                        (0, r.jsx)("p", {
                          className: "mb-2 text-xs font-medium text-[var(--color-text-tertiary)]",
                          children: "¿No coincide con tu recibo? Ajústalo:",
                        }),
                        (0, r.jsxs)("div", {
                          className: "grid gap-3 sm:grid-cols-2",
                          children: [
                            (0, r.jsx)($, {
                              label: "kWh del periodo",
                              valor: e.kwhPeriodo,
                              onCambio: (r) => o({ type: "consumoRecibo", kwh: r, dias: e.diasPeriodo }),
                            }),
                            (0, r.jsx)($, {
                              label: "Días del periodo",
                              valor: e.diasPeriodo,
                              onCambio: (r) => o({ type: "consumoRecibo", kwh: e.kwhPeriodo, dias: r || 60 }),
                            }),
                          ],
                        }),
                      ],
                    }),
                    (0, r.jsx)(D, { estado: e, dispatch: o, historicoLeido: f }),
                  ],
                })
              : (0, r.jsxs)("div", {
                  onDragOver: (e) => {
                    (e.preventDefault(), m(!0));
                  },
                  onDragLeave: () => m(!1),
                  onDrop: (e) => {
                    (e.preventDefault(), m(!1));
                    let r = e.dataTransfer.files?.[0];
                    r && _(r);
                  },
                  className: `flex flex-col items-center gap-2 rounded-lg border border-dashed p-8 text-center ${u ? "border-[var(--color-border-brand)] bg-[var(--color-bg-brand-surface,#edf3ff)]" : "border-[var(--color-border-primary)]"}`,
                  children: [
                    (0, r.jsx)(y.FilePlus01, {
                      size: 24,
                      className: "text-[var(--color-fg-quaternary)]",
                      "aria-hidden": !0,
                    }),
                    (0, r.jsx)("p", {
                      className: "text-sm text-[var(--color-text-secondary)]",
                      children: "Arrastra aquí tu recibo de CFE (PDF o foto)",
                    }),
                    (0, r.jsx)("button", {
                      type: "button",
                      onClick: () => t.current?.click(),
                      className:
                        "rounded-lg bg-[var(--color-bg-primary)] px-3.5 py-2 text-sm font-semibold text-[var(--color-text-secondary)] ring-1 ring-inset ring-[var(--color-border-primary)] shadow-xs hover:bg-[var(--color-bg-secondary)]",
                      children: "Elegir archivo",
                    }),
                  ],
                }),
          l &&
            (0, r.jsx)("p", { className: "mt-3 text-sm text-[var(--color-text-error-primary,#d92d20)]", children: l }),
        ],
      });
    }
    function S(e) {
      let a,
        t,
        i,
        s = (0, o.c)(8),
        { etiqueta: l, valor: n, destacado: c, alerta: d } = e;
      s[0] !== l
        ? ((a = (0, r.jsx)("dt", {
            className: "text-xs font-medium uppercase tracking-wide text-[var(--color-text-quaternary)]",
            children: l,
          })),
          (s[0] = l),
          (s[1] = a))
        : (a = s[1]);
      let u = `mt-0.5 font-semibold ${c ? "text-lg" : "text-sm"} ${d ? "text-[#b54708]" : "text-[var(--color-text-primary)]"}`;
      return (
        s[2] !== u || s[3] !== n
          ? ((t = (0, r.jsx)("dd", { className: u, children: n })), (s[2] = u), (s[3] = n), (s[4] = t))
          : (t = s[4]),
        s[5] !== a || s[6] !== t
          ? ((i = (0, r.jsxs)("div", { children: [a, t] })), (s[5] = a), (s[6] = t), (s[7] = i))
          : (i = s[7]),
        i
      );
    }
    function M(e) {
      let a,
        t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m = (0, o.c)(19),
        { estado: p, dispatch: x } = e;
      return (
        m[0] === Symbol.for("react.memo_cache_sentinel")
          ? ((a = (0, r.jsxs)("p", {
              className: "text-sm text-[var(--color-text-tertiary)]",
              children: [
                "En tu recibo de CFE busca el recuadro ",
                (0, r.jsx)("strong", { children: "“Energía (kWh)”" }),
                " o la diferencia entre la lectura actual y la anterior: es tu consumo del periodo (normalmente 2 meses).",
              ],
            })),
            (m[0] = a))
          : (a = m[0]),
        m[1] !== x || m[2] !== p.diasPeriodo
          ? ((t = (e) => x({ type: "consumoRecibo", kwh: e, dias: p.diasPeriodo })),
            (m[1] = x),
            (m[2] = p.diasPeriodo),
            (m[3] = t))
          : (t = m[3]),
        m[4] !== p.kwhPeriodo || m[5] !== t
          ? ((i = (0, r.jsx)($, {
              label: "kWh de tu recibo",
              valor: p.kwhPeriodo,
              placeholder: "ej. 350",
              onCambio: t,
            })),
            (m[4] = p.kwhPeriodo),
            (m[5] = t),
            (m[6] = i))
          : (i = m[6]),
        m[7] === Symbol.for("react.memo_cache_sentinel")
          ? ((s = (0, r.jsx)("label", {
              className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
              children: "Tu recibo llega cada…",
            })),
            (m[7] = s))
          : (s = m[7]),
        m[8] !== x || m[9] !== p.kwhPeriodo
          ? ((l = (e) => x({ type: "consumoRecibo", kwh: p.kwhPeriodo, dias: Number(e.target.value) })),
            (m[8] = x),
            (m[9] = p.kwhPeriodo),
            (m[10] = l))
          : (l = m[10]),
        m[11] === Symbol.for("react.memo_cache_sentinel")
          ? ((n = (0, r.jsx)("option", { value: 60, children: "2 meses (bimestral)" })),
            (c = (0, r.jsx)("option", { value: 30, children: "1 mes (mensual)" })),
            (m[11] = n),
            (m[12] = c))
          : ((n = m[11]), (c = m[12])),
        m[13] !== p.diasPeriodo || m[14] !== l
          ? ((d = (0, r.jsxs)("div", {
              children: [
                s,
                (0, r.jsxs)("select", {
                  value: p.diasPeriodo,
                  onChange: l,
                  className:
                    "w-full rounded-lg border border-[var(--color-border-primary)] bg-[var(--color-bg-primary)] px-3 py-2 text-sm text-[var(--color-text-primary)] shadow-xs focus:border-[var(--color-border-brand)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-border-brand)]",
                  children: [n, c],
                }),
              ],
            })),
            (m[13] = p.diasPeriodo),
            (m[14] = l),
            (m[15] = d))
          : (d = m[15]),
        m[16] !== i || m[17] !== d
          ? ((u = (0, r.jsxs)("div", {
              className: "space-y-4",
              children: [a, (0, r.jsxs)("div", { className: "grid gap-3 sm:grid-cols-2", children: [i, d] })],
            })),
            (m[16] = i),
            (m[17] = d),
            (m[18] = u))
          : (u = m[18]),
        u
      );
    }
    function P(e) {
      let a,
        t,
        i,
        s,
        l,
        n,
        c,
        d = (0, o.c)(18),
        { estado: u, dispatch: m } = e,
        p = u.aparatos;
      d[0] !== m || d[1] !== p
        ? ((a = (e) => {
            p.find((r) => r.nombre === e)
              ? m({ type: "aparatos", valor: p.filter((r) => r.nombre !== e) })
              : m({
                  type: "aparatos",
                  valor: [...p, { ..._.find((r) => r.nombre === e), cantidad: 1, simultaneo: !1, critico: !1 }],
                });
          }),
          (d[0] = m),
          (d[1] = p),
          (d[2] = a))
        : (a = d[2]);
      let x = a;
      d[3] !== m || d[4] !== p
        ? ((t = (e, r) => {
            m({ type: "aparatos", valor: p.map((o) => (o.nombre === e ? { ...o, ...r } : o)) });
          }),
          (d[3] = m),
          (d[4] = p),
          (d[5] = t))
        : (t = d[5]);
      let b = t;
      return (
        d[6] === Symbol.for("react.memo_cache_sentinel")
          ? ((i = (0, r.jsxs)("p", {
              className: "text-sm text-[var(--color-text-tertiary)]",
              children: [
                "Marca lo que vas a conectar. Marca ",
                (0, r.jsx)("strong", { children: "“al mismo tiempo”" }),
                " en lo que suele encenderse junto (define el tamaño del inversor).",
              ],
            })),
            (d[6] = i))
          : (i = d[6]),
        d[7] !== p || d[8] !== x
          ? ((s = _.map((e) => {
              let o = p.some((r) => r.nombre === e.nombre);
              return (0, r.jsxs)(
                "button",
                {
                  type: "button",
                  onClick: () => x(e.nombre),
                  className: `rounded-full px-3 py-1.5 text-xs font-medium ring-1 ring-inset ${o ? "bg-[var(--color-bg-brand-solid)] text-white ring-transparent" : "bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)] ring-[var(--color-border-primary)] hover:bg-[var(--color-bg-secondary)]"}`,
                  children: [e.nombre, " · ", e.watts, " W"],
                },
                e.nombre,
              );
            })),
            (d[7] = p),
            (d[8] = x),
            (d[9] = s))
          : (s = d[9]),
        d[10] !== s
          ? ((l = (0, r.jsx)("div", { className: "flex flex-wrap gap-2", children: s })), (d[10] = s), (d[11] = l))
          : (l = d[11]),
        d[12] !== b || d[13] !== p
          ? ((n =
              p.length > 0 &&
              (0, r.jsx)("div", {
                className:
                  "divide-y divide-[var(--color-border-secondary)] rounded-lg border border-[var(--color-border-secondary)]",
                children: p.map((e) =>
                  (0, r.jsxs)(
                    "div",
                    {
                      className: "flex flex-wrap items-center gap-3 p-3",
                      children: [
                        (0, r.jsx)("span", {
                          className: "min-w-0 flex-1 text-sm font-medium text-[var(--color-text-primary)]",
                          children: e.nombre,
                        }),
                        (0, r.jsxs)("label", {
                          className: "flex items-center gap-1 text-xs text-[var(--color-text-tertiary)]",
                          children: [
                            "Cant.",
                            (0, r.jsx)("input", {
                              type: "number",
                              min: 1,
                              value: e.cantidad,
                              onChange: (r) => b(e.nombre, { cantidad: Math.max(1, Number(r.target.value) || 1) }),
                              className:
                                "w-14 rounded-lg border border-[var(--color-border-primary)] px-2 py-1 text-sm text-[var(--color-text-primary)]",
                            }),
                          ],
                        }),
                        (0, r.jsxs)("label", {
                          className: "flex items-center gap-1 text-xs text-[var(--color-text-tertiary)]",
                          children: [
                            "Hrs/día",
                            (0, r.jsx)("input", {
                              type: "number",
                              min: 0,
                              max: 24,
                              step: 0.5,
                              value: e.horasDia,
                              onChange: (r) =>
                                b(e.nombre, { horasDia: Math.min(24, Math.max(0, Number(r.target.value) || 0)) }),
                              className:
                                "w-16 rounded-lg border border-[var(--color-border-primary)] px-2 py-1 text-sm text-[var(--color-text-primary)]",
                            }),
                          ],
                        }),
                        (0, r.jsxs)("label", {
                          className: "flex items-center gap-1.5 text-xs text-[var(--color-text-tertiary)]",
                          children: [
                            (0, r.jsx)("input", {
                              type: "checkbox",
                              checked: e.simultaneo,
                              onChange: (r) => b(e.nombre, { simultaneo: r.target.checked }),
                              className:
                                "h-4 w-4 rounded border-[var(--color-border-primary)] accent-[var(--color-bg-brand-solid)]",
                            }),
                            "al mismo tiempo",
                          ],
                        }),
                      ],
                    },
                    e.nombre,
                  ),
                ),
              })),
            (d[12] = b),
            (d[13] = p),
            (d[14] = n))
          : (n = d[14]),
        d[15] !== l || d[16] !== n
          ? ((c = (0, r.jsxs)("div", { className: "space-y-4", children: [i, l, n] })),
            (d[15] = l),
            (d[16] = n),
            (d[17] = c))
          : (c = d[17]),
        c
      );
    }
    function $(e) {
      let a,
        t,
        i,
        s,
        l = (0, o.c)(11),
        { label: n, valor: c, placeholder: d, onCambio: u } = e;
      l[0] !== n
        ? ((a = (0, r.jsx)("label", {
            className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
            children: n,
          })),
          (l[0] = n),
          (l[1] = a))
        : (a = l[1]);
      let m = c ?? "";
      return (
        l[2] !== u
          ? ((t = (e) => u("" === e.target.value ? null : Math.max(0, Number(e.target.value)))), (l[2] = u), (l[3] = t))
          : (t = l[3]),
        l[4] !== d || l[5] !== m || l[6] !== t
          ? ((i = (0, r.jsx)("input", {
              type: "number",
              min: 0,
              value: m,
              placeholder: d,
              onChange: t,
              className:
                "w-full rounded-lg border border-[var(--color-border-primary)] bg-[var(--color-bg-primary)] px-3 py-2 text-sm text-[var(--color-text-primary)] shadow-xs focus:border-[var(--color-border-brand)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-border-brand)]",
            })),
            (l[4] = d),
            (l[5] = m),
            (l[6] = t),
            (l[7] = i))
          : (i = l[7]),
        l[8] !== a || l[9] !== i
          ? ((s = (0, r.jsxs)("div", { children: [a, i] })), (l[8] = a), (l[9] = i), (l[10] = s))
          : (s = l[10]),
        s
      );
    }
    function D(e) {
      let t,
        i,
        s = (0, o.c)(15),
        { estado: l, dispatch: n, historicoLeido: c } = e,
        [d, u] = (0, a.useState)(!1);
      if (s[0] !== d || s[1] !== n || s[2] !== l || s[3] !== c) {
        i = Symbol.for("react.early_return_sentinel");
        e: {
          let e,
            o,
            a = e$(l),
            m = l.historico.length !== c.length;
          if (!a) {
            let e, o;
            if (!m || 0 === c.length) {
              i = null;
              break e;
            }
            (s[6] !== n || s[7] !== c
              ? ((e = () => n({ type: "historico", valor: c })), (s[6] = n), (s[7] = c), (s[8] = e))
              : (e = s[8]),
              s[9] !== c.length || s[10] !== e
                ? ((o = (0, r.jsx)("div", {
                    className: "border-t border-[var(--color-border-secondary)] px-4 py-3",
                    children: (0, r.jsxs)("p", {
                      className: "text-xs text-[var(--color-text-tertiary)]",
                      children: [
                        "Estás dimensionando solo con el periodo de este recibo.",
                        " ",
                        (0, r.jsxs)("button", {
                          type: "button",
                          onClick: e,
                          className: "font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline",
                          children: ["Restaurar los ", c.length, " periodos de tu historial"],
                        }),
                      ],
                    }),
                  })),
                  (s[9] = c.length),
                  (s[10] = e),
                  (s[11] = o))
                : (o = s[11]),
              (i = o));
            break e;
          }
          s[12] !== l.diasPeriodo
            ? ((e = (e) => (e / l.diasPeriodo).toFixed(1)), (s[12] = l.diasPeriodo), (s[13] = e))
            : (e = s[13]);
          let p = e,
            x = "aislado" === l.tipoSistema;
          (s[14] === Symbol.for("react.memo_cache_sentinel") ? ((o = () => u(W)), (s[14] = o)) : (o = s[14]),
            (t = (0, r.jsxs)("div", {
              className: "border-t border-[var(--color-border-secondary)] px-4 py-4",
              children: [
                (0, r.jsxs)("button", {
                  type: "button",
                  onClick: o,
                  className: "text-xs font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline",
                  children: [d ? "Ocultar" : "Ver", " los ", a.nPeriodos, " periodos de tu historial"],
                }),
                d &&
                  (0, r.jsxs)("div", {
                    className: "mt-3 space-y-2",
                    children: [
                      (0, r.jsx)("ul", {
                        className:
                          "divide-y divide-[var(--color-border-secondary)] rounded-lg border border-[var(--color-border-secondary)]",
                        children: l.historico.map((e, o) =>
                          (0, r.jsxs)(
                            "li",
                            {
                              className: "flex items-center gap-3 px-3 py-1.5 text-xs",
                              children: [
                                (0, r.jsx)("span", {
                                  className: "flex-1 text-[var(--color-text-primary)]",
                                  children: e.etiqueta || `Periodo ${o + 1}`,
                                }),
                                (0, r.jsxs)("span", {
                                  className: "tabular-nums text-[var(--color-text-secondary)]",
                                  children: [e.kwh.toLocaleString("es-MX"), " kWh"],
                                }),
                                (0, r.jsxs)("span", {
                                  className: "w-20 text-right tabular-nums text-[var(--color-text-quaternary)]",
                                  children: [p(e.kwh), " kWh/día"],
                                }),
                                (0, r.jsx)("button", {
                                  type: "button",
                                  onClick: () => n({ type: "historico", valor: l.historico.filter((e, r) => r !== o) }),
                                  "aria-label": `Descartar ${e.etiqueta || `periodo ${o + 1}`}`,
                                  className:
                                    "text-[var(--color-text-quaternary)] hover:text-[var(--color-text-primary)]",
                                  children: "×",
                                }),
                              ],
                            },
                            `${e.etiqueta}-${o}`,
                          ),
                        ),
                      }),
                      (0, r.jsxs)("p", {
                        className: "text-xs text-[var(--color-text-tertiary)]",
                        children: [
                          "Promedio ",
                          a.promedioDiario.toFixed(1),
                          " kWh/día · pico ",
                          a.picoDiario.toFixed(1),
                          a.etiquetaPico ? ` (${a.etiquetaPico})` : "",
                        ],
                      }),
                      (0, r.jsxs)("div", {
                        className: "flex gap-3 text-xs",
                        children: [
                          (0, r.jsx)("button", {
                            type: "button",
                            onClick: () => n({ type: "historico", valor: [] }),
                            className: "font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline",
                            children: "Usar solo el periodo actual",
                          }),
                          m &&
                            (0, r.jsx)("button", {
                              type: "button",
                              onClick: () => n({ type: "historico", valor: c }),
                              className:
                                "font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline",
                              children: "Restaurar lo leído",
                            }),
                        ],
                      }),
                    ],
                  }),
                a.anioCompleto
                  ? (0, r.jsx)("p", {
                      className: "mt-2 text-xs text-[var(--color-text-secondary)]",
                      children: x
                        ? (0, r.jsxs)(r.Fragment, {
                            children: [
                              "Dimensionamos con tu ",
                              (0, r.jsx)("strong", { children: "periodo más alto" }),
                              " (",
                              a.picoDiario.toFixed(1),
                              " kWh/día",
                              a.etiquetaPico ? `, ${a.etiquetaPico}` : "",
                              "): un sistema aislado no tiene red que lo respalde en el mes pico.",
                            ],
                          })
                        : (0, r.jsxs)(r.Fragment, {
                            children: [
                              "Dimensionamos con tu ",
                              (0, r.jsx)("strong", { children: "promedio anual" }),
                              " (",
                              a.promedioDiario.toFixed(1),
                              " kWh/día) y no con un solo recibo: con interconexión, el excedente de invierno compensa el verano.",
                            ],
                          }),
                    })
                  : (0, r.jsxs)("p", {
                      className: "mt-2 text-xs text-[var(--color-text-secondary)]",
                      children: [
                        "Leímos ",
                        a.nPeriodos,
                        " periodo",
                        a.nPeriodos > 1 ? "s" : "",
                        "; no alcanzan un año, así que dimensionamos con el periodo actual.",
                      ],
                    }),
              ],
            })));
        }
        ((s[0] = d), (s[1] = n), (s[2] = l), (s[3] = c), (s[4] = t), (s[5] = i));
      } else ((t = s[4]), (i = s[5]));
      return i !== Symbol.for("react.early_return_sentinel") ? i : t;
    }
    function W(e) {
      return !e;
    }
    let q = ({ size: e = 24, color: r = "currentColor", ...o }) =>
      a.createElement(
        "svg",
        { viewBox: "0 0 24 24", fill: "none", width: e, height: e, color: r, "aria-hidden": "true", ...o },
        a.createElement("path", {
          d: "M11.293 1.293a1 1 0 0 1 1.414 0l4 4a1 1 0 0 1-1.414 1.414L13 4.414V9a1 1 0 1 1-2 0V4.414L8.707 6.707a1 1 0 0 1-1.414-1.414l4-4ZM2 17a1 1 0 1 0 0 2h2a1 1 0 1 0 0-2H2ZM4.193 10.193a1 1 0 0 1 1.414 0l1.414 1.414a1 1 0 1 1-1.414 1.414l-1.414-1.414a1 1 0 0 1 0-1.414ZM19.807 11.607a1 1 0 0 0-1.414-1.414l-1.414 1.414a1 1 0 0 0 1.414 1.414l1.414-1.414ZM19 18a1 1 0 0 1 1-1h2a1 1 0 1 1 0 2h-2a1 1 0 0 1-1-1ZM6 18a6 6 0 0 1 12 0 1 1 0 0 1-1 1H7a1 1 0 0 1-1-1ZM2 21a1 1 0 1 0 0 2h20a1 1 0 1 0 0-2H2Z",
          fill: "currentColor",
        }),
      );
    function A(e) {
      let a,
        t,
        i,
        s,
        l,
        n,
        c,
        m,
        p,
        x = (0, o.c)(15),
        { estado: b, dispatch: h } = e,
        v = b.estado ? d[b.estado] : null;
      (x[0] === Symbol.for("react.memo_cache_sentinel")
        ? ((a = (0, r.jsx)("h2", {
            className: "text-xl font-semibold text-[var(--color-text-primary)]",
            children: "¿Dónde se instalará?",
          })),
          (t = (0, r.jsx)("p", {
            className: "mt-1 text-sm text-[var(--color-text-tertiary)]",
            children: "El sol no pega igual en todo el país: con tu estado calculamos las horas de sol aprovechables.",
          })),
          (x[0] = a),
          (x[1] = t))
        : ((a = x[0]), (t = x[1])),
        x[2] === Symbol.for("react.memo_cache_sentinel")
          ? ((i = (0, r.jsx)("label", {
              className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
              children: "Estado de la república",
            })),
            (x[2] = i))
          : (i = x[2]));
      let g = b.estado ?? "";
      return (
        x[3] !== h ? ((s = (e) => h({ type: "estado", valor: e.target.value })), (x[3] = h), (x[4] = s)) : (s = x[4]),
        x[5] === Symbol.for("react.memo_cache_sentinel")
          ? ((l = (0, r.jsx)("option", { value: "", disabled: !0, children: "Elige tu estado…" })),
            (n = u.map(R)),
            (x[5] = l),
            (x[6] = n))
          : ((l = x[5]), (n = x[6])),
        x[7] !== g || x[8] !== s
          ? ((c = (0, r.jsxs)("div", {
              className: "mt-6 max-w-sm",
              children: [
                i,
                (0, r.jsxs)("select", {
                  value: g,
                  onChange: s,
                  className:
                    "w-full rounded-lg border border-[var(--color-border-primary)] bg-[var(--color-bg-primary)] px-3 py-2 text-sm text-[var(--color-text-primary)] shadow-xs focus:border-[var(--color-border-brand)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-border-brand)]",
                  children: [l, n],
                }),
              ],
            })),
            (x[7] = g),
            (x[8] = s),
            (x[9] = c))
          : (c = x[9]),
        x[10] !== v
          ? ((m =
              null != v &&
              (0, r.jsxs)("div", {
                className:
                  "mt-5 flex items-center gap-3 rounded-2xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] p-4",
                children: [
                  (0, r.jsx)(q, {
                    size: 24,
                    className: "shrink-0 text-[var(--color-fg-quaternary)]",
                    "aria-hidden": !0,
                  }),
                  (0, r.jsxs)("p", {
                    className: "text-sm text-[var(--color-text-secondary)]",
                    children: [
                      "Tu zona recibe en promedio",
                      " ",
                      (0, r.jsxs)("strong", {
                        className: "text-[var(--color-text-primary)]",
                        children: [v, " horas de sol pico"],
                      }),
                      " al día — es la energía con la que dimensionamos tus paneles.",
                    ],
                  }),
                ],
              })),
            (x[10] = v),
            (x[11] = m))
          : (m = x[11]),
        x[12] !== c || x[13] !== m
          ? ((p = (0, r.jsxs)("div", { children: [a, t, c, m] })), (x[12] = c), (x[13] = m), (x[14] = p))
          : (p = x[14]),
        p
      );
    }
    function R(e) {
      return (0, r.jsx)("option", { value: e, children: e }, e);
    }
    q.displayName = "Sunrise";
    let F = [
      { id: "lifepo4", label: "Litio (LiFePO₄)", nota: "Más ciclos y 80% usable; la opción moderna" },
      { id: "agm", label: "AGM", nota: "Sin mantenimiento, menor costo inicial" },
      { id: "gel", label: "Gel", nota: "Buena en calor, descargas lentas" },
      { id: "plomo", label: "Plomo-ácido", nota: "La más económica, requiere mantenimiento" },
    ];
    function T(e) {
      let a,
        t,
        i,
        s,
        n,
        c,
        d,
        u,
        m,
        p,
        x,
        b,
        h,
        v,
        g = (0, o.c)(34),
        { estado: f, dispatch: y } = e;
      g[0] !== f ? ((t = l((a = eD(f)))), (g[0] = f), (g[1] = a), (g[2] = t)) : ((a = g[1]), (t = g[2]));
      let j = t;
      g[3] !== f ? ((i = eq(f)), (g[3] = f), (g[4] = i)) : (i = g[4]);
      let N = i,
        _ = "hibrido" === f.tipoSistema,
        w = _ ? "Tu respaldo" : "Tus baterías";
      g[5] !== w
        ? ((s = (0, r.jsx)("h2", { className: "text-xl font-semibold text-[var(--color-text-primary)]", children: w })),
          (g[5] = w),
          (g[6] = s))
        : (s = g[6]);
      let k = _
        ? "Qué quieres mantener encendido cuando se va la luz, y por cuánto tiempo."
        : "Define cuánto respaldo quieres y con qué tecnología.";
      return (
        g[7] !== k
          ? ((n = (0, r.jsx)("p", { className: "mt-1 text-sm text-[var(--color-text-tertiary)]", children: k })),
            (g[7] = k),
            (g[8] = n))
          : (n = g[8]),
        g[9] !== s || g[10] !== n
          ? ((c = (0, r.jsxs)("div", { children: [s, n] })), (g[9] = s), (g[10] = n), (g[11] = c))
          : (c = g[11]),
        g[12] !== a || g[13] !== y || g[14] !== _ || g[15] !== f
          ? ((d = _
              ? (0, r.jsx)(E, { estado: f, dispatch: y, diario: a })
              : (0, r.jsxs)("div", {
                  children: [
                    (0, r.jsxs)("label", {
                      className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
                      children: [
                        "Días de autonomía (respaldo sin sol): ",
                        (0, r.jsx)("strong", { children: f.diasAutonomia }),
                      ],
                    }),
                    (0, r.jsx)("input", {
                      type: "range",
                      min: 1,
                      max: 3,
                      step: 1,
                      value: f.diasAutonomia,
                      onChange: (e) => y({ type: "diasAutonomia", valor: Number(e.target.value) }),
                      className: "w-full max-w-sm accent-[var(--color-bg-brand-solid)]",
                    }),
                    (0, r.jsx)("p", {
                      className: "mt-1 text-xs text-[var(--color-text-quaternary)]",
                      children: "1 día es lo usual; 2-3 para zonas con días nublados seguidos.",
                    }),
                  ],
                })),
            (g[12] = a),
            (g[13] = y),
            (g[14] = _),
            (g[15] = f),
            (g[16] = d))
          : (d = g[16]),
        g[17] === Symbol.for("react.memo_cache_sentinel")
          ? ((u = (0, r.jsx)("span", {
              className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
              children: "Voltaje del sistema",
            })),
            (g[17] = u))
          : (u = g[17]),
        g[18] === Symbol.for("react.memo_cache_sentinel") ? ((m = [12, 24, 48]), (g[18] = m)) : (m = g[18]),
        g[19] !== y || g[20] !== j || g[21] !== N
          ? ((p = (0, r.jsxs)("div", {
              children: [
                u,
                (0, r.jsx)("div", {
                  className: "flex gap-2",
                  children: m.map((e) =>
                    (0, r.jsxs)(
                      "button",
                      {
                        type: "button",
                        onClick: () => y({ type: "vSistema", valor: e }),
                        className: `rounded-lg px-4 py-2 text-sm font-semibold ring-1 ring-inset ${N === e ? "bg-[var(--color-bg-brand-solid)] text-white ring-transparent" : "bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)] ring-[var(--color-border-primary)] hover:bg-[var(--color-bg-secondary)]"}`,
                        children: [e, " V", e === j ? " · recomendado" : ""],
                      },
                      e,
                    ),
                  ),
                }),
              ],
            })),
            (g[19] = y),
            (g[20] = j),
            (g[21] = N),
            (g[22] = p))
          : (p = g[22]),
        g[23] === Symbol.for("react.memo_cache_sentinel")
          ? ((x = (0, r.jsx)("span", {
              className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
              children: "Tecnología de batería",
            })),
            (g[23] = x))
          : (x = g[23]),
        g[24] !== y || g[25] !== f.tecnologia
          ? ((b = F.map((e) => {
              let { id: o, label: a, nota: t } = e,
                i = f.tecnologia === o;
              return (0, r.jsxs)(
                "button",
                {
                  type: "button",
                  onClick: () => y({ type: "tecnologia", valor: o }),
                  className: `rounded-2xl border bg-[var(--color-bg-primary)] p-4 text-left ${i ? "border-[var(--color-border-brand)] ring-1 ring-inset ring-[var(--color-border-brand)]" : "border-[var(--color-border-secondary)]"}`,
                  children: [
                    (0, r.jsx)("span", {
                      className: "block text-sm font-semibold text-[var(--color-text-primary)]",
                      children: a,
                    }),
                    (0, r.jsx)("span", {
                      className: "mt-0.5 block text-xs text-[var(--color-text-tertiary)]",
                      children: t,
                    }),
                  ],
                },
                o,
              );
            })),
            (g[24] = y),
            (g[25] = f.tecnologia),
            (g[26] = b))
          : (b = g[26]),
        g[27] !== b
          ? ((h = (0, r.jsxs)("div", {
              children: [x, (0, r.jsx)("div", { className: "grid gap-3 sm:grid-cols-2", children: b })],
            })),
            (g[27] = b),
            (g[28] = h))
          : (h = g[28]),
        g[29] !== p || g[30] !== h || g[31] !== c || g[32] !== d
          ? ((v = (0, r.jsxs)("div", { className: "space-y-8", children: [c, d, p, h] })),
            (g[29] = p),
            (g[30] = h),
            (g[31] = c),
            (g[32] = d),
            (g[33] = v))
          : (v = g[33]),
        v
      );
    }
    function E(e) {
      let a,
        t,
        i,
        l,
        n,
        c,
        d,
        u,
        m,
        p,
        x,
        b,
        h = (0, o.c)(56),
        { estado: v, dispatch: g, diario: f } = e,
        y = "aparatos" === v.metodoConsumo && v.aparatos.length > 0;
      if (h[0] !== y || h[1] !== f || h[2] !== g || h[3] !== v) {
        let e,
          o,
          u = eR(v);
        h[11] !== g || h[12] !== v.aparatos
          ? ((e = (e) => {
              (g({
                type: "aparatos",
                valor: v.aparatos.map((r) => (r.nombre === e ? { ...r, critico: !r.critico } : r)),
              }),
                g({ type: "cargasCriticas", valor: null }));
            }),
            (h[11] = g),
            (h[12] = v.aparatos),
            (h[13] = e))
          : (e = h[13]);
        let m = e;
        h[14] !== f ? ((o = Math.round((1e3 * f) / 8)), (h[14] = f), (h[15] = o)) : (o = h[15]);
        let p = Math.max(eA, o);
        if (
          h[16] !== y ||
          h[17] !== g ||
          h[18] !== v.aparatos ||
          h[19] !== v.cargasCriticasManualW ||
          h[20] !== v.horasRespaldo ||
          h[21] !== p ||
          h[22] !== m
        ) {
          let e,
            o,
            a,
            t,
            i = [
              { id: "esencial", label: "Lo esencial", nota: "Refri, luces, internet", w: eA },
              { id: "media", label: "Media casa", nota: "Lo esencial + tomas y TV", w: 1500 },
              { id: "todo", label: "Todo", nota: "Tu demanda estimada completa", w: p },
            ],
            u = i.filter((e, r) => i.findIndex((r) => r.w === e.w) === r),
            x = v.cargasCriticasManualW ?? eA;
          ((c = "space-y-6"),
            h[27] !== v.horasRespaldo
              ? ((e = (0, r.jsxs)("label", {
                  className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
                  children: ["Horas de respaldo: ", (0, r.jsxs)("strong", { children: [v.horasRespaldo, " h"] })],
                })),
                (h[27] = v.horasRespaldo),
                (h[28] = e))
              : (e = h[28]),
            h[29] !== g
              ? ((o = (e) => g({ type: "horasRespaldo", valor: Number(e.target.value) })), (h[29] = g), (h[30] = o))
              : (o = h[30]),
            h[31] !== v.horasRespaldo || h[32] !== o
              ? ((a = (0, r.jsx)("input", {
                  type: "range",
                  min: 2,
                  max: 12,
                  step: 1,
                  value: v.horasRespaldo,
                  onChange: o,
                  className: "w-full max-w-sm accent-[var(--color-bg-brand-solid)]",
                })),
                (h[31] = v.horasRespaldo),
                (h[32] = o),
                (h[33] = a))
              : (a = h[33]),
            h[34] === Symbol.for("react.memo_cache_sentinel")
              ? ((t = (0, r.jsx)("p", {
                  className: "mt-1 text-xs text-[var(--color-text-quaternary)]",
                  children:
                    "Cuánto debe aguantar el banco durante un apagón. 4 h cubre la mayoría de las interrupciones; el resto del día lo surte CFE.",
                })),
                (h[34] = t))
              : (t = h[34]),
            h[35] !== e || h[36] !== a
              ? ((d = (0, r.jsxs)("div", { children: [e, a, t] })), (h[35] = e), (h[36] = a), (h[37] = d))
              : (d = h[37]),
            h[38] === Symbol.for("react.memo_cache_sentinel")
              ? ((l = (0, r.jsx)("span", {
                  className: "mb-1.5 block text-sm font-medium text-[var(--color-text-secondary)]",
                  children: "Qué respaldar",
                })),
                (h[38] = l))
              : (l = h[38]),
            (n = y
              ? (0, r.jsxs)("div", {
                  className: "space-y-1.5",
                  children: [
                    v.aparatos.map((e) =>
                      (0, r.jsxs)(
                        "label",
                        {
                          className:
                            "flex cursor-pointer items-center gap-2.5 rounded-lg border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] px-3 py-2",
                          children: [
                            (0, r.jsx)("input", {
                              type: "checkbox",
                              checked: e.critico,
                              onChange: () => m(e.nombre),
                              className: "size-4 accent-[var(--color-bg-brand-solid)]",
                            }),
                            (0, r.jsxs)("span", {
                              className: "flex-1 text-sm text-[var(--color-text-primary)]",
                              children: [e.nombre, e.cantidad > 1 ? ` \xd7${e.cantidad}` : ""],
                            }),
                            (0, r.jsxs)("span", {
                              className: "text-xs text-[var(--color-text-quaternary)]",
                              children: [e.watts * e.cantidad, " W"],
                            }),
                          ],
                        },
                        e.nombre,
                      ),
                    ),
                    0 === s(v.aparatos) &&
                      (0, r.jsxs)("p", {
                        className: "text-xs text-[var(--color-text-quaternary)]",
                        children: ["Sin nada marcado asumimos lo esencial (", eA, " W)."],
                      }),
                  ],
                })
              : (0, r.jsx)("div", {
                  className: "grid gap-3 sm:grid-cols-3",
                  children: u.map((e) => {
                    let { id: o, label: a, nota: t, w: i } = e,
                      s = x === i;
                    return (0, r.jsxs)(
                      "button",
                      {
                        type: "button",
                        onClick: () => g({ type: "cargasCriticas", valor: i }),
                        className: `rounded-2xl border bg-[var(--color-bg-primary)] p-4 text-left ${s ? "border-[var(--color-border-brand)] ring-1 ring-inset ring-[var(--color-border-brand)]" : "border-[var(--color-border-secondary)]"}`,
                        children: [
                          (0, r.jsx)("span", {
                            className: "block text-sm font-semibold text-[var(--color-text-primary)]",
                            children: a,
                          }),
                          (0, r.jsx)("span", {
                            className: "mt-0.5 block text-xs text-[var(--color-text-tertiary)]",
                            children: t,
                          }),
                          (0, r.jsxs)("span", {
                            className: "mt-1.5 block text-xs font-semibold text-[var(--color-text-secondary)]",
                            children: ["~", i.toLocaleString("es-MX"), " W"],
                          }),
                        ],
                      },
                      o,
                    );
                  }),
                })),
            (h[16] = y),
            (h[17] = g),
            (h[18] = v.aparatos),
            (h[19] = v.cargasCriticasManualW),
            (h[20] = v.horasRespaldo),
            (h[21] = p),
            (h[22] = m),
            (h[23] = l),
            (h[24] = n),
            (h[25] = c),
            (h[26] = d));
        } else ((l = h[23]), (n = h[24]), (c = h[25]), (d = h[26]));
        ((t = "mt-2 text-sm text-[var(--color-text-secondary)]"),
          (i = "Respaldando "),
          (a = u.toLocaleString("es-MX")),
          (h[0] = y),
          (h[1] = f),
          (h[2] = g),
          (h[3] = v),
          (h[4] = a),
          (h[5] = t),
          (h[6] = i),
          (h[7] = l),
          (h[8] = n),
          (h[9] = c),
          (h[10] = d));
      } else ((a = h[4]), (t = h[5]), (i = h[6]), (l = h[7]), (n = h[8]), (c = h[9]), (d = h[10]));
      return (
        h[39] !== a ? ((u = (0, r.jsxs)("strong", { children: [a, " W"] })), (h[39] = a), (h[40] = u)) : (u = h[40]),
        h[41] !== v.horasRespaldo
          ? ((m = (0, r.jsxs)("strong", { children: [v.horasRespaldo, " h"] })), (h[41] = v.horasRespaldo), (h[42] = m))
          : (m = h[42]),
        h[43] !== t || h[44] !== i || h[45] !== u || h[46] !== m
          ? ((p = (0, r.jsxs)("p", { className: t, children: [i, u, " durante", " ", m, "."] })),
            (h[43] = t),
            (h[44] = i),
            (h[45] = u),
            (h[46] = m),
            (h[47] = p))
          : (p = h[47]),
        h[48] !== p || h[49] !== l || h[50] !== n
          ? ((x = (0, r.jsxs)("div", { children: [l, n, p] })), (h[48] = p), (h[49] = l), (h[50] = n), (h[51] = x))
          : (x = h[51]),
        h[52] !== x || h[53] !== c || h[54] !== d
          ? ((b = (0, r.jsxs)("div", { className: c, children: [d, x] })),
            (h[52] = x),
            (h[53] = c),
            (h[54] = d),
            (h[55] = b))
          : (b = h[55]),
        b
      );
    }
    var L = e.i(526732),
      z = e.i(988794),
      B = e.i(280285),
      I = e.i(311713),
      H = e.i(505870);
    let V = ({ size: e = 24, color: r = "currentColor", ...o }) =>
      a.createElement(
        "svg",
        { viewBox: "0 0 24 24", fill: "none", width: e, height: e, color: r, "aria-hidden": "true", ...o },
        a.createElement("path", {
          fillRule: "evenodd",
          clipRule: "evenodd",
          d: "M2 3h1.146l2.763 17.501c.016.101.035.226.061.335.03.129.085.306.203.487a1.5 1.5 0 0 0 .637.544c.197.089.38.115.513.125.112.008.237.008.34.008H19a1 1 0 1 0 0-2H7.854l-.315-2h9.825c.445 0 .834 0 1.157-.024a3.032 3.032 0 0 0 1.026-.233 3 3 0 0 0 1.265-1.056c.208-.307.325-.633.412-.967.082-.314.152-.696.231-1.135l1.362-7.489c.025-.139.055-.299.068-.44.015-.16.022-.4-.073-.664a1.5 1.5 0 0 0-.66-.79 1.515 1.515 0 0 0-.639-.19C21.372 5 21.209 5 21.068 5H5.486l-.395-2.501a4.65 4.65 0 0 0-.061-.335 1.503 1.503 0 0 0-.203-.487 1.5 1.5 0 0 0-.637-.544 1.503 1.503 0 0 0-.513-.125A4.645 4.645 0 0 0 3.337 1H2a1 1 0 0 0 0 2Z",
          fill: "currentColor",
        }),
      );
    V.displayName = "ShoppingCart02";
    var Z = e.i(738531);
    function O(e) {
      return e.existencia > 0 || e.bajoPedido;
    }
    function U(e, r) {
      return e.bajoPedido || e.existencia >= r;
    }
    function K(e, r) {
      return [...e].sort((e, o) => {
        let a = r(e) ?? 1 / 0,
          t = r(o) ?? 1 / 0;
        if (a !== t) return a - t;
        let i = e.precio1 ?? 1 / 0,
          s = o.precio1 ?? 1 / 0;
        return i !== s ? i - s : o.existencia - e.existencia;
      });
    }
    function X(e, r) {
      let o = e.filter((e) => (e.atributos?.potencia_w ?? 0) > 0 && O(e));
      if (!o.length) return null;
      let a = o.filter((e) => e.atributos.potencia_w >= 400),
        t = a.length ? a : o,
        i = t.filter((e) => U(e, n(r, e.atributos.potencia_w))),
        s = K(i.length ? i : t, (e) => e.atributos.potencia_w)[0];
      return { producto: s, cantidad: n(r, s.atributos.potencia_w) };
    }
    function G(e, r, o) {
      let a = K(
        e
          .filter((e) => {
            let a;
            return (
              e.atributos?.tipo_controlador === "mppt" &&
              (e.atributos.corriente_a ?? 0) >= r.controladorMinA &&
              (null == (a = e.atributos.voltaje_sistema) || a.split("/").includes(String(r.vSistema))) &&
              (null == o || null == e.atributos.voc_max_v || e.atributos.voc_max_v >= 2 * o * 1.15)
            );
          })
          .filter(O),
        (e) => e.atributos.corriente_a,
      )[0];
      return a ? { producto: a, cantidad: 1 } : null;
    }
    function Q(e) {
      return e
        .filter(O)
        .sort((e, r) =>
          r.existencia !== e.existencia ? r.existencia - e.existencia : (e.precio1 ?? 1 / 0) - (r.precio1 ?? 1 / 0),
        );
    }
    function J(e, r) {
      return [
        { rol: "panel", sel: e.panel },
        { rol: "inversor", sel: e.inversor },
        ...(r
          ? []
          : [
              { rol: "bateria", sel: e.bateria },
              ...(e.controladorIntegrado ? [] : [{ rol: "controlador", sel: e.controlador }]),
            ]),
      ];
    }
    function Y(e) {
      return e
        ? [
            ["Cable fotovoltaico", e.cableFotovoltaico],
            ["Conectores MC4", e.conectores],
            ["Cable de batería", e.cableBateria],
            ["Protección DC", e.breakerDC],
            ["Supresor de picos DC", e.supresor],
          ]
            .filter((e) => null != e[1])
            .map(([e, r]) => ({ etiqueta: e, sel: r }))
        : [];
    }
    function ee(e, r, o) {
      return [
        ...J(e, o)
          .map((e) => e.sel)
          .filter((e) => null != e),
        ...Y(r).map((e) => e.sel),
      ];
    }
    function er(e) {
      return e.reduce((e, r) => (null != r.producto.precio1 ? e + r.producto.precio1 * r.cantidad : e), 0);
    }
    function eo(e) {
      let a,
        t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m,
        p,
        x,
        b = (0, o.c)(35),
        { producto: h, kwp: v, totalArmadoUsd: g, conPrecios: f, formatoPrecio: y, botonCarrito: j } = e,
        N = h.precio1,
        _ = f && null != N && g > 0 ? N - g : null,
        w = h.atributos?.num_paneles ?? null,
        k = h.atributos?.potencia_w ?? null,
        C = /montaje\s+inclu/i.test(h.titulo);
      (b[0] === Symbol.for("react.memo_cache_sentinel")
        ? ((a = (0, r.jsxs)("div", {
            className: "border-b border-[var(--color-border-secondary)] px-5 py-4",
            children: [
              (0, r.jsx)("h3", {
                className: "text-sm font-semibold text-[var(--color-text-primary)]",
                children: "O llévate el kit completo",
              }),
              (0, r.jsx)("p", {
                className: "mt-0.5 text-xs text-[var(--color-text-tertiary)]",
                children: "Un solo número de parte en lugar de armarlo pieza por pieza.",
              }),
            ],
          })),
          (b[0] = a))
        : (a = b[0]),
        b[1] !== h.imagen || b[2] !== h.titulo
          ? ((t =
              h.imagen &&
              (0, r.jsx)("img", {
                src: h.imagen,
                alt: h.titulo,
                loading: "lazy",
                className: "size-16 shrink-0 rounded-lg object-contain",
              })),
            (b[1] = h.imagen),
            (b[2] = h.titulo),
            (b[3] = t))
          : (t = b[3]),
        b[4] !== h.titulo
          ? ((i = (0, r.jsx)("p", {
              className: "text-sm font-medium text-[var(--color-text-primary)]",
              children: h.titulo,
            })),
            (b[4] = h.titulo),
            (b[5] = i))
          : (i = b[5]));
      let S = h.marca,
        M = h.modelo;
      b[6] !== v ? ((s = v.toFixed(2)), (b[6] = v), (b[7] = s)) : (s = b[7]);
      let P = w && k ? ` \xb7 ${w} paneles de ${k} W` : "",
        $ = h.existencia > 0 ? "" : " · bajo pedido";
      (b[8] !== h.marca || b[9] !== h.modelo || b[10] !== s || b[11] !== P || b[12] !== $
        ? ((l = (0, r.jsxs)("p", {
            className: "mt-0.5 text-xs text-[var(--color-text-tertiary)]",
            children: [S, " ", M, " · ", s, " kWp", P, $],
          })),
          (b[8] = h.marca),
          (b[9] = h.modelo),
          (b[10] = s),
          (b[11] = P),
          (b[12] = $),
          (b[13] = l))
        : (l = b[13]),
        b[14] !== i || b[15] !== l
          ? ((n = (0, r.jsxs)("div", { className: "min-w-0 flex-1", children: [i, l] })),
            (b[14] = i),
            (b[15] = l),
            (b[16] = n))
          : (n = b[16]),
        b[17] !== f || b[18] !== _ || b[19] !== y || b[20] !== N
          ? ((c =
              f &&
              null != N &&
              (0, r.jsxs)("div", {
                className: "shrink-0 text-right",
                children: [
                  (0, r.jsx)("p", {
                    className: "text-sm font-semibold text-[var(--color-text-primary)]",
                    children: y(N),
                  }),
                  null != _ &&
                    0 !== _ &&
                    (0, r.jsxs)("p", {
                      className: "text-xs text-[var(--color-text-tertiary)]",
                      children: [_ < 0 ? "−" : "+", y(Math.abs(_)), " vs. armado"],
                    }),
                ],
              })),
            (b[17] = f),
            (b[18] = _),
            (b[19] = y),
            (b[20] = N),
            (b[21] = c))
          : (c = b[21]),
        b[22] !== n || b[23] !== c || b[24] !== t
          ? ((d = (0, r.jsxs)("div", {
              className: "flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center",
              children: [t, n, c],
            })),
            (b[22] = n),
            (b[23] = c),
            (b[24] = t),
            (b[25] = d))
          : (d = b[25]));
      let D = C ? " y montaje" : "";
      return (
        b[26] === Symbol.for("react.memo_cache_sentinel")
          ? ((u = (0, r.jsx)("strong", { children: "no trae el cableado ni las protecciones DC" })), (b[26] = u))
          : (u = b[26]),
        b[27] !== D
          ? ((m = (0, r.jsxs)("p", {
              className:
                "border-t border-[var(--color-border-secondary)] px-5 py-3 text-xs text-[var(--color-text-secondary)]",
              children: [
                "El kit incluye paneles e inversor",
                D,
                ", pero",
                " ",
                u,
                " que sí lleva la lista de abajo: agrégalos aparte para comparar de forma justa.",
              ],
            })),
            (b[27] = D),
            (b[28] = m))
          : (m = b[28]),
        b[29] !== j
          ? ((p =
              j &&
              (0, r.jsx)("div", {
                className: "border-t border-[var(--color-border-secondary)] px-5 py-3 print:hidden",
                children: j,
              })),
            (b[29] = j),
            (b[30] = p))
          : (p = b[30]),
        b[31] !== d || b[32] !== m || b[33] !== p
          ? ((x = (0, r.jsxs)("section", {
              className:
                "overflow-hidden rounded-2xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] shadow-[0_1px_2px_rgba(10,13,18,0.05)]",
              children: [a, d, m, p],
            })),
            (b[31] = d),
            (b[32] = m),
            (b[33] = p),
            (b[34] = x))
          : (x = b[34]),
        x
      );
    }
    var ea = e.i(253477);
    let et = {
        panel: "Paneles solares",
        inversor: "Inversor",
        bateria: "Baterías",
        controlador: "Controlador de carga",
      },
      ei = [
        { id: "lamina", label: "Lámina" },
        { id: "teja", label: "Teja" },
        { id: "concreto", label: "Concreto / losa" },
        { id: "piso", label: "Piso" },
        { id: "poste", label: "Poste" },
      ];
    async function es(e, r = !1) {
      try {
        let r = await fetch(`/api/energia/productos?${e}`);
        if (!r.ok) throw Error(`cat\xe1logo ${r.status}`);
        return (await r.json()).productos ?? [];
      } catch (e) {
        if (r) return [];
        throw e;
      }
    }
    async function el(e) {
      return (
        await Promise.all(
          (e ? ["interconexion", "micro"] : ["aislado", "hibrido"]).map((e) =>
            es(`tipo=inversor&tipo_inversor=${e}&limit=60`),
          ),
        )
      ).flat();
    }
    function en(e, r, o, a, t) {
      if ("panel" === e)
        return r.atributos?.potencia_w ? n(o ? 1e3 * o.kwp : a.arregloW, r.atributos.potencia_w) : null;
      if ("inversor" === e) return r.atributos?.tipo_inversor === "micro" ? t : 1;
      if ("bateria" === e) {
        if (!a || !r.atributos?.voltaje_v || !r.atributos.capacidad_ah) return null;
        let e = c(a.bancoAh, a.vSistema, r.atributos.voltaje_v, r.atributos.capacidad_ah);
        return e?.total ?? null;
      }
      return 1;
    }
    function ec({ estado: e, dispatch: o }) {
      let { status: s } = (0, L.useSession)(),
        l = "authenticated" === s,
        [n, d] = (0, a.useState)(null),
        [u, m] = (0, a.useState)(!1),
        p = "interconexion" === e.tipoSistema,
        x = eD(e),
        b = eF(e),
        h = eq(e),
        v = (0, a.useMemo)(() => {
          let e;
          return p ? { kwp: (e = x / (0.77 * b)), inversorMinW: Math.ceil((1e3 * e) / 1.2), pr: 0.77 } : null;
        }, [p, x, b]),
        g = (0, a.useMemo)(
          () =>
            p
              ? null
              : "hibrido" === e.tipoSistema
                ? (function (e) {
                    var r, o;
                    let {
                        consumoDiarioKwh: a,
                        hsp: i,
                        cargasCriticasW: s,
                        horasRespaldo: l,
                        tecnologia: n,
                        vSistema: c,
                      } = e,
                      d = t[n],
                      u = (s * l) / 1e3,
                      m = ((r = a), (o = u), 0.77 - (0.77 - 0.7) * (r > 0 ? Math.min(1, Math.max(0, o / r)) : 0)),
                      p = Math.ceil((1e3 * a) / (i * m)),
                      x = u / (0.9 * d);
                    return {
                      arregloW: p,
                      bancoKwh: x,
                      bancoAh: (1e3 * x) / c,
                      inversorMinW: Math.ceil(
                        Math.max(1.25 * (e.demandaSimultaneaW || (1e3 * a) / 8), 1.25 * s, p / 1.2),
                      ),
                      controladorMinA: Math.ceil((p / c) * 1.25),
                      vSistema: c,
                      modo: "hibrido",
                      pr: m,
                    };
                  })({
                    consumoDiarioKwh: x,
                    hsp: b,
                    cargasCriticasW: eR(e),
                    horasRespaldo: e.horasRespaldo,
                    tecnologia: e.tecnologia,
                    vSistema: h,
                    demandaSimultaneaW: "aparatos" === e.metodoConsumo ? i(e.aparatos) : void 0,
                  })
                : (function (e) {
                    let { consumoDiarioKwh: r, hsp: o, diasAutonomia: a, tecnologia: i, vSistema: s } = e,
                      l = t[i],
                      n = Math.ceil((1e3 * r) / (0.7 * o)),
                      c = (r * a) / (0.9 * l);
                    return {
                      arregloW: n,
                      bancoKwh: c,
                      bancoAh: (1e3 * c) / s,
                      inversorMinW: Math.ceil(1.25 * (e.demandaSimultaneaW || (1e3 * r) / 8)),
                      controladorMinA: Math.ceil((n / s) * 1.25),
                      vSistema: s,
                      modo: "aislado",
                      pr: 0.7,
                    };
                  })({
                    consumoDiarioKwh: x,
                    hsp: b,
                    diasAutonomia: e.diasAutonomia,
                    tecnologia: e.tecnologia,
                    vSistema: h,
                    demandaSimultaneaW: "aparatos" === e.metodoConsumo ? i(e.aparatos) : void 0,
                  }),
          [
            p,
            x,
            b,
            h,
            e.tipoSistema,
            e.diasAutonomia,
            e.horasRespaldo,
            e.cargasCriticasManualW,
            e.tecnologia,
            e.metodoConsumo,
            e.aparatos,
          ],
        );
      (0, a.useEffect)(() => {
        let r = !1;
        return (
          d(null),
          m(!1),
          (async () => {
            try {
              let [o, a, t, i, s, l, n, c] = await Promise.all([
                es("tipo=panel&limit=60"),
                el(p),
                p ? Promise.resolve([]) : es(`tipo=bateria&tecnologia_bat=${e.tecnologia}&limit=60`),
                p ? Promise.resolve([]) : es("tipo=controlador&limit=60"),
                es("tipo=cable&limit=60", !0),
                es("tipo=proteccion&limit=60", !0),
                es("tipo=montaje&tipo_montaje=kit&limit=60", !0),
                es(`tipo=kit&tipo_inversor=${e.tipoSistema}&limit=60`, !0),
              ]);
              if (r) return;
              d({
                panel: o,
                inversor: a,
                bateria: t,
                controlador: i,
                cables: s,
                protecciones: l,
                montajes: n,
                kits: c,
              });
            } catch {
              r || m(!0);
            }
          })(),
          () => {
            r = !0;
          }
        );
      }, [p, e.tecnologia, e.tipoSistema]);
      let [f, y] = (0, a.useState)({}),
        N = (0, a.useMemo)(() => {
          var r, o, a, t, i, s, l, d;
          let u, m, x, b, h, y, j, N, _, w, k;
          return n
            ? (function (e, r, o, a, t) {
                let i = { ...e },
                  s = (e, r) => o[e].find((e) => e.producto_id === r);
                if (null != r.panel) {
                  let e = s("panel", r.panel),
                    o = e ? en("panel", e, a, t, 0) : null;
                  e && null != o && (i.panel = { producto: e, cantidad: o });
                }
                if (null != r.inversor) {
                  let e = s("inversor", r.inversor);
                  e &&
                    ((i.inversor = { producto: e, cantidad: 1 }),
                    (i.controladorIntegrado = !!(e.atributos?.con_cargador && null != e.atributos.mppt_max_v)));
                }
                if (
                  (i.inversor?.producto.atributos?.tipo_inversor === "micro" &&
                    (i.inversor = { ...i.inversor, cantidad: i.panel?.cantidad ?? 1 }),
                  null != r.bateria && t)
                ) {
                  let e = s("bateria", r.bateria),
                    o =
                      e?.atributos?.voltaje_v && e.atributos.capacidad_ah
                        ? c(t.bancoAh, t.vSistema, e.atributos.voltaje_v, e.atributos.capacidad_ah)
                        : null;
                  e && o && (i.bateria = { producto: e, cantidad: o.total, serie: o.serie, paralelo: o.paralelo });
                }
                if (i.controladorIntegrado) i.controlador = null;
                else if (null != r.controlador) {
                  let e = s("controlador", r.controlador);
                  e && (i.controlador = { producto: e, cantidad: 1 });
                } else
                  !i.controlador &&
                    t &&
                    (i.controlador = G(o.controlador, t, i.panel?.producto.atributos?.voc_v ?? null));
                return i;
              })(
                p
                  ? ((r = n.panel),
                    (o = n.inversor),
                    (m = (u = X(r, 1e3 * v.kwp))
                      ? (function (e, r, o) {
                          let a = K(
                            e.filter(
                              (e) =>
                                e.atributos?.tipo_inversor === "interconexion" &&
                                (e.atributos.potencia_continua_w ?? 0) >= r &&
                                O(e),
                            ),
                            (e) => e.atributos.potencia_continua_w,
                          );
                          if (a[0]) return { producto: a[0], cantidad: 1 };
                          let t = K(
                            e.filter((e) => e.atributos?.tipo_inversor === "micro" && U(e, o)),
                            (e) => e.atributos.potencia_continua_w,
                          );
                          return t[0] ? { producto: t[0], cantidad: o } : null;
                        })(o, v.inversorMinW, u.cantidad)
                      : null),
                    { panel: u, inversor: m, bateria: null, controlador: null, controladorIntegrado: !1 })
                  : ((a = e.tipoSistema),
                    (t = e.tecnologia),
                    (i = n.panel),
                    (s = n.inversor),
                    (l = n.bateria),
                    (d = n.controlador),
                    (x = X(i, g.arregloW)),
                    (b = "hibrido" === a ? ["hibrido"] : ["aislado", "hibrido"]),
                    (N = (j = K(
                      (y = (h = s.filter(
                        (e) =>
                          e.atributos?.tipo_inversor != null &&
                          b.includes(e.atributos.tipo_inversor) &&
                          "modificada" !== e.atributos.onda &&
                          (e.atributos.potencia_continua_w ?? 0) >= g.inversorMinW &&
                          (null == e.atributos.voltaje_dc_v || e.atributos.voltaje_dc_v === g.vSistema),
                      )).filter((e) => e.atributos.voltaje_dc_v === g.vSistema && O(e))).length
                        ? y
                        : h.filter(O),
                      (e) => e.atributos.potencia_continua_w,
                    )[0])
                      ? { producto: j, cantidad: 1 }
                      : null),
                    (_ = (function (e, r, o) {
                      let a = e
                        .filter((e) => {
                          var r;
                          return (
                            ((r = e.atributos?.tecnologia_bat ?? null) === o ||
                              ("lifepo4" === o && "litio_otro" === r)) &&
                            (e.atributos.voltaje_v ?? 0) > 0 &&
                            (e.atributos.capacidad_ah ?? 0) > 0
                          );
                        })
                        .map((e) => {
                          let o = c(r.bancoAh, r.vSistema, e.atributos.voltaje_v, e.atributos.capacidad_ah);
                          return o ? { p: e, arreglo: o } : null;
                        })
                        .filter((e) => null != e);
                      if (!a.length) return null;
                      let t = a.filter((e) => O(e.p));
                      if (!t.length) return null;
                      let i = t.filter((e) => U(e.p, e.arreglo.total)),
                        s = i.length ? i : t;
                      s.sort((e, r) => {
                        if (e.arreglo.total !== r.arreglo.total) return e.arreglo.total - r.arreglo.total;
                        let o = null != e.p.precio1 ? e.p.precio1 * e.arreglo.total : 1 / 0,
                          a = null != r.p.precio1 ? r.p.precio1 * r.arreglo.total : 1 / 0;
                        return o !== a ? o - a : r.p.existencia - e.p.existencia;
                      });
                      let l = s[0];
                      return {
                        producto: l.p,
                        cantidad: l.arreglo.total,
                        serie: l.arreglo.serie,
                        paralelo: l.arreglo.paralelo,
                      };
                    })(l, g, t)),
                    (k = (w = !!(N?.producto.atributos?.con_cargador && null != N.producto.atributos.mppt_max_v))
                      ? null
                      : G(d, g, x?.producto.atributos?.voc_v ?? null)),
                    { panel: x, inversor: N, bateria: _, controlador: k, controladorIntegrado: w }),
                f,
                n,
                v,
                g,
              )
            : null;
        }, [n, p, v, g, e.tipoSistema, e.tecnologia, f]),
        _ = N?.panel?.cantidad ?? 1,
        w = (0, a.useMemo)(() => {
          var e, r, o, a, t, i;
          let s, l, c, d, u, m, x, b;
          if (!n || !N?.panel) return null;
          let h = N.panel.producto.atributos,
            v = h?.isc_a ?? 12,
            g = h?.voc_v ?? 45;
          return (
            (e = n.cables),
            (r = n.protecciones),
            (o = { corrienteProteccion: 1.25 * v, vocString: 2 * g * 1.15, conBateria: !p }),
            (l = Q((s = (r) => e.filter((e) => e.atributos?.subtipo_cable === r))("fotovoltaico"))[0] ?? null),
            (c = Q(s("conector"))[0] ?? null),
            (d = o.conBateria ? (Q(s("bateria"))[0] ?? null) : null),
            (a = r.filter(
              (e) =>
                (e.atributos?.tipo_proteccion === "breaker_dc" || e.atributos?.tipo_proteccion === "fusible") &&
                (null == e.atributos.voc_max_v || e.atributos.voc_max_v >= o.vocString),
            )),
            (t = (e) => e.atributos.corriente_a),
            (i = o.corrienteProteccion),
            (x = (m = (u = a.filter(O)).filter((e) => (t(e) ?? 0) >= i)).length
              ? (K(m, t)[0] ?? null)
              : ([...u].sort((e, r) => (t(r) ?? 0) - (t(e) ?? 0))[0] ?? null)),
            {
              cableFotovoltaico: l && { producto: l, cantidad: 1 },
              conectores: c && { producto: c, cantidad: 1 },
              cableBateria: d && { producto: d, cantidad: 1 },
              breakerDC: x && { producto: x, cantidad: 1 },
              supresor: (b = Q(r.filter((e) => e.atributos?.tipo_proteccion === "supresor"))[0] ?? null) && {
                producto: b,
                cantidad: 1,
              },
            }
          );
        }, [n, N, p]),
        k = (0, a.useMemo)(() => {
          let e = N?.panel?.producto.atributos?.potencia_w;
          return (
            (e && N?.panel ? (N.panel.cantidad * e) / 1e3 : p ? (v?.kwp ?? 0) : (g?.arregloW ?? 0) / 1e3) *
            b *
            365 *
            ((p ? v?.pr : g?.pr) ?? 0.77)
          );
        }, [N, p, v, g, b]),
        C = p ? (v?.kwp ?? 0) : (g?.arregloW ?? 0) / 1e3,
        S = (0, a.useMemo)(
          () =>
            n && e.tipoSistema
              ? (function (e, r, o, a) {
                  if (r <= 0) return null;
                  let t = 0.9 * r,
                    i = 1.25 * r,
                    s = e
                      .filter((e) => e.atributos?.tipo === "kit" && O(e))
                      .filter((e) => e.atributos?.tipo_inversor === o)
                      .filter(
                        (e) =>
                          "interconexion" === o || (!!a && !(a <= 0) && (e.atributos?.capacidad_kwh ?? 0) >= 0.8 * a),
                      )
                      .map((e) => {
                        let r;
                        return {
                          producto: e,
                          kwp:
                            (r = e.atributos) && "kit" === r.tipo && r.potencia_w && r.num_paneles
                              ? (r.potencia_w * r.num_paneles) / 1e3
                              : null,
                        };
                      })
                      .filter((e) => null != e.kwp)
                      .filter((e) => e.kwp >= t && e.kwp <= i);
                  return s.length
                    ? s.sort((e, o) => {
                        let a = Math.abs(e.kwp - r),
                          t = Math.abs(o.kwp - r);
                        if (Math.abs(a - t) > 1e-9) return a - t;
                        let i = e.producto.precio1 ?? 1 / 0,
                          s = o.producto.precio1 ?? 1 / 0;
                        return i !== s ? i - s : o.producto.existencia - e.producto.existencia;
                      })[0]
                    : null;
                })(n.kits, C, e.tipoSistema, g?.bancoKwh)
              : null,
          [n, C, e.tipoSistema, g?.bancoKwh],
        ),
        M = (0, a.useMemo)(() => (N ? er(ee(N, w, p)) : 0), [N, w, p]),
        P = "aislado" === e.tipoSistema,
        $ = eW(e),
        D = $ > 0 ? (k / $) * 100 : 0,
        W = e$(e);
      return (0, r.jsxs)("div", {
        className: "space-y-8 print:space-y-6",
        children: [
          (0, r.jsxs)("div", {
            className: "flex items-start justify-between gap-4 print:hidden",
            children: [
              (0, r.jsxs)("div", {
                children: [
                  (0, r.jsx)("h2", {
                    className: "text-xl font-semibold text-[var(--color-text-primary)]",
                    children: "Tu sistema solar",
                  }),
                  (0, r.jsxs)("p", {
                    className: "mt-1 text-sm text-[var(--color-text-tertiary)]",
                    children: [
                      x.toFixed(1),
                      " kWh/día",
                      W?.anioCompleto
                        ? P
                          ? ` \xb7 periodo m\xe1s alto de ${W.nPeriodos}`
                          : ` \xb7 promedio de ${W.nPeriodos} periodos`
                        : "",
                      " ",
                      "· ",
                      b,
                      " horas de sol pico",
                      p ? " · interconexión a CFE" : ` \xb7 banco a ${h} V`,
                    ],
                  }),
                ],
              }),
              (0, r.jsx)("button", {
                type: "button",
                onClick: () => o({ type: "reiniciar" }),
                className:
                  "shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-tertiary)]",
                children: "Empezar de nuevo",
              }),
            ],
          }),
          (0, r.jsxs)("div", {
            className: "grid gap-3 sm:grid-cols-2 lg:grid-cols-4",
            children: [
              p && v
                ? (0, r.jsxs)(r.Fragment, {
                    children: [
                      (0, r.jsx)(ew, { titulo: "Arreglo solar", valor: `${v.kwp.toFixed(2)} kWp` }),
                      (0, r.jsx)(ew, { titulo: "Inversor mínimo", valor: `${eC(v.inversorMinW)}` }),
                    ],
                  })
                : g
                  ? (0, r.jsxs)(r.Fragment, {
                      children: [
                        (0, r.jsx)(ew, { titulo: "Arreglo solar", valor: eC(g.arregloW) }),
                        (0, r.jsx)(ew, { titulo: "Inversor mínimo", valor: eC(g.inversorMinW) }),
                        (0, r.jsx)(ew, {
                          titulo: "hibrido" === g.modo ? "Banco de respaldo" : "Banco de autonomía",
                          valor: `${g.bancoKwh.toFixed(1)} kWh`,
                          nota:
                            "hibrido" === g.modo
                              ? `${e.horasRespaldo} h de ${eR(e).toLocaleString("es-MX")} W`
                              : `${Math.round(g.bancoAh)} Ah @ ${g.vSistema} V`,
                        }),
                        (0, r.jsx)(ew, { titulo: "Controlador", valor: `${g.controladorMinA} A`, nota: "MPPT" }),
                      ],
                    })
                  : null,
              k > 0 &&
                (0, r.jsxs)(r.Fragment, {
                  children: [
                    (0, r.jsx)(ew, {
                      titulo: "Generación estimada",
                      valor: `${Math.round(k / 12).toLocaleString("es-MX")} kWh/mes`,
                      nota: `promedio mensual \xb7 ≈ ${Math.round(k).toLocaleString("es-MX")} kWh al a\xf1o`,
                    }),
                    (0, r.jsx)(ew, {
                      titulo: P ? "Margen de generación" : "Cobertura",
                      valor: `${Math.round(D)}%`,
                      nota: P
                        ? "margen sobre tu consumo anual; el banco cubre las noches"
                        : D > 105
                          ? "de tu consumo anual · el excedente se va a la red como créditos de kWh"
                          : "de tu consumo anual",
                    }),
                  ],
                }),
            ],
          }),
          u
            ? (0, r.jsx)("p", {
                className: "text-sm text-[var(--color-text-error-primary,#d92d20)]",
                children: "No pudimos cargar el catálogo; intenta de nuevo en un momento.",
              })
            : n && N
              ? (0, r.jsxs)(r.Fragment, {
                  children: [
                    S &&
                      (0, r.jsx)(eo, {
                        producto: S.producto,
                        kwp: S.kwp,
                        totalArmadoUsd: M,
                        conPrecios: l,
                        formatoPrecio: eS,
                        botonCarrito:
                          l && null != S.producto.precio1
                            ? (0, r.jsx)(e_, { items: [{ producto: S.producto, cantidad: 1 }] })
                            : void 0,
                      }),
                    (0, r.jsx)(ed, {
                      combo: N,
                      catalogos: n,
                      cableado: w,
                      esOnGrid: p,
                      conPrecios: l,
                      calcularCantidad: (e, r) => en(e, r, v, g, _),
                      onCambiar: (e, r) => y((o) => ({ ...o, [e]: r })),
                    }),
                    (0, r.jsx)(ef, { montajes: n.montajes, conPrecios: l }),
                  ],
                })
              : (0, r.jsxs)("div", {
                  className: "flex items-center gap-2 py-8 text-sm text-[var(--color-text-quaternary)]",
                  children: [
                    (0, r.jsx)(j.Loading01, { size: 20, className: "animate-spin", "aria-hidden": !0 }),
                    " Buscando equipos en el catálogo…",
                  ],
                }),
          (0, r.jsx)("div", {
            className: "flex justify-center pt-2 print:hidden",
            children: (0, r.jsxs)("button", {
              type: "button",
              onClick: () => {
                (o({ type: "reiniciar" }), window.scrollTo({ top: 0, behavior: "smooth" }));
              },
              className:
                "flex items-center gap-2 rounded-lg bg-[var(--color-bg-brand-surface,#edf3ff)] px-6 py-3 text-sm font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] ring-1 ring-inset ring-[var(--color-border-brand)] transition-colors hover:bg-[#e0e9ff]",
              children: [(0, r.jsx)(B.RefreshCcw05, { size: 20, "aria-hidden": !0 }), " Calcular otro sistema"],
            }),
          }),
        ],
      });
    }
    function ed(e) {
      let a,
        t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m,
        p,
        x,
        b,
        h,
        v = (0, o.c)(45),
        { combo: g, catalogos: f, cableado: y, esOnGrid: j, conPrecios: N, calcularCantidad: _, onCambiar: w } = e;
      if (v[0] !== y || v[1] !== _ || v[2] !== f || v[3] !== g || v[4] !== N || v[5] !== j || v[6] !== w) {
        let e,
          o,
          u = J(g, j),
          m = Y(y),
          p = er((t = ee(g, y, j)));
        ((a = N && t.some(em)),
          (d = "space-y-6"),
          (l =
            "overflow-hidden rounded-2xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] shadow-[0_1px_2px_rgba(10,13,18,0.05)]"),
          v[15] !== _ || v[16] !== f || v[17] !== g || v[18] !== N || v[19] !== w
            ? ((e = (e) => {
                let { rol: o, sel: a } = e;
                return (0, r.jsx)(
                  ep,
                  {
                    rol: o,
                    sel: a,
                    alternativas: f[o],
                    conPrecios: N,
                    calcularCantidad: (e) => _(o, e),
                    nota:
                      "bateria" === o && a && "serie" in a && a.cantidad > 1
                        ? `${a.serie} en serie \xd7 ${a.paralelo} en paralelo`
                        : "controlador" === o && g.controladorIntegrado
                          ? "Integrado en el inversor híbrido"
                          : void 0,
                    onCambiar: w,
                  },
                  o,
                );
              }),
              (v[15] = _),
              (v[16] = f),
              (v[17] = g),
              (v[18] = N),
              (v[19] = w),
              (v[20] = e))
            : (e = v[20]));
        let x = u.map(e);
        (v[21] !== g || v[22] !== j
          ? ((o =
              !j &&
              g.controladorIntegrado &&
              (0, r.jsx)("p", {
                className: "px-4 py-3 text-xs text-[var(--color-text-quaternary)]",
                children: "El inversor híbrido sugerido ya integra el controlador de carga MPPT.",
              })),
            (v[21] = g),
            (v[22] = j),
            (v[23] = o))
          : (o = v[23]),
          v[24] !== x || v[25] !== o
            ? ((n = (0, r.jsxs)("div", {
                className: "divide-y divide-[var(--color-border-secondary)]",
                children: [x, o],
              })),
              (v[24] = x),
              (v[25] = o),
              (v[26] = n))
            : (n = v[26]),
          (c =
            m.length > 0 &&
            (0, r.jsxs)("div", {
              className: "border-t border-[var(--color-border-secondary)]",
              children: [
                (0, r.jsxs)("div", {
                  className: "flex items-center gap-1.5 bg-[var(--color-bg-secondary)] px-4 py-2",
                  children: [
                    (0, r.jsx)(H.ShieldTick, {
                      size: 16,
                      className: "text-[var(--color-fg-quaternary)]",
                      "aria-hidden": !0,
                    }),
                    (0, r.jsx)("p", {
                      className: "text-xs font-semibold uppercase tracking-wide text-[var(--color-text-tertiary)]",
                      children: "Cableado y protecciones · necesarias para un sistema seguro",
                    }),
                  ],
                }),
                (0, r.jsx)("div", {
                  className: "divide-y divide-[var(--color-border-secondary)]",
                  children: m.map((e) => {
                    let { etiqueta: o, sel: a } = e;
                    return (0, r.jsx)(ev, { etiqueta: o, sel: a, conPrecios: N }, o);
                  }),
                }),
              ],
            })),
          (i =
            "flex flex-wrap items-center justify-between gap-3 border-t border-[var(--color-border-secondary)] bg-[var(--color-bg-secondary)] px-4 py-3"),
          (s = a
            ? (0, r.jsxs)("p", {
                className: "text-sm text-[var(--color-text-secondary)]",
                children: [
                  "Subtotal (precio de lista, USD):",
                  " ",
                  (0, r.jsx)("strong", { className: "text-[var(--color-text-primary)]", children: eS(p) }),
                ],
              })
            : (0, r.jsx)("a", {
                href: "/login?callbackUrl=%2Fcalculadoras%2Fcalculador_solar",
                className: "text-sm font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline",
                children: "Inicia sesión para ver precios y agregar al carrito →",
              })),
          (v[0] = y),
          (v[1] = _),
          (v[2] = f),
          (v[3] = g),
          (v[4] = N),
          (v[5] = j),
          (v[6] = w),
          (v[7] = a),
          (v[8] = t),
          (v[9] = i),
          (v[10] = s),
          (v[11] = l),
          (v[12] = n),
          (v[13] = c),
          (v[14] = d));
      } else ((a = v[7]), (t = v[8]), (i = v[9]), (s = v[10]), (l = v[11]), (n = v[12]), (c = v[13]), (d = v[14]));
      return (
        v[27] === Symbol.for("react.memo_cache_sentinel")
          ? ((u = (0, r.jsxs)("button", {
              type: "button",
              onClick: eu,
              className:
                "flex items-center gap-1.5 rounded-lg bg-[var(--color-bg-primary)] px-3.5 py-2 text-sm font-semibold text-[var(--color-text-secondary)] ring-1 ring-inset ring-[var(--color-border-primary)] shadow-xs hover:bg-[var(--color-bg-secondary)]",
              children: [(0, r.jsx)(z.Printer, { size: 16, "aria-hidden": !0 }), " Imprimir"],
            })),
            (v[27] = u))
          : (u = v[27]),
        v[28] !== a || v[29] !== t
          ? ((m = a && (0, r.jsx)(e_, { items: t })), (v[28] = a), (v[29] = t), (v[30] = m))
          : (m = v[30]),
        v[31] !== m
          ? ((p = (0, r.jsxs)("div", { className: "flex gap-2 print:hidden", children: [u, m] })),
            (v[31] = m),
            (v[32] = p))
          : (p = v[32]),
        v[33] !== i || v[34] !== s || v[35] !== p
          ? ((x = (0, r.jsxs)("div", { className: i, children: [s, p] })),
            (v[33] = i),
            (v[34] = s),
            (v[35] = p),
            (v[36] = x))
          : (x = v[36]),
        v[37] !== x || v[38] !== l || v[39] !== n || v[40] !== c
          ? ((b = (0, r.jsxs)("div", { className: l, children: [n, c, x] })),
            (v[37] = x),
            (v[38] = l),
            (v[39] = n),
            (v[40] = c),
            (v[41] = b))
          : (b = v[41]),
        v[42] !== b || v[43] !== d
          ? ((h = (0, r.jsx)("div", { className: d, children: b })), (v[42] = b), (v[43] = d), (v[44] = h))
          : (h = v[44]),
        h
      );
    }
    function eu() {
      return window.print();
    }
    function em(e) {
      return null != e.producto.precio1;
    }
    function ep(e) {
      let t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m,
        p,
        x,
        b,
        h = (0, o.c)(42),
        { rol: v, sel: g, alternativas: f, conPrecios: y, nota: j, calcularCantidad: N, onCambiar: _ } = e,
        [w, k] = (0, a.useState)(!1),
        C = g && y && null != g.producto.precio1 ? g.producto.precio1 * g.cantidad : null;
      if (h[0] !== f || h[1] !== N || h[2] !== y || h[3] !== g?.producto) {
        let e, r;
        h[5] !== N || h[6] !== y
          ? ((e = (e) => {
              let r = N(e);
              if (null == r) return null;
              let o = y && null != e.precio1 ? e.precio1 * r : null;
              return { p: e, qty: r, total: o };
            }),
            (h[5] = N),
            (h[6] = y),
            (h[7] = e))
          : (e = h[7]);
        let o = f.map(e).filter(eb);
        (h[8] !== g?.producto
          ? ((r = (e, r) => {
              let o = +(e.p.producto_id !== g?.producto.producto_id),
                a = +(r.p.producto_id !== g?.producto.producto_id);
              return o !== a ? o - a : (e.total ?? 1 / 0) - (r.total ?? 1 / 0);
            }),
            (h[8] = g?.producto),
            (h[9] = r))
          : (r = h[9]),
          (t = o.sort(r)),
          (h[0] = f),
          (h[1] = N),
          (h[2] = y),
          (h[3] = g?.producto),
          (h[4] = t));
      } else t = h[4];
      let S = t,
        M = et[v];
      (h[10] !== M
        ? ((i = (0, r.jsx)("p", {
            className: "text-xs font-medium uppercase tracking-wide text-[var(--color-text-quaternary)]",
            children: M,
          })),
          (h[10] = M),
          (h[11] = i))
        : (i = h[11]),
        h[12] !== j || h[13] !== g
          ? ((s = g
              ? (0, r.jsxs)("div", {
                  className: "mt-1 flex items-center gap-3",
                  children: [
                    g.producto.imagen &&
                      (0, r.jsx)("img", {
                        src: g.producto.imagen,
                        alt: "",
                        width: 48,
                        height: 48,
                        loading: "lazy",
                        className:
                          "h-12 w-12 shrink-0 rounded border border-[var(--color-border-secondary)] bg-[var(--color-brand-25,#f5f8ff)] object-contain",
                      }),
                    (0, r.jsxs)("div", {
                      className: "min-w-0",
                      children: [
                        (0, r.jsxs)("a", {
                          href: g.producto.link,
                          target: "_blank",
                          rel: "noreferrer",
                          className: "text-sm font-semibold text-[var(--color-text-primary)] hover:underline",
                          children: [g.producto.marca, " ", g.producto.modelo],
                        }),
                        (0, r.jsx)("p", {
                          className: "truncate text-xs text-[var(--color-text-tertiary)]",
                          children: ek(g.producto) || g.producto.titulo,
                        }),
                        j && (0, r.jsx)("p", { className: "text-xs text-[var(--color-text-quaternary)]", children: j }),
                        (0, r.jsx)(eh, { sel: g }),
                      ],
                    }),
                  ],
                })
              : (0, r.jsx)("p", {
                  className: "mt-1 text-sm text-[var(--color-text-tertiary)]",
                  children: "No encontramos un modelo que cumpla — elige uno manualmente.",
                })),
            (h[12] = j),
            (h[13] = g),
            (h[14] = s))
          : (s = h[14]),
        h[15] !== i || h[16] !== s
          ? ((l = (0, r.jsxs)("div", { className: "min-w-0 flex-1", children: [i, s] })),
            (h[15] = i),
            (h[16] = s),
            (h[17] = l))
          : (l = h[17]),
        h[18] !== y || h[19] !== g
          ? ((n =
              g &&
              (0, r.jsxs)(r.Fragment, {
                children: [
                  (0, r.jsxs)("p", {
                    className: "text-sm font-semibold text-[var(--color-text-primary)]",
                    children: ["× ", g.cantidad],
                  }),
                  y &&
                    null != g.producto.precio1 &&
                    (0, r.jsx)("p", {
                      className: "text-xs text-[var(--color-text-secondary)]",
                      children: eS(g.producto.precio1 * g.cantidad),
                    }),
                ],
              })),
            (h[18] = y),
            (h[19] = g),
            (h[20] = n))
          : (n = h[20]),
        h[21] === Symbol.for("react.memo_cache_sentinel") ? ((c = () => k(ex)), (h[21] = c)) : (c = h[21]),
        h[22] === Symbol.for("react.memo_cache_sentinel")
          ? ((d = (0, r.jsx)(I.RefreshCw01, { size: 12, "aria-hidden": !0 })), (h[22] = d))
          : (d = h[22]));
      let P = w ? "Ocultar" : `Ver ${S.length} ${1 === S.length ? "opción" : "opciones"}`;
      return (
        h[23] !== P
          ? ((u = (0, r.jsxs)("button", {
              type: "button",
              onClick: c,
              className:
                "mt-1 flex items-center gap-1 text-xs font-semibold text-[var(--color-text-brand-secondary,#2c46a3)] hover:underline print:hidden",
              children: [d, P],
            })),
            (h[23] = P),
            (h[24] = u))
          : (u = h[24]),
        h[25] !== u || h[26] !== n
          ? ((m = (0, r.jsxs)("div", { className: "shrink-0 text-right", children: [n, u] })),
            (h[25] = u),
            (h[26] = n),
            (h[27] = m))
          : (m = h[27]),
        h[28] !== m || h[29] !== l
          ? ((p = (0, r.jsxs)("div", { className: "flex items-center gap-3", children: [l, m] })),
            (h[28] = m),
            (h[29] = l),
            (h[30] = p))
          : (p = h[30]),
        h[31] !== w || h[32] !== y || h[33] !== _ || h[34] !== S || h[35] !== v || h[36] !== g?.producto || h[37] !== C
          ? ((x =
              w &&
              (0, r.jsxs)("div", {
                className: "mt-3 rounded-lg border border-[var(--color-border-secondary)] print:hidden",
                children: [
                  (0, r.jsx)("p", {
                    className:
                      "border-b border-[var(--color-border-secondary)] bg-[var(--color-bg-secondary)] px-3 py-2 text-xs text-[var(--color-text-tertiary)]",
                    children:
                      "Precio y cantidad para TU sistema. El recomendado equilibra capacidad, existencia y precio — el más barato no siempre es el mejor.",
                  }),
                  (0, r.jsxs)("ul", {
                    className: "max-h-72 divide-y divide-[var(--color-border-secondary)] overflow-y-auto",
                    children: [
                      S.map((e) => {
                        let { p: o, qty: a, total: t } = e,
                          i = o.producto_id === g?.producto.producto_id,
                          s = null != t && null != C ? t - C : null;
                        return (0, r.jsx)(
                          "li",
                          {
                            children: (0, r.jsxs)("button", {
                              type: "button",
                              onClick: () => {
                                (_(v, o.producto_id), k(!1));
                              },
                              className: `flex w-full items-start gap-3 px-3 py-2.5 text-left hover:bg-[var(--color-bg-secondary)] ${i ? "bg-[var(--color-bg-brand-surface,#edf3ff)]" : ""}`,
                              children: [
                                (0, r.jsxs)("div", {
                                  className: "min-w-0 flex-1",
                                  children: [
                                    (0, r.jsxs)("div", {
                                      className: "flex items-center gap-2",
                                      children: [
                                        (0, r.jsxs)("span", {
                                          className: "truncate text-xs font-semibold text-[var(--color-text-primary)]",
                                          children: [o.marca, " ", o.modelo],
                                        }),
                                        i &&
                                          (0, r.jsx)("span", {
                                            className:
                                              "shrink-0 rounded-full bg-[var(--color-bg-brand-solid)] px-1.5 py-0.5 text-[10px] font-semibold text-white",
                                            children: "Recomendado",
                                          }),
                                      ],
                                    }),
                                    (0, r.jsx)("span", {
                                      className: "block truncate text-xs text-[var(--color-text-tertiary)]",
                                      children: ek(o),
                                    }),
                                    o.bajoPedido &&
                                      (0, r.jsx)("span", {
                                        className: "text-[11px] text-[var(--color-text-quaternary)]",
                                        children: "Bajo pedido",
                                      }),
                                  ],
                                }),
                                (0, r.jsxs)("div", {
                                  className: "shrink-0 text-right",
                                  children: [
                                    (0, r.jsxs)("span", {
                                      className: "block text-xs font-semibold text-[var(--color-text-primary)]",
                                      children: ["× ", a],
                                    }),
                                    y &&
                                      null != t &&
                                      (0, r.jsx)("span", {
                                        className: "block text-xs text-[var(--color-text-secondary)]",
                                        children: eS(t),
                                      }),
                                    y &&
                                      null != s &&
                                      0 !== s &&
                                      (0, r.jsxs)("span", {
                                        className: "block text-[11px] text-[var(--color-text-quaternary)]",
                                        children: [s > 0 ? "+" : "−", eS(Math.abs(s))],
                                      }),
                                  ],
                                }),
                              ],
                            }),
                          },
                          o.producto_id,
                        );
                      }),
                      !S.length &&
                        (0, r.jsx)("li", {
                          className: "px-3 py-2 text-xs text-[var(--color-text-quaternary)]",
                          children: "Sin alternativas en catálogo",
                        }),
                    ],
                  }),
                ],
              })),
            (h[31] = w),
            (h[32] = y),
            (h[33] = _),
            (h[34] = S),
            (h[35] = v),
            (h[36] = g?.producto),
            (h[37] = C),
            (h[38] = x))
          : (x = h[38]),
        h[39] !== p || h[40] !== x
          ? ((b = (0, r.jsxs)("div", { className: "p-4", children: [p, x] })), (h[39] = p), (h[40] = x), (h[41] = b))
          : (b = h[41]),
        b
      );
    }
    function ex(e) {
      return !e;
    }
    function eb(e) {
      return null != e;
    }
    function eh(e) {
      let a = (0, o.c)(3),
        { sel: t } = e;
      if (t.producto.bajoPedido) {
        let e;
        return (
          a[0] === Symbol.for("react.memo_cache_sentinel")
            ? ((e = (0, r.jsx)("p", {
                className: "text-xs font-medium text-[var(--color-text-secondary)]",
                children: "Bajo pedido (sin stock inmediato)",
              })),
              (a[0] = e))
            : (e = a[0]),
          e
        );
      }
      if (t.cantidad > t.producto.existencia) {
        let e;
        return (
          a[1] !== t.producto.existencia
            ? ((e = (0, r.jsxs)("p", {
                className: "text-xs font-medium text-[#b54708]",
                children: ["Solo ", t.producto.existencia, " en stock — ajusta la cantidad o cambia de modelo"],
              })),
              (a[1] = t.producto.existencia),
              (a[2] = e))
            : (e = a[2]),
          e
        );
      }
      return null;
    }
    function ev(e) {
      let a,
        t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m = (0, o.c)(27),
        { etiqueta: p, sel: x, conPrecios: b } = e;
      return (
        m[0] !== x.producto.imagen
          ? ((a =
              x.producto.imagen &&
              (0, r.jsx)("img", {
                src: x.producto.imagen,
                alt: "",
                width: 36,
                height: 36,
                loading: "lazy",
                className:
                  "h-9 w-9 shrink-0 rounded border border-[var(--color-border-secondary)] bg-[var(--color-brand-25,#f5f8ff)] object-contain",
              })),
            (m[0] = x.producto.imagen),
            (m[1] = a))
          : (a = m[1]),
        m[2] !== p
          ? ((t = (0, r.jsx)("p", {
              className: "text-xs font-medium uppercase tracking-wide text-[var(--color-text-quaternary)]",
              children: p,
            })),
            (m[2] = p),
            (m[3] = t))
          : (t = m[3]),
        m[4] !== x.producto.link || m[5] !== x.producto.marca || m[6] !== x.producto.modelo
          ? ((i = (0, r.jsxs)("a", {
              href: x.producto.link,
              target: "_blank",
              rel: "noreferrer",
              className: "text-sm font-semibold text-[var(--color-text-primary)] hover:underline",
              children: [x.producto.marca, " ", x.producto.modelo],
            })),
            (m[4] = x.producto.link),
            (m[5] = x.producto.marca),
            (m[6] = x.producto.modelo),
            (m[7] = i))
          : (i = m[7]),
        m[8] !== x.producto.bajoPedido
          ? ((s =
              x.producto.bajoPedido &&
              (0, r.jsx)("span", {
                className: "ml-2 text-xs text-[var(--color-text-quaternary)]",
                children: "Bajo pedido",
              })),
            (m[8] = x.producto.bajoPedido),
            (m[9] = s))
          : (s = m[9]),
        m[10] !== t || m[11] !== i || m[12] !== s
          ? ((l = (0, r.jsxs)("div", { className: "min-w-0 flex-1", children: [t, i, s] })),
            (m[10] = t),
            (m[11] = i),
            (m[12] = s),
            (m[13] = l))
          : (l = m[13]),
        m[14] !== x.cantidad
          ? ((n = (0, r.jsxs)("p", {
              className: "text-sm font-semibold text-[var(--color-text-primary)]",
              children: ["× ", x.cantidad],
            })),
            (m[14] = x.cantidad),
            (m[15] = n))
          : (n = m[15]),
        m[16] !== b || m[17] !== x.cantidad || m[18] !== x.producto.precio1
          ? ((c =
              b &&
              null != x.producto.precio1 &&
              (0, r.jsx)("p", {
                className: "text-xs text-[var(--color-text-secondary)]",
                children: eS(x.producto.precio1 * x.cantidad),
              })),
            (m[16] = b),
            (m[17] = x.cantidad),
            (m[18] = x.producto.precio1),
            (m[19] = c))
          : (c = m[19]),
        m[20] !== n || m[21] !== c
          ? ((d = (0, r.jsxs)("div", { className: "shrink-0 text-right", children: [n, c] })),
            (m[20] = n),
            (m[21] = c),
            (m[22] = d))
          : (d = m[22]),
        m[23] !== a || m[24] !== l || m[25] !== d
          ? ((u = (0, r.jsxs)("div", { className: "flex items-center gap-3 px-4 py-3", children: [a, l, d] })),
            (m[23] = a),
            (m[24] = l),
            (m[25] = d),
            (m[26] = u))
          : (u = m[26]),
        u
      );
    }
    let eg = {
      riel: "Riel",
      sujetador: "Sujetador",
      soporte: "Soporte",
      herraje: "Herraje",
      tierra: "Tierra física",
      kit: "Kit",
      estructura: "Estructura",
    };
    function ef(e) {
      let t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m,
        p,
        x = (0, o.c)(21),
        { montajes: b, conPrecios: h } = e,
        [v, g] = (0, a.useState)(null);
      x[0] !== b ? ((t = b.filter(ej)), (x[0] = b), (x[1] = t)) : (t = x[1]);
      let f = t;
      x[2] !== f || x[3] !== v
        ? ((i = [
            ...(v
              ? f.filter((e) => {
                  let r = e.atributos?.tipo_techo;
                  return r === v || "universal" === r || null == r;
                })
              : f),
          ]
            .sort(ey)
            .slice(0, 8)),
          (x[2] = f),
          (x[3] = v),
          (x[4] = i))
        : (i = x[4]);
      let y = i;
      x[5] === Symbol.for("react.memo_cache_sentinel")
        ? ((s = (0, r.jsxs)("div", {
            className: "flex items-center gap-1.5",
            children: [
              (0, r.jsx)(Z.Tool01, { size: 20, className: "text-[var(--color-fg-quaternary)]", "aria-hidden": !0 }),
              (0, r.jsx)("h3", {
                className: "text-lg font-semibold text-[var(--color-text-primary)]",
                children: "Montaje",
              }),
            ],
          })),
          (l = (0, r.jsx)("p", {
            className: "mt-0.5 text-sm text-[var(--color-text-tertiary)]",
            children:
              "Kits de montaje completos. Elige tu tipo de techo para ver el kit adecuado; las cantidades dependen del tamaño de tu arreglo.",
          })),
          (x[5] = s),
          (x[6] = l))
        : ((s = x[5]), (l = x[6]));
      let j = null === v;
      return (
        x[7] === Symbol.for("react.memo_cache_sentinel") ? ((n = () => g(null)), (x[7] = n)) : (n = x[7]),
        x[8] !== j
          ? ((c = (0, r.jsx)(eN, { activo: j, onClick: n, children: "Todos" })), (x[8] = j), (x[9] = c))
          : (c = x[9]),
        x[10] !== v
          ? ((d = ei.map((e) =>
              (0, r.jsx)(eN, { activo: v === e.id, onClick: () => g(e.id), children: e.label }, e.id),
            )),
            (x[10] = v),
            (x[11] = d))
          : (d = x[11]),
        x[12] !== c || x[13] !== d
          ? ((u = (0, r.jsxs)("div", { className: "mt-3 flex flex-wrap gap-2 print:hidden", children: [c, d] })),
            (x[12] = c),
            (x[13] = d),
            (x[14] = u))
          : (u = x[14]),
        x[15] !== h || x[16] !== y
          ? ((m = y.length
              ? (0, r.jsx)("ul", {
                  className: "mt-3 grid gap-2 sm:grid-cols-2",
                  children: y.map((e) =>
                    (0, r.jsxs)(
                      "li",
                      {
                        className:
                          "flex items-center gap-3 rounded-xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] p-3",
                        children: [
                          e.imagen &&
                            (0, r.jsx)("img", {
                              src: e.imagen,
                              alt: "",
                              width: 40,
                              height: 40,
                              loading: "lazy",
                              className:
                                "h-10 w-10 shrink-0 rounded border border-[var(--color-border-secondary)] bg-[var(--color-brand-25,#f5f8ff)] object-contain",
                            }),
                          (0, r.jsxs)("div", {
                            className: "min-w-0 flex-1",
                            children: [
                              e.atributos?.tipo_montaje &&
                                (0, r.jsx)("p", {
                                  className:
                                    "text-xs font-medium uppercase tracking-wide text-[var(--color-text-quaternary)]",
                                  children: eg[e.atributos.tipo_montaje] ?? "Montaje",
                                }),
                              (0, r.jsxs)("a", {
                                href: e.link,
                                target: "_blank",
                                rel: "noreferrer",
                                className:
                                  "block truncate text-sm font-semibold text-[var(--color-text-primary)] hover:underline",
                                children: [e.marca, " ", e.modelo],
                              }),
                            ],
                          }),
                          h &&
                            null != e.precio1 &&
                            (0, r.jsx)("span", {
                              className: "shrink-0 text-xs font-semibold text-[var(--color-text-secondary)]",
                              children: eS(e.precio1),
                            }),
                        ],
                      },
                      e.producto_id,
                    ),
                  ),
                })
              : (0, r.jsx)("p", {
                  className:
                    "mt-3 rounded-xl border border-dashed border-[var(--color-border-primary)] px-4 py-6 text-center text-sm text-[var(--color-text-quaternary)]",
                  children: "Aún no tenemos un kit de montaje para este techo. Pronto agregaremos más.",
                })),
            (x[15] = h),
            (x[16] = y),
            (x[17] = m))
          : (m = x[17]),
        x[18] !== m || x[19] !== u
          ? ((p = (0, r.jsxs)("div", { children: [s, l, u, m] })), (x[18] = m), (x[19] = u), (x[20] = p))
          : (p = x[20]),
        p
      );
    }
    function ey(e, r) {
      return r.existencia - e.existencia || (e.precio1 ?? 1 / 0) - (r.precio1 ?? 1 / 0);
    }
    function ej(e) {
      return e.atributos?.tipo_montaje === "kit";
    }
    function eN(e) {
      let a,
        t = (0, o.c)(4),
        { activo: i, onClick: s, children: l } = e,
        n = `rounded-full px-3 py-1.5 text-xs font-medium ring-1 ring-inset ${i ? "bg-[var(--color-bg-brand-solid)] text-white ring-transparent" : "bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)] ring-[var(--color-border-primary)] hover:bg-[var(--color-bg-secondary)]"}`;
      return (
        t[0] !== l || t[1] !== s || t[2] !== n
          ? ((a = (0, r.jsx)("button", { type: "button", onClick: s, className: n, children: l })),
            (t[0] = l),
            (t[1] = s),
            (t[2] = n),
            (t[3] = a))
          : (a = t[3]),
        a
      );
    }
    function e_({ items: e }) {
      let [o, t] = (0, a.useState)("idle"),
        i = (0, a.useRef)(new Set());
      async function s() {
        t("agregando");
        try {
          for (let r of e)
            if (!i.current.has(r.producto.producto_id)) {
              if (
                !(
                  await fetch("/api/carrito/producto", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                    body: JSON.stringify({
                      producto_id: String(r.producto.producto_id),
                      cantidad: r.cantidad,
                      asterisco: "",
                    }),
                  })
                ).ok
              )
                throw Error("carrito");
              i.current.add(r.producto.producto_id);
            }
          (i.current.clear(), t("listo"), (0, ea.hapticSuccess)(), setTimeout(() => t("idle"), 2500));
        } catch {
          (t("error"), (0, ea.hapticError)(), setTimeout(() => t("idle"), 3e3));
        }
      }
      return (0, r.jsxs)("button", {
        type: "button",
        disabled: "agregando" === o,
        onClick: s,
        className:
          "flex items-center gap-1.5 rounded-lg bg-[var(--color-bg-brand-solid)] px-3.5 py-2 text-sm font-semibold text-white shadow-xs hover:bg-[var(--color-bg-brand-solid_hover)] disabled:opacity-60",
        style: { boxShadow: "inset 0 0 0 1px rgba(10,13,18,.18), inset 0 -2px 0 0 rgba(10,13,18,.05)" },
        children: [
          (0, r.jsx)(V, { size: 16, "aria-hidden": !0 }),
          "agregando" === o
            ? "Agregando…"
            : "listo" === o
              ? "Agregado ✓"
              : "error" === o
                ? "Error, reintenta"
                : "Agregar todo al carrito",
        ],
      });
    }
    function ew(e) {
      let a,
        t,
        i,
        s,
        l = (0, o.c)(10),
        { titulo: n, valor: c, nota: d } = e;
      return (
        l[0] !== n
          ? ((a = (0, r.jsx)("p", {
              className: "text-xs font-medium uppercase tracking-wide text-[var(--color-text-quaternary)]",
              children: n,
            })),
            (l[0] = n),
            (l[1] = a))
          : (a = l[1]),
        l[2] !== c
          ? ((t = (0, r.jsx)("p", {
              className: "mt-1 text-lg font-semibold text-[var(--color-text-primary)]",
              children: c,
            })),
            (l[2] = c),
            (l[3] = t))
          : (t = l[3]),
        l[4] !== d
          ? ((i = d && (0, r.jsx)("p", { className: "text-xs text-[var(--color-text-tertiary)]", children: d })),
            (l[4] = d),
            (l[5] = i))
          : (i = l[5]),
        l[6] !== a || l[7] !== t || l[8] !== i
          ? ((s = (0, r.jsxs)("div", {
              className:
                "rounded-2xl border border-[var(--color-border-secondary)] bg-[var(--color-bg-primary)] p-4 shadow-[0_1px_2px_rgba(10,13,18,0.05)]",
              children: [a, t, i],
            })),
            (l[6] = a),
            (l[7] = t),
            (l[8] = i),
            (l[9] = s))
          : (s = l[9]),
        s
      );
    }
    function ek(e) {
      let r = e.atributos;
      if (!r) return "";
      let o = [];
      return (
        "panel" === r.tipo
          ? (r.potencia_w && o.push(`${r.potencia_w} W`),
            r.tipo_celda && o.push("mono" === r.tipo_celda ? "monocristalino" : "policristalino"))
          : "inversor" === r.tipo
            ? (r.potencia_continua_w && o.push(eC(r.potencia_continua_w)),
              r.voltaje_dc_v && o.push(`${r.voltaje_dc_v} V`),
              r.onda && o.push("pura" === r.onda ? "onda pura" : "onda modificada"))
            : "bateria" === r.tipo
              ? (r.voltaje_v && o.push(`${r.voltaje_v} V`),
                r.capacidad_ah && o.push(`${r.capacidad_ah} Ah`),
                r.ciclos && o.push(`${r.ciclos.toLocaleString("es-MX")} ciclos`))
              : "controlador" === r.tipo &&
                (r.corriente_a && o.push(`${r.corriente_a} A`),
                r.tipo_controlador && o.push(r.tipo_controlador.toUpperCase()),
                r.voc_max_v && o.push(`Voc ${r.voc_max_v}`)),
        o.filter(Boolean).join(" · ")
      );
    }
    function eC(e) {
      return e >= 1e4 ? `${(e / 1e3).toFixed(1)} kW` : `${Math.round(e).toLocaleString("es-MX")} W`;
    }
    function eS(e) {
      return e.toLocaleString("es-MX", { style: "currency", currency: "USD", maximumFractionDigits: 2 });
    }
    let eM = {
      paso: 0,
      tipoSistema: null,
      metodoConsumo: "recibo",
      kwhPeriodo: null,
      diasPeriodo: 60,
      tarifa: null,
      historico: [],
      aparatos: [],
      estado: null,
      diasAutonomia: 1,
      horasRespaldo: 4,
      cargasCriticasManualW: null,
      vSistemaManual: null,
      tecnologia: "lifepo4",
    };
    function eP(e, r) {
      switch (r.type) {
        case "paso":
          return { ...e, paso: r.paso };
        case "tipoSistema":
          return { ...e, tipoSistema: r.valor };
        case "metodoConsumo":
          return {
            ...e,
            metodoConsumo: r.valor,
            cargasCriticasManualW: null,
            historico: "recibo" === r.valor ? e.historico : [],
          };
        case "consumoRecibo":
          return {
            ...e,
            kwhPeriodo: r.kwh,
            diasPeriodo: r.dias,
            tarifa: r.tarifa ?? e.tarifa,
            historico: r.historico ?? e.historico,
          };
        case "historico":
          return { ...e, historico: r.valor };
        case "aparatos":
          return { ...e, aparatos: r.valor };
        case "estado":
          return { ...e, estado: r.valor };
        case "diasAutonomia":
          return { ...e, diasAutonomia: r.valor };
        case "horasRespaldo":
          return { ...e, horasRespaldo: r.valor };
        case "cargasCriticas":
          return { ...e, cargasCriticasManualW: r.valor };
        case "vSistema":
          return { ...e, vSistemaManual: r.valor };
        case "tecnologia":
          return { ...e, tecnologia: r.valor };
        case "reiniciar":
          return eM;
      }
    }
    function e$(e) {
      return "recibo" !== e.metodoConsumo || 0 === e.historico.length
        ? null
        : (function (e, r) {
            if (0 === e.length) return null;
            let o = r > 0 ? r : 60,
              a = e.length * o > 400 ? 365 / e.length : o,
              t = e.reduce((e, r) => e + r.kwh, 0),
              i = e.length * a,
              s = e.reduce((e, r) => (r.kwh > e.kwh ? r : e), e[0]);
            return {
              nPeriodos: e.length,
              promedioDiario: t / i,
              picoDiario: s.kwh / a,
              etiquetaPico: s.etiqueta,
              diasCubiertos: i,
              anioCompleto: i >= 330,
            };
          })(e.historico, e.diasPeriodo);
    }
    function eD(e) {
      var r, o;
      if ("aparatos" === e.metodoConsumo)
        return e.aparatos.reduce((e, r) => e + (r.watts * r.horasDia * r.cantidad) / 1e3, 0);
      let a = e$(e);
      return a?.anioCompleto
        ? "aislado" === e.tipoSistema
          ? a.picoDiario
          : a.promedioDiario
        : null == e.kwhPeriodo
          ? 0
          : ((r = e.kwhPeriodo), (o = e.diasPeriodo), r <= 0 || o <= 0 ? 0 : r / o);
    }
    function eW(e) {
      let r = e$(e);
      return (r?.anioCompleto ? r.promedioDiario : eD(e)) * 365;
    }
    function eq(e) {
      return e.vSistemaManual ?? l(eD(e));
    }
    let eA = 500;
    function eR(e) {
      if (null != e.cargasCriticasManualW) return e.cargasCriticasManualW;
      if ("aparatos" === e.metodoConsumo) {
        let r = s(e.aparatos);
        if (r > 0) return r;
      }
      return eA;
    }
    function eF(e) {
      return (e.estado && d[e.estado]) || 5;
    }
    function eT() {
      let e,
        t,
        i,
        s,
        l,
        n,
        c,
        d,
        u,
        m,
        p,
        x = (0, o.c)(34),
        [b, v] = (0, a.useReducer)(eP, eM),
        g = "aislado" === b.tipoSistema || "hibrido" === b.tipoSistema;
      x[0] !== g || x[1] !== b.tipoSistema
        ? ((e = g
            ? [
                "Tipo de sistema",
                "Tu consumo",
                "Ubicación",
                "hibrido" === b.tipoSistema ? "Respaldo" : "Baterías",
                "Resultados",
              ]
            : ["Tipo de sistema", "Tu consumo", "Ubicación", "Resultados"]),
          (x[0] = g),
          (x[1] = b.tipoSistema),
          (x[2] = e))
        : (e = x[2]);
      let f = e;
      x[3] !== b
        ? ((t = (e) => (0 === e ? null != b.tipoSistema : 1 === e ? eD(b) > 0 : 2 !== e || null != b.estado)),
          (x[3] = b),
          (x[4] = t))
        : (t = x[4]);
      let y = t;
      x[5] === Symbol.for("react.memo_cache_sentinel")
        ? ((i = (e) => v({ type: "paso", paso: e })), (x[5] = i))
        : (i = x[5]);
      let j = i;
      return (
        x[6] !== b.paso || x[7] !== f
          ? ((s = (0, r.jsx)("ol", {
              className: "mb-8 flex items-center gap-1 sm:gap-2",
              children: f.map((e, o) => {
                let a = o === b.paso,
                  t = o < b.paso;
                return (0, r.jsxs)(
                  "li",
                  {
                    className: "flex flex-1 flex-col items-center gap-1.5",
                    children: [
                      (0, r.jsx)("button", {
                        type: "button",
                        disabled: o > b.paso,
                        onClick: () => j(o),
                        className: `h-1.5 w-full rounded-full transition-colors ${a || t ? "bg-[var(--color-bg-brand-solid)]" : "bg-[var(--color-border-secondary)]"} ${o > b.paso ? "cursor-default" : "cursor-pointer"}`,
                        "aria-label": `Paso ${o + 1}: ${e}`,
                      }),
                      (0, r.jsx)("span", {
                        className: `hidden text-xs sm:block ${a ? "font-semibold text-[var(--color-text-primary)]" : "text-[var(--color-text-quaternary)]"}`,
                        children: e,
                      }),
                    ],
                  },
                  e,
                );
              }),
            })),
            (x[6] = b.paso),
            (x[7] = f),
            (x[8] = s))
          : (s = x[8]),
        x[9] !== b.paso || x[10] !== b.tipoSistema
          ? ((l =
              0 === b.paso &&
              (0, r.jsx)(h, {
                seleccionado: b.tipoSistema,
                onSeleccionar: (e) => {
                  (v({ type: "tipoSistema", valor: e }), j(1));
                },
              })),
            (x[9] = b.paso),
            (x[10] = b.tipoSistema),
            (x[11] = l))
          : (l = x[11]),
        x[12] !== b
          ? ((n = 1 === b.paso && (0, r.jsx)(k, { estado: b, dispatch: v })), (x[12] = b), (x[13] = n))
          : (n = x[13]),
        x[14] !== b
          ? ((c = 2 === b.paso && (0, r.jsx)(A, { estado: b, dispatch: v })), (x[14] = b), (x[15] = c))
          : (c = x[15]),
        x[16] !== g || x[17] !== b
          ? ((d = g && 3 === b.paso && (0, r.jsx)(T, { estado: b, dispatch: v })),
            (x[16] = g),
            (x[17] = b),
            (x[18] = d))
          : (d = x[18]),
        x[19] !== b || x[20] !== f.length
          ? ((u = b.paso === f.length - 1 && b.paso > 0 && (0, r.jsx)(ec, { estado: b, dispatch: v })),
            (x[19] = b),
            (x[20] = f.length),
            (x[21] = u))
          : (u = x[21]),
        x[22] !== b.paso || x[23] !== f.length || x[24] !== y
          ? ((m =
              b.paso > 0 &&
              b.paso < f.length - 1 &&
              (0, r.jsxs)("div", {
                className: "mt-8 flex items-center justify-between",
                children: [
                  (0, r.jsx)("button", {
                    type: "button",
                    onClick: () => j(b.paso - 1),
                    className:
                      "rounded-lg px-4 py-2.5 text-sm font-semibold text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-tertiary)]",
                    children: "← Atrás",
                  }),
                  (0, r.jsx)("button", {
                    type: "button",
                    disabled: !y(b.paso),
                    onClick: () => j(b.paso + 1),
                    className:
                      "rounded-lg bg-[var(--color-bg-brand-solid)] px-5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[var(--color-bg-brand-solid_hover)] disabled:cursor-not-allowed disabled:opacity-40",
                    style: { boxShadow: "inset 0 0 0 1px rgba(10,13,18,.18), inset 0 -2px 0 0 rgba(10,13,18,.05)" },
                    children: "Continuar →",
                  }),
                ],
              })),
            (x[22] = b.paso),
            (x[23] = f.length),
            (x[24] = y),
            (x[25] = m))
          : (m = x[25]),
        x[26] !== s || x[27] !== l || x[28] !== n || x[29] !== c || x[30] !== d || x[31] !== u || x[32] !== m
          ? ((p = (0, r.jsxs)("div", { className: "mx-auto max-w-3xl", children: [s, l, n, c, d, u, m] })),
            (x[26] = s),
            (x[27] = l),
            (x[28] = n),
            (x[29] = c),
            (x[30] = d),
            (x[31] = u),
            (x[32] = m),
            (x[33] = p))
          : (p = x[33]),
        p
      );
    }
  },
  744196,
  function (e) {
    e.n(e.i(887364));
  },
]);

//# debugId=48478e4d-65d7-ba6f-1944-9dd3cff8bebc
