<script setup>
defineProps({ tenant: Object, sectionData: Object, doctors: Array });
</script>
<template>
    <div id="booking" class="py-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-8 text-gray-900">{{ __('Our Doctors') }}</h2>
        
        <div v-if="doctors && doctors.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <div v-for="doctor in doctors" :key="doctor.id" class="p-6 border rounded-lg bg-white shadow-sm text-left">
                <h3 class="text-lg font-bold">{{ doctor.name }}</h3>
                <p class="text-gray-600 text-sm mb-2">{{ doctor.specialty }}</p>
                <p class="text-xs text-gray-500">{{ doctor.credentials }}</p>
            </div>
        </div>
        
        <a href="/book" class="inline-block px-8 py-3 rounded-full font-bold shadow-md hover:shadow-lg transition-all text-white" :style="{ backgroundColor: tenant.theme_color }">
            {{ __('Book an Appointment') }}
        </a>
    </div>
</template>
