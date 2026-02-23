import { NavLink } from "react-router-dom";
import { theme } from "../theme/uiTheme"; // عدّل المسار

const links = [
  { to: "/member", label: "Home" },
  { to: "/member/workouts", label: "Workouts" },
  { to: "/member/meals", label: "Meals" },
  { to: "/member/health", label: "Health Condition" },
  { to: "/member/trainers", label: "Trainers" },
  { to: "/member/booking", label: "Booking" },
  { to: "/member/chat", label: "Chat" },
  { to: "/member/payments", label: "Payments" },
  { to: "/member/exercise-types", label: "Exercise Type" },
  { to: "/member/equipment", label: "Equipment" },
];

export default function Sidebar() {
  return (
    <aside style={s.side}>
      <div style={s.brand}>GymPro</div>

      <div style={s.menu}>
        {links.map((l) => (
          <NavLink
            key={l.to}
            to={l.to}
            end={l.to === "/member"}
            style={({ isActive }) => ({
              ...s.link,
              ...(isActive ? s.linkActive : null),
            })}
          >
            {l.label}
          </NavLink>
        ))}
      </div>
    </aside>
  );
}

const s = {
  side: {
    width: 260,
    padding: 20,
    borderRight: `1px solid ${theme.colors.border}`,
    background: theme.colors.bg1,
    color: theme.colors.text,
    backdropFilter: "blur(14px)",
    position: "relative",
  },

  brand: {
    fontWeight: 900,
    fontSize: 20,
    marginBottom: 20,
    letterSpacing: 1,
    background: theme.gradients.primary,
    WebkitBackgroundClip: "text",
    WebkitTextFillColor: "transparent",
  },

  menu: {
    display: "grid",
    gap: 10,
  },

  link: {
    display: "block",
    padding: "12px 14px",
    borderRadius: theme.radius.md,
    textDecoration: "none",
    color: theme.colors.textDim,
    border: `1px solid ${theme.colors.border}`,
    background: theme.colors.surface,
    fontWeight: 700,
    transition: theme.motion.base,
  },

  linkActive: {
    background: "rgba(0,245,212,.12)",
    borderColor: "rgba(0,245,212,.35)",
    color: theme.colors.primary,
    boxShadow: theme.shadow.glow,
  },
};