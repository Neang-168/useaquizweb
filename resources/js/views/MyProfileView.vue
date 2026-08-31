<template>
  <div class="space-y-6 w-full max-w-7xl mx-auto pb-16 font-sans">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-black tracking-tight m-0">My Profile</h1>
        <p class="text-slate-500 text-sm mt-1 m-0">View and manage your account information</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#002060]/5 text-[#002060] border border-[#002060]/10">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          System Online
        </span>
      </div>
    </div>

    <!-- ======= CLEAN PROFILE SUMMARY CARD (NO COVER) ======= -->
    <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
      
      <!-- Left: Avatar & User Details -->
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
        <!-- Avatar Container -->
        <div class="relative group shrink-0">
          <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-[#002060]/10 text-[#002060] flex items-center justify-center text-2xl font-bold overflow-hidden ring-2 ring-[#002060]/10">
            <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Avatar" class="w-full h-full object-cover" />
            <span v-else class="text-[#002060] font-extrabold text-3xl">
              {{ userInitial }}
            </span>
          </div>
          <button 
            type="button"
            @click="avatarInputRef?.click()"
            class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-[#63c7df] text-white flex items-center justify-center shadow-md hover:bg-[#63d7cf] transition-colors duration-200 border border-white cursor-pointer"
            title="Change Photo"
          >
            <i class="pi pi-camera text-xs "></i>
          </button>
        </div>

        <!-- Name & Badges -->
        <div class="space-y-2">
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
            <h2 class="text-xl font-bold text-slate-800 m-0">{{ userFullName }}</h2>
            <span v-if="currentUser?.name_kh" class="text-sm font-semibold text-slate-500 font-khmer">
              ({{ currentUser.name_kh }})
            </span>
          </div>

          <!-- Status Badges -->
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
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

            <span v-if="currentUser?.created_at" class="text-xs text-slate-400 font-medium ml-1">
              <i class="pi pi-calendar text-[11px] mr-1"></i>Joined {{ formatDate(currentUser.created_at) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Username & Email Cards -->
      <div class="flex items-center justify-center gap-3 w-full md:w-auto pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
        <div class="flex-1 md:flex-none px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center md:text-left">
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider m-0">Username</p>
          <p class="text-xs font-bold text-slate-800 m-0 mt-0.5 font-mono">{{ currentUser?.username || '—' }}</p>
        </div>
        <div class="flex-1 md:flex-none px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center md:text-left">
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider m-0">Email</p>
          <p class="text-xs font-bold text-slate-800 m-0 mt-0.5 truncate max-w-[160px]">{{ currentUser?.email || '—' }}</p>
        </div>
      </div>

    </div>

    <!-- ======= FORMS GRID ======= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

      <!-- Personal Information Card -->
      <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#002060]/10 text-[#002060] flex items-center justify-center font-bold">
              <i class="pi pi-user text-sm"></i>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800 m-0">Personal Information</h3>
              <p class="text-xs text-slate-400 m-0 mt-0.5">Update your personal details</p>
            </div>
          </div>
        </div>

        <form class="p-6 space-y-5" @submit.prevent="saveProfile">

          <!-- Avatar Pick Row -->
          <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-[#002060] text-white flex items-center justify-center text-lg font-bold overflow-hidden shrink-0 shadow-xs">
              <img v-if="displayedAvatarUrl" :src="displayedAvatarUrl" alt="Avatar" class="w-full h-full object-cover" />
              <span v-else>{{ userInitial }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <span class="text-xs font-bold text-slate-700 block mb-1">Profile picture</span>
              <div class="flex items-center gap-2">
                <Button 
                  type="button" 
                  label="Choose Photo" 
                  icon="pi pi-camera" 
                  size="small" 
                  class="!bg-[#63c7df] !border-[#d8e7ec] hover:!bg-[#63dfcf] !text-white !rounded-lg !text-xs !font-semibold"
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
              <label for="email" class="text-xs font-bold text-slate-700">Email</label>
              <InputText id="email" v-model="profileForm.email" type="email" placeholder="jane@example.com" class="w-full !rounded-lg !border-slate-200 focus:!border-[#002060] focus:!ring-2 focus:!ring-[#002060]/20" />
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

      <!-- Change Password Card -->
      <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
              <i class="pi pi-lock text-sm"></i>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800 m-0">Change Password</h3>
              <p class="text-xs text-slate-400 m-0 mt-0.5">Manage your account security</p>
            </div>
          </div>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="savePassword">

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

          <div class="pt-3 border-t border-slate-100 flex justify-end">
            <Button 
              type="submit" 
              label="Update Password" 
              icon="pi pi-lock" 
              class="!bg-[#e4ac40] !border-white hover:!bg-[#e8ba5c] !text-white !rounded-lg !px-5 !py-2 !font-semibold shadow-2xl"
              :loading="savingPassword" 
            />
          </div>

        </form>
      </div>

    </div>
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
  email: '',
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
      email: data.email || '',
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
    toast.add({ severity: 'success', summary: 'Password changed', detail: 'Your password was updated.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Could not change password', detail: extractError(error), life: 5000 })
  } finally {
    savingPassword.value = false
  }
}
</script>