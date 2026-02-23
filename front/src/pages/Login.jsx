import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { login } from "../services/authService";
import AuthCard, { AuthUI } from "../components/AuthCard";

function EyeIcon({ off = false, size = 18 }) {
  if (off) {
    return (
      <svg width={size} height={size} viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M3 3l18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <path d="M10.58 10.58A3 3 0 0012 15a3 3 0 002.42-4.42" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <path d="M7.12 7.12C5 8.6 3.53 10.7 2.5 12c1.8 2.3 5.3 6 9.5 6 1.4 0 2.7-.3 3.85-.8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <path d="M9.9 4.3C10.6 4.1 11.3 4 12 4c4.2 0 7.7 3.7 9.5 6-0.7 0.9-1.6 2.1-2.7 3.1" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
      </svg>
    );
  }
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M2.5 12c1.8-2.3 5.3-6 9.5-6s7.7 3.7 9.5 6c-1.8 2.3-5.3 6-9.5 6s-7.7-3.7-9.5-6z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
      <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" strokeWidth="2" />
    </svg>
  );
}

export default function Login() {
  const navigate = useNavigate();

  const [form, setForm] = useState({ email: "", password: "" });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [showPassword, setShowPassword] = useState(false);
  const [eyeHover, setEyeHover] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      await login(form);
      navigate("/");
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          JSON.stringify(err?.response?.data) ||
          "Login failed"
      );
    } finally {
      setLoading(false);
    }
  };

  const eyeActive = showPassword || eyeHover;

  return (
    <AuthCard title="Welcome Back 💪" subtitle="Train hard. Sign in fast.">
      {error && <p style={AuthUI.error}>{error}</p>}

      <form onSubmit={submit} style={s.form}>
        <label style={s.label}>
          <span style={s.labelText}>Email</span>
          <input
            style={AuthUI.input}
            placeholder="you@domain.com"
            type="email"
            value={form.email}
            onChange={(e) => setForm({ ...form, email: e.target.value })}
            required
          />
        </label>

        <label style={s.label}>
          <span style={s.labelText}>Password</span>

          <div style={s.passwordWrap}>
            <input
              style={{ ...AuthUI.input, marginBottom: 0, paddingRight: 56 }}
              placeholder="••••••••"
              type={showPassword ? "text" : "password"}
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
              required
            />

            <button
              type="button"
              onClick={() => setShowPassword((v) => !v)}
              onMouseEnter={() => setEyeHover(true)}
              onMouseLeave={() => setEyeHover(false)}
              style={{
                ...s.eyeBtn,
                color: eyeActive ? "#00f5d4" : "rgba(255,255,255,.55)",
                boxShadow: eyeActive ? "0 0 0 4px rgba(0,245,212,.12)" : "none",
                borderColor: eyeActive ? "rgba(0,245,212,.35)" : "rgba(255,255,255,.10)",
                transform: eyeHover ? "translateY(-50%) scale(1.02)" : "translateY(-50%)",
              }}
              aria-label={showPassword ? "Hide password" : "Show password"}
            >
              <EyeIcon off={showPassword} />
            </button>
          </div>
        </label>

        <button
          style={{ ...AuthUI.button(loading), ...s.submitBtn }}
          disabled={loading}
        >
          {loading ? "Logging in..." : "Login"}
        </button>
      </form>

      <p style={AuthUI.linkRow}>
        No account?{" "}
        <Link to="/register" style={s.link}>
          Create one
        </Link>
      </p>
    </AuthCard>
  );
}

const s = {
  form: {
    width: "100%",
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
  },
  label: {
    width: "100%",
    display: "grid",
    gap: 8,
    marginBottom: 6,
  },
  labelText: {
    fontSize: 12,
    color: "rgba(255,255,255,.65)",
    letterSpacing: 0.4,
  },

  passwordWrap: {
    position: "relative",
    width: "100%",
    display: "flex",
    alignItems: "center",
  },

  eyeBtn: {
    position: "absolute",
    right: 12,
    top: "50%",
    width: 36,
    height: 36,
    display: "grid",
    placeItems: "center",
    borderRadius: 10,
    background: "rgba(15,23,42,.9)",
    border: "1px solid rgba(255,255,255,.10)",
    cursor: "pointer",
    transition: "all .15s ease",
  },

  submitBtn: {
    maxWidth: 320,
    width: "100%",
    alignSelf: "center",
    marginTop: 10,
  },

  link: {
    color: "#00f5d4",
    textDecoration: "none",
    fontWeight: 800,
  },
};
