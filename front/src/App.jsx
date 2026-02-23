import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { useAuth } from "./context/AuthContext";
import ProtectedRoute from "./routes/ProtectedRoute";

import Login from "./pages/Login";
import Register from "./pages/Register";
import Unauthorized from "./pages/Unauthorized";
import MainLanding from "./pages/MainLanding";

import MemberLayout from "./layouts/MemberLayout";
import MemberHome from "./pages/member/Home";
import Workouts from "./pages/member/Workouts";
import Meals from "./pages/member/Meals";
import HealthConditions from "./pages/member/HealthConditions";
import Trainers from "./pages/member/Trainers";
import Booking from "./pages/member/Booking";
import Payments from "./pages/member/Payments";
import ExerciseTypes from "./pages/member/ExerciseTypes";
import Equipment from "./pages/member/Equipment";

// ✅ NEW: Chat page
import Chat from "./pages/member/Chat";

function App() {
  const { user, profile, loading } = useAuth();

  if (loading) {
    return (
      <div style={{ minHeight: "100vh", display: "grid", placeItems: "center" }}>
        Loading...
      </div>
    );
  }

  const roleHome = {
    admin: "/admin",
    trainer: "/trainer",
    member: "/member",
  };

  const authedRedirectTo = roleHome[profile?.role] || "/login";

  return (
    <BrowserRouter>
      <Routes>
        {/* Public */}
        <Route
          path="/login"
          element={user ? <Navigate to={authedRedirectTo} replace /> : <Login />}
        />
        <Route
          path="/register"
          element={user ? <Navigate to={authedRedirectTo} replace /> : <Register />}
        />

        {/* Home */}
        <Route
          path="/"
          element={<Navigate to={user ? authedRedirectTo : "/login"} replace />}
        />

        {/* Member area with sidebar */}
        <Route
          path="/member"
          element={
            <ProtectedRoute roles={["member"]}>
              <MemberLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<MemberHome />} />
          <Route path="workouts" element={<Workouts />} />
          <Route path="meals" element={<Meals />} />
          <Route path="health" element={<HealthConditions />} />
          <Route path="trainers" element={<Trainers />} />
          <Route path="booking" element={<Booking />} />
          <Route path="payments" element={<Payments />} />
          <Route path="exercise-types" element={<ExerciseTypes />} />
          <Route path="equipment" element={<Equipment />} />

          {/* ✅ NEW: Chat route (nested => keeps Sidebar) */}
          <Route path="chat" element={<Chat />} />
        </Route>

        {/* Unauthorized */}
        <Route path="/unauthorized" element={<Unauthorized />} />

        {/* Fallback */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
