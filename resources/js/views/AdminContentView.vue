<template>
  <div class="admin-layout">
    <main class="admin-main">
      <div class="content-panel">
        
        <!-- Content Header -->
        <div class="content-header">
          <div class="header-identity">
            <div class="id-mark">
              <span class="id-mark-ring"></span>
              <span class="id-mark-letter">{{ initials }}</span>
            </div>
            <div>
              <p class="header-eyebrow">Authenticated profile</p>
              <h1 class="header-title">{{ user?.role || 'User' }} dashboard</h1>
            </div>
          </div>
        </div>

        <!-- PROFILE VIEW -->
        <section v-if="activeView === 'profile'" class="view-stack">
          <div class="profile-grid">
            <Card class="profile-card">
              <template #content>
                <p class="field-label"><span class="field-dot"></span>Username</p>
                <p class="field-value">{{ user?.username || '—' }}</p>
              </template>
            </Card>
            <Card class="profile-card">
              <template #content>
                <p class="field-label"><span class="field-dot"></span>Email</p>
                <p class="field-value">{{ user?.email || '—' }}</p>
              </template>
            </Card>
          </div>

          <Card class="section-card">
            <template #content>
              <p class="field-label"><span class="field-dot"></span>Permissions</p>
              <div class="chip-row">
                <span v-for="permission in permissions" :key="permission" class="permission-chip">
                  {{ permission }}
                </span>
                <span v-if="!permissions.length" class="empty-hint">No permissions assigned</span>
              </div>
            </template>
          </Card>
        </section>

        <!-- USERS TABLE VIEW -->
        <section v-else class="view-stack">
          <Card class="section-card">
            <template #title>
              <div class="section-header">
                <div>
                  <h2 class="section-title">User management</h2>
                  <p class="section-subtitle">Create and review users from the API.</p>
                </div>
                <div class="section-actions">
                  <Button icon="pi pi-refresh" text rounded aria-label="Refresh" class="icon-btn" :loading="loadingUsers" @click="emit('refresh-users')" />
                  <Button label="New user" icon="pi pi-plus" class="primary-btn" @click="emit('open-dialog')" />
                </div>
              </div>
            </template>

            <template #content>
              <DataTable
                :value="users"
                :loading="loadingUsers"
                paginator
                :rows="8"
                dataKey="id"
                responsiveLayout="scroll"
                class="users-table"
              >
                <template #empty>
                  <div class="empty-state">No users yet — create the first one.</div>
                </template>

                <Column field="name" header="Name">
                  <template #body="{ data }">
                    <div class="name-cell">
                      <span class="row-mark">{{ nameInitials(data) }}</span>
                      <span>{{ data.first_name }} {{ data.last_name }}</span>
                    </div>
                  </template>
                </Column>
                <Column field="email" header="Email" />
                <Column field="role" header="Role">
                  <template #body="{ data }">
                    {{ data.role?.name || '—' }}
                  </template>
                </Column>
                <Column field="status" header="Status">
                  <template #body="{ data }">
                    <span class="status-pill" :class="data.status ? 'is-active' : 'is-inactive'">
                      <span class="status-dot"></span>
                      {{ data.status ? 'Active' : 'Inactive' }}
                    </span>
                  </template>
                </Column>
              </DataTable>
            </template>
          </Card>
        </section>

      </div>
    </main>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import AdminSidebar from '../components/AdminSidebar.vue'

const props = defineProps({
  activeView: { type: String, default: 'profile' },
  user: { type: Object, default: () => ({ username: 'super.admin', email: 'superadmin@example.com', role: 'Super Admin' }) },
  permissions: { type: Array, default: () => ['manage_users', 'manage_roles', 'manage_courses', 'manage_exams', 'view_dashboard', 'view_reports', 'manage_profile', 'view_schedule'] },
  users: { type: Array, default: () => [] },
  loadingUsers: { type: Boolean, default: false },
})

const emit = defineEmits(['refresh-users', 'open-dialog'])

const initials = computed(() => (props.user?.username || 'U').slice(0, 2).toUpperCase())

function nameInitials(item) {
  const first = item.first_name?.[0] || ''
  const last = item.last_name?.[0] || ''
  return (first + last || item.email?.[0] || 'U').toUpperCase()
}
</script>

<style scoped>
/* ==========================================================================
   LAYOUT WRAPPER (Fix បញ្ហាអត់ស្មើគែម & Horizontal Scrollbar)
   ========================================================================== */
