<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({ tenant: Object, sectionData: Object });

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');

const switchLocale = (locale) => {
    router.post('/locale', { locale }, { preserveScroll: true });
};
</script>
<template>
    <footer class="bg-gray-900 text-gray-400 py-10 text-center mt-auto">
        <p class="text-sm">&copy; {{ new Date().getFullYear() }} {{ tenant.name || 'Medical Center' }}. {{ __('All rights reserved.') }}</p>
        <div class="mt-4 flex justify-center items-center gap-4 text-sm">
            <button @click="switchLocale('en')" :class="['hover:text-white transition-colors', currentLocale === 'en' ? 'text-white font-bold' : 'text-gray-500']">English</button>
            <span class="text-gray-600">|</span>
            <button @click="switchLocale('bn')" :class="['hover:text-white transition-colors', currentLocale === 'bn' ? 'text-white font-bold' : 'text-gray-500']">বাংলা</button>
        </div>
    </footer>
</template>
