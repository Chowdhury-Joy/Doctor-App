<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenant: Object,
    doctors: Array
});

const selectedSession = ref(null);

const form = useForm({
    session_id: '',
    date: new Date().toISOString().split('T')[0], // Default to today
    name: '',
    phone: '',
});

const selectSession = (session) => {
    selectedSession.value = session;
    form.session_id = session.id;
};

const submitBooking = () => {
    form.post('/bookings', {
        onSuccess: () => {
            // success handles redirect by the controller
        },
    });
};
</script>

<template>
    <Head :title="tenant.id" />
    
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center uppercase tracking-widest">
                {{ tenant.id }} Medical Center
            </h1>

            <div class="bg-white rounded-xl shadow p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Select a Doctor & Session</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="doctor in doctors" :key="doctor.id" class="border rounded p-4">
                        <h3 class="font-bold text-lg">{{ doctor.name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ doctor.specialty }}</p>
                        
                        <div v-if="doctor.schedule_sessions.length > 0">
                            <h4 class="text-sm font-semibold mb-2">Available Sessions:</h4>
                            <div class="space-y-2">
                                <button 
                                    v-for="session in doctor.schedule_sessions" 
                                    :key="session.id"
                                    @click="selectSession(session)"
                                    :class="[
                                        'w-full text-left p-3 rounded border text-sm transition',
                                        form.session_id === session.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'
                                    ]"
                                >
                                    <div class="font-medium">{{ session.session_name }} ({{ session.chamber.name }})</div>
                                    <div class="text-xs text-gray-500">{{ session.start_time }} - {{ session.end_time }} (Cap: {{ session.slot_cap }})</div>
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            No sessions scheduled.
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="selectedSession" class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Book Your Appointment</h2>
                
                <form @submit.prevent="submitBooking" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" v-model="form.date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <div v-if="form.errors.date" class="text-red-500 text-xs mt-1">{{ form.errors.date }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Patient Name</label>
                        <input type="text" v-model="form.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" v-model="form.phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                    </div>
                    
                    <div v-if="form.errors.error" class="p-3 bg-red-100 text-red-700 rounded text-sm">
                        {{ form.errors.error }}
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50">
                        Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
