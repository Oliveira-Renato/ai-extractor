<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#f4f4f1] text-[#111214]">
        <nav class="border-b border-white/10 bg-[#101113] text-white">
            <div class="mx-auto flex h-[4.5rem] max-w-7xl items-center justify-between px-5 sm:px-8">
                <div class="flex items-center gap-8">
                    <Link :href="route('dashboard')" class="flex items-center gap-3" aria-label="Ledgerly home">
                        <ApplicationLogo class="h-9 w-9 text-[#d7ff45]" />
                        <span class="hidden text-sm font-semibold tracking-[.16em] text-white sm:inline">LEDGERLY</span>
                    </Link>
                    <div class="hidden items-center gap-1 md:flex">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</NavLink>
                        <NavLink :href="route('pricing')" :active="route().current('pricing')">Pricing</NavLink>
                    </div>
                </div>
                <div class="hidden items-center sm:flex">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-2 text-sm text-white/70 transition hover:border-white/25 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#d7ff45]">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#d7ff45] text-xs font-bold text-[#101113]">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                                <span class="max-w-28 truncate">{{ $page.props.auth.user.name }}</span>
                                <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/70 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-[#d7ff45] sm:hidden" :aria-expanded="showingNavigationDropdown" aria-label="Toggle navigation" @click="showingNavigationDropdown = !showingNavigationDropdown">
                    <svg v-if="!showingNavigationDropdown" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
            <div v-show="showingNavigationDropdown" class="border-t border-white/10 bg-[#17181a] px-5 py-4 sm:hidden">
                <div class="grid gap-1">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('pricing')" :active="route().current('pricing')">Pricing</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log out</ResponsiveNavLink>
                </div>
            </div>
        </nav>
        <main><slot /></main>
        <footer class="border-t border-[#deded8] bg-[#101113] text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-8 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <div class="flex items-center gap-3"><ApplicationLogo class="h-7 w-7 text-[#d7ff45]" /><span class="font-semibold tracking-[.14em]">LEDGERLY</span></div>
                <p class="text-white/50">Built and offered by <a href="https://oliveiratechworks.com" target="_blank" rel="noreferrer" class="text-white/80 underline decoration-[#d7ff45] underline-offset-4 transition hover:text-[#d7ff45]">oliveiratechworks.com</a></p>
            </div>
        </footer>
    </div>
</template>
