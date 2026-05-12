<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { ArrowLeft, Grid3x3, Gem, Bomb, DollarSign } from "lucide-vue-next";
import axios from "axios";
import MiniGameControlPanel from "@/Components/Casino/MiniGameControlPanel.vue";
import ProvablyFairInfoCard from "@/Components/Casino/ProvablyFairInfoCard.vue";

const page = usePage();
const balance = computed(() => page.props.auth?.user?.balance ?? 0);

const betAmount = ref(10);
const mineCount = ref(5);
const isPlaying = ref(false);
const sessionId = ref(null);
const grid = ref(Array(25).fill({ revealed: false, isMine: false }));
const currentMultiplier = ref(1.0);
const potentialPayout = ref(0);
const gameResult = ref(null);
const revealedMines = ref([]);
const history = ref([]);

function startGame() {
	if (betAmount.value <= 0 || betAmount.value > balance.value) return;

	grid.value = Array(25).fill(null).map(() => ({ revealed: false, isMine: false }));
	gameResult.value = null;
	revealedMines.value = [];
	currentMultiplier.value = 1.0;
	potentialPayout.value = 0;

	axios
		.post(route("games.mini.mines.start"), {
			amount: betAmount.value,
			mines: mineCount.value,
		})
		.then((res) => {
			sessionId.value = res.data.session_id;
			isPlaying.value = true;
			page.props.auth.user.balance = res.data.balance;
		})
		.catch((err) => {
			alert(err.response?.data?.errors?.amount?.[0] || "Error starting game");
		});
}

function revealTile(index) {
	if (!isPlaying.value || grid.value[index].revealed) return;

	axios
		.post(route("games.mini.mines.reveal"), {
			session_id: sessionId.value,
			position: index,
		})
		.then((res) => {
			if (res.data.is_mine) {
				grid.value[index] = { revealed: true, isMine: true };
				isPlaying.value = false;
				gameResult.value = "lost";
				revealedMines.value = res.data.mines;
				res.data.mines.forEach((m) => {
					grid.value[m] = { revealed: true, isMine: true };
				});
				page.props.auth.user.balance = res.data.balance;
				history.value.unshift({ won: false, payout: 0, amount: betAmount.value });
				if (history.value.length > 10) history.value.pop();
			} else {
				grid.value[index] = { revealed: true, isMine: false };
				currentMultiplier.value = res.data.multiplier;
				potentialPayout.value = res.data.potential_payout;
			}
		})
		.catch(() => {});
}

function cashout() {
	if (!isPlaying.value) return;

	axios
		.post(route("games.mini.mines.cashout"), {
			session_id: sessionId.value,
		})
		.then((res) => {
			isPlaying.value = false;
			gameResult.value = "won";
			page.props.auth.user.balance = res.data.balance;
			revealedMines.value = res.data.mines;
			res.data.mines.forEach((m) => {
				grid.value[m] = { revealed: true, isMine: true };
			});
			history.value.unshift({
				won: true,
				payout: res.data.payout,
				multiplier: res.data.multiplier,
				amount: betAmount.value,
			});
			if (history.value.length > 10) history.value.pop();
		})
		.catch(() => {});
}

const revealedSafeCount = computed(() => {
	return grid.value.filter(c => c.revealed && !c.isMine).length;
});
</script>

