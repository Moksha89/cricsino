<script setup>
import BetStatusBadge from "@/Components/Sports/BetStatusBadge.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";

defineProps({
	stake: Object,
});
</script>

<template>
	<div class="bg-surface-light rounded-xl border border-white/[0.06] p-4">
		<!-- Top row: ID + status -->
		<div class="flex items-center justify-between mb-2">
			<span class="text-[11px] text-gray-500 font-mono">{{ stake.uid }}</span>
			<BetStatusBadge :status="stake.status" />
		</div>

		<!-- Game/market info -->
		<div class="text-sm font-semibold text-white mb-0.5">{{ stake.game_info || '—' }}</div>
		<div class="text-xs text-gray-400 mb-3">
			{{ stake.market_info || '' }}
			<span v-if="stake.bet_info" class="text-gray-500"> &middot; </span>
			{{ stake.bet_info || '' }}
		</div>

		<!-- Stats grid -->
		<div class="grid grid-cols-3 gap-3">
			<div class="text-center">
				<div class="text-[10px] text-gray-500 uppercase font-medium mb-0.5">Type</div>
				<span
					class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold"
					:class="stake.isLay ? 'bg-sky-500/20 text-sky-400' : 'bg-primary/20 text-primary-light'">
					{{ stake.isLay ? 'LAY' : 'BACK' }}
				</span>
			</div>
			<div class="text-center">
				<div class="text-[10px] text-gray-500 uppercase font-medium mb-0.5">Odds</div>
				<div class="text-sm font-bold text-white tabular-nums">{{ Number(stake.odds).toFixed(2) }}</div>
			</div>
			<div class="text-center">
				<div class="text-[10px] text-gray-500 uppercase font-medium mb-0.5">Stake</div>
				<div class="text-sm font-bold text-white">
					<MoneyFormat :amount="stake.amount" />
				</div>
			</div>
		</div>

		<!-- Bottom row: matched + date -->
		<div class="flex items-center justify-between mt-3 pt-3 border-t border-white/[0.04]">
			<div class="text-xs text-gray-400">
				Matched: <MoneyFormat :amount="stake.filled" class="text-white font-medium" />
			</div>
			<div v-if="stake.created_at" class="text-[11px] text-gray-500">
				{{ new Date(stake.created_at).toLocaleDateString() }}
			</div>
		</div>
	</div>
</template>
