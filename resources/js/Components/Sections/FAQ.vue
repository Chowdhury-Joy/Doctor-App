<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <div v-if="tenant.faq && tenant.faq.length" class="py-16 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <h2 class="text-3xl font-bold mb-8 text-center text-gray-900">{{ __('Frequently Asked Questions') }}</h2>
        <div class="space-y-6">
            <div v-for="(item, idx) in tenant.faq" :key="idx" class="bg-white p-6 rounded-lg shadow-sm border">
                <h3 class="text-lg font-semibold mb-2 text-gray-900">{{ item.question }}</h3>
                <p class="text-gray-600 whitespace-pre-wrap">{{ item.answer }}</p>
            </div>
        </div>
    </div>
</template>
