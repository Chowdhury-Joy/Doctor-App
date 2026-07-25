<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <!-- Placeholder for Task 6 Lab Tests -->
    <div v-if="tenant.plan_tier === 'clinic'" class="py-16 px-4 sm:px-6 lg:px-8 text-center bg-white max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ __('Services & Lab Tests') }}</h2>
        <p class="text-gray-600">Lab testing coming soon.</p>
    </div>
</template>
