<script setup>
import { computed } from 'vue';
import Hero from '@/Components/Sections/Hero.vue';
import About from '@/Components/Sections/About.vue';
import Doctors from '@/Components/Sections/Doctors.vue';
import Services from '@/Components/Sections/Services.vue';
import Chambers from '@/Components/Sections/Chambers.vue';
import Features from '@/Components/Sections/Features.vue';
import FAQ from '@/Components/Sections/FAQ.vue';
import Contact from '@/Components/Sections/Contact.vue';

const props = defineProps({
    tenant: Object,
    doctors: Array
});

const sectionMap = {
    'hero': Hero,
    'about': About,
    'doctors': Doctors,
    'services': Services,
    'chambers': Chambers,
    'features': Features,
    'faq': FAQ,
    'contact': Contact,
};

const visibleSections = computed(() => {
    let defaultSections = [
        { type: 'hero', is_visible: true },
        { type: 'about', is_visible: true },
        { type: 'doctors', is_visible: true },
        { type: 'services', is_visible: true },
        { type: 'chambers', is_visible: true },
        { type: 'features', is_visible: true },
        { type: 'faq', is_visible: true },
        { type: 'contact', is_visible: true },
    ];
    let sections = props.tenant.sections && props.tenant.sections.length > 0 
        ? props.tenant.sections 
        : defaultSections;
        
    return sections.filter(s => s.is_visible);
});
</script>

<template>
    <template v-for="section in visibleSections" :key="section.type">
        <slot :name="section.type" :section="section" :tenant="tenant" :doctors="doctors">
            <component 
                :is="sectionMap[section.type]" 
                v-if="sectionMap[section.type]"
                :tenant="tenant" 
                :doctors="doctors" 
                :section-data="section" 
            />
        </slot>
    </template>
</template>
