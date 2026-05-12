<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Gift, Clock, Tag, ChevronRight, Wallet, Star } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
import AccountEmptyState from "@/Components/Account/AccountEmptyState.vue";

const props = defineProps({
    promotions: { type: Array, default: () => [] },
    claims: { type: Array, default: () => [] },
    bonusBalance: { type: Number, default: 0 },
});

const activeTab = ref("promotions");
const claimingId = ref(null);
const depositAmount = ref("");

const flash = computed(() => usePage().props.flash ?? {});

function claimPromotion(promo) {
    if (claimingId.value) return;
    claimingId.value = promo.id;
    router.post(
        route("promotions.claim", promo.id),
        { deposit_amount: depositAmount.value || 0 },
        {
            preserveScroll: true,
            onFinish: () => {
                claimingId.value = null;
                depositAmount.value = "";
            },
        }
    );
}

function typeLabel(type) {
    const labels = {
        welcome: "Welcome Bonus",
        deposit: "Deposit Bonus",
        cashback: "Cashback",
        manual: "Promo Credit",
        referral: "Referral Bonus",
    };
    return labels[type] || type;
}

function typeColor(type) {
    const colors = {
        welcome: "bg-green-500/20 text-green-400",
        deposit: "bg-blue-500/20 text-blue-400",
        cashback: "bg-yellow-500/20 text-yellow-400",
        manual: "bg-purple-500/20 text-purple-400",
        referral: "bg-pink-500/20 text-pink-400",
    };
    return colors[type] || "bg-gray-500/20 text-gray-400";
}

function statusColor(status) {
    const colors = {
        pending: "bg-yellow-500/20 text-yellow-400",
        approved: "bg-blue-500/20 text-blue-400",
        credited: "bg-green-500/20 text-green-400",
        rejected: "bg-red-500/20 text-red-400",
        cancelled: "bg-gray-500/20 text-gray-400",
        expired: "bg-gray-500/20 text-gray-400",
    };
    return colors[status] || "bg-gray-500/20 text-gray-400";
}

