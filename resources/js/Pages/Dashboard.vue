<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ documentsUsed: Number, documents: Array });
const fileInput = ref(null);
const isDragging = ref(false);
const form = useForm({ file: null });

function chooseFile(file) {
    if (file && !form.processing) {
        form.file = file;
    }
}

function handleDrop(event) {
    isDragging.value = false;
    chooseFile(event.dataTransfer.files?.[0]);
}

function clearFile() {
    form.file = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function submit() {
    if (form.file && !form.processing) {
        form.post(route('documents.store'), {
            forceFormData: true,
            onError: () => clearFile(),
        });
    }
}

function formatStatus(status) {
    return { processed: 'Ready', pending: 'Processing', failed: 'Failed' }[status] || status;
}
</script>

<template>
    <Head title="Workspace" />
    <AuthenticatedLayout>
        <div class="bg-[#f4f4f1]">
            <main class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-16">
                <div class="mb-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                    <div class="max-w-2xl">
                        <p class="mb-4 text-xs font-bold uppercase tracking-[.24em] text-[#72756e]">Workspace / Overview</p>
                        <h1 class="text-4xl font-semibold tracking-[-.04em] text-[#111214] sm:text-6xl">Turn documents into decisions.</h1>
                        <p class="mt-5 max-w-xl text-base leading-7 text-[#686b65] sm:text-lg">Upload one invoice or receipt and get clean, structured data in seconds.</p>
                    </div>
                    <div class="flex w-fit items-center gap-3 rounded-full border border-[#d9d9d3] bg-white px-4 py-2 text-sm text-[#555852] shadow-sm"><span class="h-2 w-2 rounded-full bg-[#d7ff45]"></span>Starter plan <span class="text-[#b2b3ad]">·</span> {{ documentsUsed }} / 300 this month</div>
                </div>

                <section class="grid gap-5 lg:grid-cols-[1.45fr_1fr]">
                    <form class="relative overflow-hidden rounded-[1.75rem] bg-[#111214] p-6 text-white shadow-[0_20px_60px_rgba(17,18,20,.15)] sm:p-10" :class="{ 'ring-4 ring-[#d7ff45]/70': isDragging }" @submit.prevent="submit" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop">
                        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#d7ff45]/10 blur-3xl"></div>
                        <div class="relative">
                            <div class="mb-10 flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.22em] text-white/45">Step 01</p><h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Start a new extraction</h2></div><span class="rounded-full border border-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#d7ff45]">AI powered</span></div>
                            <button type="button" class="group flex min-h-56 w-full flex-col items-center justify-center rounded-2xl border border-dashed border-white/25 bg-white/[.04] px-5 text-center transition hover:border-[#d7ff45] hover:bg-white/[.08] focus:outline-none focus:ring-2 focus:ring-[#d7ff45]" :disabled="form.processing" @click="fileInput.click()">
                                <span class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#d7ff45] text-3xl font-light text-[#111214] transition group-hover:scale-105">+</span>
                                <span class="font-medium">{{ form.file ? form.file.name : 'Drop one document here' }}</span>
                                <span class="mt-2 text-sm text-white/50">PDF, JPG, PNG, or WEBP · up to 20 MB</span>
                            </button>
                            <input ref="fileInput" type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp" @change="chooseFile($event.target.files[0])" />
                            <div v-if="form.file" class="mt-4 flex items-center justify-between rounded-xl border border-white/10 bg-white/[.06] px-4 py-3 text-sm"><span class="min-w-0 truncate text-white/80">{{ form.file.name }}</span><button type="button" class="ml-4 shrink-0 text-white/45 underline underline-offset-4 hover:text-white" @click="clearFile">Remove</button></div>
                            <button type="submit" :disabled="!form.file || form.processing" class="mt-5 flex w-full items-center justify-center gap-3 rounded-xl bg-[#d7ff45] px-5 py-4 text-sm font-bold text-[#111214] transition hover:bg-[#e5ff8c] disabled:cursor-not-allowed disabled:opacity-40">{{ form.processing ? 'Uploading document...' : 'Extract data' }}<span v-if="!form.processing">→</span><span v-else class="h-4 w-4 animate-spin rounded-full border-2 border-[#111214]/25 border-t-[#111214]"></span></button>
                            <p v-if="form.errors.file" class="mt-3 text-sm text-[#ffb4a8]">{{ form.errors.file }}</p>
                        </div>
                    </form>

                    <div class="rounded-[1.75rem] border border-[#deded8] bg-white p-6 sm:p-8"><div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-[.22em] text-[#8b8e87]">Monthly usage</p><p class="mt-4 text-5xl font-semibold tracking-[-.05em] text-[#111214]">{{ documentsUsed }}<span class="ml-2 text-xl font-normal text-[#a4a69f]">/ 300</span></p></div><span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f0f0eb] text-[#111214]">↗</span></div><div class="mt-9 h-2 overflow-hidden rounded-full bg-[#ededE8]"><div class="h-full rounded-full bg-[#111214] transition-all" :style="{ width: `${Math.min((documentsUsed / 300) * 100, 100)}%` }"></div></div><p class="mt-4 text-sm text-[#686b65]">{{ Math.max(300 - documentsUsed, 0) }} extractions remaining</p><div class="mt-10 grid grid-cols-2 gap-4 border-t border-[#ededE8] pt-6"><div><p class="text-xs text-[#8b8e87]">Avg. accuracy</p><p class="mt-2 text-2xl font-semibold">98.4%</p></div><div><p class="text-xs text-[#8b8e87]">Typical time</p><p class="mt-2 text-2xl font-semibold">&lt; 30s</p></div></div></div>
                </section>

                <section class="mt-16"><div class="mb-5 flex items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.22em] text-[#8b8e87]">Your activity</p><h2 class="mt-2 text-2xl font-semibold tracking-tight">Recent documents</h2></div><span class="text-sm text-[#8b8e87]">{{ documents.length }} files</span></div><div class="overflow-hidden rounded-2xl border border-[#deded8] bg-white"><div v-if="!documents.length" class="px-6 py-14 text-center text-[#8b8e87]">Your processed documents will appear here.</div><Link v-for="document in documents" :key="document.id" :href="route('documents.show', document.id)" class="group flex items-center justify-between gap-4 border-b border-[#ededE8] px-5 py-4 last:border-0 hover:bg-[#fafaf7] sm:px-6"><div class="flex min-w-0 items-center gap-4"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f0f0eb] text-[10px] font-bold uppercase text-[#555852]">PDF</span><div class="min-w-0"><p class="truncate text-sm font-semibold text-[#111214]">{{ document.original_filename }}</p><p class="mt-1 text-xs text-[#979a92]">{{ new Date(document.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</p></div></div><div class="flex items-center gap-3"><span class="rounded-full px-3 py-1 text-xs font-semibold" :class="document.status === 'processed' ? 'bg-[#e9f5c9] text-[#3f5311]' : document.status === 'failed' ? 'bg-[#fde8e5] text-[#a34d3d]' : 'bg-[#fff4c7] text-[#796212]'">{{ formatStatus(document.status) }}</span><span class="hidden text-lg text-[#b0b2ab] transition group-hover:translate-x-1 sm:inline">→</span></div></Link></div></section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
