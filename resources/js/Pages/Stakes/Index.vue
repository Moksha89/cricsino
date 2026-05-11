<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { Search } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";
import StatusBadge from "@/Components/User/StatusBadge.vue";
import EmptyState from "@/Components/User/EmptyState.vue";
import ResponsiveTableWrapper from "@/Components/User/ResponsiveTableWrapper.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";

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
		<div class="p-4 sm:p-6">
			<PageHeader title="Bet History" subtitle="View your exchange bet history and manage open positions" />

			<div class="flex flex-col sm:flex-row gap-3 mb-6">
				<form @submit.prevent="doSearch" class="flex-1 max-w-sm">
					<FormInput v-model="search" placeholder="Search bets..." @keyup.enter="doSearch">
						<template #lead>
							<Search class="w-4 h-4 text-gray-400" />
						</template>
					</FormInput>
				</form>
			</div>

			<EmptyState
				v-if="!stakes.data?.length"
				title="No bets yet"
				message="Your exchange bets will appear here once you place your first bet."
				icon="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />

			<template v-else>
				<!-- Desktop table -->
				<div class="hidden md:block">
					<ResponsiveTableWrapper>
						<table class="min-w-full">
							<thead>
								<tr class="border-b border-white/10">
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Game</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Market</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Selection</th>
									<th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Odds</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Stake</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Matched</th>
									<th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-white/5">
								<tr v-for="stake in stakes.data" :key="stake.uid" class="hover:bg-white/5 transition-colors">
									<td class="px-4 py-3 text-sm text-gray-300 font-mono">{{ stake.uid }}</td>
									<td class="px-4 py-3 text-sm text-white">{{ stake.game_info || '—' }}</td>
									<td class="px-4 py-3 text-sm text-gray-300">{{ stake.market_info || '—' }}</td>
									<td class="px-4 py-3 text-sm text-gray-300">{{ stake.bet_info || '—' }}</td>
									<td class="px-4 py-3 text-center">
										<span
											class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
											:class="stake.isLay ? 'bg-pink-500/20 text-pink-400 border border-pink-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30'">
											{{ stake.isLay ? 'LAY' : 'BACK' }}
										</span>
									</td>
									<td class="px-4 py-3 text-sm text-right text-white font-medium">{{ Number(stake.odds).toFixed(2) }}</td>
									<td class="px-4 py-3 text-sm text-right">
										<MoneyFormat :amount="stake.amount" class="text-white" />
									</td>
									<td class="px-4 py-3 text-sm text-right">
										<MoneyFormat :amount="stake.filled" class="text-gray-300" />
									</td>
									<td class="px-4 py-3 text-center">
										<StatusBadge :status="stake.status" />
									</td>
								</tr>
							</tbody>
						</table>
					</ResponsiveTableWrapper>
				</div>

				<!-- Mobile cards -->
				<div class="md:hidden space-y-3">
					<div
						v-for="stake in stakes.data"
						:key="stake.uid"
						class="bg-surface-light rounded-lg p-4 border border-white/10">
						<div class="flex items-center justify-between mb-2">
							<span class="text-xs text-gray-400 font-mono">{{ stake.uid }}</span>
							<StatusBadge :status="stake.status" />
						</div>
						<div class="text-sm text-white font-medium mb-1">{{ stake.game_info || '—' }}</div>
						<div class="text-xs text-gray-400 mb-3">{{ stake.market_info }} &middot; {{ stake.bet_info }}</div>
						<div class="grid grid-cols-3 gap-2 text-center">
							<div>
								<div class="text-xs text-gray-500">Type</div>
								<span
									class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
									:class="stake.isLay ? 'bg-pink-500/20 text-pink-400' : 'bg-blue-500/20 text-blue-400'">
									{{ stake.isLay ? 'LAY' : 'BACK' }}
								</span>
							</div>
							<div>
								<div class="text-xs text-gray-500">Odds</div>
								<div class="text-sm text-white font-medium">{{ Number(stake.odds).toFixed(2) }}</div>
							</div>
							<div>
								<div class="text-xs text-gray-500">Stake</div>
								<MoneyFormat :amount="stake.amount" class="text-sm text-white font-medium" />
							</div>
						</div>
					</div>
				</div>

				<Pagination :meta="stakes.meta" class="mt-4" />
			</template>
		</div>
	</UserLayout>
</template>
