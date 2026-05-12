<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { ArrowLeft, ArrowUpDown, ChevronUp, ChevronDown, RotateCcw } from "lucide-vue-next";
import axios from "axios";
import MiniGameControlPanel from "@/Components/Casino/MiniGameControlPanel.vue";
import ProvablyFairInfoCard from "@/Components/Casino/ProvablyFairInfoCard.vue";

const page = usePage();
const balance = computed(() => page.props.auth?.user?.balance ?? 0);

const betAmount = ref(10);
const isPlaying = ref(false);
const currentCard = ref(null);
const result = ref(null);
const history = ref([]);

const suits = { hearts: "\u2665", diamonds: "\u2666", clubs: "\u2663", spades: "\u2660" };
const suitColors = {
	hearts: "text-red-500",
	diamonds: "text-red-500",
	clubs: "text-white",
	spades: "text-white",
};

function getRandomCard() {
	const values = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
	const suitKeys = ["hearts", "diamonds", "clubs", "spades"];
	const idx = Math.floor(Math.random() * 52);
	return {
		value: values[idx % 13],
		suit: suitKeys[Math.floor(idx / 13)],
		numeric: (idx % 13) + 1,
	};
}

function startNewRound() {
	currentCard.value = getRandomCard();
	result.value = null;
	isPlaying.value = true;
}

function guess(direction) {
	if (!isPlaying.value || betAmount.value <= 0) return;

	axios
		.post(route("games.mini.hilo.bet"), {
			amount: betAmount.value,
			guess: direction,
			current_card: currentCard.value.numeric,
		})
		.then((res) => {
			result.value = res.data;
			page.props.auth.user.balance = res.data.balance;
			isPlaying.value = false;

			history.value.unshift({
				current: currentCard.value,
				drawn: res.data.card,
				won: res.data.won,
				guess: direction,
				payout: res.data.payout,
			});
			if (history.value.length > 15) history.value.pop();
		})
		.catch((err) => {
			alert(err.response?.data?.errors?.amount?.[0] || "Error placing bet");
		});
}
</script>

