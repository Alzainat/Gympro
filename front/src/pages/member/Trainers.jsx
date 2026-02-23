import { useEffect, useMemo, useState } from "react";
import api from "../../api/axios";
import { unwrap } from "../../api/unwrap";
import { theme, ui } from "../../theme/uiTheme";

export default function Trainers() {
  const [items, setItems] = useState([]);
  const [q, setQ] = useState("");
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  useEffect(() => {
    let alive = true;

    setLoading(true);
    setErr("");

    api
      .get("/trainers")
      .then((res) => {
        if (!alive) return;
        setItems(unwrap(res.data) ?? []);
      })
      .catch((e) => {
        if (!alive) return;
        setErr(e?.response?.data?.message || "Failed to load trainers.");
      })
      .finally(() => {
        if (!alive) return;
        setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, []);

  const filtered = useMemo(() => {
    const s = q.trim().toLowerCase();
    if (!s) return items;

    return items.filter((t) => {
      const name = (t.full_name ?? "").toLowerCase();
      const bio = (t.bio ?? "").toLowerCase();
      return name.includes(s) || bio.includes(s);
    });
  }, [items, q]);

  return (
    <div style={page.pageWrap}>
      {/* نفس جو auth pages */}
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={page.container}>
        <div style={page.headerRow}>
          <div>
            <h2 style={page.title}>Trainers</h2>
            <div style={page.subtitle}>
              Browse trainers, ratings, hourly rate, and bios.
            </div>
          </div>

          <div style={page.searchWrap}>
            <input
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="Search by name or bio..."
              style={page.searchInput}
            />
          </div>
        </div>

        {err ? <div style={ui.error}>{err}</div> : null}

        {loading ? (
          <div style={page.skeletonGrid}>
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} style={page.skeletonCard} />
            ))}
          </div>
        ) : filtered.length === 0 ? (
          <div style={page.empty}>
            <div style={page.emptyTitle}>No trainers found</div>
            <div style={page.emptySub}>Try a different search.</div>
          </div>
        ) : (
          <div style={page.grid}>
            {filtered.map((t) => (
              <div key={t.id} style={page.card}>
                <div style={page.cardTop}>
                  <div style={page.name}>{t.full_name}</div>

                  <div style={page.badge}>
                    <span style={page.badgeDot} />
                    {t.rating ?? "5.0"} ⭐
                  </div>
                </div>

                <div style={page.metaRow}>
                  <div style={page.metaItem}>
                    <div style={page.metaLabel}>Hourly</div>
                    <div style={page.metaValue}>
                      {t.hourly_rate ?? "-"}
                      {t.hourly_rate ? " / hr" : ""}
                    </div>
                  </div>

                  <div style={page.metaItem}>
                    <div style={page.metaLabel}>Experience</div>
                    <div style={page.metaValue}>{t.experience_years ?? "-"}</div>
                  </div>
                </div>

                <div style={page.bioLabel}>Bio</div>
                <div style={page.bio}>{t.bio ?? "-"}</div>

                {/* اختياري: زر */}
                {/* <button style={page.btn}>View Profile</button> */}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

const page = {
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

  headerRow: {
    display: "flex",
    gap: 14,
    alignItems: "flex-end",
    justifyContent: "space-between",
    flexWrap: "wrap",
    marginBottom: 16,
  },

  title: {
    margin: 0,
    fontSize: 26,
    fontWeight: 950,
    letterSpacing: 0.5,
    color: theme.colors.text,
  },

  subtitle: {
    marginTop: 6,
    color: theme.colors.textDim,
    fontSize: 14,
  },

  searchWrap: {
    minWidth: 260,
    flex: "0 1 360px",
  },

  searchInput: {
    ...ui.input,
    height: 44,
  },

  grid: {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
    gap: 14,
  },

  card: {
    background: theme.colors.card,
    border: `1px solid ${theme.colors.border}`,
    borderRadius: theme.radius.lg,
    padding: 16,
    boxShadow: theme.shadow.card,
    backdropFilter: "blur(16px)",
    color: theme.colors.text,
    transition: theme.motion.base,
  },

  cardTop: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 10,
    marginBottom: 10,
  },

  name: {
    fontWeight: 950,
    fontSize: 16,
    letterSpacing: 0.3,
  },

  badge: {
    display: "inline-flex",
    alignItems: "center",
    gap: 8,
    padding: "6px 10px",
    borderRadius: 999,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    fontSize: 13,
    whiteSpace: "nowrap",
  },

  badgeDot: {
    width: 8,
    height: 8,
    borderRadius: "50%",
    background: theme.gradients.dot,
    boxShadow: theme.shadow.glow,
  },

  metaRow: {
    display: "grid",
    gridTemplateColumns: "1fr 1fr",
    gap: 10,
    marginBottom: 12,
  },

  metaItem: {
    border: `1px solid ${theme.colors.border}`,
    background: theme.colors.surface,
    borderRadius: theme.radius.md,
    padding: 10,
  },

  metaLabel: {
    color: theme.colors.textFaint,
    fontSize: 12,
    marginBottom: 4,
  },

  metaValue: {
    fontWeight: 900,
    color: theme.colors.text,
    fontSize: 14,
  },

  bioLabel: {
    color: theme.colors.textFaint,
    fontSize: 12,
    marginBottom: 6,
  },

  bio: {
    color: theme.colors.textDim,
    fontSize: 14,
    lineHeight: 1.5,
    minHeight: 42,
  },

  btn: {
    marginTop: 12,
    width: "100%",
    padding: "12px 14px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: theme.gradients.primary,
    color: "#061018",
    fontWeight: 900,
    cursor: "pointer",
    boxShadow: theme.shadow.glow,
    transition: theme.motion.base,
  },

  // loading skeleton
  skeletonGrid: {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
    gap: 14,
  },

  skeletonCard: {
    height: 170,
    borderRadius: theme.radius.lg,
    border: `1px solid ${theme.colors.border}`,
    background:
      "linear-gradient(90deg, rgba(255,255,255,.04), rgba(255,255,255,.08), rgba(255,255,255,.04))",
    backgroundSize: "200% 100%",
    animation: "shimmer 1.2s ease-in-out infinite",
  },

  empty: {
    border: `1px solid ${theme.colors.border}`,
    background: theme.colors.card,
    borderRadius: theme.radius.lg,
    padding: 18,
    color: theme.colors.text,
  },

  emptyTitle: {
    fontWeight: 950,
    marginBottom: 6,
  },

  emptySub: {
    color: theme.colors.textDim,
    fontSize: 14,
  },
};