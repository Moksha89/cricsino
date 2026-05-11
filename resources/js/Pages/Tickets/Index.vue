<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { Search, ChevronDown, ChevronUp } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";
import StatusBadge from "@/Components/User/StatusBadge.vue";
import EmptyState from "@/Components/User/EmptyState.vue";
import ResponsiveTableWrapper from "@/Components/User/ResponsiveTableWrapper.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";

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
		<div class="p-4 sm:p-6">
			<PageHeader title="Ticket History" subtitle="View your accumulator and multi-bet tickets" />

			<div class="flex flex-col sm:flex-row gap-3 mb-6">
				<form @submit.prevent="doSearch" class="flex-1 max-w-sm">
					<FormInput v-model="search" placeholder="Search tickets..." @keyup.enter="doSearch">
						<template #lead>
							<Search class="w-4 h-4 text-gray-400" />
						</template>
					</FormInput>
				</form>
			</div>

			<EmptyState
				v-if="!tickets.data?.length"
				title="No tickets yet"
				message="Your accumulator tickets will appear here once you place a multi-bet."
				icon="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />

			<template v-else>
				<!-- Desktop table -->
				<div class="hidden md:block">
					<ResponsiveTableWrapper>
						<table class="min-w-full">
							<thead>
								<tr class="border-b border-white/10">
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Ticket ID</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Stake</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Combined Odds</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Potential Payout</th>
									<th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
									<th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Selections</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-white/5">
								<template v-for="ticket in tickets.data" :key="ticket.uid">
									<tr class="hover:bg-white/5 transition-colors cursor-pointer" @click="toggleExpand(ticket.uid)">
										<td class="px-4 py-3 text-sm text-gray-300 font-mono">{{ ticket.uid }}</td>
										<td class="px-4 py-3 text-sm text-right">
											<MoneyFormat :amount="ticket.amount" class="text-white" />
										</td>
										<td class="px-4 py-3 text-sm text-right text-white font-medium">{{ Number(ticket.total_odds).toFixed(2) }}</td>
										<td class="px-4 py-3 text-sm text-right">
											<MoneyFormat :amount="ticket.payout" class="text-green-400 font-medium" />
										</td>
										<td class="px-4 py-3 text-center">
											<StatusBadge :status="ticket.status" />
										</td>
										<td class="px-4 py-3 text-center">
											<button class="inline-flex items-center gap-1 text-xs text-purple-400 hover:text-purple-300">
												{{ ticket.wagers?.length || 0 }} legs
												<ChevronUp v-if="expandedTicket === ticket.uid" class="w-3 h-3" />
												<ChevronDown v-else class="w-3 h-3" />
											</button>
										</td>
									</tr>
									<tr v-if="expandedTicket === ticket.uid && ticket.wagers?.length">
										<td colspan="6" class="px-4 py-3 bg-white/5">
											<div class="space-y-2">
												<div
													v-for="(wager, i) in ticket.wagers"
													:key="i"
													class="flex items-center justify-between text-sm py-1 border-b border-white/5 last:border-0">
													<div>
														<span class="text-white">{{ wager.game_info }}</span>
														<span class="text-gray-500 mx-1">&middot;</span>
														<span class="text-gray-400">{{ wager.market_info }}</span>
														<span class="text-gray-500 mx-1">&middot;</span>
														<span class="text-purple-400">{{ wager.bet_info }}</span>
													</div>
													<span class="text-white font-medium">{{ Number(wager.odds).toFixed(2) }}</span>
												</div>
											</div>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
					</ResponsiveTableWrapper>
				</div>

				<!-- Mobile cards -->
				<div class="md:hidden space-y-3">
					<div
						v-for="ticket in tickets.data"
						:key="ticket.uid"
						class="bg-surface-light rounded-lg border border-white/10"
						@click="toggleExpand(ticket.uid)">
						<div class="p-4">
							<div class="flex items-center justify-between mb-2">
								<span class="text-xs text-gray-400 font-mono">{{ ticket.uid }}</span>
								<StatusBadge :status="ticket.status" />
							</div>
							<div class="grid grid-cols-3 gap-2 text-center mt-3">
								<div>
									<div class="text-xs text-gray-500">Stake</div>
									<MoneyFormat :amount="ticket.amount" class="text-sm text-white font-medium" />
								</div>
								<div>
									<div class="text-xs text-gray-500">Odds</div>
									<div class="text-sm text-white font-medium">{{ Number(ticket.total_odds).toFixed(2) }}</div>
								</div>
								<div>
									<div class="text-xs text-gray-500">Payout</div>
									<MoneyFormat :amount="ticket.payout" class="text-sm text-green-400 font-medium" />
								</div>
							</div>
							<div class="text-center mt-2">
								<span class="text-xs text-purple-400">{{ ticket.wagers?.length || 0 }} selections</span>
							</div>
						</div>
						<div v-if="expandedTicket === ticket.uid && ticket.wagers?.length" class="border-t border-white/10 px-4 py-3 space-y-2">
							<div
								v-for="(wager, i) in ticket.wagers"
								:key="i"
								class="text-xs py-1 border-b border-white/5 last:border-0">
								<div class="text-white">{{ wager.game_info }}</div>
								<div class="text-gray-400">{{ wager.market_info }} &middot; <span class="text-purple-400">{{ wager.bet_info }}</span> @ {{ Number(wager.odds).toFixed(2) }}</div>
							</div>
						</div>
					</div>
				</div>

				<Pagination :meta="tickets.meta" class="mt-4" />
			</template>
		</div>
	</UserLayout>
</template>