<template>
<Head :title="$t('Hi-Lo Game')" />
<UserLayout :showRightSidebar="false">
	<div class="p-4 lg:p-6 pb-24 lg:pb-6 space-y-4">
		<!-- Header -->
		<div class="flex items-center gap-3">
			<Link
				:href="route('casino.index')"
				class="w-9 h-9 rounded-xl bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition border border-white/[0.06]">
				<ArrowLeft class="w-4 h-4 text-gray-400" />
			</Link>
			<div class="flex items-center gap-2.5">
				<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
					<ArrowUpDown class="w-5 h-5 text-white" />
				</div>
				<div>
					<h1 class="text-xl font-bold text-white">Hi-Lo</h1>
					<span class="text-purple-400 text-[10px] font-bold uppercase tracking-widest">Provably Fair</span>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<!-- Game Display -->
			<div class="lg:col-span-2 space-y-4">
				<div class="bg-gray-800/80 rounded-2xl p-6 lg:p-8 border border-white/[0.06] min-h-[400px] flex flex-col items-center justify-center">
					<!-- Cards Display -->
					<div class="flex items-center gap-4 lg:gap-8">
						<!-- Current Card -->
						<div
							v-if="currentCard"
							class="relative bg-white rounded-2xl w-28 h-40 lg:w-36 lg:h-52 flex flex-col items-center justify-center shadow-2xl shadow-purple-900/30 border-2 border-white/20">
							<span class="text-3xl lg:text-5xl font-black text-gray-900">{{ currentCard.value }}</span>
							<span :class="['text-3xl lg:text-4xl mt-1', suitColors[currentCard.suit]]">
								{{ suits[currentCard.suit] }}
							</span>
							<span class="absolute top-2 left-2.5 text-xs font-bold text-gray-400">{{ currentCard.value }}</span>
							<span class="absolute bottom-2 right-2.5 text-xs font-bold text-gray-400 rotate-180">{{ currentCard.value }}</span>
						</div>

						<!-- VS / Arrow -->
						<div v-if="result" class="text-gray-600">
							<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
						</div>

						<!-- Result Card -->
						<div
							v-if="result?.card"
							:class="[
								'relative rounded-2xl w-28 h-40 lg:w-36 lg:h-52 flex flex-col items-center justify-center shadow-2xl border-3',
								result.won
									? 'bg-white border-emerald-500 shadow-emerald-500/20'
									: 'bg-white border-red-500 shadow-red-500/20',
							]">
							<span class="text-3xl lg:text-5xl font-black text-gray-900">{{ result.card.value }}</span>
							<span :class="['text-3xl lg:text-4xl mt-1', suitColors[result.card.suit]]">
								{{ suits[result.card.suit] }}
							</span>
						</div>

						<!-- Hidden Card -->
						<div
							v-else-if="isPlaying"
							class="relative rounded-2xl w-28 h-40 lg:w-36 lg:h-52 flex items-center justify-center shadow-2xl bg-gradient-to-br from-purple-800 to-indigo-900 border-2 border-purple-500/30">
							<div class="absolute inset-2 border-2 border-purple-400/20 rounded-xl"></div>
							<div class="text-purple-300/30 text-4xl font-black">?</div>
						</div>
					</div>

					<!-- Result Banner -->
					<div v-if="result?.won" class="mt-6">
						<div class="inline-flex items-center gap-2 bg-emerald-600/15 text-emerald-400 px-5 py-2.5 rounded-xl font-bold text-lg border border-emerald-500/20">
							Won {{ '\u20B9' }}{{ result.payout }} ({{ result.multiplier }}x)
						</div>
					</div>
					<div v-else-if="result && !result.won" class="mt-6">
						<div class="inline-flex items-center gap-2 bg-red-600/15 text-red-400 px-5 py-2.5 rounded-xl font-bold text-lg border border-red-500/20">
							Wrong guess!
						</div>
					</div>

					<!-- Guess Buttons (in play area for mobile) -->
					<div v-if="isPlaying" class="flex gap-3 mt-8 w-full max-w-xs">
						<button
							@click="guess('higher')"
							class="flex-1 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-lg transition-all active:scale-95 flex items-center justify-center gap-2 min-h-[56px] shadow-lg shadow-emerald-600/20">
							<ChevronUp class="w-6 h-6" />
							Higher
						</button>
						<button
							@click="guess('lower')"
							class="flex-1 py-4 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-lg transition-all active:scale-95 flex items-center justify-center gap-2 min-h-[56px] shadow-lg shadow-red-600/20">
							<ChevronDown class="w-6 h-6" />
							Lower
						</button>
					</div>
				</div>

				<!-- History -->
				<div v-if="history.length > 0" class="flex flex-wrap gap-2">
					<div
						v-for="(h, i) in history"
						:key="i"
						:class="[
							'px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 border',
							h.won ? 'bg-emerald-600/15 text-emerald-400 border-emerald-500/10' : 'bg-red-600/15 text-red-400 border-red-500/10'
						]">
						{{ h.current.value }}{{ suits[h.current.suit] }}
						<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
						{{ h.drawn.value }}{{ suits[h.drawn.suit] }}
					</div>
				</div>

				<!-- Provably Fair -->
				<ProvablyFairInfoCard
					:server-seed="result?.server_seed" />
			</div>

			<!-- Controls -->
			<div class="bg-gray-800/80 rounded-2xl p-5 border border-white/[0.06] self-start lg:sticky lg:top-4">
				<MiniGameControlPanel
					v-model:bet-amount="betAmount"
					:balance="balance"
					:is-playing="isPlaying">

					<!-- Deal Button -->
					<button
						v-if="!isPlaying"
						@click="startNewRound"
						:disabled="betAmount <= 0"
						class="w-full py-4 rounded-xl font-bold text-base bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 hover:shadow-lg hover:shadow-purple-600/25 text-white transition-all duration-200 active:scale-[0.98] min-h-[52px] disabled:opacity-50 disabled:cursor-not-allowed">
						<span class="flex items-center justify-center gap-2">
							<RotateCcw class="w-5 h-5" />
							Deal Card
						</span>
					</button>

					<div v-if="isPlaying" class="bg-gray-900/60 rounded-xl p-4 text-center border border-white/[0.04]">
						<p class="text-gray-400 text-sm">Will the next card be <span class="text-emerald-400 font-bold">Higher</span> or <span class="text-red-400 font-bold">Lower</span>?</p>
					</div>
				</MiniGameControlPanel>
			</div>
		</div>
	</div>
</UserLayout>
</template>
