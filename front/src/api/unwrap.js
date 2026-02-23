export const unwrap = (payload) => {
  // Laravel paginator shape: { data: [], ... }
  if (payload && Array.isArray(payload.data)) return payload.data;
  // plain array
  if (Array.isArray(payload)) return payload;
  // single object -> wrap
  if (payload && typeof payload === "object") return [payload];
  return [];
};