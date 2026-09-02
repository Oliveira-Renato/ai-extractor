<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ checkoutUrl: String });
</script>

<template>
    <Head title="Pricing" />
    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-4.5rem)] bg-[#f4f4f1] px-5 py-12 text-[#111214] sm:px-8 sm:py-16">
            <main class="mx-auto max-w-6xl">
                <Link :href="route('dashboard')" class="text-sm text-[#72756e] transition hover:text-[#111214]">← Back to workspace</Link>
                <div class="mt-12 max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[.24em] text-[#72756e]">Simple, transparent pricing</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-[-.04em] sm:text-6xl">More data. Less manual work.</h1>
                    <p class="mt-5 text-lg leading-8 text-[#686b65]">Everything you need to turn documents into decisions, without a complicated setup.</p>
                </div>
                <div class="mt-12 grid gap-5 lg:grid-cols-[minmax(0,26rem)_1fr]">
                    <section class="rounded-[1.75rem] bg-[#111214] p-7 text-white shadow-[0_20px_60px_rgba(17,18,20,.15)] sm:p-9">
                        <div class="flex items-start justify-between gap-4"><div><p class="text-2xl font-semibold">Starter</p><p class="mt-2 text-sm text-white/50">For lean teams and growing workflows</p></div><span class="rounded-full bg-[#d7ff45] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#111214]">Popular</span></div>
                        <p class="mt-10 text-5xl font-semibold tracking-[-.05em]">$29<span class="text-base font-normal text-white/45"> / month</span></p>
                        <ul class="mt-8 grid gap-4 text-sm text-white/75"><li>✓ 300 documents per month</li><li>✓ Gemini-powered extraction</li><li>✓ CSV and JSON exports</li><li>✓ PDF and image files</li></ul>
                        <a v-if="checkoutUrl" :href="checkoutUrl" class="mt-10 block rounded-xl bg-[#d7ff45] px-5 py-3.5 text-center text-sm font-bold text-[#111214] transition hover:bg-[#e5ff8c]">Subscribe to Starter <span class="ml-2">→</span></a>
                        <div v-else class="mt-10 rounded-xl border border-white/15 px-5 py-3.5 text-center text-sm text-white/55">Checkout coming soon</div>
                    </section>
                    <section class="rounded-[1.75rem] border border-[#deded8] bg-white p-7 sm:p-9"><p class="text-xs font-bold uppercase tracking-[.22em] text-[#8b8e87]">Built for focus</p><h2 class="mt-4 text-2xl font-semibold">A calm workspace for busy teams.</h2><p class="mt-4 max-w-lg leading-7 text-[#686b65]">Ledgerly keeps the upload, extraction, and export flow clear so your team can spend time acting on information—not retyping it.</p><div class="mt-9 grid gap-4 sm:grid-cols-3"><div class="rounded-2xl bg-[#f4f4f1] p-4"><p class="text-2xl font-semibold">98.4%</p><p class="mt-1 text-xs text-[#72756e]">Typical accuracy</p></div><div class="rounded-2xl bg-[#f4f4f1] p-4"><p class="text-2xl font-semibold">&lt;30s</p><p class="mt-1 text-xs text-[#72756e]">Typical processing</p></div><div class="rounded-2xl bg-[#f4f4f1] p-4"><p class="text-2xl font-semibold">24/7</p><p class="mt-1 text-xs text-[#72756e]">Always available</p></div></div></section>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
