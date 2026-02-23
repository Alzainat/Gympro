import { useEffect, useMemo, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { theme, ui as UI } from "../../theme/uiTheme";
import { logout } from "../../services/authService";

export default function Home() {
  const navigate = useNavigate();

  // Parallax refs
  const heroRef = useRef(null);
  const glowRef = useRef(null);

  useEffect(() => {
    // Scroll reveal
    const items = document.querySelectorAll("[data-reveal]");
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) e.target.classList.add("reveal-in");
        });
      },
      { threshold: 0.15 }
    );
    items.forEach((el) => io.observe(el));
    return () => io.disconnect();
  }, []);

  const handleLogout = async () => {
    try {
      await logout(); // ✅ POST /logout + clear token + setAuthToken(null)
    } finally {
      // ✅ always redirect
      navigate("/login", { replace: true });
    }
  };

  // ✅ display only (no tracking/progress features on UI)
  const stats = useMemo(
    () => [
      { k: "4.9/5", v: "Member rating" },
      { k: "120+", v: "Programs & classes" },
      { k: "24/7", v: "Portal access" },
      { k: "Mon–Sat", v: "Open hours" },
    ],
    []
  );

  // Parallax mouse move
  useEffect(() => {
    const el = heroRef.current;
    const glow = glowRef.current;
    if (!el) return;

    let raf = 0;

    const onMove = (e) => {
      const r = el.getBoundingClientRect();
      const px = (e.clientX - r.left) / r.width; // 0..1
      const py = (e.clientY - r.top) / r.height; // 0..1
      const dx = (px - 0.5) * 2; // -1..1
      const dy = (py - 0.5) * 2; // -1..1

      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(() => {
        el.style.setProperty("--mx", `${px * 100}%`);
        el.style.setProperty("--my", `${py * 100}%`);
        el.style.setProperty("--rx", `${(-dy * 3).toFixed(2)}deg`);
        el.style.setProperty("--ry", `${(dx * 4).toFixed(2)}deg`);

        if (glow) glow.style.transform = `translate(${dx * 10}px, ${dy * 10}px)`;
      });
    };

    const onLeave = () => {
      cancelAnimationFrame(raf);
      el.style.setProperty("--rx", `0deg`);
      el.style.setProperty("--ry", `0deg`);
      el.style.setProperty("--mx", `50%`);
      el.style.setProperty("--my", `20%`);
      if (glow) glow.style.transform = `translate(0px, 0px)`;
    };

    el.addEventListener("mousemove", onMove);
    el.addEventListener("mouseleave", onLeave);
    return () => {
      el.removeEventListener("mousemove", onMove);
      el.removeEventListener("mouseleave", onLeave);
      cancelAnimationFrame(raf);
    };
  }, []);

  const features = [
    {
      icon: "📅",
      title: "Smart Scheduling",
      desc: "Book classes quickly, get reminders, and manage your week with ease.",
    },
    {
      icon: "🏋️",
      title: "Workout Programs",
      desc: "Structured plans built by coaches — strength, hypertrophy, fat loss, mobility.",
    },
    {
      icon: "🥗",
      title: "Nutrition Guidance",
      desc: "Simple nutrition logging and guidance to support your training goals.",
    },
    {
      icon: "💳",
      title: "Payments & Invoices",
      desc: "View subscriptions, invoices, and payment history in one place.",
    },
    {
      icon: "🧑‍🏫",
      title: "Trainer Directory",
      desc: "Browse trainers, specialties, and request a 1:1 coaching session.",
    },
    {
      icon: "💬",
      title: "Member Chat",
      desc: "Message support or your trainer directly — fast and convenient.",
    },
  ];

  const testimonials = [
    {
      name: "Olivia Carter",
      role: "Member • 8 months",
      quote:
        "The portal feels premium and focused — booking and programs are always easy to find.",
      score: "★★★★★",
    },
    {
      name: "Daniel Reed",
      role: "Member • 1 year",
      quote:
        "Everything is clean and organized. It genuinely makes the gym experience smoother.",
      score: "★★★★★",
    },
    {
      name: "Maya Johnson",
      role: "Member • 5 months",
      quote:
        "Fast, modern, and simple. I love how everything I need is in one place.",
      score: "★★★★★",
    },
  ];

  const s = {
    page: {
      ...UI.page, // ✅ same background as auth pages
      color: theme.colors.text,
      fontFamily:
        'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"',
    },

    container: {
      width: `min(${theme.layout.contentMax}px, calc(100% - 40px))`,
      margin: "0 auto",
      padding: 22,
      position: "relative",
      zIndex: 1,
      boxSizing: "border-box",
    },

    topRow: {
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 12,
      marginBottom: 14,
    },
    titleWrap: { display: "grid", gap: 4 },
    pageTitle: { margin: 0, fontSize: 22, fontWeight: 980, letterSpacing: 0.2 },
    pageSub: { margin: 0, color: theme.colors.textDim, fontSize: 13 },

    logoutBtn: {
      cursor: "pointer",
      padding: "10px 12px",
      borderRadius: theme.radius.md,
      border: `1px solid ${theme.colors.borderSoft}`,
      background: "rgba(255,255,255,0.06)",
      color: "rgba(255,255,255,0.90)",
      fontWeight: 900,
      fontSize: 13,
      transition: theme.motion.base,
      whiteSpace: "nowrap",
    },

    section: { padding: "18px 0" },

    // Ultra Premium Hero (full width)
    heroFull: {
      width: "100%",
      borderRadius: theme.radius.lg,
      border: `1px solid ${theme.colors.borderSoft}`,
      overflow: "hidden",
      position: "relative",
      minHeight: 420,
      boxShadow: theme.shadow.card,
      backgroundImage:
        `linear-gradient(135deg, rgba(5,7,13,0.86), rgba(5,7,13,0.52)),` +
        `url("https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=2200&q=60")`,
      backgroundSize: "cover",
      backgroundPosition: "center",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      padding: 34,
      boxSizing: "border-box",
      transformStyle: "preserve-3d",
      transform: "perspective(900px) rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg))",
    },

    heroInner: {
      width: "100%",
      maxWidth: 860,
      textAlign: "center",
      position: "relative",
      zIndex: 2,
      transform: "translateZ(12px)",
    },

    pill: {
      display: "inline-flex",
      alignItems: "center",
      gap: 10,
      padding: "8px 12px",
      borderRadius: 999,
      border: `1px solid ${theme.colors.borderSoft}`,
      background: "rgba(0,0,0,0.35)",
      fontSize: 12,
      color: "rgba(255,255,255,0.82)",
      fontWeight: 800,
    },

    heroTitle: {
      margin: "16px 0 0",
      fontSize: 40,
      lineHeight: 1.04,
      letterSpacing: -0.7,
      fontWeight: 990,
    },

    heroText: {
      margin: "12px auto 0",
      color: theme.colors.textDim,
      fontSize: 14,
      lineHeight: 1.85,
      maxWidth: 760,
    },

    actions: { display: "flex", gap: 10, marginTop: 18, flexWrap: "wrap", justifyContent: "center" },

    btnPrimary: {
      cursor: "pointer",
      padding: "12px 14px",
      borderRadius: theme.radius.md,
      border: "none",
      background: theme.gradients.primary,
      color: "#061018",
      fontWeight: 950,
      fontSize: 13,
      transition: theme.motion.base,
      boxShadow: theme.shadow.glow,
      minWidth: 160,
    },

    btnGhost: {
      cursor: "pointer",
      padding: "12px 14px",
      borderRadius: theme.radius.md,
      border: `1px solid ${theme.colors.borderSoft}`,
      background: "rgba(255,255,255,0.06)",
      color: "rgba(255,255,255,0.92)",
      fontWeight: 900,
      fontSize: 13,
      transition: theme.motion.base,
      minWidth: 160,
    },

    // Stat chips
    chips: {
      marginTop: 18,
      display: "flex",
      gap: 10,
      flexWrap: "wrap",
      justifyContent: "center",
    },
    chip: {
      padding: "10px 12px",
      borderRadius: 999,
      border: `1px solid ${theme.colors.borderSoft}`,
      background: "rgba(0,0,0,0.28)",
      display: "grid",
      gap: 2,
      minWidth: 160,
      boxSizing: "border-box",
    },
    chipK: { fontSize: 12, fontWeight: 950, color: "rgba(255,255,255,0.92)" },
    chipV: { fontSize: 12, fontWeight: 900, color: theme.colors.textDim },

    // Sections
    sectionTitle: { margin: 0, fontSize: 20, fontWeight: 950, letterSpacing: 0.2 },
    sectionSub: {
      margin: "8px 0 0",
      color: theme.colors.textDim,
      fontSize: 13,
      lineHeight: 1.7,
      maxWidth: 900,
    },

    // Cards
    card: {
      background: theme.colors.card,
      border: `1px solid ${theme.colors.borderSoft}`,
      borderRadius: theme.radius.lg,
      padding: 16,
      boxShadow: theme.shadow.card,
      backdropFilter: "blur(20px)",
      position: "relative",
      overflow: "hidden",
      boxSizing: "border-box",
    },

    grid3: {
      display: "grid",
      gridTemplateColumns: "repeat(3, minmax(0, 1fr))",
      gap: 14,
      marginTop: 18,
    },

    featureCard: {
      background: theme.colors.card,
      border: `1px solid ${theme.colors.borderSoft}`,
      borderRadius: theme.radius.lg,
      padding: 16,
      boxShadow: theme.shadow.card,
      transition: theme.motion.base,
      position: "relative",
      overflow: "hidden",
      boxSizing: "border-box",
    },
    icon: { fontSize: 22, marginBottom: 10 },
    fTitle: { margin: 0, fontSize: 14, fontWeight: 950, letterSpacing: 0.2 },
    fDesc: {
      margin: "8px 0 0",
      color: theme.colors.textDim,
      fontSize: 13,
      lineHeight: 1.7,
    },

    // Testimonials
    tGrid: {
      display: "grid",
      gridTemplateColumns: "repeat(3, minmax(0, 1fr))",
      gap: 14,
      marginTop: 18,
    },
    quote: { margin: "10px 0 0", color: "rgba(255,255,255,0.78)", lineHeight: 1.7, fontSize: 13 },
    person: {
      marginTop: 12,
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
    },
    personLeft: { display: "flex", alignItems: "center", gap: 10 },
    avatar: {
      width: 36,
      height: 36,
      borderRadius: 12,
      background: "rgba(255,255,255,0.08)",
      border: `1px solid ${theme.colors.borderSoft}`,
      display: "grid",
      placeItems: "center",
      fontWeight: 900,
    },
    role: { margin: 0, color: theme.colors.textDim, fontSize: 12 },

    // Contact
    formWrap: {
      marginTop: 18,
      display: "grid",
      gridTemplateColumns: "1.05fr 0.95fr",
      gap: 14,
      alignItems: "start",
    },
    label: { fontSize: 12, color: theme.colors.textDim, fontWeight: 800, marginBottom: 8 },
    input: {
      width: "100%",
      padding: "12px 12px",
      borderRadius: theme.radius.md,
      border: `1px solid ${theme.colors.border}`,
      background: theme.colors.surface,
      color: theme.colors.text,
      outline: "none",
      fontSize: 13,
      boxSizing: "border-box",
      transition: theme.motion.base,
    },
    textarea: {
      width: "100%",
      padding: "12px 12px",
      borderRadius: theme.radius.md,
      border: `1px solid ${theme.colors.border}`,
      background: theme.colors.surface,
      color: theme.colors.text,
      outline: "none",
      fontSize: 13,
      minHeight: 130,
      resize: "vertical",
      boxSizing: "border-box",
      transition: theme.motion.base,
    },
    formRow: { display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 },
    submit: {
      cursor: "pointer",
      padding: "12px 12px",
      borderRadius: theme.radius.md,
      border: "none",
      background: theme.gradients.primary,
      color: "#061018",
      fontWeight: 950,
      fontSize: 13,
      width: "100%",
      boxShadow: theme.shadow.glow,
      transition: theme.motion.base,
    },

    helpList: { marginTop: 10, display: "grid", gap: 10 },
    helpItem: {
      border: `1px solid ${theme.colors.border}`,
      background: "rgba(0,0,0,0.18)",
      borderRadius: theme.radius.md,
      padding: "10px 12px",
      display: "flex",
      gap: 10,
      alignItems: "flex-start",
    },
    helpIcon: { fontSize: 16, marginTop: 1 },
    helpText: { display: "grid", gap: 4 },
    helpTitle: { margin: 0, fontWeight: 950, fontSize: 13 },
    helpSub: { margin: 0, color: theme.colors.textDim, fontSize: 12, lineHeight: 1.6 },

    footer: {
      padding: "22px 0 34px",
      color: theme.colors.textFaint,
      fontSize: 12,
      textAlign: "center",
      borderTop: `1px solid ${theme.colors.border}`,
      marginTop: 10,
    },
  };

  return (
    <div style={s.page}>
      {/* ✅ auth-like decorative background */}
      <div style={UI.bgGrid} />
      <div style={UI.glowTop} />
      <div style={UI.glowBottom} />

      <style>{`
        [data-reveal]{ opacity: 0; transform: translateY(16px); transition: all 700ms ease; }
        .reveal-in{ opacity: 1 !important; transform: translateY(0) !important; }

        .hoverUp:hover{ transform: translateY(-3px); border-color: rgba(0,245,212,.28) !important; }
        .btnHover:hover{ transform: translateY(-1px); filter: brightness(1.05); }
        .focusable:focus{ border-color: rgba(0,245,212,.40) !important; box-shadow: 0 0 0 3px rgba(0,245,212,.12); }

        /* Ultra premium glow border + floating gradients */
        .heroGlow::before{
          content:"";
          position:absolute;
          inset:-2px;
          border-radius: ${theme.radius.lg}px;
          background: conic-gradient(
            from 180deg at 50% 50%,
            rgba(0,245,212,.18),
            rgba(124,58,237,.18),
            rgba(0,245,212,.18)
          );
          filter: blur(10px);
          opacity: .65;
          z-index: 0;
          animation: spinGlow 10s linear infinite;
          pointer-events:none;
        }

        .heroGlow::after{
          content:"";
          position:absolute;
          inset:0;
          border-radius: ${theme.radius.lg}px;
          background:
            radial-gradient(900px 420px at var(--mx, 50%) var(--my, 20%), rgba(0,245,212,.16), transparent 55%),
            radial-gradient(700px 420px at 15% 85%, rgba(124,58,237,.12), transparent 55%),
            radial-gradient(900px 600px at 90% 20%, rgba(255,255,255,.06), transparent 60%);
          z-index: 1;
          pointer-events:none;
        }

        @keyframes spinGlow {
          0%{ transform: rotate(0deg); }
          100%{ transform: rotate(360deg); }
        }

        @media (max-width: 980px){
          .grid3{ grid-template-columns: 1fr !important; }
          .tGrid3{ grid-template-columns: 1fr !important; }
          .formGrid{ grid-template-columns: 1fr !important; }
        }
      `}</style>

      <div style={s.container}>
        {/* Top row (title + logout) */}
        <div style={s.topRow} data-reveal>
          <div style={s.titleWrap}>
            <h1 style={s.pageTitle}>Member Home</h1>
            <p style={s.pageSub}>Everything you need — in one premium dashboard.</p>
          </div>

          <button className="btnHover" style={s.logoutBtn} onClick={handleLogout} type="button">
            Logout
          </button>
        </div>

        {/* ✅ HERO FULL WIDTH 100% */}
        <section style={{ ...s.section, paddingTop: 8 }}>
          <div ref={heroRef} data-reveal className="heroGlow" style={s.heroFull}>
            {/* parallax blob */}
            <div
              ref={glowRef}
              style={{
                position: "absolute",
                width: 520,
                height: 520,
                borderRadius: "50%",
                background: theme.gradients.glow,
                filter: "blur(90px)",
                top: -220,
                right: -240,
                opacity: 0.85,
                pointerEvents: "none",
                zIndex: 1,
              }}
            />

            <div style={s.heroInner}>
              <div style={s.pill}>✨ Ultra Premium Experience</div>

              <h2 style={s.heroTitle}>Welcome to your Member Portal</h2>

              <p style={s.heroText}>
                Manage bookings, discover programs, connect with trainers, and get support —
                all from one clean, modern, and lightning-fast experience.
              </p>

              <div style={s.actions}>
                <button
                  className="btnHover"
                  style={s.btnPrimary}
                  type="button"
                  onClick={() => navigate("/member/booking")}
                >
                  Book a Class
                </button>

                <button
                  className="btnHover"
                  style={s.btnGhost}
                  type="button"
                  onClick={() => navigate("/member/workouts")}
                >
                  Explore Programs
                </button>
              </div>

              <div style={s.chips}>
                {stats.map((x) => (
                  <div key={x.v} style={s.chip}>
                    <div style={s.chipK}>{x.k}</div>
                    <div style={s.chipV}>{x.v}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </section>

        {/* FEATURES */}
        <section style={s.section}>
          <div data-reveal>
            <h2 style={s.sectionTitle}>Platform Features</h2>
            <p style={s.sectionSub}>
              A premium experience built to manage bookings, payments, trainers, and support.
            </p>
          </div>

          <div className="grid3" style={s.grid3}>
            {features.map((f) => (
              <div key={f.title} data-reveal className="hoverUp" style={s.featureCard}>
                <div style={s.icon}>{f.icon}</div>
                <h3 style={s.fTitle}>{f.title}</h3>
                <p style={s.fDesc}>{f.desc}</p>
              </div>
            ))}
          </div>
        </section>

        {/* TESTIMONIALS */}
        <section style={s.section}>
          <div data-reveal>
            <h2 style={s.sectionTitle}>What Members Say</h2>
            <p style={s.sectionSub}>
              Real feedback from members who love the clean and premium experience.
            </p>
          </div>

          <div className="tGrid3" style={s.tGrid}>
            {testimonials.map((t) => (
              <div key={t.name} data-reveal className="hoverUp" style={s.card}>
                <div style={{ display: "flex", justifyContent: "space-between", gap: 10 }}>
                  <div style={{ fontWeight: 950, fontSize: 14 }}>{t.score}</div>
                  <div style={{ color: theme.colors.textFaint, fontSize: 12, fontWeight: 900 }}>
                    Verified
                  </div>
                </div>

                <p style={s.quote}>“{t.quote}”</p>

                <div style={s.person}>
                  <div style={s.personLeft}>
                    <div style={s.avatar}>{t.name.slice(0, 1)}</div>
                    <div>
                      <div style={{ fontWeight: 950, fontSize: 13 }}>{t.name}</div>
                      <p style={s.role}>{t.role}</p>
                    </div>
                  </div>
                  <div style={{ color: theme.colors.textFaint, fontSize: 12, fontWeight: 900 }}>★</div>
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* CONTACT */}
        <section style={s.section}>
          <div data-reveal>
            <h2 style={s.sectionTitle}>Contact Us</h2>
            <p style={s.sectionSub}>Send a message and our team will respond as soon as possible.</p>
          </div>

          <div className="formGrid" style={s.formWrap}>
            {/* Form */}
            <div data-reveal style={s.card}>
              <div style={s.formRow}>
                <div>
                  <div style={s.label}>First name</div>
                  <input className="focusable" style={s.input} placeholder="John" />
                </div>
                <div>
                  <div style={s.label}>Last name</div>
                  <input className="focusable" style={s.input} placeholder="Doe" />
                </div>
              </div>

              <div style={{ marginTop: 10 }}>
                <div style={s.label}>Email</div>
                <input className="focusable" style={s.input} placeholder="john@example.com" />
              </div>

              <div style={{ marginTop: 10 }}>
                <div style={s.label}>Message</div>
                <textarea className="focusable" style={s.textarea} placeholder="Tell us how we can help..." />
              </div>

              <div style={{ marginTop: 12, display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
                <button className="btnHover" style={s.submit} type="button">
                  Send Message
                </button>
                <div style={{ color: theme.colors.textFaint, fontSize: 12, fontWeight: 800 }}>
                  Response time: within 24 hours
                </div>
              </div>
            </div>

            {/* Support Center */}
            <div data-reveal style={s.card}>
              <div style={{ fontWeight: 980, fontSize: 14 }}>Support Center</div>
              <p style={{ margin: "10px 0 0", color: theme.colors.textDim, fontSize: 13, lineHeight: 1.7 }}>
                Choose a category and we’ll guide you quickly.
              </p>

              <div style={s.helpList}>
                <div style={s.helpItem}>
                  <div style={s.helpIcon}>🧾</div>
                  <div style={s.helpText}>
                    <p style={s.helpTitle}>Billing & Membership</p>
                    <p style={s.helpSub}>Invoices, payments, plan changes, membership pause.</p>
                  </div>
                </div>

                <div style={s.helpItem}>
                  <div style={s.helpIcon}>📅</div>
                  <div style={s.helpText}>
                    <p style={s.helpTitle}>Bookings & Classes</p>
                    <p style={s.helpSub}>Reservations, cancellations, schedule questions.</p>
                  </div>
                </div>

                <div style={s.helpItem}>
                  <div style={s.helpIcon}>🧑‍🏫</div>
                  <div style={s.helpText}>
                    <p style={s.helpTitle}>Coaching</p>
                    <p style={s.helpSub}>Trainer requests, programs, technique help.</p>
                  </div>
                </div>

                <div style={s.helpItem}>
                  <div style={s.helpIcon}>⏰</div>
                  <div style={s.helpText}>
                    <p style={s.helpTitle}>Working Hours</p>
                    <p style={s.helpSub}>Mon–Sat, 8:00 AM – 10:00 PM • Downtown Branch</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <div style={s.footer}>© {new Date().getFullYear()} IronPulse Gym • All rights reserved.</div>
      </div>
    </div>
  );
}
