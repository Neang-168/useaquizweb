<template>
  <div class="fixed top-0 left-0 w-screen h-screen m-0 p-0 flex bg-slate-100 font-sans antialiased overflow-hidden">
    
    <TeacherSidebar class="shrink-0 h-full" />

    <!--Content-->
    <main class="flex-1 h-full overflow-y-auto p-6 md:p-8">
      <router-view  />
    </main>

  </div>
</template>

<script setup>
import TeacherSidebar from '../components/TeacherSidebar.vue'
</script>