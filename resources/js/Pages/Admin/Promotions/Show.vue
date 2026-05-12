<script setup>
import { router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    promotion: Object,
    claims: Object,
    claimStatuses: Array,
});

function formatCurrency(val) {
    return "₹" + Number(val || 0).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("en-IN", { year: "numeric", month: "short", day: "numeric", hour: "2-digit", minute: "2-digit" });
}

function statusBadge(status) {
    const colors = {
        active: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400",
        inactive: "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300",
        expired: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400",
    };
    return colors[status] || "bg-gray-100 text-gray-700";
}

function claimStatusBadge(status) {
    const colors = {
        pending: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400",
        approved: "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400",
        credited: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400",
        rejected: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400",
        cancelled: "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300",
        expired: "bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300",
    };
    return colors[status] || "bg-gray-100 text-gray-700";
}

function approveClaim(claim) {
    if (!confirm("Approve this claim?")) return;
    router.put(route("admin.promotions.claims.approve", claim.id), {}, { preserveScroll: true });
}

function rejectClaim(claim) {
    if (!confirm("Reject this claim?")) return;
    router.put(route("admin.promotions.claims.reject", claim.id), {}, { preserveScroll: true });
}

function creditClaim(claim) {
    if (!confirm(`Credit ${formatCurrency(claim.bonus_amount)} to ${claim.user?.name}?`)) return;
    router.put(route("admin.promotions.claims.credit", claim.id), {}, { preserveScroll: true });
}

function cancelClaim(claim) {
    if (!confirm("Cancel this claim?")) return;
    router.put(route("admin.promotions.claims.cancel", claim.id), {}, { preserveScroll: true });
}

function toggleStatus() {
    const action = props.promotion.status === "active" ? "Deactivate" : "Activate";
    if (!confirm(`${action} this promotion?`)) return;
    router.put(route("admin.promotions.toggle", props.promotion.id), {}, { preserveScroll: true });
}

function deletePromo() {
    if (!confirm("Delete this promotion? This cannot be undone.")) return;
    router.delete(route("admin.promotions.destroy", props.promotion.id));
}
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <Link :href="route('admin.promotions.index')" class="text-sm text-purple-500 hover:text-purple-400 mb-2 inline-block">&larr; Back to Promotions</Link>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white" v-text="promotion.title" />
                        <div class="flex items-center gap-2 mt-1">
                            <span :class="statusBadge(promotion.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" v-text="promotion.status" />
                            <span class="text-sm text-gray-500 dark:text-gray-400 capitalize" v-text="promotion.type" />
                            <span class="text-xs text-gray-400 font-mono" v-text="promotion.code" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('admin.promotions.edit', promotion.id)" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg">Edit</Link>
                        <button @click="toggleStatus" class="px-3 py-1.5 text-sm rounded-lg" :class="promotion.status === 'active' ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white'">
                            {{ promotion.status === "active" ? "Deactivate" : "Activate" }}
                        </button>
                        <button @click="deletePromo" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Claims</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ promotion.claims_count ?? 0 }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending</div>
                    <div class="text-2xl font-bold text-yellow-500">{{ promotion.pending_claims_count ?? 0 }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Credited</div>
                    <div class="text-2xl font-bold text-green-500">{{ promotion.credited_claims_count ?? 0 }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Bonus</div>
                    <div class="text-2xl font-bold text-purple-500">{{ formatCurrency(promotion.total_bonus_credited) }}</div>
                </div>
            </div>

            <!-- Promotion Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Description</dt><dd class="text-gray-900 dark:text-white text-right max-w-xs" v-text="promotion.description || '-'" /></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Start</dt><dd class="text-gray-900 dark:text-white">{{ formatDate(promotion.start_at) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">End</dt><dd class="text-gray-900 dark:text-white">{{ formatDate(promotion.end_at) }}</dd></div>
                        <div v-if="promotion.terms_text" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400 mb-1">Terms</dt>
                            <dd class="text-gray-700 dark:text-gray-300 text-xs" v-text="promotion.terms_text" />
                        </div>
                    </dl>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Bonus Configuration</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Bonus %</dt><dd class="text-gray-900 dark:text-white">{{ promotion.bonus_percent ? promotion.bonus_percent + '%' : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Fixed Bonus</dt><dd class="text-gray-900 dark:text-white">{{ promotion.fixed_bonus_amount ? formatCurrency(promotion.fixed_bonus_amount) : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Max Bonus</dt><dd class="text-gray-900 dark:text-white">{{ promotion.max_bonus ? formatCurrency(promotion.max_bonus) : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Min Deposit</dt><dd class="text-gray-900 dark:text-white">{{ promotion.min_deposit ? formatCurrency(promotion.min_deposit) : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Wagering</dt><dd class="text-gray-900 dark:text-white">{{ promotion.wagering_multiplier ? promotion.wagering_multiplier + 'x' : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">User Limit</dt><dd class="text-gray-900 dark:text-white">{{ promotion.claim_limit_per_user ?? 'Unlimited' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total Limit</dt><dd class="text-gray-900 dark:text-white">{{ promotion.total_claim_limit ?? 'Unlimited' }}</dd></div>
                    </dl>
                </div>
            </div>

            <!-- Claims Table -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Claims</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bonus</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Approved By</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="claims.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No claims yet.</td>
                            </tr>
                            <tr v-for="claim in claims.data" :key="claim.id" class="border-b border-gray-100 dark:border-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-gray-900 dark:text-white font-medium" v-text="claim.user?.name ?? '-'" />
                                    <div class="text-xs text-gray-500" v-text="claim.user?.email" />
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ formatCurrency(claim.bonus_amount) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="claimStatusBadge(claim.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize" v-text="claim.status" />
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(claim.created_at) }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs" v-text="claim.approver?.name ?? '-'" />
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button v-if="claim.status === 'pending'" @click="approveClaim(claim)" class="px-2 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded">Approve</button>
                                        <button v-if="claim.status === 'pending' || claim.status === 'approved'" @click="creditClaim(claim)" class="px-2 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded">Credit</button>
                                        <button v-if="claim.status === 'pending' || claim.status === 'approved'" @click="rejectClaim(claim)" class="px-2 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded">Reject</button>
                                        <button v-if="!['credited', 'cancelled'].includes(claim.status)" @click="cancelClaim(claim)" class="px-2 py-1 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded">Cancel</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
