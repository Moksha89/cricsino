<script setup>
import { computed, ref, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { XIcon, Minus, Plus } from "lucide-vue-next";

import OddInput from "@/Components/Cards/OddInput.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import { useExchangeForm } from "@/Pages/Games/bettingForm";

const { exchangeForm, removeBet } = useExchangeForm();

const isOpen = ref(false);
const betCount = computed(() => Object.keys(exchangeForm.value).length);
const activeBet = computed(() => {
	const keys = Object.keys(exchangeForm.value);
	return keys.length > 0 ? exchangeForm.value[keys[keys.length - 1]] : null;
});

watch(betCount, (newVal, oldVal) => {
	if (newVal > oldVal) isOpen.value = true;
});

function close() {
	isOpen.value = false;
}

function calculateReturn(stake, price, isLay) {
	const s = parseFloat(stake ?? 0);
	const p = parseFloat(price ?? 0);
	if (s <= 0 || p < 1) return { liability: 0, payout: 0 };
	return {
		liability: Number((isLay ? s * (p - 1) : s).toFixed(2)),
		payout: Number((s * p).toFixed(2)),
	};
}

const currency = computed(() => usePage().props.currency?.currency_symbol ?? "₹");

function submitBet(bet) {
	const form = useForm({ ...bet });
	form.transform((data) => ({
		price: bet.price,
		stake: bet.stake,
		...bet,
	})).post(window.route("stakes.store"), {
		onSuccess() {
			removeBet(bet);
			if (Object.keys(exchangeForm.value).length === 0) close();
		},
		preserveScroll: true,
		preserveState: true,
	});
}
</script>

<template>
	<!-- Floating badge trigger -->
	<button
		v-if="betCount > 0 && !isOpen"
		@click="isOpen = true"
		class="fixed bottom-20 right-4 z-[90] bg-primary hover:bg-primary-light text-white rounded-full shadow-lg shadow-primary/30 flex items-center gap-2 px-4 py-3 min-h-[48px] transition-all">
		<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
		</svg>
		<span class="text-sm font-bold">{{ betCount }}</span>
	</button>

	<!-- Sheet overlay -->
	<Teleport to="body">
		<Transition
			enter-active-class="transition-opacity duration-200"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition-opacity duration-200"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0">
			<div v-if="isOpen && betCount > 0" class="fixed inset-0 z-[95] bg-black/60 backdrop-blur-sm" @click="close"></div>
		</Transition>

		<Transition
			enter-active-class="transition-transform duration-300 ease-out"
			enter-from-class="translate-y-full"
			enter-to-class="translate-y-0"
			leave-active-class="transition-transform duration-200 ease-in"
			leave-from-class="translate-y-0"
			leave-to-class="translate-y-full">
			<div
				v-if="isOpen && betCount > 0"
				class="fixed bottom-0 left-0 right-0 z-[100] bg-gray-900 rounded-t-2xl shadow-2xl max-h-[80vh] overflow-y-auto pb-safe"
				style="padding-bottom: calc(env(safe-area-inset-bottom, 16px) + 72px);">
				<!-- Handle bar -->
				<div class="flex justify-center pt-3 pb-1">
					<div class="w-10 h-1 rounded-full bg-gray-600"></div>
				</div>

				<!-- Header -->
				<div class="flex items-center justify-between px-4 pb-3 border-b border-white/[0.06]">
					<h3 class="text-base font-bold text-white">Bet Slip ({{ betCount }})</h3>
					<button @click="close" class="p-2 text-gray-400 hover:text-white hover:bg-white/[0.06] rounded-lg transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center">
						<XIcon class="w-5 h-5" />
					</button>
				</div>

				<!-- Bets list -->
				<div class="divide-y divide-white/[0.06]">
					<div
						v-for="(bet, guid) in exchangeForm"
						:key="guid"
						class="p-4">
						<!-- Bet info -->
						<div class="flex items-start justify-between mb-3">
							<div class="flex-1 min-w-0">
								<div class="text-sm font-bold text-white">{{ bet.game }}</div>
								<div class="text-xs text-gray-400 mt-0.5">{{ bet.market }}</div>
								<div class="flex items-center gap-1.5 mt-1.5">
									<span
										class="text-[11px] font-bold px-2 py-0.5 rounded"
										:class="bet.isLay
											? 'bg-sky-500/20 text-sky-400 border border-sky-500/30'
											: 'bg-primary/20 text-primary-light border border-primary/30'">
										{{ bet.isLay ? 'LAY' : 'BACK' }}
									</span>
									<span class="text-sm text-white font-medium">{{ bet.bet }}</span>
								</div>
							</div>
							<button
								@click="removeBet(bet)"
								class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
								type="button">
								<XIcon class="w-4 h-4" />
							</button>
						</div>

						<!-- Inputs -->
						<div class="grid grid-cols-2 gap-3">
							<!-- Stake -->
							<div>
								<label class="text-[11px] text-gray-500 font-medium uppercase mb-1.5 block">Stake</label>
								<div class="flex items-center bg-gray-800 border border-white/[0.1] rounded-xl overflow-hidden h-12">
									<span class="text-sm text-gray-400 pl-3">{{ currency }}</span>
									<input
										type="number"
										:value="bet.stake"
										@input="bet.stake = $event.target.value"
										class="flex-1 bg-transparent text-white text-base font-bold outline-none px-2 h-full [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
										placeholder="0"
										inputmode="decimal"
										min="0" />
									<div class="flex flex-col border-l border-white/[0.1]">
										<button type="button" @click="bet.stake = parseFloat(bet.stake ?? 0) + 10" class="px-2 h-6 flex items-center justify-center text-primary-light">
											<Plus class="w-3.5 h-3.5" />
										</button>
										<button type="button" @click="bet.stake = Math.max(0, parseFloat(bet.stake ?? 0) - 10)" class="px-2 h-6 flex items-center justify-center text-primary-light border-t border-white/[0.1]">
											<Minus class="w-3.5 h-3.5" />
										</button>
									</div>
								</div>
							</div>
							<!-- Odds/Price -->
							<div>
								<label class="text-[11px] text-gray-500 font-medium uppercase mb-1.5 block">Odds</label>
								<div class="flex items-center bg-gray-800 border border-white/[0.1] rounded-xl overflow-hidden h-12">
									<OddInput
										:modelValue="bet.price"
										@update:modelValue="(val) => bet.price = val"
										class="flex-1 bg-transparent text-white text-base font-bold outline-none px-3 h-full"
										placeholder="0" />
									<div class="flex flex-col border-l border-white/[0.1]">
										<button type="button" @click="bet.price = parseFloat(bet.price ?? 0) + 0.1" class="px-2 h-6 flex items-center justify-center text-primary-light">
											<Plus class="w-3.5 h-3.5" />
										</button>
										<button type="button" @click="bet.price = Math.max(1, parseFloat(bet.price ?? 0) - 0.1)" class="px-2 h-6 flex items-center justify-center text-primary-light border-t border-white/[0.1]">
											<Minus class="w-3.5 h-3.5" />
										</button>
									</div>
								</div>
							</div>
						</div>

						<!-- Payout -->
						<div class="flex items-center justify-between mt-3 px-1">
							<span class="text-sm text-gray-400">
								{{ bet.isLay ? 'Liability' : 'Potential Profit' }}
							</span>
							<span class="text-sm font-bold text-green-400">
								<MoneyFormat :amount="bet.isLay ? calculateReturn(bet.stake, bet.price, true).liability : calculateReturn(bet.stake, bet.price, false).payout" />
							</span>
						</div>

						<!-- Submit -->
						<button
							type="button"
							@click="submitBet(bet)"
							:disabled="!bet.stake || !bet.price || parseFloat(bet.stake) <= 0"
							class="w-full mt-3 h-12 rounded-xl text-base font-bold text-white transition-all min-h-[48px]"
							:class="bet.isLay
								? 'bg-sky-600 hover:bg-sky-500 disabled:bg-sky-600/30 disabled:text-sky-400/50'
								: 'bg-primary hover:bg-primary-light disabled:bg-primary/30 disabled:text-primary-light/50'">
							{{ bet.isLay ? 'Place Lay Bet' : 'Place Back Bet' }}
						</button>
					</div>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>
