<template>
  <div class="flex h-screen bg-slate-100">
    <!-- 1. Sidebar -->
    <!-- <AdminSidebar /> -->

    <!-- 2. Main Content Container -->
    <div class="flex-1 flex flex-col overflow-y-auto">
      
      <!-- 3. កន្លែងលោតបង្ហាញ Dashboard / Profile / Users -->
      <main class="p-6">
        <router-view />  <!--  ចំណុចសំខាន់គឺត្រង់នេះ! -->
      </main>

    </div>
  </div>
</template>

<script setup>
import AdminSidebar from '@/components/AdminSidebar.vue'
</script>