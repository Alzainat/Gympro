import api from "../api/axios";

export const getInbox = () => api.get("/member/chat/inbox");
export const getThread = (trainerId) => api.get(`/member/chat/thread/${trainerId}`);
export const sendMessage = (receiver_id, content) =>
  api.post("/member/chat/send", { receiver_id, content });
export const markRead = (trainerId) => api.post(`/member/chat/${trainerId}/read`);
export const allowedTrainers = () => api.get("/member/chat/allowed-trainers");