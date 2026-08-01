<template>
  <div class="min-h-screen bg-slate-50 px-4 py-10">
    <div class="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium uppercase tracking-wide text-blue-600">Authenticated Profile</p>
          <h2 class="text-2xl font-semibold text-slate-800">{{ user?.role || 'User' }} Dashboard</h2>
        </div>
        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700" @click="logout">
          Logout
        </button>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-slate-200 p-4">
          <p class="text-sm text-slate-500">Username</p>
          <p class="mt-1 font-semibold text-slate-800">{{ user?.username }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 p-4">
          <p class="text-sm text-slate-500">Email</p>
          <p class="mt-1 font-semibold text-slate-800">{{ user?.email }}</p>
        </div>
      </div>

      <div class="mt-6 rounded-lg border border-slate-200 p-4">
        <p class="text-sm text-slate-500">Permissions</p>
        <div class="mt-3 flex flex-wrap gap-2">
          <span v-for="permission in permissions" :key="permission" class="rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700">
            {{ permission }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: { type: Object, default: null },
});

const permissions = computed(() => props.user?.permissions || []);

function logout() {
  localStorage.removeItem('auth_token');
  window.location.reload();
}
</script>
