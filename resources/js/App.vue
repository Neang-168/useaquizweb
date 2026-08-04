<template>
  <!-- Main Container: ប្រើ w-full h-screen លុប Margin/Padding ចោលទាំងអស់ -->
  <div class="flex h-screen w-full m-0 p-0 overflow-hidden bg-slate-100 font-sans antialiased">

    <!-- 2. Main Content Container ខាងស្តាំ -->
    <div class="flex-1 h-full overflow-y-auto">
      
      <!-- 3. កន្លែងលោតបង្ហាញ Dashboard / Profile / Users -->
      <!-- ដក p-6 ចេញពី <main> ហើយយកទៅដាក់ក្នុង Wrapper ខាងក្នុងវិញ ដើម្បីឱ្យ Content មិនបុកគែម តែ Layout មេនៅតែបឺតជាប់ ១០០% -->
      <main class="w-full h-full p-6 md:p-8">
        <router-view />
      </main>

    </div>
  </div>
</template>

<script setup>
</script>