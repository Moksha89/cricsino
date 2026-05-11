<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { ArrowLeft, Dices, RotateCcw } from "lucide-vue-next";
import axios from "axios";
import MiniGameControlPanel from "@/Components/Casino/MiniGameControlPanel.vue";
import ProvablyFairInfoCard from "@/Components/Casino/ProvablyFairInfoCard.vue";

const page = usePage();
const balance = computed(() => page.props.auth?.user?.balance ?? 0);

const betAmount = ref(10);
const target = ref(50);
const direction = ref("over");
const isRolling = ref(false);
const result = ref(null);
const history = ref([]);

const winChance = computed(() => {
	return direction.value === "over"
		? (100 - target.value).toFixed(2)
		: target.value.toFixed(2);
});

const multiplier = computed(() => {
	const chance = parseFloat(winChance.value);
	return chance > 0 ? ((100 / chance) * 0.97).toFixed(4) : "0";
});

const potentialWin = computed(() => {
	return (betAmount.value * parseFloat(multiplier.value)).toFixed(2);
});

function roll() {
	if (betAmount.value <= 0 || betAmount.value > balance.value) return;
	isRolling.value = true;
	result.value = null;

	axios
		.post(route("games.mini.dice.bet"), {
			amount: betAmount.value,
			target: target.value,
			direction: direction.value,
		})
		.then((res) => {
			result.value = res.data;
			page.props.auth.user.balance = res.data.balance;
			history.value.unshift({
				roll: res.data.roll,
				won: res.data.won,
				payout: res.data.payout,
			});
			if (history.value.length > 20) history.value.pop();
		})
		.catch((err) => {
			alert(err.response?.data?.errors?.amount?.[0] || "Error placing bet");
		})
		.finally(() => {
			isRolling.value = false;
		});
}
</script>

<template>
<Head :title="$t('Dice Game')" />
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
				<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
					<Dices class="w-5 h-5 text-white" />
				</div>
				<div>
					<h1 class="text-xl font-bold text-white">Dice</h1>
					<span class="text-purple-400 text-[10px] font-bold uppercase tracking-widest">Provably Fair</span>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<!-- Game Display -->
			<div class="lg:col-span-2 space-y-4">
				<div class="bg-gray-800/80 rounded-2xl p-6 lg:p-8 border border-white/[0.06] min-h-[300px] flex flex-col items-center justify-center">
					<!-- Result -->
					<div
						v-if="result"
						:class="['text-7xl lg:text-8xl font-black tabular-nums transition-all', result.won ? 'text-emerald-400' : 'text-red-400']">
						{{ result.roll.toFixed(2) }}
					</div>
					<div v-else class="text-7xl lg:text-8xl font-black text-gray-700 tabular-nums">??.??</div>

					<div v-if="result?.won" class="text-emerald-400 text-xl mt-3 font-bold">
						Won {{ '\u20B9' }}{{ result.payout }} ({{ result.multiplier }}x)
					</div>
					<div v-else-if="result" class="text-red-400 text-xl mt-3 font-bold">
						Lost
					</div>

					<!-- Slider -->
					<div class="w-full mt-8 px-2 max-w-lg">
						<div class="relative">
							<div class="h-3 rounded-full overflow-hidden bg-gray-700">
								<div
									:class="['h-full transition-all duration-200', direction === 'under' ? 'bg-emerald-500' : 'bg-red-500']"
									:style="{ width: target + '%' }"></div>
							</div>
							<input
								type="range"
								v-model.number="target"
								min="1"
								max="98"
								class="absolute inset-0 w-full h-3 opacity-0 cursor-pointer" />
							<div
								class="absolute top-1/2 -translate-y-1/2 w-6 h-6 bg-white rounded-full shadow-lg border-2 border-purple-500 transition-all duration-200 pointer-events-none"
								:style="{ left: `calc(${target}% - 12px)` }"></div>
						</div>
						<div class="flex justify-between text-xs text-gray-500 mt-2">
							<span>0</span>
							<span class="text-white font-bold text-sm bg-gray-800 px-2.5 py-0.5 rounded-lg border border-white/[0.08]">{{ target }}</span>
							<span>100</span>
						</div>
					</div>
				</div>

				<!-- History -->
				<div v-if="history.length > 0" class="flex flex-wrap gap-2">
					<span
						v-for="(h, i) in history"
						:key="i"
						:class="[
							'px-3 py-1.5 rounded-xl text-xs font-bold border',
							h.won ? 'bg-emerald-600/15 text-emerald-400 border-emerald-500/10' : 'bg-red-600/15 text-red-400 border-red-500/10'
						]">
						{{ h.roll.toFixed(2) }}
					</span>
				</div>

				<!-- Provably Fair -->
				<ProvablyFairInfoCard
					:server-seed="result?.server_seed"
					:nonce="result?.nonce" />
			</div>

			<!-- Controls -->
			<div class="bg-gray-800/80 rounded-2xl p-5 border border-white/[0.06] self-start lg:sticky lg:top-4">
				<MiniGameControlPanel
					v-model:bet-amount="betAmount"
					:balance="balance"
					:is-playing="isRolling">

					<!-- Direction -->
					<div>
						<label class="text-xs text-gray-400 font-medium uppercase tracking-wider block mb-1.5">Direction</label>
						<div class="flex gap-2">
							<button
								@click="direction = 'over'"
								:class="[
									'flex-1 py-3 rounded-xl font-bold text-sm transition-all min-h-[48px]',
									direction === 'over'
										? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20'
										: 'bg-gray-900/60 text-gray-400 border border-white/[0.06] hover:text-white',
								]">
								Over {{ target }}
							</button>
							<button
								@click="direction = 'under'"
								:class="[
									'flex-1 py-3 rounded-xl font-bold text-sm transition-all min-h-[48px]',
									direction === 'under'
										? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20'
										: 'bg-gray-900/60 text-gray-400 border border-white/[0.06] hover:text-white',
								]">
								Under {{ target }}
							</button>
						</div>
					</div>

					<!-- Stats -->
					<div class="grid grid-cols-2 gap-2">
						<div class="bg-gray-900/60 rounded-xl p-3 border border-white/[0.04]">
							<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider block">Win Chance</span>
							<span class="text-white font-bold text-lg">{{ winChance }}%</span>
						</div>
						<div class="bg-gray-900/60 rounded-xl p-3 border border-white/[0.04]">
							<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider block">Multiplier</span>
							<span class="text-white font-bold text-lg">{{ multiplier }}x</span>
						</div>
					</div>

					<div class="bg-gray-900/60 rounded-xl p-3 border border-white/[0.04]">
						<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider block">Potential Win</span>
						<span class="text-emerald-400 font-bold text-xl">{{ '\u20B9' }}{{ potentialWin }}</span>
					</div>

					<!-- Roll Button -->
					<button
						@click="roll"
						:disabled="isRolling || betAmount <= 0"
						:class="[
							'w-full py-4 rounded-xl font-bold text-base transition-all duration-200 min-h-[52px]',
							isRolling
								? 'bg-gray-700 cursor-not-allowed text-gray-500'
								: 'bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 hover:shadow-lg hover:shadow-blue-600/25 text-white active:scale-[0.98]',
						]">
						<span v-if="isRolling" class="flex items-center justify-center gap-2">
							<RotateCcw class="w-4 h-4 animate-spin" />
							Rolling...
						</span>
						<span v-else class="flex items-center justify-center gap-2">
							<Dices class="w-5 h-5" />
							Roll Dice
						</span>
					</button>
				</MiniGameControlPanel>
			</div>
		</div>
	</div>
</UserLayout>
</template>
