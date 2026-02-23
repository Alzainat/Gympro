import { useMemo, useState } from "react";
import { theme, ui } from "../../theme/uiTheme";

const library = [
  {
    name: "Chest",
    tagline: "Pressing strength + chest control",
    exercises: [
      {
        title: "Push-Up",
        summary: "A classic chest movement that builds strength and control.",
        level: "Beginner",
        equipment: "Bodyweight",
        duration: "2–4 sets",
        steps: [
          { title: "Set your position", text: "Hands under shoulders, body in a straight line, core braced." },
          { title: "Controlled descent", text: "Lower your chest toward the floor with elbows at a comfortable angle." },
          { title: "Strong press", text: "Push the floor away and return to the top without locking your joints hard." },
          { title: "Breathe & repeat", text: "Inhale down, exhale up. Keep a steady rhythm." },
        ],
        cues: ["Keep hips aligned (no sagging).", "Neck neutral, eyes slightly forward."],
        youtube: "https://youtube.com/shorts/yQEx9OC2C3E?si=OKHcmA_UctkPv-6Z",
      },
      {
        title: "Dumbbell Bench Press",
        summary: "Targets chest with great range of motion using dumbbells.",
        level: "Intermediate",
        equipment: "Dumbbells + Bench",
        duration: "3–5 sets",
        steps: [
          { title: "Setup", text: "Feet planted, shoulders down and back, dumbbells near your chest." },
          { title: "Press path", text: "Press up and slightly inward to stack dumbbells over your chest." },
          { title: "Slow return", text: "Lower under control until dumbbells are near chest level again." },
          { title: "Stay tight", text: "Maintain core tension and stable shoulder position." },
        ],
        cues: ["Avoid flaring elbows too wide.", "Control the lowering phase."],
        youtube: "",
      },
    ],
  },
  {
    name: "Back",
    tagline: "Pulling strength + posture",
    exercises: [
      {
        title: "Lat Pulldown",
        summary: "Builds width and strength in your lats.",
        level: "Beginner",
        equipment: "Machine",
        duration: "3–4 sets",
        steps: [
          { title: "Grip", text: "Hands slightly wider than shoulders, chest tall." },
          { title: "Pull to chest", text: "Pull the bar toward upper chest while squeezing shoulder blades down." },
          { title: "Pause", text: "Hold for 1 second at the bottom to feel your lats." },
          { title: "Controlled release", text: "Return the bar slowly without losing posture." },
        ],
        cues: ["Don’t swing your body.", "Keep ribs down and chest proud."],
        youtube: "",
      },
    ],
  },
];

function Badge({ children }) {
  return <span style={p.badge}>{children}</span>;
}

function Chevron({ open }) {
  return (
    <span
      style={{
        ...p.chev,
        transform: open ? "rotate(180deg)" : "rotate(0deg)",
      }}
      aria-hidden="true"
    >
      ▾
    </span>
  );
}

function StepCard({ index, step }) {
  return (
    <div style={p.stepCard}>
      <div style={p.stepIndex}>{index}</div>
      <div>
        <div style={p.stepTitle}>{step.title}</div>
        <div style={p.stepText}>{step.text}</div>
      </div>
    </div>
  );
}

