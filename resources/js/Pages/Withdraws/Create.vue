<script setup>
import { computed } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ArrowUpFromLine, Shield, AlertCircle } from "lucide-vue-next";
import { useI18n } from "vue-i18n";
import UserLayout from "@/Layouts/UserLayout.vue";
import WalletSummaryCard from "@/Components/Wallet/WalletSummaryCard.vue";
import AmountInput from "@/Components/Wallet/AmountInput.vue";
import WalletEmptyState from "@/Components/Wallet/WalletEmptyState.vue";
import PaymentStatusBadge from "@/Components/Wallet/PaymentStatusBadge.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
	accounts: Array,
	gateways: Object,
	currencies: Object,
	auth: Object,
	withdraws: Object,
});

const form = useForm({
	tos: false,
	amount: 0,
	account_id: null,
});

const selectedAccount = computed(() =>
	form.account_id ? props.accounts.find((c) => c.id === form.account_id) : null,
);
const gateway = computed(
	() => props.gateways[selectedAccount.value?.currency?.gateway?.gid] ?? null,
);

const createWithdraw = () => {
	form.post(window.route("withdraws.store"));
};

const estimate = computed(() => {
	const fee = parseFloat(usePage().props.auth.user.levelConfig?.withdrawFees ?? 0);
	if (parseFloat(form.amount) === 0) return 0;
	if (!selectedAccount.value?.currency) return null;
	const rate = selectedAccount.value?.currency?.rate;
	if (!rate) return null;
	const amount = fee > 0 ? parseFloat(form.amount) * ((100 - fee) / 100) : parseFloat(form.amount);
	return (amount / parseFloat(rate)).toFixed(8) * 1;
});

const { t } = useI18n();
const terms = [
	t("I confirm this withdrawal is accurate and authorize {site} to process it per the terms of service.", { site: usePage().props.appName }),
	t("By confirming, I acknowledge this withdrawal is final and subject to {site}'s processing times and fees.", { site: usePage().props.appName }),
	t("I certify that I own this account and authorize this withdrawal as per {site}'s policies.", { site: usePage().props.appName }),
	t("I understand this withdrawal may be reviewed and agree to cooperate with any required verification."),
	t("I confirm this withdrawal complies with all laws and {site}'s terms of service.", { site: usePage().props.appName }),
];
</script>

