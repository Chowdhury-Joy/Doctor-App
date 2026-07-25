<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <div class="py-20 px-4 sm:px-6 lg:px-8 text-center max-w-4xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4 text-gray-900" :style="{ color: tenant.theme_color }">
            {{ tenant.name || 'Medical Center' }}
        </h1>
        <p class="text-lg sm:text-xl text-gray-600 mb-8 font-light leading-relaxed">
            {{ tenant.tagline || 'Expert care is just a few clicks away.' }}
        </p>
        <div v-if="sectionData.show_button !== false" class="flex justify-center">
            <a href="#booking" class="px-8 py-3 rounded-full font-bold shadow-md hover:shadow-lg transition-all text-white" :style="{ backgroundColor: tenant.theme_color }">
                {{ __('Book Now') }}
            </a>
        </div>
    </div>
</template>
