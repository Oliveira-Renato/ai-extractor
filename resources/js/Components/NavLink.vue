<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-[#d7ff45] transition duration-150 ease-in-out'
        : 'inline-flex items-center rounded-full px-4 py-2 text-sm font-medium text-white/55 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#d7ff45] transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