<template>
<Head title="Withdraw" />
<UserLayout :show-right-sidebar="false">
	<div class="p-4 lg:p-6 space-y-6 max-w-5xl mx-auto">
		<!-- Page Header -->
		<div class="flex items-center gap-3">
			<div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
				<ArrowUpFromLine class="w-5 h-5 text-purple-400" />
			</div>
			<div>
				<h1 class="text-xl font-bold text-white">Withdraw Funds</h1>
				<p class="text-sm text-gray-400">Transfer money to your linked accounts</p>
			</div>
		</div>

		<!-- Wallet Summary -->
		<WalletSummaryCard />

		<!-- No Accounts Warning -->
		<div v-if="accounts.length === 0">
			<WalletEmptyState
				title="No whitelisted accounts"
				description="You need to whitelist at least one withdrawal account before you can withdraw funds."
				:action-route="route('whitelists.index')"
				action-label="Whitelist Account"
				:icon="Shield" />
		</div>

		<!-- Main Form -->
		<template v-else>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] overflow-hidden">
				<!-- Amount Input -->
				<div class="p-5 border-b border-white/[0.06]">
					<AmountInput
						v-model="form.amount"
						label="Amount to withdraw"
						:error="form.errors.amount"
						:min="gateway?.min"
						:max="gateway?.max" />
				</div>

				<!-- Account Selection -->
				<div class="p-5 border-b border-white/[0.06]">
					<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">
						Select Withdrawal Account
					</h2>
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
						<button
							v-for="account in accounts"
							:key="account.id"
							@click="form.account_id = account.id"
							class="flex items-center gap-3 p-4 rounded-xl border transition-all duration-200 text-left"
							:class="form.account_id === account.id
								? 'border-purple-500 bg-purple-500/10 ring-1 ring-purple-500/50'
								: 'border-white/10 bg-white/[0.03] hover:bg-white/[0.06]'">
							<img
								v-if="account.currency?.logo_url"
								:src="account.currency.logo_url"
								class="w-8 h-8 rounded-full object-contain" />
							<div class="min-w-0 flex-1">
								<p class="text-sm font-bold text-white">{{ account.currency?.code }}</p>
								<p class="text-xs text-gray-400 truncate">{{ account.currency?.name }}</p>
								<p class="text-xs text-gray-500 truncate mt-0.5 font-mono">{{ account.payout_address }}</p>
							</div>
							<div v-if="form.account_id === account.id" class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0">
								<svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
								</svg>
							</div>
						</button>
					</div>
					<p v-if="form.errors.currency_id" class="text-xs text-red-400 font-medium mt-2">
						{{ form.errors.currency_id }}
					</p>
				</div>

				<!-- Selected Account Details -->
				<div v-if="selectedAccount" class="p-5 border-b border-white/[0.06]">
					<div class="flex items-center gap-3 mb-4">
						<img
							v-if="selectedAccount?.currency?.gateway?.logo"
							:src="selectedAccount.currency.gateway.logo"
							class="w-8 h-8 rounded-full" />
						<div>
							<p class="text-sm font-semibold text-white">
								Processed via {{ selectedAccount?.currency?.gateway?.name }}
							</p>
							<p class="text-xs text-gray-400">{{ selectedAccount?.payout_address }}</p>
						</div>
					</div>
					<div v-if="estimate" class="rounded-lg border border-purple-500/20 bg-purple-500/5 p-3 flex items-center justify-between">
						<span class="text-sm text-gray-300">Estimated payout</span>
						<span class="text-lg font-bold text-white">
							{{ estimate }} {{ selectedAccount?.currency?.code }}
						</span>
					</div>
					<p class="text-xs text-gray-400 mt-2">
						Converted amount is an estimate. Final withdrawal amount may differ based on gateway.
					</p>
				</div>

				<!-- Terms & Submit -->
				<div class="p-5">
					<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 mb-5">
						<div class="flex items-start gap-3">
							<Shield class="w-5 h-5 text-purple-400 flex-shrink-0 mt-0.5" />
							<div>
								<h3 class="text-sm font-semibold text-white mb-2">Withdrawal Terms</h3>
								<ul class="space-y-1.5">
									<li
										v-for="(term, index) in terms"
										:key="index"
										class="text-xs text-gray-400 leading-relaxed flex items-start gap-2">
										<span class="w-1.5 h-1.5 rounded-full bg-gray-500 flex-shrink-0 mt-1.5"></span>
										{{ term }}
									</li>
								</ul>
							</div>
						</div>
					</div>

					<label class="flex items-center gap-3 mb-5 cursor-pointer group">
						<input
							type="checkbox"
							v-model="form.tos"
							class="w-5 h-5 rounded border-gray-600 bg-white/[0.05] text-purple-500
								   focus:ring-purple-500 focus:ring-offset-0 focus:ring-1 cursor-pointer" />
						<span class="text-sm text-gray-300 group-hover:text-white transition-colors">
							I acknowledge these terms
						</span>
					</label>

					<p
						v-if="gateway && !gateway?.enable_withdraw"
						class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 mb-4">
						{{ gateway?.label }} is currently offline. Please select another gateway.
					</p>

					<button
						@click="createWithdraw"
						:disabled="form.processing || !gateway?.enable_withdraw || !form.tos"
						class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white
							   font-bold text-sm uppercase tracking-wide transition-all duration-200
							   disabled:opacity-40 disabled:cursor-not-allowed
							   flex items-center justify-center gap-2">
						<ArrowUpFromLine class="w-4 h-4" />
						<span v-if="form.processing">Processing...</span>
						<span v-else>Initiate Withdrawal</span>
					</button>
				</div>
			</div>
		</template>

		<!-- Withdrawal History -->
		<div v-if="withdraws?.data?.length > 0" class="space-y-4">
			<h2 class="text-lg font-bold text-white">Recent Withdrawals</h2>

			<!-- Desktop Table -->
			<div class="hidden sm:block rounded-xl border border-white/[0.06] overflow-hidden">
				<table class="w-full">
					<thead>
						<tr class="border-b border-white/[0.06]">
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">ID</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Gateway</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Amount</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Date</th>
							<th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Status</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="withdraw in withdraws.data"
							:key="withdraw.id"
							class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
							<td class="px-4 py-3">
								<Link
									:href="route('withdraws.show', { withdraw: withdraw.uuid })"
									class="text-sm font-semibold text-purple-400 hover:text-purple-300 uppercase">
									#{{ withdraw.uid }}
								</Link>
							</td>
							<td class="px-4 py-3">
								<div class="flex items-center gap-2">
									<img :src="withdraw.gateway.logo" class="w-6 h-6 rounded-full" />
									<span class="text-sm text-gray-300">{{ withdraw.gateway.name }}</span>
								</div>
							</td>
							<td class="px-4 py-3">
								<MoneyFormat class="text-sm font-bold text-white" :amount="withdraw.amount" />
							</td>
							<td class="px-4 py-3 text-sm text-gray-400">{{ withdraw.date }}</td>
							<td class="px-4 py-3 text-right">
								<PaymentStatusBadge :status="withdraw.status" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Mobile Cards -->
			<div class="sm:hidden space-y-3">
				<Link
					v-for="withdraw in withdraws.data"
					:key="withdraw.id"
					:href="route('withdraws.show', { withdraw: withdraw.uuid })"
					class="block rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 hover:bg-white/[0.04] transition-colors">
					<div class="flex items-center justify-between mb-2">
						<span class="text-sm font-bold text-purple-400 uppercase">#{{ withdraw.uid }}</span>
						<PaymentStatusBadge :status="withdraw.status" />
					</div>
					<div class="flex items-center justify-between">
						<div class="flex items-center gap-2">
							<img :src="withdraw.gateway.logo" class="w-5 h-5 rounded-full" />
							<span class="text-xs text-gray-400">{{ withdraw.gateway.name }}</span>
						</div>
						<MoneyFormat class="text-sm font-bold text-white" :amount="withdraw.amount" />
					</div>
					<p class="text-xs text-gray-500 mt-2">{{ withdraw.date }}</p>
				</Link>
			</div>

			<Pagination :meta="withdraws.meta" />
		</div>
	</div>
</UserLayout>
</template>
