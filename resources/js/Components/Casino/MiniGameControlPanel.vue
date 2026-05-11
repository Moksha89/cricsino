<script setup>
import { Wallet } from "lucide-vue-next";

defineProps({
	balance: { type: Number, default: 0 },
	betAmount: { type: Number, default: 10 },
	isPlaying: { type: Boolean, default: false },
	quickAmounts: { type: Array, default: () => [10, 50, 100, 500] },
	showHalf: { type: Boolean, default: true },
	showDouble: { type: Boolean, default: true },
});

const emit = defineEmits(["update:betAmount"]);

function setBet(val) {
	emit("update:betAmount", val);
}
</script>

<template>
	<div class="space-y-4">
		<div class="flex items-center justify-between mb-1">
			<label class="text-xs text-gray-400 font-medium uppercase tracking-wider">Bet Amount</label>
			<div class="flex items-center gap-1.5 text-xs text-gray-500">
				<Wallet class="w-3.5 h-3.5" />
				<span>Balance: <span class="text-white font-semibold">{{ '\u20B9' }}{{ balance.toLocaleString() }}</span></span>
			</div>
		</div>

		<div class="relative">
			<span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">{{ '\u20B9' }}</span>
			<input
				:value="betAmount"
				@input="setBet(Number($event.target.value))"
				type="number"
				min="1"
				class="w-full bg-gray-900/80 text-white text-lg font-bold rounded-xl pl-8 pr-4 py-3.5 border border-white/[0.08] focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/30 outline-none"
				:disabled="isPlaying" />
		</div>

		<div class="flex gap-1.5">
			<button
				v-for="amt in quickAmounts"
				:key="amt"
				@click="setBet(amt)"
				:disabled="isPlaying"
				class="flex-1 bg-gray-900/60 text-gray-300 text-xs font-semibold py-2.5 rounded-xl hover:bg-gray-700 hover:text-white transition border border-white/[0.06] disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
				{{ '\u20B9' }}{{ amt }}
			</button>
		</div>

		<div v-if="showHalf || showDouble" class="flex gap-1.5">
			<button
				v-if="showHalf"
				@click="setBet(Math.max(1, Math.floor(betAmount / 2)))"
				:disabled="isPlaying"
				class="flex-1 bg-gray-900/40 text-gray-400 text-xs font-semibold py-2 rounded-xl hover:bg-gray-700 hover:text-white transition border border-white/[0.04] disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
				1/2
			</button>
			<button
				v-if="showDouble"
				@click="setBet(betAmount * 2)"
				:disabled="isPlaying"
				class="flex-1 bg-gray-900/40 text-gray-400 text-xs font-semibold py-2 rounded-xl hover:bg-gray-700 hover:text-white transition border border-white/[0.04] disabled:opacity-40 disabled:cursor-not-allowed min-h-[44px]">
				2x
			</button>
		</div>

		<slot />
	</div>
</template>
