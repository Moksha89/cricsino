<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    types: Array,
});

const form = useForm({
    title: "",
    code: "",
    description: "",
    type: "welcome",
    status: "active",
    start_at: "",
    end_at: "",
    min_deposit: "",
    max_bonus: "",
    bonus_percent: "",
    fixed_bonus_amount: "",
    wagering_multiplier: "",
    eligible_user_level: "",
    claim_limit_per_user: "",
    total_claim_limit: "",
    terms_text: "",
});

function submit() {
    form.post(route("admin.promotions.store"));
}
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 max-w-3xl">
            <div class="mb-6">
                <Link :href="route('admin.promotions.index')" class="text-sm text-purple-500 hover:text-purple-400 mb-2 inline-block">&larr; Back to Promotions</Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Promotion</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
                        <input v-model="form.title" type="text" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                        <div v-if="form.errors.title" class="text-red-500 text-xs mt-1" v-text="form.errors.title" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                        <input v-model="form.code" type="text" placeholder="Auto-generated if empty" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                        <div v-if="form.errors.code" class="text-red-500 text-xs mt-1" v-text="form.errors.code" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                        <select v-model="form.type" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                            <option v-for="t in types" :key="t" :value="t" class="capitalize">{{ t }}</option>
                        </select>
                        <div v-if="form.errors.type" class="text-red-500 text-xs mt-1" v-text="form.errors.type" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                        <select v-model="form.status" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    <div v-if="form.errors.description" class="text-red-500 text-xs mt-1" v-text="form.errors.description" />
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                        <input v-model="form.start_at" type="datetime-local" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                        <input v-model="form.end_at" type="datetime-local" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                        <div v-if="form.errors.end_at" class="text-red-500 text-xs mt-1" v-text="form.errors.end_at" />
                    </div>
                </div>

                <!-- Bonus Configuration -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Bonus Configuration</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bonus %</label>
                            <input v-model="form.bonus_percent" type="number" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fixed Bonus</label>
                            <input v-model="form.fixed_bonus_amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Bonus</label>
                            <input v-model="form.max_bonus" type="number" step="0.01" min="0" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Limits -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Deposit</label>
                        <input v-model="form.min_deposit" type="number" step="0.01" min="0" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Wagering Multiplier</label>
                        <input v-model="form.wagering_multiplier" type="number" step="0.01" min="0" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Level</label>
                        <input v-model="form.eligible_user_level" type="text" placeholder="e.g. level_one" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Claim Limit Per User</label>
                        <input v-model="form.claim_limit_per_user" type="number" min="1" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Claim Limit</label>
                        <input v-model="form.total_claim_limit" type="number" min="1" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terms & Conditions</label>
                    <textarea v-model="form.terms_text" rows="3" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link :href="route('admin.promotions.index')" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-500 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ form.processing ? "Creating..." : "Create Promotion" }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