.admin-layout {
  display: flex;
  width: 100vw;
  height: 100vh;
  margin: 0 !important;
  padding: 0 !important;
  overflow: hidden;
  background-color: #f8fafc;
}

.admin-sidebar {
  flex-shrink: 0;
  height: 100%;
}

.admin-main {
  flex: 1;
  height: 100%;
  width: 100%;
  min-width: 0; /* ការពារ DataTable មិនឱ្យរុញអេក្រង់លេច Scrollbar ក្រោម */
  margin: 0 !important;
  padding: 0 !important;
  overflow-y: auto; /* Scroll តែផ្នែក Content ខាងស្តាំ */
  background-color: #17b26a;
}

/* ==========================================================================
   CONTENT PANEL STYLES
   ========================================================================== */
.content-panel {
  --ink: #191b3a;
  --paper-card: #ffffff;
  --text: #1e2033;
  --text-muted: #6b6f8a;
  --amber: #ffb020;
  --green: #17b26a;
  --green-soft: #e3f8ee;
  --grey-soft: #eef0f6;
  --border: rgba(30, 32, 51, 0.08);

  width: 100%;
  min-height: 100%;
  box-sizing: border-box;
  padding: 2rem;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--text);
}

.content-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
  background-color: #17b26a;
}

.view-stack {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.header-identity {
  display: flex;
  align-items: center;
  gap: 0.9rem;
}

.id-mark {
  position: relative;
  width: 2.9rem;
  height: 2.9rem;
  flex-shrink: 0;
  display: grid;
  place-items: center;
}

.id-mark-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 1.5px solid var(--amber);
  opacity: 0.55;
}

.id-mark-letter {
  width: 2.3rem;
  height: 2.3rem;
  border-radius: 50%;
  background: var(--ink);
  color: #fff;
  display: grid;
  place-items: center;
  font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
  font-weight: 700;
  font-size: 0.85rem;
}

.header-eyebrow {
  margin: 0 0 0.15rem;
  font-family: 'IBM Plex Mono', ui-monospace, monospace;
  font-size: 0.7rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.header-title {
  margin: 0;
  font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.01em;
  color: var(--text);
  text-transform: capitalize;
}

.profile-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

/* Card Styling */
.profile-card,
.section-card {
  border-radius: 1rem !important;
  border: 1px solid var(--border) !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
  background: var(--paper-card) !important;
}

.profile-card :deep(.p-card-body) {
  padding: 1.1rem 1.25rem;
}

.section-card :deep(.p-card-body) {
  padding: 1.25rem 1.5rem;
}

.field-label {
  margin: 0 0 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'IBM Plex Mono', ui-monospace, monospace;
  font-size: 0.72rem;
  font-weight: 500;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.field-dot {
  width: 0.4rem;
  height: 0.4rem;
  border-radius: 50%;
  background: var(--amber);
  flex-shrink: 0;
}

.field-value {
  margin: 0;
  font-weight: 600;
  font-size: 1rem;
  color: var(--text);
}

.chip-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.permission-chip {
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  background: var(--grey-soft);
  border: 1px solid var(--border);
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text);
}

.empty-hint {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.section-title {
  margin: 0;
  font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text);
}

.section-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.875rem;
  color: var(--text-muted);
}

.section-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon-btn {
  color: var(--text-muted) !important;
}

.primary-btn {
  background: var(--ink) !important;
  border-color: var(--ink) !important;
  font-weight: 600;
}

.primary-btn:hover {
  background: #262a52 !important;
}

.name-cell {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.row-mark {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: var(--grey-soft);
  border: 1px solid var(--border);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
  font-weight: 700;
  font-size: 0.72rem;
  color: var(--text);
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.28rem 0.7rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
}

.status-dot {
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 50%;
}

.status-pill.is-active {
  background: var(--green-soft);
  color: #0e7a4d;
}

.status-pill.is-active .status-dot {
  background: var(--green);
}

.status-pill.is-inactive {
  background: var(--grey-soft);
  color: var(--text-muted);
}

.status-pill.is-inactive .status-dot {
  background: var(--text-muted);
}

.empty-state {
  padding: 1.5rem 0;
  text-align: center;
  color: var(--text-muted);
}

.users-table :deep(.p-datatable-thead > tr > th) {
  background: transparent;
  border-color: var(--border);
  font-family: 'IBM Plex Mono', ui-monospace, monospace;
  font-size: 0.7rem;
  font-weight: 500;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.users-table :deep(.p-datatable-tbody > tr > td) {
  border-color: var(--border);
}
</style>