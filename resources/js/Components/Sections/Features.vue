<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <div v-if="tenant.features && tenant.features.length" class="py-16 px-4 sm:px-6 lg:px-8 bg-white max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold mb-10 text-center text-gray-900">{{ __('Why Choose Us') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="(feature, idx) in tenant.features" :key="idx" class="p-6 border rounded-lg bg-gray-50 shadow-sm">
                <h3 class="text-xl font-semibold mb-2" :style="{ color: tenant.theme_color }">{{ feature.title }}</h3>
                <p class="text-gray-600">{{ feature.description }}</p>
            </div>
        </div>
    </div>
</template>
