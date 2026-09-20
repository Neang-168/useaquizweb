<template>
  <div class="flex flex-col gap-4 w-full font-sans text-slate-800">

    <!-- ======= PAGE HEADER ======= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 bg-white/60 backdrop-blur-md p-4 rounded-xl border border-slate-200/60 shadow-xs">
      <div>
        <h1 class="text-xl font-bold text-[#002060] tracking-tight flex items-center gap-2.5">
          <i class="pi pi-user text-[#e4ac40] text-2xl"></i>
          My Profile
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 m-0 mt-1 pl-0.5">View and manage your account information</p>
      </div>
      <Button
        type="button"
        label="Change Password"
        icon="pi pi-lock"
        class="!bg-[#e4ac40] hover:!bg-[#d69c30] !text-white !border-0 !rounded-lg !py-2.5 !px-4 !text-xs !font-semibold shadow-sm hover:shadow transition-all duration-200 gap-2 shrink-0 cursor-pointer"
        @click="openPasswordDialog"
      />
    </div>

    <!-- ======= PERSONAL INFO + ACCOUNT OVERVIEW (SAME ROW) ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

      <!-- Personal Information Card -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">

        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-[#002060]/10 text-[#002060] flex items-center justify-center font-bold">
            <i class="pi pi-user text-sm"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-[#002060] m-0">Personal Information</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Update your personal details</p>
          </div>
        </div>

        <form class="p-4 space-y-4" @submit.prevent="saveProfile">

          <!-- Avatar Pick Row -->
          <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-center gap-4">
            <div class="relative group shrink-0">
              <div class="w-16 h-16 rounded-full bg-[#002060]/10 text-[#002060] flex items-center justify-center text-xl font-bold overflow-hidden ring-2 ring-[#002060]/10">
                <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Avatar" class="w-full h-full object-cover" />
                <span v-else class="text-[#002060] font-extrabold text-xl">{{ userInitial }}</span>
              </div>
              <button
                type="button"
                @click="avatarInputRef?.click()"
                class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-[#63c7df] text-white flex items-center justify-center shadow-md hover:bg-[#4fb3cc] transition-colors duration-200 border border-white cursor-pointer"
                title="Change Photo"
              >
                <i class="pi pi-camera text-[10px]"></i>
              </button>
            </div>
            <div class="flex-1 min-w-0">
              <span class="text-xs font-bold text-slate-700 block mb-1">Profile picture</span>
              <div class="flex items-center gap-2">
                <Button
                  type="button"
                  label="Choose Photo"
                  icon="pi pi-camera"
                  size="small"
                  class="!bg-[#63c7df] !border-[#63c7df] hover:!bg-[#4fb3cc] !text-white !rounded-lg !text-xs !font-semibold"
                  @click="avatarInputRef?.click()"
                />
                <input ref="avatarInputRef" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onAvatarSelected" />
              </div>
              <p class="text-[11px] text-slate-400 mt-1 m-0 truncate">
                {{ avatarFile ? `Selected: ${avatarFile.name}` : 'JPG, PNG or WEBP. Max 2MB.' }}
              </p>
            </div>
          </div>

          <!-- Input Fields Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div class="space-y-1.5">
              <label for="first_name" class="text-xs font-bold text-slate-700">First name</label>
              <InputText id="first_name" v-model="profileForm.first_name" placeholder="Jane" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20" />
            </div>

            <div class="space-y-1.5">
              <label for="last_name" class="text-xs font-bold text-slate-700">Last name</label>
              <InputText id="last_name" v-model="profileForm.last_name" placeholder="Doe" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20" />
            </div>

            <div class="space-y-1.5">
              <label for="name_kh" class="text-xs font-bold text-slate-700">Khmer name</label>
              <InputText id="name_kh" v-model="profileForm.name_kh" placeholder="ឧ. សុខ វិសាល" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20 font-khmer" />
            </div>

            <div class="space-y-1.5">
              <label for="gender" class="text-xs font-bold text-slate-700">Gender</label>
              <Dropdown id="gender" v-model="profileForm.gender" :options="['Male', 'Female']" placeholder="Select gender" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060]" />
            </div>

            <div class="space-y-1.5">
              <label for="dob" class="text-xs font-bold text-slate-700">Date of birth</label>
              <InputText id="dob" v-model="profileForm.dob" type="date" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20" />
            </div>

            <div class="space-y-1.5">
              <label for="phone" class="text-xs font-bold text-slate-700">Phone</label>
              <InputText id="phone" v-model="profileForm.phone" placeholder="012 345 678" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20" />
            </div>

            <div class="space-y-1.5 sm:col-span-2">
              <label for="address" class="text-xs font-bold text-slate-700">Address</label>
              <Textarea id="address" v-model="profileForm.address" rows="2" placeholder="Street, city, country" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20" />
            </div>

          </div>

          <div class="pt-3 border-t border-slate-100 flex justify-end">
            <Button
              type="submit"
              label="Save Changes"
              icon="pi pi-check"
              class="!bg-[#002060] !border-[#002060] hover:!bg-[#001540] !text-white !rounded-lg !px-5 !py-2 !font-semibold"
              :loading="savingProfile"
            />
          </div>

        </form>
      </div>

      <!-- Account Overview Card -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-[#002060]/10 text-[#002060] flex items-center justify-center font-bold">
            <i class="pi pi-id-card text-sm"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-[#002060] m-0">Account Overview</h3>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Your account at a glance</p>
          </div>
        </div>

        <div class="p-4 flex flex-col items-center text-center gap-3">
          <div class="w-20 h-20 rounded-full bg-[#002060]/10 text-[#002060] flex items-center justify-center text-2xl font-bold overflow-hidden ring-2 ring-[#002060]/10">
            <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Avatar" class="w-full h-full object-cover" />
            <span v-else class="text-[#002060] font-extrabold text-2xl">{{ userInitial }}</span>
          </div>

          <div>
            <h2 class="text-base font-bold text-slate-800 m-0">{{ userFullName }}</h2>
            <p v-if="currentUser?.name_kh" class="text-xs font-semibold text-slate-500 font-khmer m-0 mt-0.5">
              {{ currentUser.name_kh }}
            </p>
          </div>

          <div class="flex flex-wrap items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#002060]/10 text-[#002060] border border-[#002060]/20 uppercase tracking-wider">
              <i class="pi pi-shield text-[10px]"></i>
              {{ currentUser?.role || '—' }}
            </span>

            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold border"
              :class="currentUser?.status ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-rose-50 text-rose-700 border-rose-200/80'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="currentUser?.status ? 'bg-emerald-500' : 'bg-rose-500'"></span>
              {{ currentUser?.status ? 'Active Account' : 'Inactive' }}
            </span>
          </div>

          <div class="w-full grid grid-cols-1 gap-2 pt-2 border-t border-slate-100 mt-1">
            <div class="bg-slate-50 rounded-xl px-3.5 py-2.5 border border-slate-200/60 text-left">
              <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider m-0">Username</p>
              <p class="text-xs font-bold text-slate-800 m-0 mt-0.5 font-mono truncate">{{ currentUser?.username || '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl px-3.5 py-2.5 border border-slate-200/60 text-left">
              <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider m-0">Joined</p>
              <p class="text-xs font-bold text-slate-800 m-0 mt-0.5">{{ currentUser?.created_at ? formatDate(currentUser.created_at) : '—' }}</p>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ======= CHANGE PASSWORD DIALOG ======= -->
    <Teleport to="body">
      <div v-if="showPasswordDialog" class="fixed inset-0 z-100 flex items-center justify-center p-4 bg-slate-900/40" @click.self="closePasswordDialog">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
          <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                <i class="pi pi-lock text-sm"></i>
              </div>
              <div>
                <h3 class="text-sm font-bold text-[#002060] m-0">Change Password</h3>
                <p class="text-xs text-slate-400 m-0 mt-0.5">Manage your account security</p>
              </div>
            </div>
            <button type="button" @click="closePasswordDialog" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 border-0 bg-transparent cursor-pointer flex items-center justify-center">
              <i class="pi pi-times text-sm"></i>
            </button>
          </div>

          <form class="p-4 space-y-4" @submit.prevent="savePassword">
            <div class="space-y-3">
              <div class="space-y-1.5">
                <label for="current_password" class="text-xs font-bold text-slate-700">Current password</label>
                <Password
                  id="current_password"
                  v-model="passwordForm.current_password"
                  placeholder="Current password"
                  :feedback="false"
                  toggleMask
                  inputClass="w-full !rounded-lg !border-slate-200 focus:!border-[#002060]"
                  class="w-full"
                />
              </div>

              <div class="space-y-1.5">
                <label for="new_password" class="text-xs font-bold text-slate-700">New password</label>
                <Password
                  id="new_password"
                  v-model="passwordForm.password"
                  placeholder="Minimum 8 characters"
                  :feedback="false"
                  toggleMask
                  inputClass="w-full !rounded-lg !border-slate-200 focus:!border-[#002060]"
                  class="w-full"
                />
              </div>

              <div class="space-y-1.5">
                <label for="new_password_confirmation" class="text-xs font-bold text-slate-700">Confirm new password</label>
                <Password
                  id="new_password_confirmation"
                  v-model="passwordForm.password_confirmation"
                  placeholder="Repeat new password"
                  :feedback="false"
                  toggleMask
                  inputClass="w-full !rounded-lg !border-slate-200 focus:!border-[#002060]"
                  class="w-full"
                />
              </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
              <Button
                type="button"
                label="Cancel"
                class="!bg-slate-100 !border-slate-100 hover:!bg-slate-200 !text-slate-600 !rounded-lg !px-4 !py-2 !font-semibold"
                @click="closePasswordDialog"
              />
              <Button
                type="submit"
                label="Update Password"
                icon="pi pi-lock"
                class="!bg-[#e4ac40] !border-[#e4ac40] hover:!bg-[#d69c30] !text-white !rounded-lg !px-5 !py-2 !font-semibold"
                :loading="savingPassword"
              />
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Password from 'primevue/password'
import { useToast } from 'primevue/usetoast'
import api, { extractError } from '../api'
import { setAuthUser } from '../store/authUser'

const toast = useToast()

const currentUser = ref(null)
const savingProfile = ref(false)
const savingPassword = ref(false)
const avatarInputRef = ref(null)
const showPasswordDialog = ref(false)

const avatarFile = ref(null)
const avatarPreviewUrl = ref(null)

const displayedAvatarUrl = computed(() => avatarPreviewUrl.value || currentUser.value?.avatar_url || null)

const profileForm = ref({
  first_name: '',
  last_name: '',
  name_kh: '',
  gender: null,
  dob: '',
  phone: '',
  address: '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const userFullName = computed(() => {
  const fullName = `${currentUser.value?.first_name || ''} ${currentUser.value?.last_name || ''}`.trim()
  return fullName || currentUser.value?.username || 'User'
})

const userInitial = computed(() => userFullName.value.charAt(0).toUpperCase())

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

const fetchCurrentUser = async () => {
  try {
    const { data } = await api.get('/me')
    currentUser.value = data
    profileForm.value = {
      first_name: data.first_name || '',
      last_name: data.last_name || '',
      name_kh: data.name_kh || '',
      gender: data.gender || null,
      dob: data.dob || '',
      phone: data.phone || '',
      address: data.address || '',
    }
    setAuthUser(data)
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: extractError(error), life: 4000 })
  }
}

onMounted(() => {
  fetchCurrentUser()
})

function revokeAvatarPreview() {
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value)
    avatarPreviewUrl.value = null
  }
}

function onAvatarSelected(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  revokeAvatarPreview()
  avatarFile.value = file
  avatarPreviewUrl.value = URL.createObjectURL(file)
}

onBeforeUnmount(() => revokeAvatarPreview())

const saveProfile = async () => {
  savingProfile.value = true
  try {
    let updatedUser = {}

    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('avatar', avatarFile.value)
      const { data: avatarData } = await api.post('/me/avatar', formData)
      updatedUser = { ...updatedUser, ...avatarData.user }
    }

    const { data } = await api.put('/me', profileForm.value)
    updatedUser = { ...updatedUser, ...data.user }

    currentUser.value = { ...currentUser.value, ...updatedUser }
    setAuthUser(updatedUser)

    avatarFile.value = null
    revokeAvatarPreview()

    toast.add({ severity: 'success', summary: 'Profile updated', detail: 'Your profile was saved.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Could not update profile', detail: extractError(error), life: 5000 })
  } finally {
    savingProfile.value = false
  }
}

function openPasswordDialog() {
  passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
  showPasswordDialog.value = true
}

function closePasswordDialog() {
  showPasswordDialog.value = false
}

const savePassword = async () => {
  if (!passwordForm.value.current_password || !passwordForm.value.password || !passwordForm.value.password_confirmation) {
    toast.add({ severity: 'warn', summary: 'Missing fields', detail: 'Please fill in all password fields.', life: 4000 })
    return
  }

  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    toast.add({ severity: 'warn', summary: 'Passwords do not match', detail: 'New password and confirmation must match.', life: 4000 })
    return
  }

  savingPassword.value = true
  try {
    await api.put('/me/password', passwordForm.value)
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    showPasswordDialog.value = false
    toast.add({ severity: 'success', summary: 'Password changed', detail: 'Your password was updated.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Could not change password', detail: extractError(error), life: 5000 })
  } finally {
    savingPassword.value = false
  }
}
</script>