<template>
<Head :title="$t('Mines Game')" />
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
				<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
					<Grid3x3 class="w-5 h-5 text-white" />
				</div>
				<div>
					<h1 class="text-xl font-bold text-white">Mines</h1>
					<span class="text-purple-400 text-[10px] font-bold uppercase tracking-widest">Provably Fair</span>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<!-- Game Display -->
			<div class="lg:col-span-2 space-y-4">
				<div class="bg-gray-800/80 rounded-2xl p-4 lg:p-6 border border-white/[0.06]">
					<!-- 5x5 Grid -->
					<div class="grid grid-cols-5 gap-2 max-w-[400px] mx-auto">
						<button
							v-for="(cell, i) in grid"
							:key="i"
							@click="revealTile(i)"
							:disabled="!isPlaying || cell.revealed"
							:class="[
								'aspect-square rounded-xl flex items-center justify-center transition-all duration-300 min-h-[44px] border',
								cell.revealed && cell.isMine
									? 'bg-red-600/20 border-red-500/30 scale-95'
									: cell.revealed && !cell.isMine
										? 'bg-emerald-600/20 border-emerald-500/30 scale-95'
										: isPlaying
											? 'bg-gray-700/60 hover:bg-gray-600/80 cursor-pointer border-white/[0.06] hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/10 active:scale-95'
											: 'bg-gray-800/60 border-white/[0.04] cursor-default',
							]">
							<Bomb v-if="cell.revealed && cell.isMine" class="w-6 h-6 text-red-400" />
							<Gem v-else-if="cell.revealed && !cell.isMine" class="w-6 h-6 text-emerald-400" />
							<span v-else-if="isPlaying" class="w-2 h-2 bg-gray-500 rounded-full"></span>
						</button>
					</div>

					<!-- Result Banner -->
					<div v-if="gameResult === 'won'" class="text-center mt-5">
						<div class="inline-flex items-center gap-2 bg-emerald-600/15 text-emerald-400 px-5 py-2.5 rounded-xl font-bold text-lg border border-emerald-500/20">
							<DollarSign class="w-5 h-5" />
							Cashed Out at {{ currentMultiplier }}x
						</div>
					</div>
					<div v-if="gameResult === 'lost'" class="text-center mt-5">
						<div class="inline-flex items-center gap-2 bg-red-600/15 text-red-400 px-5 py-2.5 rounded-xl font-bold text-lg border border-red-500/20">
							<Bomb class="w-5 h-5" />
							Mine Hit!
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
						{{ h.won ? `${h.multiplier}x` : 'Lost' }}
					</span>
				</div>

				<!-- Provably Fair -->
				<ProvablyFairInfoCard />
			</div>

			<!-- Controls -->
			<div class="bg-gray-800/80 rounded-2xl p-5 border border-white/[0.06] self-start lg:sticky lg:top-4">
				<MiniGameControlPanel
					v-model:bet-amount="betAmount"
					:balance="balance"
					:is-playing="isPlaying">

					<!-- Mines Selector -->
					<div>
						<label class="text-xs text-gray-400 font-medium uppercase tracking-wider block mb-1.5">Number of Mines</label>
						<div class="flex gap-1.5">
							<button
								v-for="n in [1, 3, 5, 10, 24]"
								:key="n"
								@click="mineCount = n"
								:class="[
									'flex-1 py-2.5 rounded-xl font-bold text-sm transition-all min-h-[44px] border',
									mineCount === n
										? 'bg-purple-600 text-white border-purple-500 shadow-lg shadow-purple-600/20'
										: 'bg-gray-900/60 text-gray-400 border-white/[0.06] hover:text-white hover:border-white/10',
								]"
								:disabled="isPlaying">
								{{ n }}
							</button>
						</div>
					</div>

					<!-- Current Multiplier (while playing) -->
					<div v-if="isPlaying" class="bg-gray-900/60 rounded-xl p-4 text-center border border-white/[0.04]">
						<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider block">Current Multiplier</span>
						<span class="text-emerald-400 font-black text-3xl block mt-1">{{ currentMultiplier }}x</span>
						<span class="text-gray-400 text-sm block mt-1">{{ '\u20B9' }}{{ potentialPayout.toFixed(2) }}</span>
						<span class="text-gray-600 text-xs block mt-1">{{ revealedSafeCount }} gems found</span>
					</div>

					<!-- Start / Cashout Button -->
					<button
						v-if="!isPlaying"
						@click="startGame"
						:disabled="betAmount <= 0"
						class="w-full py-4 rounded-xl font-bold text-base bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 hover:shadow-lg hover:shadow-emerald-600/25 text-white transition-all duration-200 active:scale-[0.98] min-h-[52px] disabled:opacity-50 disabled:cursor-not-allowed">
						Start Game
					</button>

					<button
						v-if="isPlaying"
						@click="cashout"
						class="w-full py-4 rounded-xl font-bold text-base bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-600 hover:to-amber-600 hover:shadow-lg hover:shadow-yellow-500/25 text-black transition-all duration-200 active:scale-[0.98] min-h-[52px]">
						Cashout {{ '\u20B9' }}{{ potentialPayout.toFixed(2) }}
					</button>
				</MiniGameControlPanel>
			</div>
		</div>
	</div>
</UserLayout>
</template>
