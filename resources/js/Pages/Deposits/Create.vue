<script setup>
import { computed, watch } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ArrowDownToLine, Shield, CreditCard } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import WalletSummaryCard from "@/Components/Wallet/WalletSummaryCard.vue";
import PaymentGatewayCard from "@/Components/Wallet/PaymentGatewayCard.vue";
import AmountInput from "@/Components/Wallet/AmountInput.vue";
import WalletEmptyState from "@/Components/Wallet/WalletEmptyState.vue";
import PaymentStatusBadge from "@/Components/Wallet/PaymentStatusBadge.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
	gateways: Array,
	auth: Object,
	deposits: Object,
	currencies: Object,
});

const form = useForm({
	tos: false,
	amount: 0,
	currency: null,
	gateway: Object.values(props.gateways)?.[0]?.value ?? null,
});

const gateway = computed(() => props.gateways[form.gateway] ?? null);
const activeCurrencies = computed(() => props.currencies[form.gateway] ?? []);
watch(gateway, () => (form.currency = null));

const createDeposit = () => {
	form.post(window.route("deposits.store"));
};
</script>

<template>
<Head title="Deposit" />
<UserLayout :show-right-sidebar="false">
	<div class="p-4 lg:p-6 space-y-6 max-w-5xl mx-auto">
		<!-- Page Header -->
		<div class="flex items-center gap-3">
			<div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center">
				<ArrowDownToLine class="w-5 h-5 text-green-400" />
			</div>
			<div>
				<h1 class="text-xl font-bold text-white">Deposit Funds</h1>
				<p class="text-sm text-gray-400">Add money to your account securely</p>
			</div>
		</div>

		<!-- Wallet Summary -->
		<WalletSummaryCard />

		<!-- Main Form -->
		<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] overflow-hidden">
			<!-- Gateway Selection -->
			<div class="p-5 border-b border-white/[0.06]">
				<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">
					Select Payment Method
				</h2>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
					<PaymentGatewayCard
						v-for="(gw, key) in gateways"
						:key="key"
						:gateway="gw"
						:selected="form.gateway === gw.value"
						@select="form.gateway = $event" />
				</div>
			</div>

			<!-- Amount Input -->
			<div class="p-5 border-b border-white/[0.06]">
				<AmountInput
					v-model="form.amount"
					label="Amount to deposit"
					:error="form.errors.amount"
					:min="gateway?.min"
					:max="gateway?.max" />
			</div>

			<!-- Currency Selection (if multiple currencies) -->
			<div v-if="activeCurrencies.length > 0" class="p-5 border-b border-white/[0.06]">
				<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">
					Select Currency
				</h2>
				<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
					<button
						v-for="curr in activeCurrencies"
						:key="curr.id"
						@click="form.currency = curr.id"
						class="flex items-center gap-3 p-3 rounded-xl border transition-all duration-200"
						:class="form.currency === curr.id
							? 'border-purple-500 bg-purple-500/10 ring-1 ring-purple-500/50'
							: 'border-white/10 bg-white/[0.03] hover:bg-white/[0.06]'">
						<img
							v-if="curr.logo_url"
							:src="curr.logo_url"
							:alt="curr.code"
							class="w-8 h-8 rounded-full object-contain" />
						<div class="min-w-0 text-left">
							<p class="text-sm font-bold text-white">{{ curr.code }}</p>
							<p class="text-xs text-gray-400 truncate">{{ curr.name }}</p>
						</div>
					</button>
				</div>
				<p v-if="form.errors.currency" class="text-xs text-red-400 font-medium mt-2">
					{{ form.errors.currency }}
				</p>
			</div>

			<!-- Terms & Submit -->
			<div class="p-5">
				<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 mb-5">
					<div class="flex items-start gap-3">
						<Shield class="w-5 h-5 text-purple-400 flex-shrink-0 mt-0.5" />
						<div>
							<h3 class="text-sm font-semibold text-white mb-1">Terms & Conditions</h3>
							<p class="text-xs text-gray-400 leading-relaxed">
								By initiating a deposit, you confirm that you are at least 18 years old and that the
								funds are legally obtained. You agree to use the deposited funds solely for betting
								activities on this site, in compliance with our full terms of service and applicable laws.
							</p>
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
					v-if="gateway && !gateway.enable_deposit"
					class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 mb-4">
					{{ gateway.label }} is currently offline. Please select another gateway.
				</p>

				<button
					@click="createDeposit"
					:disabled="form.processing || !gateway?.enable_deposit || !form.tos || !form.gateway"
					class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-green-600 hover:bg-green-700 text-white
						   font-bold text-sm uppercase tracking-wide transition-all duration-200
						   disabled:opacity-40 disabled:cursor-not-allowed
						   flex items-center justify-center gap-2">
					<ArrowDownToLine class="w-4 h-4" />
					<span v-if="form.processing">Processing...</span>
					<span v-else>Initiate Deposit</span>
				</button>
			</div>
		</div>

		<!-- Deposit History -->
		<div v-if="deposits?.data?.length > 0" class="space-y-4">
			<h2 class="text-lg font-bold text-white">Recent Deposits</h2>

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
							v-for="deposit in deposits.data"
							:key="deposit.id"
							class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
							<td class="px-4 py-3">
								<Link
									:href="route('deposits.show', { deposit: deposit.uuid })"
									class="text-sm font-semibold text-purple-400 hover:text-purple-300 uppercase">
									#{{ deposit.uid }}
								</Link>
							</td>
							<td class="px-4 py-3">
								<div class="flex items-center gap-2">
									<img :src="deposit.gateway.logo" class="w-6 h-6 rounded-full" />
									<span class="text-sm text-gray-300">{{ deposit.gateway.name }}</span>
								</div>
							</td>
							<td class="px-4 py-3">
								<MoneyFormat class="text-sm font-bold text-white" :amount="deposit.amount" />
							</td>
							<td class="px-4 py-3 text-sm text-gray-400">{{ deposit.date }}</td>
							<td class="px-4 py-3 text-right">
								<PaymentStatusBadge :status="deposit.status" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Mobile Cards -->
			<div class="sm:hidden space-y-3">
				<Link
					v-for="deposit in deposits.data"
					:key="deposit.id"
					:href="route('deposits.show', { deposit: deposit.uuid })"
					class="block rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 hover:bg-white/[0.04] transition-colors">
					<div class="flex items-center justify-between mb-2">
						<span class="text-sm font-bold text-purple-400 uppercase">#{{ deposit.uid }}</span>
						<PaymentStatusBadge :status="deposit.status" />
					</div>
					<div class="flex items-center justify-between">
						<div class="flex items-center gap-2">
							<img :src="deposit.gateway.logo" class="w-5 h-5 rounded-full" />
							<span class="text-xs text-gray-400">{{ deposit.gateway.name }}</span>
						</div>
						<MoneyFormat class="text-sm font-bold text-white" :amount="deposit.amount" />
					</div>
					<p class="text-xs text-gray-500 mt-2">{{ deposit.date }}</p>
				</Link>
			</div>

			<Pagination :meta="deposits.meta" />
		</div>

		<!-- Empty State -->
		<WalletEmptyState
			v-else-if="!deposits?.data?.length"
			title="No deposits yet"
			description="Make your first deposit to start playing."
			:icon="CreditCard" />
	</div>
</UserLayout>
</template>
