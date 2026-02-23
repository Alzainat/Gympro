import { useEffect, useMemo, useState } from "react";
import api from "../../api/axios";
import { theme, ui } from "../../theme/uiTheme";

export default function HealthConditions() {
  const [text, setText] = useState("");
  const [blocked, setBlocked] = useState([]);
  const [warnings, setWarnings] = useState([]);
  const [loading, setLoading] = useState(false);

  // لإعادة تشغيل الأنيميشن بعد كل Check
  const [animKey, setAnimKey] = useState(0);

  const canSubmit = useMemo(() => text.trim().length > 0 && !loading, [text, loading]);

  const check = async () => {
    if (!text.trim()) return;
    setLoading(true);

    try {
      const res = await api.post("/member/health-conditions/check", {
        conditions: [text.trim()],
      });

      setBlocked(res.data?.blocked_exercises || []);
      setWarnings(res.data?.warnings || []);
      setAnimKey((k) => k + 1);
    } catch (e) {
      setBlocked([]);
      setWarnings([]);
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const reset = () => {
    setText("");
    setBlocked([]);
    setWarnings([]);
    setAnimKey((k) => k + 1);
  };

  const hasResults = blocked.length > 0 || warnings.length > 0;

  return (
    <div style={page.page}>
      {/* Auth-like decorative background */}
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={page.container}>
        <div style={page.card}>
          <div style={page.header}>
            <div>
              <h2 style={page.title}>Health Conditions</h2>
              <div style={page.subtitle}>
                Enter an injury or condition and we’ll highlight exercises to avoid or perform with caution.
              </div>
            </div>
            <div style={page.badge}>Safety Check</div>
          </div>

          <div style={form.wrap}>
            <label style={form.label}>Condition / Injury</label>
            <textarea
              style={form.textarea}
              value={text}
              onChange={(e) => setText(e.target.value)}
              placeholder="Examples: knee injury, shoulder pain, lower back pain..."
            />

            <div style={form.actions}>
              <button style={form.primary(!canSubmit)} disabled={!canSubmit} onClick={check}>
                {loading ? "Checking..." : "Check Exercises"}
              </button>

              <button style={form.secondary(loading)} disabled={loading} onClick={reset}>
                Reset
              </button>
            </div>

            <div style={form.helperRow}>
              <span style={form.helperDot} />
              <span style={form.helperText}>
                Tip: be specific (e.g., “ACL injury”, “rotator cuff pain”) for better matches.
              </span>
            </div>
          </div>

          {/* Results */}
          <div key={animKey} style={anim.wrap}>
            <div style={section.wrap}>
              <div style={section.head}>
                <div style={section.title}>Blocked Exercises</div>
                <div style={section.pillBlocked}>Strict</div>
                <div style={section.line} />
                <div style={section.count}>{blocked.length}</div>
              </div>

              {blocked.length === 0 ? (
                <div style={empty.box}>
                  <div style={empty.title}>No blocked exercises found.</div>
                  <div style={empty.sub}>If you expected results, try a more specific condition.</div>
                </div>
              ) : (
                <div style={cards.grid}>
                  {blocked.map((x) => (
                    <div key={x.exercise_id} style={cards.card}>
                      <div style={cards.top}>
                        <div style={cards.name}>{x.name}</div>
                        <span style={cards.tagBlocked}>BLOCKED</span>
                      </div>

                      {x.reason ? <div style={cards.reason}>Reason: {x.reason}</div> : null}

                      {x.matched_condition || x.matched_keyword ? (
                        <div style={cards.meta}>
                          Matched:{" "}
                          <b style={{ color: theme.colors.text }}>{x.matched_condition || "-"}</b>{" "}
                          <span style={{ color: theme.colors.textFaint }}>→</span>{" "}
                          <b style={{ color: theme.colors.text }}>{x.matched_keyword || "-"}</b>
                        </div>
                      ) : null}
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div style={{ height: 14 }} />

            <div style={section.wrap}>
              <div style={section.head}>
                <div style={section.title}>Warnings</div>
                <div style={section.pillWarn}>Caution</div>
                <div style={section.line} />
                <div style={section.count}>{warnings.length}</div>
              </div>

              {warnings.length === 0 ? (
                <div style={empty.box}>
                  <div style={empty.title}>No warnings found.</div>
                  <div style={empty.sub}>You’re good to go based on the current input.</div>
                </div>
              ) : (
                <div style={cards.grid}>
                  {warnings.map((x) => (
                    <div key={x.exercise_id} style={cards.card}>
                      <div style={cards.top}>
                        <div style={cards.name}>{x.name}</div>
                        <span style={cards.tagWarn}>WARNING</span>
                      </div>

                      {x.reason ? <div style={cards.reason}>Reason: {x.reason}</div> : null}

                      {x.matched_condition || x.matched_keyword ? (
                        <div style={cards.meta}>
                          Matched:{" "}
                          <b style={{ color: theme.colors.text }}>{x.matched_condition || "-"}</b>{" "}
                          <span style={{ color: theme.colors.textFaint }}>→</span>{" "}
                          <b style={{ color: theme.colors.text }}>{x.matched_keyword || "-"}</b>
                        </div>
                      ) : null}
                    </div>
                  ))}
                </div>
              )}
            </div>

            {!hasResults && !loading ? (
              <div style={{ ...page.note, marginTop: 14 }}>
                No results yet — enter a condition and click <b>Check Exercises</b>.
              </div>
            ) : null}
          </div>
        </div>
      </div>
    </div>
  );
}

/* ---------------- Styles (theme-based) ---------------- */

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
  header: {
    display: "flex",
    alignItems: "flex-start",
    justifyContent: "space-between",
    gap: 14,
    marginBottom: 14,
  },
  title: {
    margin: 0,
    fontSize: 22,
    fontWeight: 900,
    letterSpacing: 0.6,
  },
  subtitle: {
    marginTop: 6,
    color: theme.colors.textDim,
    fontSize: 14,
    lineHeight: 1.4,
    maxWidth: 720,
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
    whiteSpace: "nowrap",
  },
  note: {
    color: theme.colors.textDim,
    fontSize: 13,
  },
};

const form = {
  wrap: {
    padding: 16,
    borderRadius: theme.radius.lg,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
  },
  label: {
    display: "block",
    fontSize: 12,
    color: theme.colors.textFaint,
    letterSpacing: 0.8,
    textTransform: "uppercase",
    fontWeight: 900,
    marginBottom: 8,
  },
  textarea: {
    width: "100%",
    minHeight: 120,
    padding: "12px 14px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.border}`,
    background: "rgba(11,18,32,.75)",
    color: theme.colors.text,
    outline: "none",
    boxSizing: "border-box",
    resize: "vertical",
    transition: theme.motion.base,
  },
  actions: {
    display: "flex",
    gap: 10,
    marginTop: 12,
    flexWrap: "wrap",
  },
  primary: (disabled) => ({
    padding: "12px 14px",
    borderRadius: theme.radius.md,
    border: "none",
    fontWeight: 900,
    letterSpacing: 0.6,
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.6 : 1,
    background: theme.gradients.primary,
    color: "#061018",
    boxShadow: disabled ? "none" : theme.shadow.glow,
    transition: theme.motion.base,
  }),
  secondary: (disabled) => ({
    padding: "12px 14px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.border}`,
    fontWeight: 900,
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.7 : 1,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.text,
    transition: theme.motion.base,
  }),
  helperRow: {
    display: "flex",
    alignItems: "center",
    gap: 10,
    marginTop: 12,
    color: theme.colors.textDim,
    fontSize: 13,
  },
  helperDot: {
    width: 10,
    height: 10,
    borderRadius: 999,
    background: theme.gradients.dot,
    boxShadow: theme.shadow.glow,
    flex: "0 0 auto",
  },
  helperText: {},
};

const section = {
  wrap: {
    marginTop: 16,
    padding: 16,
    borderRadius: theme.radius.lg,
    background: "rgba(255,255,255,.02)",
    border: `1px solid ${theme.colors.border}`,
  },
  head: {
    display: "flex",
    alignItems: "center",
    gap: 10,
    marginBottom: 12,
  },
  title: {
    fontWeight: 900,
    letterSpacing: 0.4,
  },
  line: {
    height: 1,
    flex: 1,
    background: `linear-gradient(90deg, ${theme.colors.border}, transparent)`,
    opacity: 0.9,
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
  pillBlocked: {
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.dangerBorder}`,
    background: theme.colors.dangerBg,
    color: theme.colors.dangerText,
    fontWeight: 900,
    fontSize: 12,
  },
  pillWarn: {
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid rgba(124,58,237,.35)`,
    background: "rgba(124,58,237,.14)",
    color: "#c4b5fd",
    fontWeight: 900,
    fontSize: 12,
  },
};

const empty = {
  box: {
    padding: 14,
    borderRadius: theme.radius.md,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
  },
  title: {
    fontWeight: 900,
    marginBottom: 6,
  },
  sub: {
    color: theme.colors.textDim,
    fontSize: 13,
    lineHeight: 1.4,
  },
};

const cards = {
  grid: {
    display: "grid",
    gap: 12,
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
  },
  card: {
    padding: 14,
    borderRadius: theme.radius.lg,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
    transition: theme.motion.base,
  },
  top: {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    gap: 10,
    marginBottom: 8,
  },
  name: {
    fontWeight: 900,
    letterSpacing: 0.2,
  },
  reason: {
    color: theme.colors.textDim,
    fontSize: 13,
    marginBottom: 8,
    lineHeight: 1.45,
  },
  meta: {
    color: theme.colors.textDim,
    fontSize: 13,
    lineHeight: 1.45,
  },
  tagBlocked: {
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.dangerBorder}`,
    background: theme.colors.dangerBg,
    color: theme.colors.dangerText,
    fontWeight: 900,
    fontSize: 11,
    letterSpacing: 0.8,
  },
  tagWarn: {
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid rgba(124,58,237,.35)`,
    background: "rgba(124,58,237,.14)",
    color: "#c4b5fd",
    fontWeight: 900,
    fontSize: 11,
    letterSpacing: 0.8,
  },
};

const anim = {
  wrap: {
    animation: "hcFadeIn .22s ease",
    willChange: "transform, opacity",
  },
};

// inject keyframes once
if (typeof document !== "undefined" && !document.getElementById("hc-anim-style")) {
  const style = document.createElement("style");
  style.id = "hc-anim-style";
  style.innerHTML = `
    @keyframes hcFadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  `;
  document.head.appendChild(style);
}