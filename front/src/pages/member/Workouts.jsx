import { useEffect, useMemo, useState } from "react";
import api from "../../api/axios";
import { theme, ui } from "../../theme/uiTheme";

const DAYS = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];

export default function Workouts() {
  const [data, setData] = useState({});
  const [loading, setLoading] = useState(true);
  const [day, setDay] = useState("Monday");

  useEffect(() => {
    api.get("/member/workouts")
      .then((res) => setData(res.data || {}))
      .finally(() => setLoading(false));
  }, []);

  const routines = useMemo(() => data?.[day] || [], [data, day]);

  return (
    <div style={page.page}>
      {/* نفس ديكوريشن auth */}
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={page.container}>
        <div style={page.card}>
          <div style={page.headerRow}>
            <h2 style={page.title}>Workouts</h2>
            <div style={page.badge}>
              {day.slice(0, 3).toUpperCase()}
            </div>
          </div>

          {loading ? (
            <div style={page.dim}>Loading workouts...</div>
          ) : !Object.keys(data || {}).length ? (
            <div style={page.emptyBox}>
              <div style={page.emptyTitle}>No workouts assigned yet</div>
              <div style={page.dim}>Go to Payments and subscribe first.</div>
            </div>
          ) : (
            <>
              <div style={tabs.wrap}>
                {DAYS.map((d) => (
                  <button
                    key={d}
                    style={tabs.btn(d === day)}
                    onClick={() => setDay(d)}
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

              {!routines.length ? (
                <div style={{ ...page.dim, marginTop: 14 }}>
                  No workouts for <b style={{ color: theme.colors.text }}>{day}</b>.
                </div>
              ) : (
                <div style={{ marginTop: 16, display: "grid", gap: 16 }}>
                  {routines.map((routine) => (
                    <div key={routine.routine_id} style={routineCard.wrap}>
                      <div style={routineCard.head}>
                        <h3 style={routineCard.name}>{routine.routine_name}</h3>
                        <div style={routineCard.line} />
                      </div>

                      <div style={table.shell}>
                        <table style={table.table}>
                          <thead>
                            <tr>
                              <th style={table.th}>Exercise</th>
                              <th style={table.th}>Target</th>
                              <th style={table.th}>Difficulty</th>
                              <th style={table.th}>Sets</th>
                              <th style={table.th}>Reps</th>
                              <th style={table.th}>Rest</th>
                            </tr>
                          </thead>
                          <tbody>
                            {routine.exercises.map((x, idx) => (
                              <tr key={`${x.exercise_id}-${idx}`} style={table.tr}>
                                <td style={table.td}>{x.exercise_name ?? "-"}</td>
                                <td style={table.tdDim}>{x.target_muscle ?? "-"}</td>
                                <td style={pill.wrap}>
                                  <span style={pill.tag(x.difficulty)}>
                                    {x.difficulty ?? "-"}
                                  </span>
                                </td>
                                <td style={table.td}>{x.sets ?? "-"}</td>
                                <td style={table.td}>{x.reps ?? "-"}</td>
                                <td style={table.td}>
                                  {x.rest_seconds ? `${x.rest_seconds}s` : "-"}
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    </div>
                  ))}
                </div>
              )}
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

const routineCard = {
  wrap: {
    padding: 16,
    borderRadius: theme.radius.lg,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
  },
  head: {
    display: "flex",
    alignItems: "center",
    gap: 12,
    marginBottom: 12,
  },
  name: {
    margin: 0,
    fontSize: 16,
    fontWeight: 900,
    letterSpacing: 0.3,
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
    minWidth: 720,
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

const pill = {
  wrap: {
    padding: "12px 14px",
    borderBottom: `1px solid rgba(255,255,255,.06)`,
    whiteSpace: "nowrap",
  },
  tag: (difficulty) => {
    const d = (difficulty || "").toLowerCase();
    const isHard = d.includes("hard") || d.includes("advanced");
    const isMid = d.includes("medium") || d.includes("intermediate");

    const bg = isHard
      ? "rgba(255,59,59,.12)"
      : isMid
      ? "rgba(124,58,237,.14)"
      : "rgba(0,245,212,.12)";

    const border = isHard
      ? theme.colors.dangerBorder
      : isMid
      ? "rgba(124,58,237,.35)"
      : "rgba(0,245,212,.30)";

    const color = isHard
      ? theme.colors.dangerText
      : isMid
      ? "#c4b5fd"
      : theme.colors.primary;

    return {
      display: "inline-flex",
      alignItems: "center",
      padding: "6px 10px",
      borderRadius: 999,
      border: `1px solid ${border}`,
      background: bg,
      color,
      fontWeight: 900,
      fontSize: 12,
      letterSpacing: 0.6,
    };
  },
};