<script setup>
defineProps({ tenant: Object, sectionData: Object });
</script>
<template>
    <div class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50 text-center">
        <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ __('Contact Us') }}</h2>
        <div class="max-w-2xl mx-auto space-y-4">
            <p class="text-lg text-gray-700">
                <span class="font-semibold">{{ __('Phone / WhatsApp') }}:</span> 
                {{ tenant.contact_phone || '8801823894527' }}
            </p>
            <div v-if="tenant.social_links && tenant.social_links.length" class="flex justify-center gap-4 mt-6">
                <a v-for="(link, idx) in tenant.social_links" :key="idx" :href="link.url" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-900 capitalize font-medium">
                    {{ link.platform }}
                </a>
            </div>
        </div>
    </div>
</template>
