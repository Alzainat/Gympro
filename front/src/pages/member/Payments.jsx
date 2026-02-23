import { useMemo, useState } from "react";
import api from "../../api/axios";
import { useNavigate } from "react-router-dom";
import { theme, ui } from "../../theme/uiTheme";

// ✅ خطط مختصرة (واجهة فقط)
const TIERS = [
  {
    tier: "bronze",
    price: 20,
    tagline: "Starter plan",
    perks: ["Workouts + Meals", "chat with trainer"],
  },
  {
    tier: "silver",
    price: 35,
    tagline: "Most popular",
    badge: "POPULAR",
    perks: ["Personalized plan", "Weekly adjustments", "Priority support"],
  },
  {
    tier: "gold",
    price: 55,
    tagline: "Premium coaching",
    perks: ["Advanced plan", "Daily tracking", "Coach messaging"],
  },
];

export default function Payments() {
  const navigate = useNavigate();

  const [msg, setMsg] = useState("");

  // modal
  const [open, setOpen] = useState(false);
  const [selectedTier, setSelectedTier] = useState(null);

  // goal
  const [goal, setGoal] = useState(""); // "cutting" | "bulking"

  // fetched pricing from backend based on goal
  // backend response: { plans: { bronze:{price,plan_key}, silver:{...}, gold:{...} } }
  const [plansMap, setPlansMap] = useState(null);
  const [plansLoading, setPlansLoading] = useState(false);

  // payment
  const [loading, setLoading] = useState(false);
  const [method, setMethod] = useState("cash");

  // demo card
  const [card, setCard] = useState({ name: "", number: "", exp: "", cvc: "" });

  const selectedPlan = useMemo(
    () => TIERS.find((p) => p.tier === selectedTier),
    [selectedTier]
  );

  // ✅ السعر النهائي اللي رح نعرضه (من الباك إذا موجود) وإلا من TIERS
  const finalPrice = useMemo(() => {
    if (!selectedTier) return 0;
    const fromApi = plansMap?.[selectedTier]?.price;
    if (typeof fromApi === "number") return fromApi;
    return selectedPlan?.price ?? 0;
  }, [plansMap, selectedTier, selectedPlan]);

  const closeCheckout = () => {
    if (loading) return;
    setOpen(false);
  };

  const startCheckout = (tier) => {
    setMsg("");
    setSelectedTier(tier);

    // reset checkout state
    setGoal("");
    setPlansMap(null);
    setPlansLoading(false);

    setMethod("cash");
    setCard({ name: "", number: "", exp: "", cvc: "" });

    setOpen(true);
  };

  // ✅ fetch plans for a goal (for price + validation)
  const fetchPlansForGoal = async (g) => {
    setPlansLoading(true);
    setPlansMap(null);

    try {
      const res = await api.get("/member/plans", { params: { goal: g } });

      // expected: { plans: { bronze:{price,plan_key}, silver:{...}, gold:{...} } }
      const map = res?.data?.plans;

      if (!map || !map.bronze || !map.silver || !map.gold) {
        throw new Error("Invalid plans response");
      }

      setPlansMap(map);
    } catch (e) {
      setMsg("❌ Failed to load plans for the selected goal.");
      setPlansMap(null);
    } finally {
      setPlansLoading(false);
    }
  };

  const onSelectGoal = async (g) => {
    if (loading) return;
    setMsg("");
    setGoal(g);
    await fetchPlansForGoal(g);
  };

  const pay = async () => {
    if (!selectedTier) return;

    if (!goal) {
      setMsg("❌ اختر الهدف: تنحيف أو تضخيم.");
      return;
    }

    if (plansLoading) {
      setMsg("⏳ عم نحمل بيانات الخطة...");
      return;
    }

    // ✅ تأكد إن الباك رجّع الخطة لهذا الهدف
    if (plansMap && !plansMap?.[selectedTier]) {
      setMsg("❌ الخطة غير متاحة لهذا الهدف.");
      return;
    }

    const needsCard = method !== "cash";
    if (needsCard) {
      if (!card.name || !card.number || !card.exp || !card.cvc) {
        setMsg("❌ Please fill card details.");
        return;
      }
    }

    setLoading(true);
    setMsg("");

    try {
      // ✅ متوافق مع الباك بعد التعديل: goal + plan_key
      await api.post("/member/subscribe", {
        goal, // cutting/bulking
        plan_key: selectedTier, // bronze/silver/gold
        payment_method: method,
      });

      setMsg("✅ Payment successful! Your workouts & meals are now available.");
      setOpen(false);
      navigate("/member/workouts");
    } catch (e) {
      const data = e?.response?.data;
      setMsg(data?.message || "❌ Payment failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={styles.page}>
      {/* نفس جو صفحات الـ auth */}
      <div style={ui.bgGrid} />
      <div style={ui.glowTop} />
      <div style={ui.glowBottom} />

      <div style={styles.container}>
        <div style={styles.header}>
          <h1 style={styles.h1}>Choose Your Plan</h1>
          <div style={styles.sub}>
            Choose your tier, then pick <b>Cutting</b> or <b>Bulking</b> at checkout.
          </div>
        </div>

        {msg && <div style={styles.msg}>{msg}</div>}

        <div style={styles.grid}>
          {TIERS.map((plan) => (
            <div
              key={plan.tier}
              style={{
                ...styles.card,
                ...(plan.badge ? styles.cardPopular : null),
              }}
            >
              {!!plan.badge && <div style={styles.badge}>{plan.badge}</div>}

              <div style={styles.tier}>{plan.tier.toUpperCase()}</div>

              <div style={styles.priceRow}>
                <div style={styles.price}>${plan.price}</div>
                <div style={styles.perMonth}>/month</div>
              </div>

              <div style={styles.tagline}>{plan.tagline}</div>

              <div style={styles.list}>
                {plan.perks.map((x) => (
                  <div key={x} style={styles.item}>
                    <span style={styles.dot} />
                    <span style={styles.text}>{x}</span>
                  </div>
                ))}
              </div>

              <button style={styles.btn(false)} onClick={() => startCheckout(plan.tier)}>
                GET STARTED
              </button>
            </div>
          ))}
        </div>
      </div>

      {/* ✅ CHECKOUT MODAL */}
      {open && selectedPlan && (
        <div style={modal.backdrop} onClick={closeCheckout}>
          <div style={modal.modal} onClick={(e) => e.stopPropagation()}>
            <div style={modal.header}>
              <div style={{ fontWeight: 900, fontSize: 16 }}>Checkout</div>
              <button style={modal.xBtn} onClick={closeCheckout} disabled={loading}>
                ✕
              </button>
            </div>

            <div style={modal.box}>
              <div style={{ fontWeight: 900 }}>
                Tier: {selectedPlan.tier.toUpperCase()}
              </div>

              <div style={{ color: theme.colors.textDim, marginTop: 6 }}>
                Price: <b>${finalPrice}</b> / month
              </div>

              <div style={modal.sep} />

              <div style={{ fontWeight: 900, marginBottom: 8 }}>Choose Goal</div>

              <div style={modal.goalRow}>
                <button
                  style={modal.goalBtn(goal === "cutting")}
                  onClick={() => onSelectGoal("cutting")}
                  disabled={plansLoading || loading}
                  type="button"
                >
                 (Cutting)
                </button>

                <button
                  style={modal.goalBtn(goal === "bulking")}
                  onClick={() => onSelectGoal("bulking")}
                  disabled={plansLoading || loading}
                  type="button"
                >
                 (Bulking)
                </button>
              </div>

              <div style={{ marginTop: 10, fontSize: 13, color: theme.colors.textDim }}>
                {plansLoading
                  ? "⏳ Loading plan from database..."
                  : goal
                  ? "✅ Goal selected. Plan will be assigned based on your tier."
                  : "       "}
              </div>
            </div>

            <div style={{ marginTop: 12 }}>
              <div style={{ fontWeight: 900, marginBottom: 8 }}>Payment Method</div>

              <div style={modal.methods}>
                <label style={modal.radio}>
                  <input
                    type="radio"
                    checked={method === "cash"}
                    onChange={() => setMethod("cash")}
                  />
                  <span>Cash</span>
                </label>

                <label style={modal.radio}>
                  <input
                    type="radio"
                    checked={method === "credit_card"}
                    onChange={() => setMethod("credit_card")}
                  />
                  <span>Credit Card</span>
                </label>

                <label style={modal.radio}>
                  <input
                    type="radio"
                    checked={method === "debit_card"}
                    onChange={() => setMethod("debit_card")}
                  />
                  <span>Visa / Debit</span>
                </label>
              </div>

              {method !== "cash" && (
                <div style={modal.cardForm}>
                  <input
                    style={modal.input}
                    placeholder="Name on card"
                    value={card.name}
                    onChange={(e) => setCard({ ...card, name: e.target.value })}
                  />

                  <input
                    style={modal.input}
                    placeholder="Card number"
                    value={card.number}
                    onChange={(e) => setCard({ ...card, number: e.target.value })}
                  />

                  <div style={{ display: "flex", gap: 10 }}>
                    <input
                      style={modal.input}
                      placeholder="MM/YY"
                      value={card.exp}
                      onChange={(e) => setCard({ ...card, exp: e.target.value })}
                    />
                    <input
                      style={modal.input}
                      placeholder="CVC"
                      value={card.cvc}
                      onChange={(e) => setCard({ ...card, cvc: e.target.value })}
                    />
                  </div>

                  <div style={{ fontSize: 12, color: theme.colors.textDim, marginTop: 8 }}>
                    * Demo checkout (no real payment gateway)
                  </div>
                </div>
              )}
            </div>

            <button
              style={modal.payBtn(loading || plansLoading || !goal)}
              onClick={pay}
              disabled={loading || plansLoading || !goal}
            >
              {loading
                ? "Processing..."
                : plansLoading
                ? "Loading plan..."
                : `Pay $${finalPrice}`}
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

const styles = {
  page: {
    ...ui.page,
    padding: theme.layout.pagePadding,
  },
  container: {
    maxWidth: theme.layout.contentMax,
    margin: "0 auto",
    position: "relative",
    zIndex: 2,
  },
  header: {
    textAlign: "center",
    marginTop: 10,
    marginBottom: 16,
  },
  h1: {
    margin: 0,
    fontSize: 36,
    fontWeight: 900,
    letterSpacing: 0.6,
  },
  sub: {
    marginTop: 10,
    color: theme.colors.textDim,
    fontSize: 14,
  },
  msg: {
    margin: "12px auto 0",
    maxWidth: 920,
    padding: 12,
    borderRadius: theme.radius.md,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
    color: theme.colors.text,
  },

  grid: {
    marginTop: 18,
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
    gap: 16,
    alignItems: "stretch",
  },

  card: {
    position: "relative",
    background: theme.colors.card,
    border: `1px solid ${theme.colors.border}`,
    borderRadius: theme.radius.lg,
    padding: 18,
    boxShadow: theme.shadow.card,
    display: "flex",
    flexDirection: "column",
    minHeight: 360,
    backdropFilter: "blur(18px)",
  },
  cardPopular: {
    border: `1px solid rgba(0,245,212,.35)`,
    boxShadow: `${theme.shadow.card}, 0 0 0 6px rgba(0,245,212,.08)`,
  },
  badge: {
    position: "absolute",
    top: 14,
    right: 14,
    fontSize: 11,
    fontWeight: 900,
    padding: "6px 10px",
    borderRadius: 999,
    background: theme.gradients.primary,
    color: "#061018",
  },

  tier: {
    fontWeight: 900,
    fontSize: 20,
    letterSpacing: 1,
  },

  priceRow: {
    display: "flex",
    alignItems: "baseline",
    gap: 8,
    marginTop: 10,
  },
  price: { fontWeight: 900, fontSize: 40 },
  perMonth: { color: theme.colors.textDim },

  tagline: { marginTop: 6, color: theme.colors.textDim },

  list: { marginTop: 14, display: "grid", gap: 10, flex: 1 },
  item: { display: "flex", gap: 10, alignItems: "center" },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 999,
    background: theme.gradients.dot,
    boxShadow: theme.shadow.glow,
  },
  text: { color: theme.colors.text },

  btn: (disabled) => ({
    width: "100%",
    padding: "12px 12px",
    borderRadius: theme.radius.md,
    border: `1px solid ${theme.colors.borderSoft}`,
    fontWeight: 900,
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.7 : 1,
    background: theme.gradients.primary,
    color: "#061018",
    marginTop: 14,
    boxShadow: disabled ? "none" : theme.shadow.glow,
    transition: theme.motion.base,
  }),
};

const modal = {
  backdrop: {
    position: "fixed",
    inset: 0,
    background: "rgba(0,0,0,.65)",
    display: "grid",
    placeItems: "center",
    zIndex: 50,
    padding: 14,
  },
  modal: {
    width: "min(720px, 100%)",
    background: theme.colors.bg1,
    border: `1px solid ${theme.colors.border}`,
    borderRadius: theme.radius.lg,
    padding: 14,
    boxShadow: theme.shadow.card,
  },
  header: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 10,
    color: theme.colors.text,
  },
  xBtn: {
    border: "none",
    background: "transparent",
    color: theme.colors.text,
    fontSize: 18,
    cursor: "pointer",
    opacity: 0.9,
  },
  box: {
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
    padding: 12,
    borderRadius: theme.radius.md,
    color: theme.colors.text,
  },
  sep: {
    height: 1,
    background: theme.colors.border,
    margin: "12px 0",
    opacity: 0.9,
  },

  goalRow: {
    display: "flex",
    gap: 10,
    flexWrap: "wrap",
  },
  goalBtn: (active) => ({
    flex: 1,
    minWidth: 180,
    padding: "12px 12px",
    borderRadius: theme.radius.md,
    border: `1px solid ${active ? "rgba(0,245,212,.45)" : theme.colors.border}`,
    background: active ? "rgba(0,245,212,.12)" : theme.colors.bg1,
    color: theme.colors.text,
    cursor: "pointer",
    fontWeight: 900,
    transition: theme.motion.base,
    boxShadow: active ? theme.shadow.glow : "none",
  }),

  methods: { display: "flex", gap: 10, flexWrap: "wrap" },
  radio: {
    display: "flex",
    gap: 8,
    alignItems: "center",
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
    padding: "8px 10px",
    borderRadius: theme.radius.md,
    cursor: "pointer",
    color: theme.colors.text,
  },

  cardForm: {
    marginTop: 10,
    background: theme.colors.surface,
    border: `1px solid ${theme.colors.border}`,
    padding: 12,
    borderRadius: theme.radius.md,
  },

  input: {
    width: "100%",
    padding: 12,
    borderRadius: theme.radius.md,
    background: theme.colors.bg1,
    color: theme.colors.text,
    border: `1px solid ${theme.colors.border}`,
    marginTop: 8,
    outline: "none",
  },

  payBtn: (disabled) => ({
    marginTop: 12,
    width: "100%",
    padding: "12px 12px",
    borderRadius: theme.radius.md,
    border: "none",
    fontWeight: 900,
    cursor: disabled ? "not-allowed" : "pointer",
    opacity: disabled ? 0.65 : 1,
    background: theme.gradients.primary,
    color: "#061018",
    boxShadow: disabled ? "none" : theme.shadow.glow,
    transition: theme.motion.base,
  }),
};
