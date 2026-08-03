<template>
  <!-- បន្ថែម Div រុំរៀបចំ flex ឱ្យលាតពេញ Screen 100% -->
  <div class="w-screen h-screen m-0 p-0 overflow-hidden bg-slate-50 font-sans">
    <router-view />
  </div>
</template>

<script setup>
// Code ផ្សេងៗបើមាន
</script>

<style>
/* Reset Global Styles ដើម្បីកាត់ចោល Margin/Padding ដើមរបស់ Browser */
html, body, #app {
  margin: 0 !important;
  padding: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  overflow: hidden !important;
}
</style>