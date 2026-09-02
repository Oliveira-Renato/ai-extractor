<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePoll } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({ document: Object });
const copied = ref(false);
const data = computed(() => props.document.extracted_json || {});
const json = computed(() => JSON.stringify(data.value, null, 2));
const vendor = computed(() => data.value.vendor || { name: data.value.vendor_name });
const customer = computed(() => data.value.customer || {});
const lineItems = computed(() => data.value.line_items || []);

const summaryFields = computed(() => [
    { label: 'Invoice number', value: data.value.invoice_number },
    { label: 'Purchase order', value: data.value.purchase_order_number },
    { label: 'Issue date', value: data.value.issue_date || data.value.date },
    { label: 'Due date', value: data.value.due_date },
    { label: 'Currency', value: data.value.currency },
    { label: 'Payment status', value: data.value.payment_status },
    { label: 'Subtotal', value: formatAmount(data.value.subtotal) },
    { label: 'Discount', value: formatAmount(data.value.discount_amount) },
    { label: 'Tax', value: formatAmount(data.value.tax_amount) },
    { label: 'Shipping', value: formatAmount(data.value.shipping_amount) },
    { label: 'Total', value: formatAmount(data.value.total_amount) },
    { label: 'Amount due', value: formatAmount(data.value.amount_due) },
]);

const bankFields = computed(() => [
    { label: 'Account holder', value: data.value.bank_details?.account_holder },
    { label: 'Bank', value: data.value.bank_details?.bank_name },
    { label: 'Routing number', value: data.value.bank_details?.routing_number },
    { label: 'Account number', value: data.value.bank_details?.account_number },
    { label: 'IBAN', value: data.value.bank_details?.iban },
    { label: 'SWIFT / BIC', value: data.value.bank_details?.swift_bic },
    { label: 'Payment reference', value: data.value.bank_details?.payment_reference },
].filter(field => field.value));

const paymentFields = computed(() => [
    { label: 'Payment terms', value: data.value.payment_terms },
    { label: 'Payment instructions', value: data.value.payment_instructions },
].filter(field => field.value));

function displayValue(value) {
    return value ?? 'Not identified';
}

function formatAmount(value) {
    if (value === null || value === undefined) {
        return null;
    }

    if (!data.value.currency) {
        return String(value);
    }

    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: data.value.currency,
        }).format(value);
    } catch {
        return `${data.value.currency} ${value}`;
    }
}

function formatAddress(party) {
    const address = party?.address || {};

    return [
        address.line_1,
        address.line_2,
        [address.city, address.state, address.postal_code].filter(Boolean).join(', '),
        address.country,
    ].filter(Boolean);
}

function partyTitle(party) {
    return party?.company_name || party?.name || 'Not identified';
}

function copyJson() {
    navigator.clipboard.writeText(json.value);
    copied.value = true;
    setTimeout(() => copied.value = false, 1600);
}

