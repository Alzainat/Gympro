import { useEffect, useMemo, useState } from "react";
import api from "../../api/axios";
import { unwrap } from "../../api/unwrap";
import { theme, ui } from "../../theme/uiTheme";

export default function Booking() {
  const [trainers, setTrainers] = useState([]);
  const [trainerId, setTrainerId] = useState("");
  const [sessions, setSessions] = useState([]); // available
  const [bookedSessions, setBookedSessions] = useState([]); // booked (all pages)
  const [msg, setMsg] = useState("");
  const [loading, setLoading] = useState(false);
  const [loadingBooked, setLoadingBooked] = useState(false);

  // ✅ Set of booked session ids (source of truth for UI منع التكرار)
  const bookedIdSet = useMemo(() => {
    return new Set(bookedSessions.map((s) => String(s.id)));
  }, [bookedSessions]);

  const goChat = (trainer_id) => {
    if (!trainer_id) {
      setMsg("⚠️ Trainer not found for this session.");
      return;
    }
    window.location.href = `/member/chat?trainerId=${trainer_id}`;
  };

  // ✅ helper: normalize booked item to session shape + inject trainer info
  const normalizeBookedSession = (bookingRow) => {
    const s = bookingRow?.session || bookingRow?.training_session || bookingRow;
    if (!s) return null;

    const trainer = s?.trainer;
    const trainer_id = trainer?.trainer_id ?? s?.trainer_id ?? bookingRow?.trainer_id;

    return {
      ...s,
      trainer_id,
      trainer_name: trainer?.full_name,
      trainer_avatar: trainer?.avatar_url,
      booking_status: bookingRow?.status,
      booked_at: bookingRow?.booked_at,
    };
  };

  // 0) ✅ جيب كل booked sessions من السيرفر (كل الصفحات)
  useEffect(() => {
    (async () => {
      setLoadingBooked(true);
      try {
        let url = "/member/bookings";
        const collected = [];

        while (url) {
          const res = await api.get(url);
          const pageRows = res.data?.data ?? unwrap(res.data);
          if (Array.isArray(pageRows)) collected.push(...pageRows);

          const next = res.data?.next_page_url;
          if (!next) {
            url = null;
          } else {
            const idx = next.indexOf("/api/");
            url = idx !== -1 ? next.slice(idx + 4) : next;
            if (url.startsWith("http")) url = null;
            if (url && !url.startsWith("/")) url = "/" + url;
          }
        }

        const sessionsFromBookings = collected.map(normalizeBookedSession).filter(Boolean);

        const map = new Map(sessionsFromBookings.map((x) => [x.id, x]));
        const unique = Array.from(map.values()).sort(
          (a, b) => new Date(a.session_date) - new Date(b.session_date)
        );

        setBookedSessions(unique);
      } catch (e) {
        setMsg(e?.response?.data?.message || "❌ Failed to load booked sessions");
      } finally {
        setLoadingBooked(false);
      }
    })();
  }, []);

  // 1) جيب كل المدربين
  useEffect(() => {
    api.get("/trainers").then((res) => setTrainers(unwrap(res.data) ?? []));
  }, []);

  // 2) بعد ما ييجوا المدربين: جيب sessions لكل مدرب واجمعهم
  useEffect(() => {
    if (trainers.length === 0) return;

    (async () => {
      setLoading(true);
      setMsg("");

      try {
        const results = await Promise.allSettled(
          trainers.map((t) => api.get(`/trainers/${t.trainer_id}/sessions`))
        );

        const all = results
          .filter((r) => r.status === "fulfilled")
          .flatMap((r) => unwrap(r.value.data) ?? []);

        const map = new Map(all.map((x) => [x.id, x]));
        let unique = Array.from(map.values());

        // ✅ شيل اللي booked أصلاً
        unique = unique.filter((s) => !bookedIdSet.has(String(s.id)));

        unique.sort((a, b) => new Date(a.session_date) - new Date(b.session_date));
        setSessions(unique);
      } catch (e) {
        setMsg(e?.response?.data?.message || "❌ Failed to load sessions");
      } finally {
        setLoading(false);
      }
    })();
  }, [trainers, bookedIdSet]);

  // فلترة available حسب المدرب
  const visibleSessions = useMemo(() => {
    if (!trainerId) return sessions;
    return sessions.filter((x) => String(x.trainer_id) === String(trainerId));
  }, [sessions, trainerId]);

  // فلترة booked حسب المدرب
  const visibleBooked = useMemo(() => {
    if (!trainerId) return bookedSessions;
    return bookedSessions.filter((x) => String(x.trainer_id) === String(trainerId));
  }, [bookedSessions, trainerId]);

  const book = async (session) => {
    const sid = String(session.id);

    if (bookedIdSet.has(sid)) {
      setMsg("⚠️ You already booked this session.");
      return;
    }

    setMsg("");
    try {
      await api.post("/member/bookings/sessions", { session_id: session.id });

      setBookedSessions((prev) => {
        const map = new Map(prev.map((x) => [x.id, x]));
        map.set(session.id, session);
        const arr = Array.from(map.values());
        arr.sort((a, b) => new Date(a.session_date) - new Date(b.session_date));
        return arr;
      });

      setSessions((prev) => prev.filter((x) => String(x.id) !== sid));

      setMsg("✅ Booked successfully.");
    } catch (e) {
      setMsg(e?.response?.data?.message || "❌ Booking failed");
    }
  };

  return (
    <div style={p.pageWrap}>
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={p.container}>
        {/* Header */}
        <div style={p.headerRow}>
          <div>
            <h2 style={p.title}>Booking</h2>
            <div style={p.subtitle}>
              Choose a trainer, browse available sessions, and manage your booked ones.
            </div>
          </div>

          <div style={p.filterWrap}>
            <label style={p.label}>Trainer</label>

            <select
              style={p.select}
              value={trainerId}
              onChange={(e) => setTrainerId(e.target.value)}
            >
              <option value="">All trainers</option>
              {trainers.map((t) => (
                <option key={t.trainer_id} value={t.trainer_id}>
                  {t.full_name}
                </option>
              ))}
            </select>
          </div>
        </div>

        {msg ? <div style={p.toast}>{msg}</div> : null}

        {/* BOOKED */}
        <div style={p.section}>
          <div style={p.sectionHeader}>
            <h3 style={p.h3}>Booked sessions</h3>
            {loadingBooked ? <span style={p.miniNote}>Loading...</span> : null}
          </div>

          {loadingBooked ? (
            <div style={p.skeletonGrid}>
              {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} style={p.skeletonItem} />
              ))}
            </div>
          ) : visibleBooked.length === 0 ? (
            <div style={p.empty}>
              <div style={p.emptyTitle}>No booked sessions yet</div>
              <div style={p.emptySub}>Book your first session from the list below.</div>
            </div>
          ) : (
            <div style={p.list}>
              {visibleBooked.map((x) => (
                <div key={x.id} style={p.itemBooked}>
                  <div style={{ minWidth: 0 }}>
                    <div style={p.itemTitleRow}>
                      <div style={p.itemTitle}>{x.title}</div>
                      <div style={p.badgeBooked}>BOOKED</div>
                    </div>

                    <div style={p.itemSub}>
                      {new Date(x.session_date).toLocaleString()} • {x.duration_minutes} min
                    </div>

                    <div style={p.metaLine}>
                      Trainer:{" "}
                      <b style={p.bold}>
                        {x.trainer_name || x.trainer?.full_name || "—"}
                      </b>
                      {x.booking_status ? (
                        <>
                          {" "}
                          • Status: <b style={p.bold}>{x.booking_status}</b>
                        </>
                      ) : null}
                    </div>
                  </div>

                  <div style={p.actions}>
                    <button style={p.chatBtn} onClick={() => goChat(x.trainer_id)}>
                      Chat
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* AVAILABLE */}
        <div style={p.section}>
          <div style={p.sectionHeader}>
            <h3 style={p.h3}>Available sessions</h3>
            {loading ? <span style={p.miniNote}>Loading...</span> : null}
          </div>

          {loading ? (
            <div style={p.skeletonGrid}>
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} style={p.skeletonItem} />
              ))}
            </div>
          ) : visibleSessions.length === 0 ? (
            <div style={p.empty}>
              <div style={p.emptyTitle}>No sessions found</div>
              <div style={p.emptySub}>Try selecting a different trainer.</div>
            </div>
          ) : (
            <div style={p.list}>
              {visibleSessions.map((x) => {
                const disabled = bookedIdSet.has(String(x.id));
                return (
                  <div key={x.id} style={p.item}>
                    <div style={{ minWidth: 0 }}>
                      <div style={p.itemTitleRow}>
                        <div style={p.itemTitle}>{x.title}</div>
                        <div style={p.badgeAvailable}>AVAILABLE</div>
                      </div>

                      <div style={p.itemSub}>
                        {new Date(x.session_date).toLocaleString()} • {x.duration_minutes} min
                      </div>
                    </div>

                    <div style={p.actions}>
                      <button
                        style={{
                          ...p.chatBtn,
                          opacity: x.trainer_id ? 1 : 0.6,
                          cursor: x.trainer_id ? "pointer" : "not-allowed",
                        }}
                        onClick={() => goChat(x.trainer_id)}
                        disabled={!x.trainer_id}
                      >
                        Chat
                      </button>

                      <button
                        style={{
                          ...p.bookBtn(disabled),
                        }}
                        disabled={disabled}
                        onClick={() => book(x)}
                      >
                        {disabled ? "Booked" : "Book"}
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
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

  filterWrap: {
    minWidth: 260,
    flex: "0 1 360px",
    display: "grid",
    gap: 8,
  },

  label: {
    color: theme.colors.textFaint,
    fontSize: 12,
    fontWeight: 800,
    letterSpacing: 0.6,
  },

  select: {
    ...ui.input,
    appearance: "none",
    WebkitAppearance: "none",
    MozAppearance: "none",
    cursor: "pointer",
    height: 44,
  },

  toast: {
    marginTop: 10,
    marginBottom: 6,
    padding: "10px 14px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.border}`,
    background: theme.colors.surface,
    color: theme.colors.textDim,
  },

  section: {
    marginTop: 18,
  },

  sectionHeader: {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    gap: 12,
    marginBottom: 10,
  },

  h3: {
    margin: 0,
    fontSize: 16,
    fontWeight: 950,
    letterSpacing: 0.3,
    color: theme.colors.text,
  },

  miniNote: {
    color: theme.colors.textDim,
    fontSize: 13,
  },

  list: {
    display: "grid",
    gap: 12,
  },

  itemBase: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 12,
    padding: 14,
    borderRadius: theme.radius.lg,
    border: `1px solid ${theme.colors.border}`,
    background: theme.colors.card,
    backdropFilter: "blur(16px)",
    boxShadow: theme.shadow.card,
  },

  item: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 12,
    padding: 14,
    borderRadius: theme.radius.lg,
    border: `1px solid ${theme.colors.border}`,
    background: theme.colors.card,
    backdropFilter: "blur(16px)",
    boxShadow: theme.shadow.card,
  },

  itemBooked: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 12,
    padding: 14,
    borderRadius: theme.radius.lg,
    border: `1px solid rgba(34,197,94,.30)`,
    background: "rgba(17,24,39,.60)",
    backdropFilter: "blur(16px)",
    boxShadow: theme.shadow.card,
  },

  itemTitleRow: {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    gap: 10,
    marginBottom: 6,
  },

  itemTitle: {
    fontWeight: 950,
    fontSize: 15,
    color: theme.colors.text,
    overflow: "hidden",
    textOverflow: "ellipsis",
    whiteSpace: "nowrap",
  },

  itemSub: {
    color: theme.colors.textDim,
    fontSize: 14,
  },

  metaLine: {
    marginTop: 8,
    color: theme.colors.textDim,
    fontSize: 13,
  },

  bold: {
    color: theme.colors.text,
  },

  actions: {
    display: "flex",
    gap: 10,
    alignItems: "center",
    flexShrink: 0,
  },

  chatBtn: {
    padding: "10px 12px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    fontWeight: 900,
    background: "rgba(0,245,212,.10)",
    color: theme.colors.primary,
    cursor: "pointer",
    transition: theme.motion.base,
    boxShadow: "0 10px 26px rgba(0,245,212,.10)",
  },

  bookBtn: (disabled) => ({
    padding: "10px 12px",
    borderRadius: theme.radius.md,
    border: "none",
    fontWeight: 950,
    background: theme.gradients.primary,
    color: "#061018",
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.6 : 1,
    transition: theme.motion.base,
    boxShadow: disabled ? "none" : theme.shadow.glow,
  }),

  badgeBase: {
    padding: "6px 10px",
    borderRadius: 999,
    fontWeight: 950,
    fontSize: 11,
    letterSpacing: 0.6,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    whiteSpace: "nowrap",
  },

  badgeBooked: {
    padding: "6px 10px",
    borderRadius: 999,
    fontWeight: 950,
    fontSize: 11,
    letterSpacing: 0.6,
    border: "1px solid rgba(34,197,94,.35)",
    background: "rgba(34,197,94,.15)",
    color: "#86efac",
    whiteSpace: "nowrap",
  },

  badgeAvailable: {
    padding: "6px 10px",
    borderRadius: 999,
    fontWeight: 950,
    fontSize: 11,
    letterSpacing: 0.6,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    whiteSpace: "nowrap",
  },

  // Skeletons
  skeletonGrid: {
    display: "grid",
    gap: 12,
  },

  skeletonItem: {
    height: 78,
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
    padding: 16,
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