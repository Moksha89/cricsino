<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { Search, ChevronDown, ChevronUp } from "lucide-vue-next";

import SportsNavTabs from "@/Components/Sports/SportsNavTabs.vue";
import BetStatusBadge from "@/Components/Sports/BetStatusBadge.vue";
import TicketCard from "@/Components/Sports/TicketCard.vue";
import SportsEmptyState from "@/Components/Sports/SportsEmptyState.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";
import UserLayout from "@/Layouts/UserLayout.vue";

const props = defineProps({
	tickets: Object,
});

const search = ref("");
const expandedTicket = ref(null);

function doSearch() {
	router.get(route("tickets.index"), { search: search.value }, { preserveState: true });
}

function toggleExpand(uid) {
	expandedTicket.value = expandedTicket.value === uid ? null : uid;
}
</script>

<template>
	<Head title="Ticket History" />
	<UserLayout>
		<div class="p-4 sm:p-6 max-w-full">
			<!-- Sports Navigation -->
			<div class="mb-5">
				<SportsNavTabs />
			</div>

			<!-- Page header -->
			<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
				<div>
					<h1 class="text-xl sm:text-2xl font-bold text-white">Ticket History</h1>
					<p class="text-sm text-gray-400 mt-1">View your accumulator and multi-bet tickets</p>
				</div>
				<form @submit.prevent="doSearch" class="flex-shrink-0 w-full sm:w-64">
					<div class="flex items-center bg-surface-light border border-white/[0.08] rounded-lg overflow-hidden h-10">
						<Search class="w-4 h-4 text-gray-500 ml-3" />
						<input
							v-model="search"
							@keyup.enter="doSearch"
							placeholder="Search tickets..."
							class="flex-1 bg-transparent text-white text-sm outline-none px-3 h-full placeholder-gray-500" />
					</div>
				</form>
			</div>

			<!-- Empty state -->
			<SportsEmptyState
				v-if="!tickets.data?.length"
				title="No tickets yet"
				message="Your accumulator tickets will appear here once you place a multi-bet."
				:showHomeLink="true" />

			<template v-else>
				<!-- Desktop table -->
				<div class="hidden md:block bg-surface-light rounded-xl border border-white/[0.06] overflow-hidden">
					<div class="overflow-x-auto">
						<table class="min-w-full">
							<thead>
								<tr class="border-b border-white/[0.06]">
									<th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Ticket ID</th>
									<th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Stake</th>
									<th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Combined Odds</th>
									<th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Potential Payout</th>
									<th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
									<th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Selections</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-white/[0.04]">
								<template v-for="ticket in tickets.data" :key="ticket.uid">
									<tr class="hover:bg-white/[0.03] transition-colors cursor-pointer" @click="toggleExpand(ticket.uid)">
										<td class="px-4 py-3.5 text-sm text-gray-400 font-mono text-[12px]">{{ ticket.uid }}</td>
										<td class="px-4 py-3.5 text-sm text-right">
											<MoneyFormat :amount="ticket.amount" class="text-white font-medium" />
										</td>
										<td class="px-4 py-3.5 text-sm text-right text-white font-semibold tabular-nums">{{ Number(ticket.total_odds).toFixed(2) }}</td>
										<td class="px-4 py-3.5 text-sm text-right">
											<MoneyFormat :amount="ticket.payout" class="text-green-400 font-semibold" />
										</td>
										<td class="px-4 py-3.5 text-center">
											<BetStatusBadge :status="ticket.status" />
										</td>
										<td class="px-4 py-3.5 text-center">
											<button class="inline-flex items-center gap-1 text-xs text-primary-light hover:text-primary transition-colors min-h-[44px]">
												{{ ticket.wagers?.length || 0 }} legs
												<ChevronUp v-if="expandedTicket === ticket.uid" class="w-3.5 h-3.5" />
												<ChevronDown v-else class="w-3.5 h-3.5" />
											</button>
										</td>
									</tr>
									<!-- Expanded selections -->
									<tr v-if="expandedTicket === ticket.uid && ticket.wagers?.length">
										<td colspan="6" class="px-4 py-3 bg-white/[0.02]">
											<div class="space-y-2">
												<div
													v-for="(wager, i) in ticket.wagers"
													:key="i"
													class="flex items-center justify-between text-sm py-1.5 border-b border-white/[0.04] last:border-0">
													<div class="min-w-0 flex-1">
														<span class="text-white text-xs font-medium">{{ wager.game_info }}</span>
														<span class="text-gray-600 mx-1.5">&middot;</span>
														<span class="text-gray-400 text-xs">{{ wager.market_info }}</span>
														<span class="text-gray-600 mx-1.5">&middot;</span>
														<span class="text-primary-light text-xs font-medium">{{ wager.bet_info }}</span>
													</div>
													<span class="text-white font-semibold tabular-nums ml-2 text-sm">{{ Number(wager.odds).toFixed(2) }}</span>
												</div>
											</div>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Mobile cards -->
				<div class="md:hidden space-y-3">
					<TicketCard
						v-for="ticket in tickets.data"
						:key="ticket.uid"
						:ticket="ticket" />
				</div>

				<!-- Pagination -->
				<Pagination :meta="tickets.meta" class="mt-6" />
			</template>
		</div>
	</UserLayout>
</template>
