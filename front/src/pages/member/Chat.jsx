import { useEffect, useMemo, useRef, useState } from "react";
import { getInbox, getThread, markRead, sendMessage } from "../../services/chatService";
import { unwrap } from "../../api/unwrap";
import { useAuth } from "../../context/AuthContext";
import { theme, ui } from "../../theme/uiTheme";

export default function Chat() {
  const { profile } = useAuth();
  const [inbox, setInbox] = useState([]);
  const [activeTrainer, setActiveTrainer] = useState(null);
  const [thread, setThread] = useState([]);
  const [text, setText] = useState("");
  const [loadingThread, setLoadingThread] = useState(false);
  const [err, setErr] = useState("");

  const bottomRef = useRef(null);

  const loadInbox = async () => {
    const res = await getInbox();
    setInbox(unwrap(res.data) ?? []);
  };

  const openTrainer = async (trainer) => {
    setActiveTrainer(trainer);
    setErr("");
    setLoadingThread(true);
    try {
      const res = await getThread(trainer.trainer_id);
      setThread(unwrap(res.data) ?? []);
      await markRead(trainer.trainer_id);
      await loadInbox(); // تحديث unread
    } catch (e) {
      setErr(e?.response?.data?.message || "Failed to load chat.");
    } finally {
      setLoadingThread(false);
    }
  };

  useEffect(() => {
    loadInbox().catch(() => {});
  }, []);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [thread, activeTrainer]);

  const sortedInbox = useMemo(() => {
    return [...inbox].sort((a, b) => {
      const da = new Date(a?.last_message?.sent_at || 0).getTime();
      const db = new Date(b?.last_message?.sent_at || 0).getTime();
      return db - da;
    });
  }, [inbox]);

  const onSend = async () => {
    const content = text.trim();
    if (!content || !activeTrainer) return;

    setText("");
    setErr("");

    // optimistic
    const temp = {
      id: `tmp-${Date.now()}`,
      sender_id: profile?.id,
      receiver_id: activeTrainer.trainer_id,
      content,
      sent_at: new Date().toISOString(),
      is_read: true,
    };
    setThread((p) => [...p, temp]);

    try {
      const res = await sendMessage(activeTrainer.trainer_id, content);
      const newMsg = res.data;
      setThread((p) => {
        const withoutTemp = p.filter((x) => x.id !== temp.id);
        return [...withoutTemp, newMsg];
      });
      await loadInbox();
    } catch (e) {
      setErr(e?.response?.data?.message || "Send failed.");
    }
  };

  return (
    <div style={p.pageWrap}>
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={p.container}>
        <div style={p.wrap}>
          {/* LEFT: inbox */}
          <div style={p.left}>
            <div style={p.leftHeader}>
              <div>
                <div style={p.leftTitle}>Chat</div>
                <div style={p.leftSub}>Your trainers inbox</div>
              </div>
              <button style={p.refreshBtn} onClick={() => loadInbox().catch(() => {})}>
                Refresh
              </button>
            </div>

            {sortedInbox.length === 0 ? (
              <div style={p.mutedBox}>No chats yet.</div>
            ) : (
              <div style={p.list}>
                {sortedInbox.map((x) => {
                  const t = x.trainer;
                  const active = activeTrainer?.trainer_id === t?.trainer_id;

                  return (
                    <button
                      key={t?.trainer_id}
                      onClick={() => openTrainer(t)}
                      style={{
                        ...p.item,
                        background: active ? "rgba(0,245,212,.08)" : "transparent",
                        borderColor: active ? "rgba(0,245,212,.25)" : theme.colors.border,
                      }}
                    >
                      <div style={p.itemLeft}>
                        <div style={p.avatar}>
                          {t?.full_name?.slice(0, 1)?.toUpperCase() || "T"}
                        </div>

                        <div style={{ textAlign: "left", minWidth: 0 }}>
                          <div style={p.itemName}>{t?.full_name || "Trainer"}</div>
                          <div style={p.preview}>
                            {x?.last_message?.content || ""}
                          </div>
                        </div>
                      </div>

                      {x.unread_count > 0 ? (
                        <div style={p.badge}>{x.unread_count}</div>
                      ) : null}
                    </button>
                  );
                })}
              </div>
            )}
          </div>

          {/* RIGHT: thread */}
          <div style={p.right}>
            {!activeTrainer ? (
              <div style={p.empty}>
                <div style={p.emptyTitle}>اختر المدرب من اليسار</div>
                <div style={p.emptySub}>لِفتح المحادثة والبدء بإرسال الرسائل.</div>
              </div>
            ) : (
              <>
                <div style={p.topBar}>
                  <div style={p.topLeft}>
                    <div style={p.avatarLg}>
                      {activeTrainer.full_name?.slice(0, 1)?.toUpperCase() || "T"}
                    </div>
                    <div>
                      <div style={p.topName}>{activeTrainer.full_name}</div>
                      <div style={p.topRole}>Trainer</div>
                    </div>
                  </div>

                  <div style={p.topActions}>
                    <button
                      style={p.ghostBtn}
                      onClick={() => openTrainer(activeTrainer)}
                      title="Reload thread"
                    >
                      Reload
                    </button>
                  </div>
                </div>

                <div style={p.thread}>
                  {loadingThread ? (
                    <div style={p.mutedBox}>Loading...</div>
                  ) : (
                    <>
                      {thread.map((m) => {
                        const mine = String(m.sender_id) === String(profile?.id);
                        return (
                          <div
                            key={m.id}
                            style={{
                              display: "flex",
                              justifyContent: mine ? "flex-end" : "flex-start",
                              marginBottom: 10,
                            }}
                          >
                            <div style={{ ...p.bubble, ...(mine ? p.mine : p.theirs) }}>
                              <div style={{ whiteSpace: "pre-wrap" }}>{m.content}</div>
                              <div style={p.time}>
                                {new Date(m.sent_at).toLocaleString()}
                              </div>
                            </div>
                          </div>
                        );
                      })}
                      <div ref={bottomRef} />
                    </>
                  )}
                </div>

                {err ? <div style={ui.error}>{err}</div> : null}

                <div style={p.composer}>
                  <input
                    style={p.input}
                    value={text}
                    onChange={(e) => setText(e.target.value)}
                    placeholder="Type a message..."
                    onKeyDown={(e) => {
                      if (e.key === "Enter") onSend();
                    }}
                  />
                  <button style={p.sendBtn} onClick={onSend}>
                    Send
                  </button>
                </div>
              </>
            )}
          </div>
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

  wrap: {
    display: "grid",
    gridTemplateColumns: "320px 1fr",
    gap: 14,
    minHeight: "calc(100vh - 36px)",
  },

  left: {
    background: theme.colors.card,
    border: `1px solid ${theme.colors.border}`,
    borderRadius: theme.radius.lg,
    overflow: "hidden",
    backdropFilter: "blur(18px)",
    boxShadow: theme.shadow.card,
  },

  leftHeader: {
    padding: 14,
    borderBottom: `1px solid ${theme.colors.border}`,
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 10,
  },

  leftTitle: { fontWeight: 950, letterSpacing: 0.4, color: theme.colors.text },
  leftSub: { marginTop: 4, fontSize: 12, color: theme.colors.textDim },

  refreshBtn: {
    padding: "9px 10px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    fontWeight: 900,
    cursor: "pointer",
    transition: theme.motion.base,
  },

  mutedBox: {
    padding: 12,
    color: theme.colors.textDim,
  },

  list: { display: "grid" },

  item: {
    width: "100%",
    padding: 12,
    borderTop: `1px solid ${theme.colors.border}`,
    background: "transparent",
    color: theme.colors.text,
    cursor: "pointer",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 10,
    transition: theme.motion.base,
  },

  itemLeft: {
    display: "flex",
    gap: 10,
    alignItems: "center",
    minWidth: 0,
  },

  avatar: {
    width: 36,
    height: 36,
    borderRadius: 999,
    background: "rgba(255,255,255,.06)",
    border: `1px solid ${theme.colors.borderSoft}`,
    display: "grid",
    placeItems: "center",
    fontWeight: 950,
    color: theme.colors.text,
    flexShrink: 0,
  },

  itemName: {
    fontWeight: 950,
    fontSize: 14,
    overflow: "hidden",
    textOverflow: "ellipsis",
    whiteSpace: "nowrap",
  },

  preview: {
    color: theme.colors.textDim,
    fontSize: 13,
    marginTop: 3,
    maxWidth: 190,
    overflow: "hidden",
    textOverflow: "ellipsis",
    whiteSpace: "nowrap",
  },

  badge: {
    minWidth: 26,
    height: 26,
    borderRadius: 999,
    display: "grid",
    placeItems: "center",
    fontWeight: 950,
    fontSize: 12,
    background: "rgba(0,245,212,.14)",
    color: theme.colors.primary,
    border: "1px solid rgba(0,245,212,.30)",
    flexShrink: 0,
  },

  right: {
    background: theme.colors.card,
    border: `1px solid ${theme.colors.border}`,
    borderRadius: theme.radius.lg,
    overflow: "hidden",
    display: "grid",
    gridTemplateRows: "auto 1fr auto",
    backdropFilter: "blur(18px)",
    boxShadow: theme.shadow.card,
  },

  topBar: {
    padding: 14,
    borderBottom: `1px solid ${theme.colors.border}`,
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    gap: 10,
  },

  topLeft: {
    display: "flex",
    gap: 10,
    alignItems: "center",
    minWidth: 0,
  },

  avatarLg: {
    width: 42,
    height: 42,
    borderRadius: 999,
    background: "rgba(0,245,212,.12)",
    border: "1px solid rgba(0,245,212,.28)",
    display: "grid",
    placeItems: "center",
    fontWeight: 950,
    color: theme.colors.primary,
    flexShrink: 0,
  },

  topName: {
    fontWeight: 950,
    color: theme.colors.text,
    overflow: "hidden",
    textOverflow: "ellipsis",
    whiteSpace: "nowrap",
  },

  topRole: { color: theme.colors.textDim, fontSize: 13, marginTop: 3 },

  topActions: { display: "flex", gap: 8, alignItems: "center" },

  ghostBtn: {
    padding: "9px 10px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: "rgba(255,255,255,.04)",
    color: theme.colors.textDim,
    fontWeight: 900,
    cursor: "pointer",
    transition: theme.motion.base,
  },

  thread: {
    padding: 14,
    overflow: "auto",
  },

  bubble: {
    maxWidth: "70%",
    padding: "10px 12px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    background: theme.colors.surface,
    boxShadow: "0 10px 26px rgba(0,0,0,.35)",
  },

  mine: {
    background: "rgba(0,245,212,.10)",
    borderColor: "rgba(0,245,212,.24)",
  },

  theirs: {},

  time: { opacity: 0.75, fontSize: 11, marginTop: 6, color: theme.colors.textDim },

  composer: {
    display: "flex",
    gap: 10,
    padding: 12,
    borderTop: `1px solid ${theme.colors.border}`,
    background: "rgba(0,0,0,.10)",
  },

  input: {
    ...ui.input,
    flex: 1,
    height: 44,
  },

  sendBtn: {
    padding: "12px 14px",
    borderRadius: theme.radius.md,
    border: "none",
    fontWeight: 950,
    background: theme.gradients.primary,
    color: "#061018",
    cursor: "pointer",
    boxShadow: theme.shadow.glow,
    transition: theme.motion.base,
  },

  empty: {
    padding: 18,
    color: theme.colors.textDim,
  },

  emptyTitle: {
    fontWeight: 950,
    color: theme.colors.text,
    marginBottom: 6,
  },

  emptySub: {
    fontSize: 14,
    color: theme.colors.textDim,
  },
};