import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App";
import { setAuthToken } from "./api/axios";
import { AuthProvider } from "./context/AuthContext";
import "./App.css";

// ✅ استرجاع التوكن عند تشغيل التطبيق
const token = localStorage.getItem("token");
if (token) {
  setAuthToken(token);
}

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <AuthProvider>
      <App />
    </AuthProvider>
  </React.StrictMode>
);