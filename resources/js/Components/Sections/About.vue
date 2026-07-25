<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <div v-if="tenant.about_text" class="py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ __('About Us') }}</h2>
        <div class="text-gray-600 leading-relaxed whitespace-pre-wrap text-lg">
            {{ tenant.about_text }}
        </div>
    </div>
</template>