function formatDate(dateStr) {
    if (!dateStr) return "N/A";
    return new Date(dateStr).toLocaleDateString("en-IN", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function formatCurrency(val) {
    return "₹" + Number(val || 0).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <UserLayout>
        <div class="p-4 sm:p-6 pb-20 sm:pb-6">
            <AccountPageHeader
                title="Promotions"
                subtitle="View available promotions and bonus offers"
                :icon="Gift"
            />

            <!-- Bonus Balance Card -->
            <div class="max-w-4xl mb-6">
                <div class="bg-gradient-to-r from-purple-600/20 to-indigo-600/20 border border-purple-500/30 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                            <Wallet class="w-5 h-5 text-purple-400" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Bonus Balance</div>
                            <div class="text-xl font-bold text-white">{{ formatCurrency(bonusBalance) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="max-w-4xl mb-6">
                <div class="flex gap-1 bg-gray-800/50 rounded-lg p-1">
                    <button
                        @click="activeTab = 'promotions'"
                        :class="activeTab === 'promotions' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="flex-1 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        Available Promotions
                    </button>
                    <button
                        @click="activeTab = 'claims'"
                        :class="activeTab === 'claims' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="flex-1 px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        My Claims
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="flash.success" class="max-w-4xl mb-4">
                <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-3 rounded-lg text-sm">
                    {{ flash.success }}
                </div>
            </div>
            <div v-if="flash.error" class="max-w-4xl mb-4">
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded-lg text-sm">
                    {{ flash.error }}
                </div>
            </div>

            <!-- Promotions Tab -->
            <div v-if="activeTab === 'promotions'" class="max-w-4xl">
                <AccountEmptyState
                    v-if="promotions.length === 0"
                    title="No Promotions Available"
                    description="There are no active promotions at this time. Check back later for new bonus offers and special deals."
                    :icon="Gift"
                />

                <div v-else class="space-y-4">
                    <div
                        v-for="promo in promotions"
                        :key="promo.id"
                        class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-5 hover:border-purple-500/30 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-2">
                                    <h3 class="text-lg font-semibold text-white" v-text="promo.title" />
                                    <span :class="typeColor(promo.type)" class="px-2 py-0.5 rounded-full text-xs font-medium" v-text="typeLabel(promo.type)" />
                                </div>
                                <p v-if="promo.description" class="text-sm text-gray-400 mb-3" v-text="promo.description" />

                                <div class="flex flex-wrap gap-4 text-sm text-gray-400">
                                    <div v-if="promo.bonus_percent" class="flex items-center gap-1">
                                        <Star class="w-4 h-4 text-yellow-400" />
                                        <span>{{ promo.bonus_percent }}% Bonus</span>
                                    </div>
                                    <div v-if="promo.fixed_bonus_amount" class="flex items-center gap-1">
                                        <Star class="w-4 h-4 text-yellow-400" />
                                        <span>{{ formatCurrency(promo.fixed_bonus_amount) }} Bonus</span>
                                    </div>
                                    <div v-if="promo.max_bonus" class="flex items-center gap-1">
                                        <Tag class="w-4 h-4 text-blue-400" />
                                        <span>Max {{ formatCurrency(promo.max_bonus) }}</span>
                                    </div>
                                    <div v-if="promo.min_deposit" class="flex items-center gap-1">
                                        <Wallet class="w-4 h-4 text-green-400" />
                                        <span>Min Deposit {{ formatCurrency(promo.min_deposit) }}</span>
                                    </div>
                                    <div v-if="promo.end_at" class="flex items-center gap-1">
                                        <Clock class="w-4 h-4 text-orange-400" />
                                        <span>Expires {{ formatDate(promo.end_at) }}</span>
                                    </div>
                                    <div v-if="promo.wagering_multiplier" class="flex items-center gap-1">
                                        <ChevronRight class="w-4 h-4 text-gray-400" />
                                        <span>{{ promo.wagering_multiplier }}x Wagering</span>
                                    </div>
                                </div>

                                <div v-if="promo.terms_text" class="mt-3 text-xs text-gray-500 border-t border-gray-700/50 pt-2" v-text="promo.terms_text" />
                            </div>

                            <div class="flex-shrink-0">
                                <div v-if="promo.user_claims_count > 0" class="text-xs text-gray-400 mb-1 text-center">
                                    Claimed {{ promo.user_claims_count }}x
                                </div>
                                <div v-if="(promo.min_deposit || promo.bonus_percent) && promo.can_claim" class="mb-2">
                                    <input
                                        v-model="depositAmount"
                                        type="number"
                                        min="0"
                                        placeholder="Deposit amt"
                                        class="w-28 px-2 py-1 text-sm bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500"
                                    />
                                </div>
                                <button
                                    v-if="promo.can_claim"
                                    @click="claimPromotion(promo)"
                                    :disabled="claimingId === promo.id"
                                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap"
                                >
                                    {{ claimingId === promo.id ? "Claiming..." : "Claim" }}
                                </button>
                                <div v-else class="text-xs text-gray-500 text-center">
                                    {{ promo.user_claims_count > 0 ? 'Already claimed' : 'Not eligible' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claims Tab -->
            <div v-if="activeTab === 'claims'" class="max-w-4xl">
                <AccountEmptyState
                    v-if="claims.length === 0"
                    title="No Claims Yet"
                    description="You haven't claimed any promotions yet. Check the Available Promotions tab."
                    :icon="Gift"
                />

                <div v-else class="space-y-3">
                    <div
                        v-for="claim in claims"
                        :key="claim.id"
                        class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-white font-medium" v-text="claim.promotion?.title ?? 'Promotion #' + claim.promotion_id" />
                                <div class="text-sm text-gray-400 mt-1">
                                    Bonus: {{ formatCurrency(claim.bonus_amount) }}
                                    <span v-if="claim.wagering_required"> &middot; Wagering: {{ formatCurrency(claim.wagering_completed ?? 0) }} / {{ formatCurrency(claim.wagering_required) }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ formatDate(claim.created_at) }}
                                </div>
                            </div>
                            <span :class="statusColor(claim.status)" class="px-2.5 py-1 rounded-full text-xs font-medium capitalize" v-text="claim.status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
