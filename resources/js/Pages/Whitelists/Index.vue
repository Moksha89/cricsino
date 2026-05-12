<script setup>
import { computed, ref, watch } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { debouncedWatch, useUrlSearchParams } from "@vueuse/core";
import { Shield, Search, Trash2 } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PaymentGatewayCard from "@/Components/Wallet/PaymentGatewayCard.vue";
import PaymentStatusBadge from "@/Components/Wallet/PaymentStatusBadge.vue";
import CopyButton from "@/Components/Wallet/CopyButton.vue";
import WalletEmptyState from "@/Components/Wallet/WalletEmptyState.vue";
import Pagination from "@/Components/Pagination.vue";
import CollapseTransition from "@/Components/CollapseTransition.vue";
import Flag from "@/Components/Flag";

const props = defineProps({
	gateways: Array,
	currencies: Object,
	auth: Object,
	whitelists: Object,
});

const form = useForm({
	currency_id: null,
	payout_address: null,
	gateway: Object.values(props.gateways)?.[0]?.value ?? null,
});

const gateway = computed(() => props.gateways[form.gateway] ?? null);
const activeCurrencies = computed(() => form.gateway ? props.currencies[form.gateway] ?? [] : []);
const selectedCurrency = computed(() =>
	form.currency_id ? activeCurrencies.value?.find((c) => c.id === form.currency_id) : null,
);

watch(gateway, () => { form.currency_id = null; form.payout_address = null; });

const createWhitelist = () => {
	form.post(window.route("whitelists.store"));
};

const deleteWhitelistForm = useForm({});
const deleteWhitelist = (whitelist) => {
	deleteWhitelistForm.delete(
		window.route("whitelists.destroy", { whitelist: whitelist.uuid }),
		{ preserveScroll: true, preserveState: true },
	);
};

const params = useUrlSearchParams("history");
const search = ref(params.search ?? "");
debouncedWatch([search], ([s]) => {
	router.get(window.route("whitelists.index"), { search: s }, { preserveState: true, preserveScroll: true });
}, { maxWait: 700 });
</script>

