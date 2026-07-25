<script setup>
import { Head } from '@inertiajs/vue3';
import BookingWidget from '@/Components/BookingWidget.vue';
import SectionRenderer from '@/Components/SectionRenderer.vue';

defineProps({
    tenant: Object,
    doctors: Array
});
</script>

<template>
    <Head :title="tenant.name || 'Medical Center'" />
    
    <div class="min-h-screen flex flex-col md:flex-row font-sans bg-gray-50">
        <!-- Sidebar Navigation / Identity -->
        <aside class="w-full md:w-80 bg-slate-900 text-white flex flex-col min-h-[30vh] md:min-h-screen shadow-xl z-10 shrink-0">
            <div class="p-8 flex-grow">
                <div class="w-16 h-16 bg-blue-500 rounded-xl mb-6 shadow-lg flex items-center justify-center text-2xl font-bold">
                    {{ (tenant.name || 'M').charAt(0) }}
                </div>
                
                <h1 class="text-3xl font-bold tracking-tight mb-2">
                    {{ tenant.name || 'Medical Center' }}
                </h1>
                
                <p class="text-slate-400 mb-8 font-light">
                    {{ tenant.tagline || 'Excellence in healthcare.' }}
                </p>
                
                <div class="space-y-4">
                    <a href="#booking" class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 text-center rounded-lg font-medium transition-colors">
                        {{ __('Book Appointment') }}
                    </a>
                </div>
                
                <div class="mt-12 space-y-4 text-sm text-slate-400">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <p>{{ __('Main Clinic, Dhaka') }}</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <p>{{ tenant.contact_phone || '8801823894527' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-8 text-xs text-slate-500 border-t border-slate-800">
                &copy; {{ new Date().getFullYear() }} {{ tenant.name || 'Medical Center' }}
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow flex flex-col relative h-screen overflow-y-auto">
            <SectionRenderer :tenant="tenant" :doctors="doctors">
                <!-- Hide redundant sections -->
                <template #hero><div></div></template>
                <template #contact><div></div></template>
                <template #footer><div></div></template>
                
                <!-- Default sections will be rendered -->
            </SectionRenderer>
        </main>
    </div>
</template>
