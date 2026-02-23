export const theme = {
  colors: {
    bg0: "#05070d",
    bg1: "#0b1220",

    card: "rgba(17,24,39,.65)",
    surface: "rgba(15,23,42,.70)",

    border: "rgba(255,255,255,.08)",
    borderSoft: "rgba(255,255,255,.10)",

    text: "#ffffff",
    textDim: "rgba(255,255,255,.65)",
    textFaint: "rgba(255,255,255,.35)",

    primary: "#00f5d4",
    primary2: "#00d4b3",
    accent: "#7c3aed",

    dangerBg: "rgba(255,59,59,.15)",
    dangerBorder: "rgba(255,59,59,.30)",
    dangerText: "#ffb4b4",
  },

  radius: {
    sm: 10,
    md: 14,
    lg: 20,
  },

  shadow: {
    card: "0 30px 80px rgba(0,0,0,.6)",
    glow: "0 15px 35px rgba(0,245,212,.25)",
  },

 gradients: {
  page:
    "radial-gradient(1100px 700px at 20% 20%, rgba(0,245,212,.14), transparent 55%), " +
    "radial-gradient(900px 600px at 80% 25%, rgba(124,58,237,.12), transparent 55%), " +
    "linear-gradient(135deg, #05070d, #0b1220)",

  primary: "linear-gradient(90deg, #00f5d4, #00d4b3)",

  dot: "linear-gradient(90deg, #00f5d4, #7c3aed)",

  glow: "radial-gradient(circle, rgba(0,245,212,.25), transparent 60%)",

  glowAccent: "radial-gradient(circle, rgba(124,58,237,.20), transparent 62%)",
},

  motion: {
    fast: "all .15s ease",
    base: "all .2s ease",
  },

  layout: {
    pagePadding: 20,
    cardMaxWidth: 420,
    contentMax: 1100,
  },
};

// Helpers لتقليل التكرار
export const ui = {
  page: {
    minHeight: "100vh",
    background: theme.gradients.page,
    position: "relative",
    overflow: "hidden",
  },

  center: {
    minHeight: "100vh",
    display: "grid",
    placeItems: "center",
    padding: theme.layout.pagePadding,
  },

  card: {
    width: "100%",
    maxWidth: theme.layout.cardMaxWidth,
    padding: 32,
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
    marginBottom: 20,
    textAlign: "center",
  },

  title: {
    margin: 0,
    fontSize: 26,
    fontWeight: 900,
    letterSpacing: 1,
  },

  subtitle: {
    marginTop: 8,
    color: theme.colors.textDim,
    fontSize: 14,
  },

  input: {
    width: "100%",
    padding: "14px 16px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.border}`,
    outline: "none",
    fontSize: 14,
    background: theme.colors.surface,
    color: theme.colors.text,
    transition: theme.motion.base,
    boxSizing: "border-box",
  },

  button: (disabled) => ({
    width: "100%",
    padding: "14px 16px",
    borderRadius: theme.radius.md,
    border: "none",
    fontWeight: 800,
    letterSpacing: 1,
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.6 : 1,
    background: theme.gradients.primary,
    color: "#061018",
    marginTop: 6,
    boxShadow: disabled ? "none" : theme.shadow.glow,
    transition: theme.motion.base,
    boxSizing: "border-box",
  }),

  link: {
    color: theme.colors.primary,
    textDecoration: "none",
    fontWeight: 800,
  },

  linkRow: {
    marginTop: 18,
    textAlign: "center",
    fontSize: 14,
    color: "rgba(255,255,255,.6)",
  },

  error: {
    background: theme.colors.dangerBg,
    color: theme.colors.dangerText,
    padding: "10px 14px",
    borderRadius: 12,
    marginBottom: 14,
    fontSize: 14,
    border: `1px solid ${theme.colors.dangerBorder}`,
    boxSizing: "border-box",
  },

  // Decorative backgrounds (نفس الجو تبع auth pages)
  bgGrid: {
    position: "absolute",
    inset: "-40%",
    background:
      "repeating-linear-gradient(135deg, rgba(255,255,255,.04) 0px, rgba(255,255,255,.04) 2px, transparent 2px, transparent 14px)",
    transform: "rotate(-8deg)",
    opacity: 0.6,
    pointerEvents: "none",
  },

  glowTop: {
    position: "absolute",
    width: 520,
    height: 520,
    borderRadius: "50%",
    background: theme.gradients.glow,
    filter: "blur(90px)",
    top: -180,
    right: -170,
    pointerEvents: "none",
  },

  glowBottom: {
    position: "absolute",
    width: 520,
    height: 520,
    borderRadius: "50%",
    background: theme.gradients.glowAccent,
    filter: "blur(95px)",
    bottom: -220,
    left: -200,
    pointerEvents: "none",
  },
};