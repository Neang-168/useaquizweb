<template>
  <!-- ប្រើ w-full h-screen ដើម្បីឱ្យពេញអេក្រង់ស្អាត និងលុប Overflow អាក្រក់មើល -->
  <div class="flex h-screen w-full overflow-hidden bg-slate-100 font-sans antialiased">
  

    <!-- 2. Main Content ខាងស្តាំ (លាតពេញគែម) -->
    <main class="flex-1 h-full overflow-y-auto p-6 md:p-8">
      <!-- ដក max-w ចេញ ឬប្រើ w-full ដើម្បីឱ្យវាលាតពេញអេក្រង់ស្មើគែម -->
      <div class="w-full">
        <router-view />
      </div>
    </main>

  </div>
</template>

<script setup>
import AdminSidebar from '../components/AdminSidebar.vue'
</script>