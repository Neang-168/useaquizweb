<template>
  <div>
    <LoginPage v-if="!user" @logged-in="handleLogin" />
    <RoleProfile v-else :user="user" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import LoginPage from './components/LoginPage.vue';
import RoleProfile from './components/RoleProfile.vue';

const user = ref(null);

function handleLogin(loggedInUser) {
  user.value = loggedInUser;
}
</script>