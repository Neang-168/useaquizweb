<template>
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-mark">
        <span class="brand-mark-ring"></span>
        <span class="brand-mark-letter">Q</span>
      </div>
      <div>
        <p class="brand-title">UseaQuiz</p>
        <p class="brand-subtitle">Admin panel</p>
      </div>
    </div>

    <nav class="sidebar-nav">
      <button
        class="nav-item"
        :class="{ active: activeView === 'profile' }"
        @click="emit('select-view', 'profile')"
      >
        <span class="nav-bubble">
          <i class="pi pi-user"></i>
        </span>
        <span class="nav-label">Profile</span>
      </button>
      <button
        class="nav-item"
        :class="{ active: activeView === 'users' }"
        @click="emit('select-view', 'users')"
      >
        <span class="nav-bubble">
          <i class="pi pi-users"></i>
        </span>
        <span class="nav-label">Users</span>
      </button>
    </nav>

    <div class="sidebar-footer">
      <Button label="Log out" icon="pi pi-sign-out" class="logout-btn" text @click="emit('logout')" />
    </div>
  </aside>
</template>

<script setup>
import Button from 'primevue/button'

const props = defineProps({
  activeView: { type: String, default: 'profile' },
})

const emit = defineEmits(['select-view', 'logout'])
</script>

<style scoped>
.sidebar {
  --ink: #191b3a;
  --ink-soft: #262a52;
  --amber: #ffb020;
  --green: #17b26a;
  --hairline: rgba(255, 255, 255, 0.1);

  background: var(--ink);
  color: #f4f4f8;
  border-radius: 1.25rem;
  padding: 1.35rem 1.15rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  font-family: 'Inter', system-ui, sans-serif;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 2.25rem;
  padding-bottom: 1.15rem;
  border-bottom: 1px solid var(--hairline);
}

.brand-mark {
  position: relative;
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  display: grid;
  place-items: center;
}

.brand-mark-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 1.5px solid var(--amber);
  opacity: 0.55;
}

.brand-mark-letter {
  width: 1.9rem;
  height: 1.9rem;
  border-radius: 50%;
  background: var(--amber);
  color: var(--ink);
  display: grid;
  place-items: center;
  font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
  font-weight: 700;
  font-size: 0.9rem;
}

.brand-title {
  margin: 0;
  font-family: 'Space Grotesk', 'Inter', system-ui, sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.brand-subtitle {
  margin: 0.15rem 0 0;
  font-family: 'IBM Plex Mono', ui-monospace, monospace;
  font-size: 0.68rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(244, 244, 248, 0.5);
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.nav-item {
  border: 0;
  background: transparent;
  color: rgba(244, 244, 248, 0.72);
  padding: 0.6rem 0.7rem;
  border-radius: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  cursor: pointer;
  text-align: left;
  font-family: 'Inter', system-ui, sans-serif;
  font-weight: 600;
  font-size: 0.92rem;
  transition: background 0.15s ease, color 0.15s ease;
}

.nav-bubble {
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 50%;
  border: 1.5px solid rgba(244, 244, 248, 0.28);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.nav-item:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.06);
}

.nav-item:hover .nav-bubble {
  border-color: rgba(244, 244, 248, 0.5);
}

.nav-item:focus-visible {
  outline: 2px solid var(--amber);
  outline-offset: 2px;
}

.nav-item.active {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
}

.nav-item.active .nav-bubble {
  background: var(--amber);
  border-color: var(--amber);
  color: var(--ink);
}

.sidebar-footer {
  margin-top: 1.5rem;
  padding-top: 1.1rem;
  border-top: 1px solid var(--hairline);
}

.logout-btn {
  width: 100%;
  justify-content: flex-start;
  color: rgba(244, 244, 248, 0.72) !important;
  font-weight: 600;
  padding-left: 0.7rem !important;
}

.logout-btn:hover {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.06) !important;
}

@media (max-width: 900px) {
  .sidebar {
    padding: 1rem;
  }
}

@media (max-width: 560px) {
  .sidebar-nav {
    gap: 0.25rem;
  }

  .nav-item {
    padding: 0.55rem 0.6rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .nav-item,
  .nav-bubble {
    transition: none;
  }
}
</style>