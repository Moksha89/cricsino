<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { debouncedWatch, useUrlSearchParams } from "@vueuse/core";
import { FileText, Search, X, Plus, Minus } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import WalletSummaryCard from "@/Components/Wallet/WalletSummaryCard.vue";
import TransactionMobileCard from "@/Components/Wallet/TransactionMobileCard.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";
import DateRangeFilter from "@/Pages/Account/Statement/DateRangeFilter.vue";
import SportsFilter from "@/Pages/Account/Statement/SportsFilter.vue";
import TypesFilter from "@/Pages/Account/Statement/TypesFilter.vue";
import Transactable from "@/Pages/Account/Statement/Transactable.vue";
import { Button } from "@/Components/ui/button";
import {
	Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from "@/Components/ui/dialog";

defineProps({ transactions: Object });

const params = useUrlSearchParams("history");
const showFilters = ref(false);

const clear = () => router.get(window.route("accounts.statement"));

debouncedWatch(
	[() => params.search, () => params.time, () => params.from, () => params.to, () => params.types, () => params.sports, () => params.credit, () => params.debit],
	([search, time, from, to, types, sports, credit, debit]) => {
		router.get(
			window.route("accounts.statement"),
			{
				...(search ? { search } : {}),
				...(time ? { time } : {}),
				...(from ? { from } : {}),
				...(to ? { to } : {}),
				...(types ? { types } : {}),
				...(sports ? { sports } : {}),
				...(credit ? { credit } : {}),
				...(debit ? { debit } : {}),
			},
			{ preserveState: true, preserveScroll: true },
		);
	},
	{ debounce: 500 },
);

const txCurrentlyShowing = ref(null);
const dialogOpen = ref(false);
const showTx = (tx) => { txCurrentlyShowing.value = tx; dialogOpen.value = true; };
</script>

<template>
<Head title="Account Statement" />
<UserLayout :show-right-sidebar="false">
	<div class="p-4 lg:p-6 space-y-6 max-w-6xl mx-auto">
		<!-- Page Header -->
		<div class="flex items-center justify-between gap-3">
			<div class="flex items-center gap-3">
				<div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
					<FileText class="w-5 h-5 text-purple-400" />
				</div>
				<div>
					<h1 class="text-xl font-bold text-white">Account Statement</h1>
					<p class="text-sm text-gray-400">Transaction history and activity</p>
				</div>
			</div>
			<div class="flex items-center gap-2">
				<button
					@click="showFilters = !showFilters"
					class="px-3 py-2 rounded-lg text-xs font-semibold border transition-colors"
					:class="showFilters ? 'bg-purple-500/10 border-purple-500/30 text-purple-300' : 'bg-white/[0.05] border-white/10 text-gray-400 hover:text-white'">
					Filters
				</button>
			</div>
		</div>

		<!-- Wallet Summary -->
		<WalletSummaryCard />

		<!-- Stats Summary -->
		<div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3 text-center">
				<p class="text-lg font-bold text-white">{{ $page.props.bets ?? 0 }}</p>
				<p class="text-xs text-gray-400 uppercase">Bets</p>
			</div>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3 text-center">
				<p class="text-lg font-bold text-green-400">{{ $page.props.won ?? 0 }}</p>
				<p class="text-xs text-gray-400 uppercase">Won</p>
			</div>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3 text-center">
				<p class="text-lg font-bold text-red-400">{{ $page.props.lost ?? 0 }}</p>
				<p class="text-xs text-gray-400 uppercase">Lost</p>
			</div>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3 text-center">
				<p class="text-lg font-bold" :class="($page.props.profitLoss ?? 0) >= 0 ? 'text-green-400' : 'text-red-400'">
					<MoneyFormat :amount="$page.props.profitLoss ?? 0" />
				</p>
				<p class="text-xs text-gray-400 uppercase">P&L</p>
			</div>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3 text-center">
				<p class="text-lg font-bold text-amber-400">
					<MoneyFormat :amount="$page.props.exposure ?? 0" />
				</p>
				<p class="text-xs text-gray-400 uppercase">Exposure</p>
			</div>
		</div>

		<!-- Referrals Summary -->
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3 flex items-center justify-between">
				<div>
					<p class="text-lg font-bold text-white">{{ $page.props.referrals ?? 0 }}</p>
					<p class="text-xs text-gray-400">New Referrals</p>
				</div>
				<Link
					:href="route('accounts.commission')"
					class="text-xs font-semibold text-purple-400 hover:text-purple-300">
					View
				</Link>
			</div>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
				<p class="text-lg font-bold text-white"><MoneyFormat :amount="$page.props.refComMonth ?? 0" /></p>
				<p class="text-xs text-gray-400">Commission This Month</p>
			</div>
			<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
				<p class="text-lg font-bold text-white"><MoneyFormat :amount="$page.props.refComLifetime ?? 0" /></p>
				<p class="text-xs text-gray-400">Lifetime Commission</p>
			</div>
		</div>

		<!-- Filters & Search -->
		<div class="rounded-xl border border-white/[0.06] bg-white/[0.02] overflow-hidden">
			<div class="p-4 border-b border-white/[0.06] flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
				<div class="relative flex-1 max-w-md">
					<Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
					<input
						v-model="params.search"
						type="text"
						placeholder="Search transactions..."
						class="w-full pl-10 pr-10 py-2.5 bg-white/[0.05] border border-white/10 rounded-lg text-sm text-white
							   placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500/50" />
					<button
						v-if="params.search"
						@click="params.search = ''; clear()"
						class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-400">
						<X class="w-4 h-4" />
					</button>
				</div>
				<div class="flex gap-2">
					<label class="flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold cursor-pointer transition-colors"
						:class="params.credit ? 'bg-green-500/10 border-green-500/30 text-green-400' : 'bg-white/[0.05] border-white/10 text-gray-400'">
						<input type="checkbox" v-model="params.credit" class="sr-only" />
						Credit
					</label>
					<label class="flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-semibold cursor-pointer transition-colors"
						:class="params.debit ? 'bg-red-500/10 border-red-500/30 text-red-400' : 'bg-white/[0.05] border-white/10 text-gray-400'">
						<input type="checkbox" v-model="params.debit" class="sr-only" />
						Debit
					</label>
				</div>
			</div>

			<!-- Filter Row -->
			<div v-if="showFilters" class="p-4 border-b border-white/[0.06] flex flex-col sm:flex-row gap-3">
				<DateRangeFilter v-model:time="params.time" v-model:from="params.from" v-model:to="params.to" />
				<SportsFilter v-model:sports="params.sports" />
				<TypesFilter v-model:types="params.types" />
			</div>

			<!-- Desktop Table -->
			<div class="hidden sm:block">
				<table class="w-full" v-if="$page.props.transactions?.data?.length > 0">
					<thead>
						<tr class="border-b border-white/[0.06]">
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">TXID</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Date</th>
							<th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Description</th>
							<th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Amount</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="transaction in $page.props.transactions.data"
							:key="transaction.id"
							class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors cursor-pointer"
							@click="showTx(transaction)">
							<td class="px-4 py-3">
								<span class="text-sm font-semibold text-purple-400 uppercase">{{ transaction.uid }}</span>
							</td>
							<td class="px-4 py-3 text-sm text-gray-400">{{ transaction.created_at }}</td>
							<td class="px-4 py-3 text-sm text-gray-300">{{ transaction.description }}</td>
							<td class="px-4 py-3 text-right">
								<div class="flex items-center justify-end gap-1">
									<Plus v-if="transaction.action === 'credit'" class="w-3.5 h-3.5 text-green-400" />
									<Minus v-else class="w-3.5 h-3.5 text-red-400" />
									<MoneyFormat
										class="text-sm font-bold"
										:class="transaction.action === 'credit' ? 'text-green-400' : 'text-red-400'"
										:amount="transaction.amount" />
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<div v-else class="p-12 text-center">
					<div class="w-16 h-16 rounded-2xl bg-purple-500/10 flex items-center justify-center mx-auto mb-4">
						<FileText class="w-8 h-8 text-purple-400" />
					</div>
					<p class="text-white font-semibold mb-1">Your statement is empty</p>
					<p class="text-sm text-gray-400">Try editing the filters or selecting a new time period</p>
				</div>
			</div>

			<!-- Mobile Cards -->
			<div class="sm:hidden p-4 space-y-3">
				<template v-if="$page.props.transactions?.data?.length > 0">
					<TransactionMobileCard
						v-for="transaction in $page.props.transactions.data"
						:key="transaction.id"
						:transaction="transaction"
						@view="showTx" />
				</template>
				<div v-else class="py-8 text-center">
					<p class="text-white font-semibold mb-1">No transactions</p>
					<p class="text-sm text-gray-400">Try adjusting filters</p>
				</div>
			</div>
		</div>

		<Pagination v-if="$page.props.transactions?.meta" :meta="$page.props.transactions.meta" />

		<!-- Transaction Detail Dialog -->
		<Dialog v-model:open="dialogOpen">
			<DialogContent class="sm:max-w-[525px] bg-gray-900 border-white/10 text-white">
				<DialogHeader>
					<DialogTitle class="text-white font-mono uppercase">
						{{ txCurrentlyShowing?.uuid }}
					</DialogTitle>
					<DialogDescription class="text-gray-400">
						{{ txCurrentlyShowing?.description }}
					</DialogDescription>
				</DialogHeader>
				<Transactable v-if="txCurrentlyShowing" :transaction="txCurrentlyShowing" />
				<DialogFooter>
					<Button type="button" variant="secondary" size="sm" @click.prevent="dialogOpen = false"
						class="bg-white/[0.05] border-white/10 text-gray-300 hover:bg-white/[0.08]">
						Close
					</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	</div>
</UserLayout>
</template>
