import { Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

const ProtectedRoute = ({ children, roles }) => {
  const { user, profile, loading } = useAuth();

  if (loading) {
  return (
    <div style={{ minHeight: "100vh", display: "grid", placeItems: "center" }}>
      Loading...
    </div>
  );
}

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (roles && !roles.includes(profile?.role)) {
    return <Navigate to="/unauthorized" replace />;
  }

  return children;
};

export default ProtectedRoute;