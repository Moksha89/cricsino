<script setup>
import { computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { XIcon, Minus, Plus } from "lucide-vue-next";

import OddInput from "@/Components/Cards/OddInput.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import { useExchangeForm, useBookieForm } from "@/Pages/Games/bettingForm";

const { exchangeForm, removeBet } = useExchangeForm();
const { bookieForm, removeBet: removeBookie } = useBookieForm();

const multiples = computed(() => usePage().props.multiples);

function calculateReturn(stake, price, isLay) {
	const s = parseFloat(stake ?? 0);
	const p = parseFloat(price ?? 0);
	if (s <= 0 || p < 1) return { liability: 0, payout: 0 };
	return {
		liability: Number((isLay ? s * (p - 1) : s).toFixed(2)),
		payout: Number((s * p).toFixed(2)),
	};
}

function submitBet(bet) {
	const form = useForm({ ...bet });
	form.transform((data) => ({
		price: bet.price,
		stake: bet.stake,
		...bet,
	})).post(window.route("stakes.store"), {
		onSuccess() { removeBet(bet); },
		preserveScroll: true,
		preserveState: true,
	});
}

const currency = computed(() => usePage().props.currency?.currency_symbol ?? "₹");
</script>

<template>
	<div>
		<!-- Exchange Bets -->
		<div v-if="Object.keys(exchangeForm).length > 0" v-show="!multiples">
			<div class="bg-gray-800 text-white border-b border-white/[0.06] flex items-center px-4 uppercase font-inter text-xs tracking-[1px] font-bold h-11">
				<svg class="w-4 h-4 mr-2 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
				</svg>
				Bet Slip
				<span class="ml-auto bg-primary/30 text-primary-light px-2 py-0.5 rounded-full text-[10px] font-bold">
					{{ Object.keys(exchangeForm).length }}
				</span>
			</div>

			<div class="divide-y divide-white/[0.06]">
				<div
					v-for="(bet, guid) in exchangeForm"
					:key="guid"
					class="p-3">
					<!-- Header -->
					<div class="flex items-start justify-between mb-2">
						<div class="flex-1 min-w-0">
							<div class="text-xs font-bold text-white truncate">{{ bet.game }}</div>
							<div class="text-[10px] text-gray-400 truncate">{{ bet.market }}</div>
							<div class="flex items-center gap-1 mt-1">
								<span
									class="text-[10px] font-bold px-1.5 py-0.5 rounded"
									:class="bet.isLay
										? 'bg-sky-500/20 text-sky-400 border border-sky-500/30'
										: 'bg-primary/20 text-primary-light border border-primary/30'">
									{{ bet.isLay ? 'LAY' : 'BACK' }}
								</span>
								<span class="text-[10px] text-gray-300">{{ bet.bet }}</span>
							</div>
						</div>
						<button
							@click="removeBet(bet)"
							class="p-1 text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded transition-colors ml-2"
							type="button">
							<XIcon class="w-3.5 h-3.5" />
						</button>
					</div>

					<!-- Inputs -->
					<div class="grid grid-cols-2 gap-2">
						<!-- Stake -->
						<div class="relative">
							<label class="text-[10px] text-gray-500 font-medium uppercase mb-1 block">Stake</label>
							<div class="flex items-center bg-gray-800 border border-white/[0.1] rounded-lg overflow-hidden h-10">
								<span class="text-xs text-gray-400 pl-2">{{ currency }}</span>
								<input
									type="number"
									:value="bet.stake"
									@input="bet.stake = $event.target.value"
									class="flex-1 bg-transparent text-white text-sm font-bold outline-none px-2 h-full [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
									placeholder="0"
									min="0" />
								<div class="flex flex-col border-l border-white/[0.1]">
									<button type="button" @click="bet.stake = parseFloat(bet.stake ?? 0) + 1" class="px-1.5 h-5 flex items-center justify-center text-primary-light hover:bg-white/[0.06]">
										<Plus class="w-3 h-3" />
									</button>
									<button type="button" @click="bet.stake = Math.max(0, parseFloat(bet.stake ?? 0) - 1)" class="px-1.5 h-5 flex items-center justify-center text-primary-light hover:bg-white/[0.06] border-t border-white/[0.1]">
										<Minus class="w-3 h-3" />
									</button>
								</div>
							</div>
						</div>
						<!-- Price -->
						<div>
							<label class="text-[10px] text-gray-500 font-medium uppercase mb-1 block">Price</label>
							<div class="flex items-center bg-gray-800 border border-white/[0.1] rounded-lg overflow-hidden h-10">
								<OddInput
									:modelValue="bet.price"
									@update:modelValue="(val) => bet.price = val"
									class="flex-1 bg-transparent text-white text-sm font-bold outline-none px-2 h-full"
									placeholder="0" />
								<div class="flex flex-col border-l border-white/[0.1]">
									<button type="button" @click="bet.price = parseFloat(bet.price ?? 0) + 0.1" class="px-1.5 h-5 flex items-center justify-center text-primary-light hover:bg-white/[0.06]">
										<Plus class="w-3 h-3" />
									</button>
									<button type="button" @click="bet.price = Math.max(1, parseFloat(bet.price ?? 0) - 0.1)" class="px-1.5 h-5 flex items-center justify-center text-primary-light hover:bg-white/[0.06] border-t border-white/[0.1]">
										<Minus class="w-3 h-3" />
									</button>
								</div>
							</div>
						</div>
					</div>

					<!-- Payout / Liability -->
					<div class="flex items-center justify-between mt-2 text-xs">
						<span class="text-gray-400">
							{{ bet.isLay ? 'Liability' : 'Payout' }}:
							<span class="text-green-400 font-semibold">
								<MoneyFormat :amount="bet.isLay ? calculateReturn(bet.stake, bet.price, true).liability : calculateReturn(bet.stake, bet.price, false).payout" />
							</span>
						</span>
					</div>

					<!-- Submit -->
					<button
						type="button"
						@click="submitBet(bet)"
						:disabled="!bet.stake || !bet.price || parseFloat(bet.stake) <= 0"
						class="w-full mt-3 h-10 rounded-lg text-sm font-bold text-white transition-all min-h-[44px]"
						:class="bet.isLay
							? 'bg-sky-600 hover:bg-sky-500 disabled:bg-sky-600/30 disabled:text-sky-400/50'
							: 'bg-primary hover:bg-primary-light disabled:bg-primary/30 disabled:text-primary-light/50'">
						{{ bet.isLay ? 'Place Lay' : 'Place Back' }}
					</button>
				</div>
			</div>
		</div>

		<!-- Bookie/Multiple Bets -->
		<div v-if="Object.keys(bookieForm).length > 0" v-show="multiples">
			<div class="bg-gray-800 text-white border-b border-white/[0.06] flex items-center px-4 uppercase font-inter text-xs tracking-[1px] font-bold h-11">
				<svg class="w-4 h-4 mr-2 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
				</svg>
				Multiple Ticket
			</div>
			<div class="divide-y divide-white/[0.06]">
				<div
					v-for="(book, guid) in bookieForm"
					:key="guid"
					class="p-3 flex items-center justify-between">
					<div class="min-w-0 flex-1">
						<div class="text-xs text-white font-bold truncate">{{ book.game }}</div>
						<div class="text-[10px] text-gray-400 truncate">{{ book.market }} - {{ book.bet }}</div>
					</div>
					<button @click="removeBookie(book)" class="p-1 text-gray-500 hover:text-red-400 rounded ml-2" type="button">
						<XIcon class="w-3.5 h-3.5" />
					</button>
				</div>
			</div>
		</div>

		<!-- Empty bet slip -->
		<div v-if="Object.keys(exchangeForm).length === 0 && Object.keys(bookieForm).length === 0"
			class="px-4 py-8 text-center">
			<div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-3">
				<svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
				</svg>
			</div>
			<p class="text-sm text-gray-500">Click on odds to add bets</p>
		</div>
	</div>
</template>
