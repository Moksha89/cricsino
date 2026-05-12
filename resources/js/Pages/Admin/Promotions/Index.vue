<script setup>
import { ref, watch } from "vue";
import { router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    promotions: Object,
    filters: Object,
    types: Array,
    statuses: Array,
});

const search = ref(props.filters?.search || "");
const typeFilter = ref(props.filters?.type || "");
const statusFilter = ref(props.filters?.status || "");

function applyFilters() {
    router.get(route("admin.promotions.index"), {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, replace: true });
}

let debounce;
watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(applyFilters, 400);
});
watch([typeFilter, statusFilter], applyFilters);

function toggleStatus(promo) {
    if (!confirm(`${promo.status === "active" ? "Deactivate" : "Activate"} "${promo.title}"?`)) return;
    router.put(route("admin.promotions.toggle", promo.id), {}, { preserveScroll: true });
}

function deletePromo(promo) {
    if (!confirm(`Delete "${promo.title}"? This cannot be undone.`)) return;
    router.delete(route("admin.promotions.destroy", promo.id), { preserveScroll: true });
}

function formatCurrency(val) {
    return "₹" + Number(val || 0).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-IN", { year: "numeric", month: "short", day: "numeric" });
}

function typeColor(type) {
    const colors = { welcome: "text-green-400", deposit: "text-blue-400", cashback: "text-yellow-400", manual: "text-purple-400", referral: "text-pink-400" };
    return colors[type] || "text-gray-400";
}

function statusBadge(status) {
    const colors = { active: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400", inactive: "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300", expired: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400" };
    return colors[status] || "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300";
}
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promotions</h1>
                <Link :href="route('admin.promotions.create')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Create Promotion
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3 mb-6">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Search" class="w-full pl-10 pr-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                </div>
                <select v-model="typeFilter" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                    <option value="">All Types</option>
                    <option v-for="t in types" :key="t" :value="t" class="capitalize">{{ t }}</option>
                </select>
                <select v-model="statusFilter" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                    <option value="">All Statuses</option>
                    <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bonus</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Claims</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Credited</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="promotions.data.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No promotions found.</td>
                        </tr>
                        <tr v-for="promo in promotions.data" :key="promo.id" class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3">
                                <Link :href="route('admin.promotions.show', promo.id)" class="text-gray-900 dark:text-white font-medium hover:text-purple-600 dark:hover:text-purple-400" v-text="promo.title" />
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs" v-text="promo.code" />
                            <td class="px-4 py-3">
                                <span :class="typeColor(promo.type)" class="capitalize font-medium" v-text="promo.type" />
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusBadge(promo.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize" v-text="promo.status" />
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <span v-if="promo.bonus_percent">{{ promo.bonus_percent }}%</span>
                                <span v-else-if="promo.fixed_bonus_amount">{{ formatCurrency(promo.fixed_bonus_amount) }}</span>
                                <span v-else>-</span>
                                <span v-if="promo.max_bonus" class="text-xs text-gray-400 ml-1">(max {{ formatCurrency(promo.max_bonus) }})</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300" v-text="promo.claims_count ?? 0" />
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ formatCurrency(promo.total_bonus_credited) }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(promo.created_at) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.promotions.show', promo.id)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-xs font-medium">View</Link>
                                    <Link :href="route('admin.promotions.edit', promo.id)" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 text-xs font-medium">Edit</Link>
                                    <button @click="toggleStatus(promo)" class="text-xs font-medium" :class="promo.status === 'active' ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700'">
                                        {{ promo.status === "active" ? "Deactivate" : "Activate" }}
                                    </button>
                                    <button @click="deletePromo(promo)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="promotions.last_page > 1" class="mt-4 flex justify-center gap-2">
                <Link
                    v-for="link in promotions.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="link.active ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                    class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm"
                    v-html="link.label"
                    :disabled="!link.url"
                />
            </div>
        </div>
    </AdminLayout>
</template>