function ExerciseAccordion({ ex, open, onToggle }) {
  return (
    <div style={p.exerciseCard}>
      <button type="button" onClick={onToggle} style={p.exerciseHeaderBtn} aria-expanded={open}>
        <div style={{ display: "grid", gap: 8, minWidth: 0 }}>
          <div style={p.exerciseTitleRow}>
            <div style={p.exerciseTitle}>{ex.title}</div>
            <div style={p.badgesRow}>
              <Badge>{ex.level}</Badge>
              <Badge>{ex.equipment}</Badge>
              <Badge>{ex.duration}</Badge>
            </div>
          </div>

          <div style={p.exerciseSummary}>{ex.summary}</div>
        </div>

        <Chevron open={open} />
      </button>

      {open && (
        <div style={p.exerciseBody}>
          <div style={p.sectionLabel}>How to do it</div>
          <div style={p.stepsGrid}>
            {ex.steps.map((step, i) => (
              <StepCard key={i} index={i + 1} step={step} />
            ))}
          </div>

          {ex.cues?.length ? (
            <>
              <div style={{ ...p.sectionLabel, marginTop: 14 }}>Coach cues</div>
              <div style={p.cuesWrap}>
                {ex.cues.map((c, i) => (
                  <div key={i} style={p.cuePill}>
                    {c}
                  </div>
                ))}
              </div>
            </>
          ) : null}

          <div style={p.footerRow}>
            {ex.youtube ? (
              <a href={ex.youtube} target="_blank" rel="noreferrer" style={p.cta}>
                Click here to see how →
              </a>
            ) : (
              <div style={p.ctaDisabled} title="Admin will add the video soon">
                Video coming soon
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

export default function ExerciseLibrary() {
  const [q, setQ] = useState("");
  const [openKey, setOpenKey] = useState(null);

  const filtered = useMemo(() => {
    const query = q.trim().toLowerCase();
    if (!query) return library;

    return library
      .map((cat) => ({
        ...cat,
        exercises: cat.exercises.filter((ex) => {
          const hay = `${ex.title} ${ex.summary} ${ex.level} ${ex.equipment}`.toLowerCase();
          return hay.includes(query);
        }),
      }))
      .filter((cat) => cat.exercises.length > 0);
  }, [q]);

  return (
    <div style={p.pageWrap}>
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={p.container}>
        {/* Header */}
        <div style={p.header}>
          <div>
            <div style={p.kicker}>Workout Library</div>
            <h2 style={p.h2}>Learn each exercise with clear steps</h2>
            <p style={p.sub}>Everything is curated for members</p>
          </div>

          <div style={p.searchWrap}>
            <div style={p.searchLabel}>Search</div>
            <input
              style={p.search}
              placeholder="Try: push-up, dumbbell, beginner..."
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
          </div>
        </div>

        {/* Categories */}
        <div style={p.categories}>
          {filtered.map((cat) => (
            <div key={cat.name} style={p.categoryCard}>
              <div style={p.categoryHeader}>
                <div style={p.categoryName}>{cat.name}</div>
                <div style={p.categoryTagline}>{cat.tagline}</div>
              </div>

              <div style={p.exerciseList}>
                {cat.exercises.map((ex) => {
                  const key = `${cat.name}:${ex.title}`;
                  const isOpen = openKey === key;

                  return (
                    <ExerciseAccordion
                      key={key}
                      ex={ex}
                      open={isOpen}
                      onToggle={() => setOpenKey(isOpen ? null : key)}
                    />
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

const p = {
  pageWrap: {
    ...ui.page,
    padding: theme.layout.pagePadding,
  },

  container: {
    width: "100%",
    maxWidth: theme.layout.contentMax,
    margin: "0 auto",
    position: "relative",
    zIndex: 1,
  },

  header: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "flex-end",
    gap: 16,
    flexWrap: "wrap",
    marginBottom: 18,
  },

  kicker: {
    display: "inline-flex",
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.05)",
    fontWeight: 950,
    letterSpacing: 0.3,
    color: theme.colors.text,
  },

  h2: { margin: "10px 0 0", fontSize: 26, fontWeight: 950, color: theme.colors.text },
  sub: { margin: "8px 0 0", color: theme.colors.textDim, lineHeight: 1.5 },

  searchWrap: {
    width: 340,
    maxWidth: "100%",
    display: "grid",
    gap: 8,
  },

  searchLabel: { fontSize: 12, color: theme.colors.textFaint, fontWeight: 900 },
  search: {
    ...ui.input,
    borderRadius: theme.radius.lg,
  },

  categories: { display: "grid", gap: 14 },

  categoryCard: {
    background: theme.colors.card,
    border: `1px solid ${theme.colors.border}`,
    borderRadius: theme.radius.lg,
    padding: 14,
    boxShadow: theme.shadow.card,
    backdropFilter: "blur(18px)",
  },

  categoryHeader: { display: "grid", gap: 6, marginBottom: 12 },

  categoryName: { fontSize: 18, fontWeight: 950, color: theme.colors.text },
  categoryTagline: { color: theme.colors.textDim, fontSize: 13 },

  exerciseList: { display: "grid", gap: 12 },

  exerciseCard: {
    borderRadius: theme.radius.lg,
    border: `1px solid ${theme.colors.border}`,
    background: "rgba(255,255,255,.03)",
    overflow: "hidden",
  },

  exerciseHeaderBtn: {
    width: "100%",
    textAlign: "left",
    display: "flex",
    justifyContent: "space-between",
    gap: 12,
    alignItems: "center",
    padding: 14,
    cursor: "pointer",
    background: "transparent",
    border: "none",
    color: theme.colors.text,
  },

  exerciseTitleRow: {
    display: "grid",
    gap: 8,
  },

  exerciseTitle: { fontSize: 16, fontWeight: 950, color: theme.colors.text },
  exerciseSummary: { color: theme.colors.textDim, lineHeight: 1.5, fontSize: 13 },

  badgesRow: { display: "flex", gap: 8, flexWrap: "wrap" },

  badge: {
    fontSize: 12,
    padding: "5px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.05)",
    color: theme.colors.textDim,
    fontWeight: 900,
  },

  chev: {
    width: 34,
    height: 34,
    display: "grid",
    placeItems: "center",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    transition: theme.motion.base,
    flexShrink: 0,
    color: theme.colors.text,
  },

  exerciseBody: {
    padding: 14,
    borderTop: `1px solid ${theme.colors.border}`,
    background: "rgba(11,18,32,.35)",
  },

  sectionLabel: { fontSize: 12, fontWeight: 950, color: theme.colors.textDim, marginBottom: 10 },

  stepsGrid: {
    display: "grid",
    gap: 10,
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
  },

  stepCard: {
    display: "flex",
    gap: 12,
    padding: 12,
    borderRadius: theme.radius.lg,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
  },

  stepIndex: {
    width: 30,
    height: 30,
    borderRadius: theme.radius.md,
    display: "grid",
    placeItems: "center",
    fontWeight: 950,
    border: "1px solid rgba(0,245,212,.28)",
    background: "rgba(0,245,212,.10)",
    color: theme.colors.primary,
    flexShrink: 0,
    boxShadow: "0 12px 26px rgba(0,245,212,.12)",
  },

  stepTitle: { fontWeight: 950, marginBottom: 4, color: theme.colors.text },
  stepText: { color: theme.colors.textDim, lineHeight: 1.45, fontSize: 13 },

  cuesWrap: { display: "flex", gap: 8, flexWrap: "wrap" },

  cuePill: {
    padding: "8px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    fontSize: 13,
    lineHeight: 1.3,
  },

  footerRow: { display: "flex", justifyContent: "flex-end", marginTop: 14 },

  cta: {
    display: "inline-flex",
    alignItems: "center",
    gap: 10,
    textDecoration: "none",
    padding: "10px 12px",
    borderRadius: theme.radius.lg,
    background: "rgba(0,245,212,.12)",
    border: "1px solid rgba(0,245,212,.28)",
    color: theme.colors.primary,
    fontWeight: 950,
    transition: theme.motion.base,
    boxShadow: "0 12px 26px rgba(0,245,212,.10)",
  },

  ctaDisabled: {
    padding: "10px 12px",
    borderRadius: theme.radius.lg,
    background: "rgba(255,255,255,.04)",
    border: `1px solid ${theme.colors.borderSoft}`,
    color: theme.colors.textFaint,
    fontWeight: 900,
  },
};