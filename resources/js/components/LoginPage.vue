<template>
  <div class="min-h-screen flex items-center justify-center bg-surface-50 px-4 py-8">
    <Card class="w-full max-w-md shadow-xl border border-surface-200">
      <template #content>
        <!-- Header -->
        <div class="text-center mb-8">
          <div
            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-primary"
          >
            <i class="pi pi-lock text-2xl text-white"></i>
          </div>

          <h1 class="text-2xl font-bold text-surface-900">
            Welcome Back
          </h1>

          <p class="mt-2 text-sm text-surface-500">
            Sign in to access your account
          </p>
        </div>

        <!-- Error -->
        <Message
          v-if="error"
          severity="error"
          icon="pi pi-exclamation-circle"
          class="mb-5"
        >
          {{ error }}
        </Message>

        <!-- Login Form -->
        <form @submit.prevent="submitLogin" class="space-y-5">

          <!-- Email -->
          <div>
            <label
              for="email"
              class="mb-2 block text-sm font-medium text-surface-700"
            >
              Email Address
            </label>

            <InputText
              id="email"
              v-model="form.email"
              type="email"
              placeholder="Enter your email"
              class="w-full"
              :invalid="submitted && !form.email"
            />

            <small
              v-if="submitted && !form.email"
              class="mt-1 block text-red-500"
            >
              Email is required.
            </small>
          </div>

          <!-- Password -->
          <div>
            <label
              for="password"
              class="mb-2 block text-sm font-medium text-surface-700"
            >
              Password
            </label>

            <Password
              id="password"
              v-model="form.password"
              placeholder="Enter your password"
              :feedback="false"
              toggleMask
              class="w-full"
              inputClass="w-full"
              :invalid="submitted && !form.password"
            />

            <small
              v-if="submitted && !form.password"
              class="mt-1 block text-red-500"
            >
              Password is required.
            </small>
          </div>

          <!-- Remember / Forgot -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Checkbox
                v-model="rememberMe"
                inputId="remember"
                :binary="true"
              />

              <label
                for="remember"
                class="cursor-pointer text-sm text-surface-600"
              >
                Remember me
              </label>
            </div>

            <a
              href="#"
              class="text-sm font-medium text-primary hover:underline"
            >
              Forgot password?
            </a>
          </div>

          <!-- Login Button -->
          <Button
            type="submit"
            label="Sign In"
            icon="pi pi-sign-in"
            iconPos="right"
            class="w-full"
            :loading="loading"
          />
        </form>

        <!-- Footer -->
        <div class="mt-7 text-center">
          <p class="text-sm text-surface-500">
            University Quiz Management System
          </p>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { ref } from 'vue';

import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

const form = ref({
  email: 'superadmin@example.com',
  password: 'password',
});

const error = ref('');
const loading = ref(false);
const submitted = ref(false);
const rememberMe = ref(false);

const emit = defineEmits(['logged-in']);

async function submitLogin() {
  submitted.value = true;
  error.value = '';

  if (!form.value.email || !form.value.password) {
    return;
  }

  loading.value = true;

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
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Invalid email or password.');
    }

    localStorage.setItem('auth_token', data.token);

    if (rememberMe.value) {
      localStorage.setItem('remember_me', 'true');
    }

    emit('logged-in', data.user);

  } catch (err) {
    error.value = err.message || 'Login failed. Please try again.';
  } finally {
    loading.value = false;
  }
}
</script>