function exportCsv() {
    const columns = [
        'description',
        'sku',
        'quantity',
        'unit',
        'unit_price',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'amount',
        'service_period_start',
        'service_period_end',
    ];
    const rows = [columns, ...lineItems.value.map(item => columns.map(column => item[column]))];
    const csv = rows
        .map(row => row.map(value => `"${String(value ?? '').replaceAll('"', '""')}"`).join(','))
        .join('\n');
    const link = window.document.createElement('a');
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    link.download = `${props.document.original_filename}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}

const { start, stop } = usePoll(2500, { only: ['document'] }, { autoStart: false });
watch(() => props.document.status, status => status === 'pending' ? start() : stop(), { immediate: true });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="document.original_filename" />

        <div class="min-h-screen bg-[#f4f6f3] text-[#17251d]">
            <main class="mx-auto max-w-7xl px-5 py-10 sm:px-8">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <Link :href="route('dashboard')" class="text-sm text-[#718078] hover:text-[#18372b]">
                            ← Back to workspace
                        </Link>
                        <h1 class="mt-4 max-w-xl truncate text-3xl font-semibold">
                            {{ document.original_filename }}
                        </h1>
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="!document.extracted_json"
                            class="rounded-lg border border-[#ccd8cf] bg-white px-4 py-2.5 text-sm font-semibold disabled:opacity-40"
                            @click="exportCsv"
                        >
                            Export CSV
                        </button>
                        <button
                            :disabled="!document.extracted_json"
                            class="rounded-lg bg-[#18372b] px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-40"
                            @click="copyJson"
                        >
                            {{ copied ? 'Copied' : 'Copy JSON' }}
                        </button>
                    </div>
                </div>

                <div class="grid items-start gap-5 lg:grid-cols-[.85fr_1.15fr]">
                    <div class="flex min-h-[600px] items-center justify-center rounded-2xl border border-[#dce4de] bg-[#dfe5e0] p-4 lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)]">
                        <iframe
                            v-if="document.original_filename.toLowerCase().endsWith('.pdf')"
                            :src="route('documents.preview', document.id)"
                            class="h-full min-h-[568px] w-full rounded-lg bg-white"
                            title="Document preview"
                        ></iframe>
                        <div v-else class="text-center text-[#718078]">
                            <div class="mb-3 text-5xl">▧</div>
                            Preview available for PDFs
                            <div class="mt-2 text-xs">{{ document.original_filename }}</div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[#dce4de] bg-white p-6 sm:p-8">
                        <div class="flex items-start justify-between gap-4 border-b border-[#edf0ed] pb-6">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[.18em] text-[#9aa79f]">Extracted data</p>
                                <p class="mt-2 text-sm text-[#718078]">
                                    {{ document.status === 'pending' ? 'Analyzing your document...' : document.status === 'failed' ? 'We could not process this file.' : 'Review the fields before exporting.' }}
                                </p>
                            </div>
                            <span
                                class="mt-1 h-3 w-3 shrink-0 rounded-full"
                                :class="document.status === 'processed' ? 'bg-[#55a66c]' : document.status === 'failed' ? 'bg-[#d97860]' : 'animate-pulse bg-[#d6a84f]'"
                            ></span>
                        </div>

                        <div v-if="document.status === 'processed'" class="grid gap-8 pt-8">
                            <section>
                                <h2 class="text-base font-semibold">Invoice summary</h2>
                                <div class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-3">
                                    <div v-for="field in summaryFields" :key="field.label" class="border-b border-[#edf0ed] pb-3">
                                        <p class="text-xs text-[#9aa79f]">{{ field.label }}</p>
                                        <p class="mt-1 break-words text-sm font-medium">{{ displayValue(field.value) }}</p>
                                    </div>
                                </div>
                            </section>

                            <section class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl bg-[#f5f7f4] p-5">
                                    <p class="text-xs font-bold uppercase tracking-widest text-[#9aa79f]">Vendor</p>
                                    <p class="mt-3 font-semibold">{{ partyTitle(vendor) }}</p>
                                    <p v-if="vendor.name && vendor.company_name" class="mt-1 text-sm text-[#627168]">{{ vendor.name }}</p>
                                    <div class="mt-3 grid gap-1 text-sm text-[#627168]">
                                        <p v-for="line in formatAddress(vendor)" :key="line">{{ line }}</p>
                                        <p v-if="vendor.tax_id">Tax ID: {{ vendor.tax_id }}</p>
                                        <p v-if="vendor.email">{{ vendor.email }}</p>
                                        <p v-if="vendor.phone">{{ vendor.phone }}</p>
                                        <p v-if="vendor.website">{{ vendor.website }}</p>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-[#f5f7f4] p-5">
                                    <p class="text-xs font-bold uppercase tracking-widest text-[#9aa79f]">Bill to</p>
                                    <p class="mt-3 font-semibold">{{ partyTitle(customer) }}</p>
                                    <p v-if="customer.name && customer.company_name" class="mt-1 text-sm text-[#627168]">{{ customer.name }}</p>
                                    <div class="mt-3 grid gap-1 text-sm text-[#627168]">
                                        <p v-for="line in formatAddress(customer)" :key="line">{{ line }}</p>
                                        <p v-if="customer.tax_id">Tax ID: {{ customer.tax_id }}</p>
                                        <p v-if="customer.email">{{ customer.email }}</p>
                                        <p v-if="customer.phone">{{ customer.phone }}</p>
                                    </div>
                                </div>
                            </section>

                            <section>
                                <div class="flex items-center justify-between gap-4">
                                    <h2 class="text-base font-semibold">Line items</h2>
                                    <span class="text-xs text-[#87958c]">{{ lineItems.length }} items</span>
                                </div>
                                <div class="mt-4 overflow-x-auto rounded-xl border border-[#e4eae5]">
                                    <table class="min-w-full divide-y divide-[#e4eae5] text-left text-sm">
                                        <thead class="bg-[#f5f7f4] text-xs text-[#718078]">
                                            <tr>
                                                <th class="px-4 py-3 font-medium">Description</th>
                                                <th class="px-4 py-3 font-medium">Period</th>
                                                <th class="px-4 py-3 text-right font-medium">Qty</th>
                                                <th class="px-4 py-3 text-right font-medium">Unit price</th>
                                                <th class="px-4 py-3 text-right font-medium">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#edf0ed]">
                                            <tr v-for="(item, index) in lineItems" :key="`${item.sku || item.description}-${index}`">
                                                <td class="min-w-56 px-4 py-3 align-top">
                                                    <p class="font-medium">{{ displayValue(item.description) }}</p>
                                                    <p v-if="item.sku" class="mt-1 text-xs text-[#87958c]">SKU: {{ item.sku }}</p>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3 align-top text-[#627168]">
                                                    {{ item.service_period_start || '—' }}<br v-if="item.service_period_end">
                                                    <span v-if="item.service_period_end">to {{ item.service_period_end }}</span>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right align-top">{{ displayValue(item.quantity) }} {{ item.unit || '' }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right align-top">{{ displayValue(formatAmount(item.unit_price ?? item.price)) }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right align-top font-medium">{{ displayValue(formatAmount(item.amount ?? item.price)) }}</td>
                                            </tr>
                                            <tr v-if="!lineItems.length">
                                                <td colspan="5" class="px-4 py-8 text-center text-[#87958c]">No line items identified.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <section v-if="paymentFields.length || bankFields.length" class="grid gap-4 sm:grid-cols-2">
                                <div v-if="paymentFields.length" class="rounded-xl border border-[#e4eae5] p-5">
                                    <h2 class="text-base font-semibold">Payment</h2>
                                    <div class="mt-4 grid gap-4">
                                        <div v-for="field in paymentFields" :key="field.label">
                                            <p class="text-xs text-[#9aa79f]">{{ field.label }}</p>
                                            <p class="mt-1 whitespace-pre-line text-sm">{{ field.value }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="bankFields.length" class="rounded-xl border border-[#e4eae5] p-5">
                                    <h2 class="text-base font-semibold">Bank details</h2>
                                    <div class="mt-4 grid gap-3">
                                        <div v-for="field in bankFields" :key="field.label" class="flex justify-between gap-4 text-sm">
                                            <span class="text-[#87958c]">{{ field.label }}</span>
                                            <span class="break-all text-right font-medium">{{ field.value }}</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section v-if="data.notes?.length">
                                <h2 class="text-base font-semibold">Notes</h2>
                                <ul class="mt-3 grid gap-2 text-sm text-[#627168]">
                                    <li v-for="note in data.notes" :key="note">{{ note }}</li>
                                </ul>
                            </section>

                            <details class="rounded-xl bg-[#f5f7f4] p-4">
                                <summary class="cursor-pointer text-sm font-semibold">Raw JSON</summary>
                                <pre class="mt-4 max-h-80 overflow-auto text-xs leading-6 text-[#52655a]">{{ json }}</pre>
                            </details>
                        </div>

                        <div v-else class="mt-8 flex min-h-56 items-center justify-center rounded-xl bg-[#f5f7f4] text-sm text-[#87958c]">
                            {{ document.status === 'pending' ? 'This may take a few moments...' : 'Please upload the file again.' }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