<template>
<Head title="Whitelists" />
<UserLayout :show-right-sidebar="false">
	<div class="p-4 lg:p-6 space-y-6 max-w-5xl mx-auto">
		<!-- Page Header -->
		<div class="flex items-center gap-3">
			<div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
				<Shield class="w-5 h-5 text-purple-400" />
			</div>
			<div>
				<h1 class="text-xl font-bold text-white">Whitelist Accounts</h1>
				<p class="text-sm text-gray-400">Approve withdrawal accounts before you can use them</p>
			</div>
		</div>

		<!-- Add New Whitelist Form -->
		<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] overflow-hidden">
			<div class="p-5 border-b border-white/[0.06]">
				<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">
					Select Payment Gateway
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

			<!-- Currency Selection -->
			<div class="p-5 border-b border-white/[0.06]">
				<h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-3">
					Select Payout Currency
				</h2>
				<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
					<button
						v-for="curr in activeCurrencies"
						:key="curr.id"
						@click="form.currency_id = curr.id"
						class="flex items-center gap-3 p-3 rounded-xl border transition-all duration-200 text-left"
						:class="form.currency_id === curr.id
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

			<!-- Destination Address -->
			<CollapseTransition>
				<div v-if="selectedCurrency" class="p-5 border-b border-white/[0.06]">
					<label class="block text-sm font-medium text-gray-300 mb-2">
						{{ $t(gateway?.destination ?? '', { symbol: selectedCurrency?.name }) }}
					</label>
					<input
						v-model="form.payout_address"
						type="text"
						class="w-full max-w-lg px-4 py-3 bg-white/[0.05] border border-white/10 rounded-xl text-white text-sm
							   placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500/50"
						placeholder="Enter destination address" />
					<p v-if="form.errors.destination" class="text-xs text-red-400 font-medium mt-2">
						{{ form.errors.destination }}
					</p>
					<p class="text-xs text-gray-400 mt-1">
						Crosscheck destination account before submitting.
					</p>
				</div>
			</CollapseTransition>

			<!-- Submit -->
			<div class="p-5">
				<button
					@click="createWhitelist"
					:disabled="form.processing || !form.gateway || !form.currency_id || !form.payout_address"
					class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white
						   font-bold text-sm uppercase tracking-wide transition-all duration-200
						   disabled:opacity-40 disabled:cursor-not-allowed
						   flex items-center gap-2">
					<Shield class="w-4 h-4" />
					Whitelist Account
				</button>
			</div>
		</div>

		<!-- Existing Whitelists -->
		<div class="space-y-4">
			<div class="flex items-center justify-between gap-3">
				<h2 class="text-lg font-bold text-white">Current Accounts</h2>
				<div class="relative max-w-xs w-full">
					<Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
					<input
						v-model="search"
						type="text"
						placeholder="Search accounts..."
						class="w-full pl-10 pr-4 py-2 bg-white/[0.05] border border-white/10 rounded-lg text-sm text-white
							   placeholder-gray-500 focus:outline-none focus:border-purple-500" />
				</div>
			</div>

			<!-- Desktop Table -->
			<div v-if="whitelists?.data?.length > 0" class="hidden sm:block rounded-xl border border-white/[0.06] overflow-hidden">
				<table class="w-full">
					<thead>
						<tr class="border-b border-white/[0.06]">
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Gateway</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Currency</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Address</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
							<th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Actions</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="whitelist in whitelists.data"
							:key="whitelist.id"
							class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
							<td class="px-4 py-3">
								<div class="flex items-center gap-2">
									<img :src="whitelist.currency?.gateway?.logo" class="w-6 h-6 rounded-full" />
									<div>
										<span class="text-sm text-white">{{ whitelist.currency?.gateway?.name }}</span>
										<p class="text-xs text-gray-500">#{{ whitelist.uid }}</p>
									</div>
								</div>
							</td>
							<td class="px-4 py-3">
								<div class="flex items-center gap-2">
									<Flag
										v-if="whitelist.currency?.logo_url?.length === 3"
										class="w-5 h-5 rounded-full"
										:iso="whitelist.currency.logo_url" />
									<img
										v-else
										class="w-5 h-5 rounded-full"
										:src="whitelist.currency?.logo_url" />
									<span class="text-sm text-gray-300">{{ whitelist.currency?.name }}</span>
								</div>
							</td>
							<td class="px-4 py-3">
								<div class="flex items-center gap-2">
									<code class="text-xs text-gray-300 font-mono truncate max-w-[200px]">{{ whitelist.payout_address }}</code>
									<CopyButton :text="whitelist.payout_address" />
								</div>
							</td>
							<td class="px-4 py-3">
								<PaymentStatusBadge :status="whitelist.status" />
							</td>
							<td class="px-4 py-3 text-right">
								<button
									@click="deleteWhitelist(whitelist)"
									class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
									title="Remove">
									<Trash2 class="w-4 h-4" />
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Mobile Cards -->
			<div v-if="whitelists?.data?.length > 0" class="sm:hidden space-y-3">
				<div
					v-for="whitelist in whitelists.data"
					:key="whitelist.id"
					class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
					<div class="flex items-center justify-between mb-3">
						<div class="flex items-center gap-2">
							<img :src="whitelist.currency?.gateway?.logo" class="w-6 h-6 rounded-full" />
							<span class="text-sm font-semibold text-white">{{ whitelist.currency?.gateway?.name }}</span>
						</div>
						<PaymentStatusBadge :status="whitelist.status" />
					</div>
					<div class="flex items-center gap-2 mb-2">
						<Flag
							v-if="whitelist.currency?.logo_url?.length === 3"
							class="w-5 h-5 rounded-full"
							:iso="whitelist.currency.logo_url" />
						<img
							v-else
							class="w-5 h-5 rounded-full"
							:src="whitelist.currency?.logo_url" />
						<span class="text-sm text-gray-300">{{ whitelist.currency?.name }}</span>
					</div>
					<div class="flex items-center gap-2">
						<code class="text-xs text-gray-400 font-mono truncate flex-1">{{ whitelist.payout_address }}</code>
						<CopyButton :text="whitelist.payout_address" />
					</div>
					<div class="flex justify-end mt-3">
						<button
							@click="deleteWhitelist(whitelist)"
							class="text-xs text-red-400 hover:text-red-300 font-semibold flex items-center gap-1">
							<Trash2 class="w-3.5 h-3.5" />
							Remove
						</button>
					</div>
				</div>
			</div>

			<WalletEmptyState
				v-if="!whitelists?.data?.length"
				title="No whitelisted accounts"
				description="Add your first payout account above."
				:icon="Shield" />

			<Pagination v-if="whitelists?.meta" :meta="whitelists.meta" />
		</div>
	</div>
</UserLayout>
</template>
