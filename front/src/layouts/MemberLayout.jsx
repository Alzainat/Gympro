import { Outlet } from "react-router-dom";
import Sidebar from "../components/Sidebar";
import { theme } from "../theme/uiTheme";

export default function MemberLayout() {
  return (
    <div style={s.wrap}>
      <Sidebar />
      <main style={s.main}>
        <Outlet />
      </main>
    </div>
  );
}

const s = {
  wrap: {
    display: "flex",
    minHeight: "100vh",
    background: theme.gradients.page, // ✅ بدل اللون الثابت
  },

  main: {
    flex: 1,
    padding: 24,
    color: theme.colors.text,
  },
};
