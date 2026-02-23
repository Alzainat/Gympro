import { useEffect, useMemo, useState } from "react";
import api from "../../api/axios";
import { theme, ui } from "../../theme/uiTheme";

const DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
const TIMES = ["breakfast", "lunch", "dinner", "snack", "other"];

export default function Meals() {
  const [data, setData] = useState({});
  const [loading, setLoading] = useState(true);

  const [day, setDay] = useState("Monday");

  // للأنيميشن عند تغيير اليوم
  const [animKey, setAnimKey] = useState(0);

  useEffect(() => {
    api.get("/member/meals")
      .then((res) => setData(res.data || {}))
      .finally(() => setLoading(false));
  }, []);

  const dayGroups = useMemo(() => data?.[day] || {}, [data, day]);

  const hasAny = useMemo(
    () => TIMES.some((t) => (dayGroups?.[t] || []).length > 0),
    [dayGroups]
  );

  const handleDayChange = (d) => {
    setDay(d);
    setAnimKey((k) => k + 1); // يعيد تشغيل الأنيميشن
  };

  return (
    <div style={page.page}>
      {/* نفس ديكوريشن auth */}
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={page.container}>
        <div style={page.card}>
          <div style={page.headerRow}>
            <h2 style={page.title}>Meals</h2>
            <div style={page.badge}>{day.slice(0, 3).toUpperCase()}</div>
          </div>

          {loading ? (
            <div style={page.dim}>Loading meals...</div>
          ) : !Object.keys(data || {}).length ? (
            <div style={page.emptyBox}>
              <div style={page.emptyTitle}>No meals assigned yet</div>
              <div style={page.dim}>Go to Payments and subscribe first.</div>
            </div>
          ) : (
            <>
              <div style={tabs.wrap}>
                {DAYS.map((d) => (
                  <button
                    key={d}
                    style={tabs.btn(d === day)}
                    onClick={() => handleDayChange(d)}
                    onMouseEnter={(e) => {
                      if (d !== day) e.currentTarget.style.border = `1px solid ${theme.colors.borderSoft}`;
                    }}
                    onMouseLeave={(e) => {
                      if (d !== day) e.currentTarget.style.border = `1px solid ${theme.colors.border}`;
                    }}
                  >
                    {d.slice(0, 3)}
                  </button>
                ))}
              </div>

              {/* ✅ أنيميشن ناعم عند تغيير اليوم */}
              <div key={animKey} style={anim.wrap}>
                {!hasAny ? (
                  <div style={{ ...page.dim, marginTop: 14 }}>
                    No meals for <b style={{ color: theme.colors.text }}>{day}</b>.
                  </div>
                ) : (
                  <div style={{ marginTop: 16, display: "grid", gap: 16 }}>
                    {TIMES.map((time) => {
                      const items = dayGroups?.[time] || [];
                      if (!items.length) return null;

                      return (
                        <div key={time} style={section.wrap}>
                          <div style={section.head}>
                            <div style={section.pill}>{time}</div>
                            <div style={section.line} />
                            <div style={section.count}>{items.length}</div>
                          </div>

                          <div style={table.shell}>
                            <table style={table.table}>
                              <thead>
                                <tr>
                                  <th style={table.th}>Meal</th>
                                  <th style={table.th}>Calories</th>
                                  <th style={table.th}>Macros</th>
                                </tr>
                              </thead>

                              <tbody>
                                {items.map((m, idx) => (
                                  <tr
                                    key={m.assignment_id ?? `${m.meal_id}-${idx}`}
                                    style={table.tr}
                                    onMouseEnter={(e) => {
                                      e.currentTarget.style.background = "rgba(255,255,255,.04)";
                                    }}
                                    onMouseLeave={(e) => {
                                      e.currentTarget.style.background = "transparent";
                                    }}
                                  >
                                    <td style={table.td}>{m.name ?? "-"}</td>

                                    {/* ✅ Calories كـ Pill */}
                                    <td style={table.td}>
                                      <span style={calorie.pill}>
                                        {m.calories ?? "-"} {m.calories ? "kcal" : ""}
                                      </span>
                                    </td>

                                    <td style={table.tdDim}>
                                      <span style={macro.p}>P:{m.protein ?? "-"}</span>
                                      <span style={macro.sep}>/</span>
                                      <span style={macro.c}>C:{m.carbs ?? "-"}</span>
                                      <span style={macro.sep}>/</span>
                                      <span style={macro.f}>F:{m.fats ?? "-"}</span>
                                    </td>
                                  </tr>
                                ))}
                              </tbody>
                            </table>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

const page = {
  page: {
    ...ui.page,
    padding: theme.layout.pagePadding,
  },
  container: {
    maxWidth: theme.layout.contentMax,
    margin: "0 auto",
  },
  card: {
    width: "100%",
    padding: 22,
    borderRadius: theme.radius.lg,
    background: theme.colors.card,
    backdropFilter: "blur(20px)",
    border: `1px solid ${theme.colors.border}`,
    boxShadow: theme.shadow.card,
    color: theme.colors.text,
    position: "relative",
    boxSizing: "border-box",
  },
  headerRow: {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    gap: 12,
    marginBottom: 10,
  },
  title: {
    margin: 0,
    fontSize: 22,
    fontWeight: 900,
    letterSpacing: 0.6,
  },
  badge: {
    padding: "8px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.border}`,
    background: "rgba(0,245,212,.10)",
    color: theme.colors.primary,
    fontWeight: 900,
    fontSize: 12,
    letterSpacing: 1,
  },
  dim: {
    color: theme.colors.textDim,
    fontSize: 14,
  },
  emptyBox: {
    marginTop: 12,
    padding: 14,
    borderRadius: theme.radius.md,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
  },
  emptyTitle: {
    fontWeight: 900,
    marginBottom: 6,
  },
};

const tabs = {
  wrap: {
    display: "flex",
    gap: 8,
    flexWrap: "wrap",
    marginTop: 12,
  },
  btn: (active) => ({
    padding: "10px 12px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.border}`,
    background: active ? "rgba(0,245,212,.14)" : "rgba(255,255,255,.03)",
    color: active ? theme.colors.primary : theme.colors.textDim,
    fontWeight: 900,
    cursor: "pointer",
    transition: theme.motion.base,
    boxShadow: active ? theme.shadow.glow : "none",
    userSelect: "none",
  }),
};

const anim = {
  wrap: {
    animation: "mealsFadeIn .22s ease",
    willChange: "transform, opacity",
  },
};

// Inject keyframes مرة وحدة
if (typeof document !== "undefined" && !document.getElementById("meals-anim-style")) {
  const style = document.createElement("style");
  style.id = "meals-anim-style";
  style.innerHTML = `
    @keyframes mealsFadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  `;
  document.head.appendChild(style);
}

const section = {
  wrap: {
    padding: 16,
    borderRadius: theme.radius.lg,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
  },
  head: {
    display: "flex",
    alignItems: "center",
    gap: 10,
    marginBottom: 12,
  },
  pill: {
    textTransform: "capitalize",
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.border}`,
    background: "rgba(124,58,237,.12)",
    color: "#c4b5fd",
    fontWeight: 900,
    fontSize: 12,
    letterSpacing: 0.6,
  },
  count: {
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.border}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    fontWeight: 900,
    fontSize: 12,
    minWidth: 34,
    textAlign: "center",
  },
  line: {
    height: 1,
    flex: 1,
    background: `linear-gradient(90deg, ${theme.colors.border}, transparent)`,
    opacity: 0.9,
  },
};

const table = {
  shell: {
    width: "100%",
    overflowX: "auto",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.border}`,
    background: "rgba(255,255,255,.02)",
  },
  table: {
    width: "100%",
    borderCollapse: "collapse",
    minWidth: 650,
  },
  th: {
    textAlign: "left",
    padding: "12px 14px",
    borderBottom: `1px solid ${theme.colors.border}`,
    color: theme.colors.textFaint,
    fontSize: 12,
    letterSpacing: 0.8,
    textTransform: "uppercase",
    background: "rgba(255,255,255,.02)",
    whiteSpace: "nowrap",
  },
  tr: {
    transition: theme.motion.fast,
  },
  td: {
    padding: "12px 14px",
    borderBottom: `1px solid rgba(255,255,255,.06)`,
    color: theme.colors.text,
    whiteSpace: "nowrap",
    fontSize: 14,
  },
  tdDim: {
    padding: "12px 14px",
    borderBottom: `1px solid rgba(255,255,255,.06)`,
    color: theme.colors.textDim,
    whiteSpace: "nowrap",
    fontSize: 14,
  },
};

const calorie = {
  pill: {
    display: "inline-block",
    padding: "6px 10px",
    borderRadius: 999,
    background: "rgba(0,245,212,.12)",
    border: `1px solid rgba(0,245,212,.35)`,
    color: theme.colors.primary,
    fontWeight: 900,
    fontSize: 12,
    letterSpacing: 0.5,
  },
};

const macro = {
  p: { color: theme.colors.primary, fontWeight: 900 },
  c: { color: "#c4b5fd", fontWeight: 900 },
  f: { color: "rgba(255,255,255,.85)", fontWeight: 900 },
  sep: { margin: "0 8px", color: theme.colors.textFaint },
};