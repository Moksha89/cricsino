<script setup>
import { onMounted } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ArrowLeft, ExternalLink } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PaymentStatusBadge from "@/Components/Wallet/PaymentStatusBadge.vue";
import PaymentDetailCard from "@/Components/Wallet/PaymentDetailCard.vue";
import CopyButton from "@/Components/Wallet/CopyButton.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import CryptoDeposit from "@/Pages/Deposits/Show/CryptoDeposit.vue";

const props = defineProps({
	deposit: Object,
	redirect: String,
});

const isValidUrl = (string) => {
	try { new URL(string); return true; } catch { return false; }
};

const formatDate = (dateString) => new Date(dateString).toLocaleString();

onMounted(() => {
	if (props.redirect && isValidUrl(props.redirect))
		setTimeout(() => { window.location.href = props.redirect; }, 1500);
});
</script>

<template>
<Head title="Deposit Details" />
<UserLayout :show-right-sidebar="false">
	<div class="p-4 lg:p-6 space-y-6 max-w-4xl mx-auto">
		<!-- Back + Header -->
		<div class="flex items-center gap-4">
			<Link
				:href="route('deposits.create')"
				class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/10 flex items-center justify-center hover:bg-white/[0.08] transition-colors">
				<ArrowLeft class="w-5 h-5 text-gray-400" />
			</Link>
			<div class="flex-1 min-w-0">
				<h1 class="text-xl font-bold text-white">Deposit Details</h1>
				<div class="flex items-center gap-2 mt-0.5">
					<span class="text-sm text-gray-400 font-mono">{{ deposit.uuid }}</span>
					<CopyButton :text="deposit.uuid" />
				</div>
			</div>
			<PaymentStatusBadge :status="deposit.status" />
		</div>

		<!-- Crypto Processing View -->
		<template v-if="deposit.status === 'processing'">
			<CryptoDeposit
				v-if="['coinpayments', 'nowpayments'].includes(deposit.gateway?.gid)"
				:deposit="deposit" />
			<div v-else-if="redirect" class="rounded-xl border border-purple-500/20 bg-purple-500/5 p-6 text-center">
				<div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center mx-auto mb-3">
					<ExternalLink class="w-6 h-6 text-purple-400 animate-pulse" />
				</div>
				<p class="text-white font-semibold mb-1">Redirecting to payment gateway...</p>
				<p class="text-sm text-gray-400">Please wait a moment</p>
			</div>
		</template>

		<!-- Deposit Info -->
		<template v-else>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
					<PaymentDetailCard label="Tracking ID">
						<span class="uppercase font-mono">{{ deposit.uid }}</span>
					</PaymentDetailCard>

					<PaymentDetailCard label="Status">
						<PaymentStatusBadge :status="deposit.status" />
					</PaymentDetailCard>

					<PaymentDetailCard label="Amount">
						<span class="text-lg font-bold text-green-400">
							<MoneyFormat :amount="deposit.amount" :symbol="deposit.amount_currency" />
						</span>
					</PaymentDetailCard>

					<PaymentDetailCard label="Gateway Amount">
						<span v-if="deposit.gateway_amount">
							{{ parseFloat(deposit.gateway_amount).toFixed(6) * 1 }}
							{{ deposit.gateway_currency }}
						</span>
						<span v-else class="text-gray-500">Pending</span>
					</PaymentDetailCard>

					<PaymentDetailCard label="Gateway">
						<div class="flex items-center gap-2">
							<img :src="deposit.gateway?.logo" class="w-6 h-6 rounded-full" />
							<span>{{ deposit.gateway?.name }}</span>
						</div>
					</PaymentDetailCard>

					<PaymentDetailCard label="Created">
						{{ formatDate(deposit.created_at) }}
					</PaymentDetailCard>
				</div>
			</div>

			<!-- Error Section -->
			<div
				v-if="(deposit.gateway_error && deposit.status === 'failed') || deposit.status === 'rejected'"
				class="rounded-xl border border-red-500/20 bg-red-500/5 p-5">
				<h3 class="text-sm font-semibold text-red-400 mb-2">Payment Error</h3>
				<p class="text-sm text-gray-300">{{ deposit.gateway_error }}</p>
			</div>

			<!-- Deposit Address -->
			<div v-if="deposit.deposit_address" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
				<h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">Deposit Address</h3>
				<div class="flex items-center gap-2">
					<code class="text-sm text-white font-mono bg-white/[0.05] px-3 py-2 rounded-lg break-all flex-1">
						{{ deposit.deposit_address }}
					</code>
					<CopyButton :text="deposit.deposit_address" />
				</div>
			</div>

			<!-- Associated Transaction -->
			<div v-if="deposit.transaction" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
				<h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">Transaction Record</h3>
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<PaymentDetailCard label="Transaction ID">
						<span class="font-mono text-xs">{{ deposit.transaction.uuid }}</span>
					</PaymentDetailCard>
					<PaymentDetailCard label="Amount">
						<MoneyFormat :amount="deposit.transaction.amount" />
					</PaymentDetailCard>
					<PaymentDetailCard label="Type">
						<span
							class="uppercase font-bold text-xs"
							:class="deposit.transaction.action === 'credit' ? 'text-green-400' : 'text-red-400'">
							{{ deposit.transaction.action }}
						</span>
					</PaymentDetailCard>
				</div>
			</div>
		</template>
	</div>
</UserLayout>
</template>
