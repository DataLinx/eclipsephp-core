<script>
/**
 * Tenant-scoped Echo listener for Filament notifications
 *
 * WHY
 * ----
 * By default, Laravel/Filament subscribe to a user-only private channel:
 *   'Eclipse.Core.Models.User.{id}'
 *
 * If the same user opens multiple tenants in different tabs,
 * a broadcast to that channel will appear in all tabs.
 *
 * WHAT THIS FILE DOES
 * -------------------
 * - Subscribes instead to a tenant-aware channel:
 *     'Eclipse.Core.Models.User.{uid}.tenant.{tid}'
 *
 * - Reads 'uid' and 'tid' from Filament's injected script data
 *   ('window.filamentData').
 *
 * - Re-emits incoming payloads as Filament's
 *   'filament.notifications.received' 
 *   event so the existing UI can render them.
 */
window.addEventListener('EchoLoaded', () => {
  if (!window.Echo) return;

  const { user, tenant } = window.filamentData ?? {};
  const uid = user?.id;
  const tid = tenant?.id;

  if (!uid || !tid) return;

  const channel = `Eclipse.Core.Models.User.${uid}.tenant.${tid}`;

  window.Echo.private(channel).notification(payload => {
    window.dispatchEvent(
      new CustomEvent('filament.notifications.received', { detail: payload })
    );
  });
});
</script>


