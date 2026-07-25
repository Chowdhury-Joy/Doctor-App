<script setup>
import { useForm } from '@inertiajs/vue3';
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
    <div class="space-y-8">
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Select a Doctor & Session
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="doctor in doctors" :key="doctor.id" class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow bg-white">
                    <h3 class="font-bold text-xl text-gray-900">{{ doctor.name }}</h3>
                    <p class="text-blue-600 font-medium text-sm mb-5">{{ doctor.specialty }}</p>
                    
                    <div v-if="doctor.schedule_sessions.length > 0">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Available Sessions</h4>
                        <div class="space-y-3">
                            <button 
                                v-for="session in doctor.schedule_sessions" 
                                :key="session.id"
                                @click="selectSession(session)"
                                :class="[
                                    'w-full text-left p-4 rounded-lg border-2 text-sm transition-all duration-200',
                                    form.session_id === session.id 
                                        ? 'border-blue-500 bg-blue-50/50 shadow-sm' 
                                        : 'border-transparent bg-gray-50 hover:bg-gray-100 hover:border-gray-200'
                                ]"
                            >
                                <div class="font-bold text-gray-800">{{ session.session_name }} <span class="font-normal text-gray-500">({{ session.chamber.name }})</span></div>
                                <div class="text-xs text-gray-600 mt-1 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ session.start_time }} - {{ session.end_time }}
                                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-[10px] font-bold ml-auto">Cap: {{ session.slot_cap }}</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
                        No sessions scheduled at the moment.
                    </div>
                </div>
            </div>
        </div>

        <div v-if="selectedSession" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100 transform transition-all animate-fade-in-up">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Patient Details
            </h2>
            
            <form @submit.prevent="submitBooking" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Appointment Date</label>
                        <input type="date" v-model="form.date" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                        <div v-if="form.errors.date" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.date }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Patient Name</label>
                        <input type="text" v-model="form.name" placeholder="John Doe" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.name }}</div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" v-model="form.phone" placeholder="+8801700000000" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                    <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.phone }}</div>
                </div>
                
                <div v-if="form.errors.error" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ form.errors.error }}</span>
                </div>

                <div class="pt-4">
                    <button type="submit" :disabled="form.processing" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-indigo-700 focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 transition-all transform hover:-translate-y-0.5">
                        Confirm Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}
</style>
