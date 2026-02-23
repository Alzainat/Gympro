export default function AuthCard({ title, subtitle, children }) {
  return (
    <div style={s.container}>
      <div style={s.glow} aria-hidden="true"></div>

      <div style={s.card}>
        <div style={s.header}>
          <h2 style={s.title}>{title}</h2>
          {subtitle && <p style={s.subtitle}>{subtitle}</p>}
        </div>

        {children}

        <div style={s.footerHint}>
          <span style={s.dot}></span>
          Powered by Discipline
        </div>
      </div>
    </div>
  );
}

export const AuthUI = {
  input: {
    width: "100%",
    padding: "14px 16px",
    borderRadius: 14,
    border: "1px solid rgba(255,255,255,.08)",
    outline: "none",
    fontSize: 14,
    marginBottom: 14,
    background: "rgba(15,23,42,.7)",
    color: "#fff",
    transition: "all .2s ease",
    boxSizing: "border-box",
  },

  button: (disabled) => ({
    width: "100%",
    padding: "14px 16px",
    borderRadius: 14,
    border: "none",
    fontWeight: 800,
    letterSpacing: 1,
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.6 : 1,
    background: "linear-gradient(90deg, #00f5d4, #00d4b3)",
    color: "#061018",
    marginTop: 6,
    boxShadow: disabled ? "none" : "0 15px 35px rgba(0,245,212,.25)",
    transition: "all .2s ease",
    boxSizing: "border-box",
  }),

  linkRow: {
    marginTop: 18,
    textAlign: "center",
    fontSize: 14,
    color: "rgba(255,255,255,.6)",
  },

  error: {
    background: "rgba(255,59,59,.15)",
    color: "#ffb4b4",
    padding: "10px 14px",
    borderRadius: 12,
    marginBottom: 14,
    fontSize: 14,
    border: "1px solid rgba(255,59,59,.3)",
    boxSizing: "border-box",
  },
};

const s = {
  container: {
    minHeight: "100vh",
    display: "grid",
    placeItems: "center",
    padding: 20,
    background:
      "radial-gradient(1100px 700px at 20% 20%, rgba(0,245,212,.14), transparent 55%), radial-gradient(900px 600px at 80% 25%, rgba(124,58,237,.12), transparent 55%), linear-gradient(135deg, #05070d, #0b1220)",
    position: "relative",
    overflow: "hidden",
  },

  glow: {
    position: "absolute",
    width: 520,
    height: 520,
    borderRadius: "50%",
    background: "radial-gradient(circle, rgba(0,245,212,.25), transparent 60%)",
    filter: "blur(100px)",
    top: -180,
    right: -170,
    pointerEvents: "none",
  },

  card: {
    width: "100%",
    maxWidth: 420,
    padding: 32,
    borderRadius: 20,
    background: "rgba(17,24,39,.65)",
    backdropFilter: "blur(20px)",
    border: "1px solid rgba(255,255,255,.08)",
    boxShadow: "0 30px 80px rgba(0,0,0,.6)",
    color: "#fff",
    position: "relative",
    boxSizing: "border-box",
  },

  header: { marginBottom: 20, textAlign: "center" },

  title: { margin: 0, fontSize: 26, fontWeight: 900, letterSpacing: 1 },

  subtitle: {
    marginTop: 8,
    color: "rgba(255,255,255,.6)",
    fontSize: 14,
  },

  footerHint: {
    marginTop: 20,
    fontSize: 12,
    textAlign: "center",
    color: "rgba(255,255,255,.35)",
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    gap: 6,
  },

  dot: {
    width: 6,
    height: 6,
    borderRadius: "50%",
    background: "linear-gradient(90deg,#00f5d4,#7c3aed)",
  },
};
