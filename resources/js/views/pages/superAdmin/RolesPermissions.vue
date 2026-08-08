<template>
  <div class="space-y-6">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
      <div>
        <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
          <i class="pi pi-shield text-blue-600 text-2xl"></i>
          Roles & Permissions
        </h1>
        <p class="text-xs text-slate-500 m-0 mt-1">
          View each role and control which permissions it grants. Roles themselves are fixed and cannot be added or removed here.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

      <!-- ======= ROLE LIST ======= -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden lg:col-span-1">
        <div class="p-4 border-b border-slate-200">
          <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider m-0">Roles</h3>
        </div>
        <div class="divide-y divide-slate-100">
          <button
            v-for="role in roles"
            :key="role.id"
            @click="selectRole(role)"
            class="w-full text-left p-4 flex items-center justify-between gap-2 transition-colors cursor-pointer border-0"
            :class="selectedRole?.id === role.id ? 'bg-blue-50' : 'bg-white hover:bg-slate-50'"
          >
            <div>
              <div class="font-semibold text-sm" :class="selectedRole?.id === role.id ? 'text-blue-700' : 'text-slate-800'">
                {{ role.name }}
              </div>
              <div class="text-xs text-slate-400 mt-0.5">{{ role.description || 'No description' }}</div>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 shrink-0">
              {{ role.users_count ?? 0 }} users
            </span>
          </button>
        </div>
      </div>

      <!-- ======= PERMISSIONS PANEL ======= -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden lg:col-span-2">
        <div v-if="!selectedRole" class="p-10 text-center text-slate-400 text-sm">
          Select a role on the left to view and edit its permissions.
        </div>

        <div v-else>
          <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-bold text-slate-800 m-0">{{ selectedRole.name }} Permissions</h3>
              <p class="text-xs text-slate-400 m-0 mt-0.5">{{ checkedPermissionIds.length }} of {{ permissions.length }} enabled</p>
            </div>
            <Button
              label="Save Changes"
              icon="pi pi-check"
              :loading="saving"
              class="!bg-blue-600 hover:!bg-blue-700 !text-white !border-0 !rounded-xl !text-xs !font-semibold"
              @click="savePermissions"
            />
          </div>

          <div class="p-5 space-y-5 max-h-[60vh] overflow-y-auto">
            <div v-for="(modulePermissions, module) in permissionsByModule" :key="module">
              <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">{{ module }}</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label
                  v-for="perm in modulePermissions"
                  :key="perm.id"
                  class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-blue-300 cursor-pointer text-xs"
                >
                  <input
                    type="checkbox"
                    :value="perm.id"
                    v-model="checkedPermissionIds"
                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                  />
                  <span class="font-medium text-slate-700">{{ perm.name }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'
import Button from 'primevue/button'

const roles = ref([])
const permissions = ref([])
const selectedRole = ref(null)
const checkedPermissionIds = ref([])
const saving = ref(false)

const fetchRoles = async () => {
  const { data } = await api.get('/roles')
  roles.value = data
}

const fetchPermissions = async () => {
  const { data } = await api.get('/permissions')
  permissions.value = data
}

onMounted(() => {
  fetchRoles()
  fetchPermissions()
})

const permissionsByModule = computed(() => {
  const groups = {}
  for (const perm of permissions.value) {
    const key = perm.module || 'other'
    if (!groups[key]) groups[key] = []
    groups[key].push(perm)
  }
  return groups
})

const selectRole = async (role) => {
  try {
    const { data } = await api.get(`/roles/${role.id}`)
    selectedRole.value = data.role
    checkedPermissionIds.value = data.role.permissions.map(p => p.id)
  } catch (error) {
    alert(extractError(error))
  }
}

const savePermissions = async () => {
  saving.value = true
  try {
    await api.put(`/roles/${selectedRole.value.id}`, { permission_ids: checkedPermissionIds.value })
    await fetchRoles()
  } catch (error) {
    alert(extractError(error))
  } finally {
    saving.value = false
  }
}
</script>
