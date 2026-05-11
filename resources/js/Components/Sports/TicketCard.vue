<script setup>
import { ref } from "vue";
import { ChevronDown } from "lucide-vue-next";
import BetStatusBadge from "@/Components/Sports/BetStatusBadge.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";

defineProps({
	ticket: Object,
});

const expanded = ref(false);
</script>

<template>
	<div class="bg-surface-light rounded-xl border border-white/[0.06] overflow-hidden">
		<div class="p-4">
			<!-- Top row: ID + status -->
			<div class="flex items-center justify-between mb-2">
				<span class="text-[11px] text-gray-500 font-mono">{{ ticket.uid }}</span>
				<BetStatusBadge :status="ticket.status" />
			</div>

			<!-- Stats grid -->
			<div class="grid grid-cols-3 gap-3 mb-3">
				<div>
					<div class="text-[10px] text-gray-500 uppercase font-medium mb-0.5">Stake</div>
					<div class="text-sm font-bold text-white">
						<MoneyFormat :amount="ticket.amount" />
					</div>
				</div>
				<div>
					<div class="text-[10px] text-gray-500 uppercase font-medium mb-0.5">Odds</div>
					<div class="text-sm font-bold text-white tabular-nums">{{ Number(ticket.total_odds).toFixed(2) }}</div>
				</div>
				<div>
					<div class="text-[10px] text-gray-500 uppercase font-medium mb-0.5">Payout</div>
					<div class="text-sm font-bold text-green-400">
						<MoneyFormat :amount="ticket.payout" />
					</div>
				</div>
			</div>

			<!-- Expand selections -->
			<button
				v-if="ticket.wagers?.length"
				@click="expanded = !expanded"
				class="flex items-center gap-1 text-xs text-primary-light hover:text-primary transition-colors min-h-[44px] w-full justify-center"
				type="button">
				{{ ticket.wagers.length }} selections
				<ChevronDown class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': expanded }" />
			</button>
		</div>

		<!-- Selections detail -->
		<div v-if="expanded && ticket.wagers?.length" class="border-t border-white/[0.06] px-4 py-3 bg-gray-900/50">
			<div class="space-y-2">
				<div
					v-for="(wager, i) in ticket.wagers"
					:key="i"
					class="flex items-center justify-between text-sm py-1.5 border-b border-white/[0.04] last:border-0">
					<div class="min-w-0 flex-1">
						<div class="text-white text-xs font-medium truncate">{{ wager.game_info }}</div>
						<div class="text-[11px] text-gray-400">
							{{ wager.market_info }}
							<span class="text-primary-light ml-1">{{ wager.bet_info }}</span>
						</div>
					</div>
					<span class="text-sm text-white font-bold tabular-nums ml-2">{{ Number(wager.odds).toFixed(2) }}</span>
				</div>
			</div>
		</div>

		<!-- Date -->
		<div v-if="ticket.created_at" class="px-4 py-2 border-t border-white/[0.04] text-[11px] text-gray-500 text-right">
			{{ new Date(ticket.created_at).toLocaleDateString() }}
		</div>
	</div>
</template>
