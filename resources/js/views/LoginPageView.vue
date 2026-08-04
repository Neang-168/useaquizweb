<template>
  <!-- Background Split Screen Layout -->
  <div class="min-h-screen flex items-center justify-center bg-indigo-50 p-4 relative overflow-hidden">
    <!-- Top Decorative Blue Background Side -->
    <div class="absolute top-0 left-0 right-0 h-1/2 bg-indigo-50 z-0"></div>

    <!-- Main Card Container -->
    <div
      class="relative z-10 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">

      <!-- Left Section: Form -->
      <div class="p-8 sm:p-12 flex flex-col justify-center">
        <h1 class="text-3xl font-bold text-surface-900 mb-8 text-center md:text-left">
          Login
        </h1>

        <Message v-if="error" severity="error" icon="pi pi-exclamation-circle" class="mb-5">
          {{ error }}
        </Message>

        <form @submit.prevent="submitLogin" class="space-y-6">
          <!-- Email Input -->
          <div>
            <label for="email" class="mb-2 block text-xs font-semibold text-surface-400 uppercase tracking-wider">
              Username or email
            </label>
            <InputText 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="Enter your email"
              class="w-full !bg-indigo-50 !text-surface-700 placeholder:!text-surface-400 !border-0 !py-3 !px-4  transition-all" 
              :invalid="submitted && !form.email" />
            <small v-if="submitted && !form.email" class="mt-1 block text-red-500">
              Email is required.
            </small>
          </div>

          <!-- Password Input -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label for="password" class="text-xs font-semibold text-surface-400 uppercase tracking-wider">
                Password
              </label>
              <a href="#" class="text-xs font-semibold text-indigo-600 hover:underline">
                Forgot password ?
              </a>
            </div>

            <Password 
              id="password" 
              v-model="form.password" 
              placeholder="Enter your password" 
              :feedback="false"
              toggleMask class="w-full" 
              inputClass="w-full !bg-indigo-50 !text-surface-700 placeholder:!text-surface-400 !border-0 !py-3 !px-4
                           transition-all"
              toggleIconClass="!text-surface-500 !right-4"
              :invalid="submitted && !form.password"
              />
              <small v-if="submitted && !form.password" class="mt-1 block text-red-500">
                Password is required.
              </small>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center gap-2">
            <Checkbox v-model="rememberMe" inputId="remember" :binary="true" />
            <label for="remember" class="cursor-pointer text-xs text-surface-500 font-medium">
              Remember me
            </label>
          </div>

          <!-- Submit Button -->
          <Button type="submit" label="Login"
            class="w-full !bg-indigo-600 hover:!bg-indigo-700 !border-0 !py-3 !rounded-xl !text-white !font-medium"
            :loading="loading" />
        </form>

        <div class="mt-12 text-center text-xs text-surface-400">
          Don't have an account ?
          <a href="#" class="font-bold text-indigo-600 hover:underline">Sign up</a>
        </div>
      </div>

      <!-- Right Section: Logo & Branding Place -->
      <div
        class="bg-indigo-50/70 p-8 sm:p-12 flex flex-col justify-between items-center text-center border-l border-surface-100">

        <!-- Center Logo Section -->
        <div class="my-auto flex flex-col items-center justify-center space-y-6">
          <!-- Logo Image / Placeholder -->
          <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl bg-white p-4 shadow-md flex items-center justify-center">
            <img src="@/images/usea_logo.png" alt="University Logo" class="max-h-full max-w-full object-contain" />
          </div>

          <!-- Title & Description -->
          <div>
            <h2 class="text-xl font-bold text-surface-800">
              University Quiz Management System
            </h2>
            <p class="mt-3 text-sm text-surface-500 leading-relaxed max-w-xs mx-auto">
              Welcome back! Please log in with your credentials to manage quizzes, view reports, and track system
              activities.
            </p>
          </div>
        </div>

        <!-- Pagination / Indicator Line Mock -->
        <div class="flex gap-2 items-center justify-center pt-4">
          <span class="w-8 h-1 bg-indigo-600 rounded-full"></span>
          <span class="w-8 h-1 bg-indigo-600 rounded-full"></span>
          <span class="w-8 h-1 bg-indigo-600 rounded-full"></span>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

// import LogoUsea from '../images/usea_logo.png'

import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import Message from 'primevue/message'

const router = useRouter()

const form = ref({
  email: 'superadmin@example.com',
  password: 'password',
})

const error = ref('')
const loading = ref(false)
const submitted = ref(false)
const rememberMe = ref(false)

async function submitLogin() {
  submitted.value = true
  error.value = ''

  if (!form.value.email || !form.value.password) {
    return
  }

  loading.value = true

  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        email: form.value.email,
        password: form.value.password,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Invalid email or password.')
    }

    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))

    if (rememberMe.value) {
      localStorage.setItem('remember_me', 'true')
    }

    router.push({ name: 'admin.dashboard' })
  } catch (err) {
    error.value = err.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>