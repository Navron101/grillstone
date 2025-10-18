import { ref, onMounted, onBeforeUnmount } from 'vue';

export function useStock(
  productIds: number[] | (() => number[]),
  locationId = 1,
  intervalMs = 4000
) {
  const quantities = ref<Record<number, number>>({});
  let timer: number | undefined;

  function currentIds(): number[] {
    return Array.isArray(productIds) ? productIds : productIds();
  }

  async function fetchOnce() {
    const ids = currentIds();
    if (!ids.length) return;
    const qs = new URLSearchParams({
      location_id: String(locationId),
      product_ids: ids.join(','),
    });
    const res = await fetch(`/api/stock/summary?${qs.toString()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) return;
    const rows = await res.json();
    const map: Record<number, number> = {};
    for (const r of rows) map[Number(r.product_id)] = Number(r.qty);
    quantities.value = map;
  }

  function start() {
    stop();
    fetchOnce();
    timer = window.setInterval(fetchOnce, intervalMs);
  }
  function stop() {
    if (timer) window.clearInterval(timer);
    timer = undefined;
  }

  onMounted(start);
  onBeforeUnmount(stop);

  return { quantities, refresh: fetchOnce };
}
