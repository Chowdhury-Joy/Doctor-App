<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <!-- Placeholder for Chambers/Locations if we want a separate section -->
    <div v-if="tenant.plan_tier === 'clinic'" class="py-16 px-4 sm:px-6 lg:px-8 text-center bg-gray-50">
        <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ __('Our Locations') }}</h2>
        <p class="text-gray-600">Location list coming soon.</p>
    </div>
</template>
