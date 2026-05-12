<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { Search } from "lucide-vue-next";

import SportsNavTabs from "@/Components/Sports/SportsNavTabs.vue";
import BetStatusBadge from "@/Components/Sports/BetStatusBadge.vue";
import BetHistoryCard from "@/Components/Sports/BetHistoryCard.vue";
import SportsEmptyState from "@/Components/Sports/SportsEmptyState.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";
import UserLayout from "@/Layouts/UserLayout.vue";

const props = defineProps({
	stakes: Object,
});

const search = ref("");

function doSearch() {
	router.get(route("stakes.index"), { search: search.value }, { preserveState: true });
}
</script>

<template>
	<Head title="Bet History" />
	<UserLayout>
		<div class="p-4 sm:p-6 max-w-full">
			<!-- Sports Navigation -->
			<div class="mb-5">
				<SportsNavTabs />
			</div>

			<!-- Page header -->
			<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
				<div>
					<h1 class="text-xl sm:text-2xl font-bold text-white">Bet History</h1>
					<p class="text-sm text-gray-400 mt-1">View your exchange bet history and manage open positions</p>
				</div>
				<form @submit.prevent="doSearch" class="flex-shrink-0 w-full sm:w-64">
					<div class="flex items-center bg-surface-light border border-white/[0.08] rounded-lg overflow-hidden h-10">
						<Search class="w-4 h-4 text-gray-500 ml-3" />
						<input
							v-model="search"
							@keyup.enter="doSearch"
							placeholder="Search bets..."
							class="flex-1 bg-transparent text-white text-sm outline-none px-3 h-full placeholder-gray-500" />
					</div>
				</form>
			</div>

			<!-- Empty state -->
			<SportsEmptyState
				v-if="!stakes.data?.length"
				title="No bets yet"
				message="Your exchange bets will appear here once you place your first bet."
				:showHomeLink="true" />

			<template v-else>
				<!-- Desktop table -->
				<div class="hidden md:block bg-surface-light rounded-xl border border-white/[0.06] overflow-hidden">
					<div class="overflow-x-auto">
						<table class="min-w-full">
							<thead>
								<tr class="border-b border-white/[0.06]">
									<th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">ID</th>
									<th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Game</th>
									<th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Market</th>
									<th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Selection</th>
									<th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Type</th>
									<th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Odds</th>
									<th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Stake</th>
									<th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Matched</th>
									<th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-white/[0.04]">
								<tr v-for="stake in stakes.data" :key="stake.uid" class="hover:bg-white/[0.03] transition-colors">
									<td class="px-4 py-3.5 text-sm text-gray-400 font-mono text-[12px]">{{ stake.uid }}</td>
									<td class="px-4 py-3.5 text-sm text-white font-medium">{{ stake.game_info || '—' }}</td>
									<td class="px-4 py-3.5 text-sm text-gray-300">{{ stake.market_info || '—' }}</td>
									<td class="px-4 py-3.5 text-sm text-gray-300">{{ stake.bet_info || '—' }}</td>
									<td class="px-4 py-3.5 text-center">
										<span
											class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold"
											:class="stake.isLay ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'bg-primary/20 text-primary-light border border-primary/30'">
											{{ stake.isLay ? 'LAY' : 'BACK' }}
										</span>
									</td>
									<td class="px-4 py-3.5 text-sm text-right text-white font-semibold tabular-nums">{{ Number(stake.odds).toFixed(2) }}</td>
									<td class="px-4 py-3.5 text-sm text-right">
										<MoneyFormat :amount="stake.amount" class="text-white font-medium" />
									</td>
									<td class="px-4 py-3.5 text-sm text-right">
										<MoneyFormat :amount="stake.filled" class="text-gray-400" />
									</td>
									<td class="px-4 py-3.5 text-center">
										<BetStatusBadge :status="stake.status" />
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Mobile cards -->
				<div class="md:hidden space-y-3">
					<BetHistoryCard
						v-for="stake in stakes.data"
						:key="stake.uid"
						:stake="stake" />
				</div>

				<!-- Pagination -->
				<Pagination :meta="stakes.meta" class="mt-6" />
			</template>
		</div>
	</UserLayout>
</template>
