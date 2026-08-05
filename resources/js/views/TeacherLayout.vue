<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden">
    <!-- Teacher Sidebar -->
    <TeacherSidebar />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-y-auto">
      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import TeacherSidebar from '../components/TeacherSidebar.vue'
</script>