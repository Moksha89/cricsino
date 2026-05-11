<script setup>
import { onMounted } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PaymentStatusBadge from "@/Components/Wallet/PaymentStatusBadge.vue";
import PaymentDetailCard from "@/Components/Wallet/PaymentDetailCard.vue";
import CopyButton from "@/Components/Wallet/CopyButton.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";

const props = defineProps({
	withdraw: Object,
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
<Head title="Withdrawal Details" />
<UserLayout :show-right-sidebar="false">
	<div class="p-4 lg:p-6 space-y-6 max-w-4xl mx-auto">
		<!-- Back + Header -->
		<div class="flex items-center gap-4">
			<Link
				:href="route('withdraws.create')"
				class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/10 flex items-center justify-center hover:bg-white/[0.08] transition-colors">
				<ArrowLeft class="w-5 h-5 text-gray-400" />
			</Link>
			<div class="flex-1 min-w-0">
				<h1 class="text-xl font-bold text-white">Withdrawal Details</h1>
				<div class="flex items-center gap-2 mt-0.5">
					<span class="text-sm text-gray-400 font-mono">{{ withdraw.uuid }}</span>
					<CopyButton :text="withdraw.uuid" />
				</div>
			</div>
			<PaymentStatusBadge :status="withdraw.status" />
		</div>

		<!-- Withdrawal Info -->
		<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
			<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
				<PaymentDetailCard label="Tracking ID">
					<span class="uppercase font-mono">{{ withdraw.uid }}</span>
				</PaymentDetailCard>

				<PaymentDetailCard label="Status">
					<PaymentStatusBadge :status="withdraw.status" />
				</PaymentDetailCard>

				<PaymentDetailCard label="Amount">
					<span class="text-lg font-bold text-red-400">
						<MoneyFormat :amount="withdraw.amount" :symbol="withdraw.amount_currency" />
					</span>
				</PaymentDetailCard>

				<PaymentDetailCard label="Gateway Amount">
					<span v-if="withdraw.gateway_amount">
						~{{ parseFloat(withdraw.gateway_amount).toFixed(6) * 1 }}
						{{ withdraw.gateway_currency }}
					</span>
					<span v-else class="text-gray-500">Pending</span>
				</PaymentDetailCard>

				<PaymentDetailCard label="Gateway">
					<div class="flex items-center gap-2">
						<img :src="withdraw.gateway?.logo" class="w-6 h-6 rounded-full" />
						<span>{{ withdraw.gateway?.name }}</span>
					</div>
				</PaymentDetailCard>

				<PaymentDetailCard label="Created">
					{{ formatDate(withdraw.created_at) }}
				</PaymentDetailCard>

				<PaymentDetailCard label="Destination Address" class="sm:col-span-2">
					<div class="flex items-center gap-2">
						<code class="text-sm font-mono text-gray-300 break-all">{{ withdraw.to }}</code>
						<CopyButton v-if="withdraw.to" :text="withdraw.to" />
					</div>
				</PaymentDetailCard>
			</div>
		</div>

		<!-- Error Section -->
		<div
			v-if="(withdraw.gateway_error && withdraw.status === 'failed') || withdraw.status === 'rejected'"
			class="rounded-xl border border-red-500/20 bg-red-500/5 p-5">
			<h3 class="text-sm font-semibold text-red-400 mb-2">Payment Error</h3>
			<p class="text-sm text-gray-300">{{ withdraw.gateway_error }}</p>
		</div>

		<!-- Withdraw Address -->
		<div v-if="withdraw.withdraw_address" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
			<h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">Withdraw Address</h3>
			<div class="flex items-center gap-2">
				<code class="text-sm text-white font-mono bg-white/[0.05] px-3 py-2 rounded-lg break-all flex-1">
					{{ withdraw.withdraw_address }}
				</code>
				<CopyButton :text="withdraw.withdraw_address" />
			</div>
		</div>

		<!-- Associated Transaction -->
		<div v-if="withdraw.transaction" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
			<h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">Transaction Record</h3>
			<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
				<PaymentDetailCard label="Transaction ID">
					<span class="font-mono text-xs">{{ withdraw.transaction.uuid }}</span>
				</PaymentDetailCard>
				<PaymentDetailCard label="Amount">
					<MoneyFormat :amount="withdraw.transaction.amount" />
				</PaymentDetailCard>
				<PaymentDetailCard label="Type">
					<span
						class="uppercase font-bold text-xs"
						:class="withdraw.transaction.action === 'credit' ? 'text-green-400' : 'text-red-400'">
						{{ withdraw.transaction.action }}
					</span>
				</PaymentDetailCard>
			</div>
		</div>
	</div>
</UserLayout>
</template>
