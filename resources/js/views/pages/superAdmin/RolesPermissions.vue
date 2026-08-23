<template>
  <div class="min-h-screen bg-[#F8F8F8] -m-6 p-6 font-sans">
    
    <!-- CONTAINER CARD -->
    <div class="max-w-8xl mx-auto bg-white rounded-2xl shadow-xl shadow-[#002060]/5 overflow-hidden border border-[#D8E7EC]">
      
      <!-- TOP BAR / HEADER -->
      <div class="bg-white border-b border-[#D8E7EC] px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-[#63c7df]/40 flex items-center justify-center text-[#002060]">
            <i class="pi pi-shield text-lg"></i>
          </div>
          <div>
            <h1 class="text-lg font-bold text-[#002060] m-0 leading-none">Roles & Permissions Matrix</h1>
            <p class="text-xs text-slate-500 m-0 mt-1">
              Toggle access permissions directly for Admin, Teacher, and Student roles.
            </p>
          </div>
        </div>

        <!-- Global Save Button -->
        <Button
          label="Save Matrix Changes"
          icon="pi pi-check"
          :loading="saving"
          class="!bg-[#002060] hover:!bg-[#001848] !text-white !border-0 !rounded-full !py-2.5 !px-6 !text-xs !font-semibold !shadow-md !shadow-[#002060]/20 transition-all cursor-pointer"
          @click="savePermissions"
        />
      </div>

      <!-- TABLE CONTAINER -->
      <div class="p-6 overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-[#D8E7EC] bg-[#F8F8F8]">
              <th class="p-4 text-xs font-bold text-[#002060] uppercase tracking-wider rounded-l-xl">
                Role / Module Permission
              </th>
              <th v-for="roleName in ['Admin', 'Teacher', 'Student']" :key="roleName" class="p-4 text-xs font-bold uppercase tracking-wider text-center">
                <span class="px-3.5 py-1 rounded-full bg-[#D8E7EC]/50 text-[#002060] border border-[#D8E7EC] font-bold">
                  {{ roleName }}
                </span>
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading">
              <td colspan="4" class="py-16 text-center">
                <i class="pi pi-spin pi-spinner text-2xl text-[#002060]"></i>
                <p class="text-xs text-slate-400 mt-2">Loading permissions matrix...</p>
              </td>
            </tr>

            <!-- MODULE HEADERS & PERMISSIONS -->
            <template v-else v-for="(modulePermissions, module) in permissionsByModule" :key="module">
              
              <!-- Module Category Header -->
              <tr class="bg-[#F8F8F8]/60">
                <td colspan="4" class="px-4 py-2.5 text-[11px] font-bold text-[#E4AC40] uppercase tracking-wider">
                  <i class="pi pi-folder text-[10px] mr-1 text-[#63C7DF]"></i> {{ module }}
                </td>
              </tr>

              <!-- Permission Rows -->
              <tr 
                v-for="perm in modulePermissions" 
                :key="perm.id"
                class="hover:bg-[#D8E7EC]/20 transition-colors"
              >
                <!-- Role / Permission Name Column -->
                <td class="p-4 text-xs font-medium text-slate-700">
                  {{ perm.name }}
                  <div v-if="perm.name === 'manage_academic_structure'" class="text-[10px] text-slate-400 font-normal">
                    Academic Structure Management
                  </div>
                </td>

                <!-- Toggle Buttons for Admin, Teacher, Student -->
                <td v-for="roleName in ['Admin', 'Teacher', 'Student']" :key="roleName" class="p-4 text-center">
                  <InputSwitch
                    :modelValue="hasRolePermission(roleName, perm.id)"
                    @update:modelValue="val => togglePermission(roleName, perm.id, val)"
                    class="custom-switch"
                  />
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- FOOTER ACTION BAR -->
      <div class="p-6 border-t border-[#D8E7EC] bg-[#F8F8F8]/50 flex items-center justify-end">
        <Button
          label="Save Matrix Changes"
          icon="pi pi-check"
          :loading="saving"
          class="!bg-[#002060] hover:!bg-[#001848] !text-white !border-0 !rounded-full !py-2.5 !px-8 !text-xs !font-semibold !shadow-md !shadow-[#002060]/20 transition-all cursor-pointer"
          @click="savePermissions"
        />
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api, { extractError } from '../../../api'
import Button from 'primevue/button'
import InputSwitch from 'primevue/inputswitch'

const roles = ref([])
const permissions = ref([])
const matrixState = ref({})
const saving = ref(false)
const loading = ref(false)

const fetchRoles = async () => {
  const { data } = await api.get('/roles')
  roles.value = data

  const responses = await Promise.all(data.map(role => api.get(`/roles/${role.id}`)))
  data.forEach((role, i) => {
    matrixState.value[role.name] = {
      id: role.id,
      permission_ids: responses[i].data.role.permissions.map(p => p.id)
    }
  })
}

const fetchPermissions = async () => {
  const { data } = await api.get('/permissions')
  permissions.value = data
}

onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([fetchRoles(), fetchPermissions()])
  } finally {
    loading.value = false
  }
})

const permissionsByModule = computed(() => {
  const groups = {}
  for (const perm of permissions.value) {
    const key = perm.module || 'Other Permissions'
    if (!groups[key]) groups[key] = []
    groups[key].push(perm)
  }
  return groups
})

const hasRolePermission = (roleName, permId) => {
  return matrixState.value[roleName]?.permission_ids.includes(permId) || false
}

const togglePermission = (roleName, permId, value) => {
  if (!matrixState.value[roleName]) return
  
  const permList = matrixState.value[roleName].permission_ids
  if (value) {
    if (!permList.includes(permId)) permList.push(permId)
  } else {
    const index = permList.indexOf(permId)
    if (index > -1) permList.splice(index, 1)
  }
}

const savePermissions = async () => {
  saving.value = true
  try {
    const targetRoles = ['Admin', 'Teacher', 'Student']
    
    for (const roleName of targetRoles) {
      const roleData = matrixState.value[roleName]
      if (roleData) {
        await api.put(`/roles/${roleData.id}`, { permission_ids: roleData.permission_ids })
      }
    }
    await fetchRoles()
    alert('Permissions matrix updated successfully!')
  } catch (error) {
    alert(extractError(error))
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
/* Custom Switch Style - Navy Primary Color (#002060) */
:deep(.custom-switch.p-inputswitch.p-inputswitch-checked .p-inputswitch-slider) {
  background: #002060 !important;
}
:deep(.custom-switch.p-inputswitch .p-inputswitch-slider) {
  border-radius: 30px !important;
  background: #cbd5e1;
}
</style>