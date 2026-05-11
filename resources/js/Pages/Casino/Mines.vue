<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import axios from "axios";

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

	grid.value = Array(25)
		.fill(null)
		.map(() => ({ revealed: false, isMine: false }));
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
			alert(
				err.response?.data?.errors?.amount?.[0] ||
					"Error starting game"
			);
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
</script>

<template>
	<Head :title="$t('Mines Game')" />
	<AuthenticatedLayout>
		<div class="max-w-4xl mx-auto px-4 py-6">
			<h1 class="text-2xl font-bold text-white mb-6">
				{{ $t("Mines") }}
			</h1>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<div class="md:col-span-2">
					<div class="bg-gray-800 rounded-xl p-6">
						<!-- 5x5 Grid -->
						<div class="grid grid-cols-5 gap-2 max-w-[350px] mx-auto">
							<button
								v-for="(cell, i) in grid"
								:key="i"
								@click="revealTile(i)"
								:disabled="!isPlaying || cell.revealed"
								:class="[
									'aspect-square rounded-lg flex items-center justify-center text-2xl font-bold transition-all duration-200',
									cell.revealed && cell.isMine
										? 'bg-red-600 text-white'
										: cell.revealed && !cell.isMine
											? 'bg-green-600 text-white'
											: isPlaying
												? 'bg-gray-600 hover:bg-gray-500 cursor-pointer'
												: 'bg-gray-700',
								]">
								<span v-if="cell.revealed && cell.isMine">💣</span>
								<span v-else-if="cell.revealed && !cell.isMine">💎</span>
								<span v-else class="text-gray-500">?</span>
							</button>
						</div>

						<!-- Result Banner -->
						<div
							v-if="gameResult === 'won'"
							class="text-center mt-4 text-green-400 font-bold text-xl">
							Cashed Out at {{ currentMultiplier }}x!
						</div>
						<div
							v-if="gameResult === 'lost'"
							class="text-center mt-4 text-red-400 font-bold text-xl">
							💣 BOOM! Mine hit!
						</div>
					</div>

					<!-- History -->
					<div class="flex flex-wrap gap-2 mt-4">
						<span
							v-for="(h, i) in history"
							:key="i"
							:class="[
								'px-2 py-1 rounded text-xs font-bold',
								h.won
									? 'bg-green-900 text-green-300'
									: 'bg-red-900 text-red-300',
							]">
							{{ h.won ? `${h.multiplier}x` : "Lost" }}
						</span>
					</div>
				</div>

				<!-- Controls -->
				<div class="bg-gray-800 rounded-xl p-6">
					<div class="mb-4">
						<label class="text-sm text-gray-400 mb-1 block">
							{{ $t("Bet Amount") }} (₹)
						</label>
						<input
							v-model.number="betAmount"
							type="number"
							min="1"
							class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 border border-gray-600"
							:disabled="isPlaying" />
						<div class="flex gap-1 mt-2">
							<button
								v-for="amt in [10, 50, 100, 500]"
								:key="amt"
								@click="betAmount = amt"
								class="flex-1 bg-gray-700 text-white text-xs py-1 rounded hover:bg-gray-600"
								:disabled="isPlaying">
								₹{{ amt }}
							</button>
						</div>
					</div>

					<div class="mb-4">
						<label class="text-sm text-gray-400 mb-1 block">
							{{ $t("Number of Mines") }}
						</label>
						<div class="flex gap-1">
							<button
								v-for="n in [1, 3, 5, 10, 24]"
								:key="n"
								@click="mineCount = n"
								:class="[
									'flex-1 py-2 rounded text-sm font-bold',
									mineCount === n
										? 'bg-blue-600 text-white'
										: 'bg-gray-700 text-gray-300 hover:bg-gray-600',
								]"
								:disabled="isPlaying">
								{{ n }}
							</button>
						</div>
					</div>

					<div
						v-if="isPlaying"
						class="mb-4 bg-gray-700 rounded-lg p-3 text-center">
						<span class="text-gray-400 text-sm block">{{ $t("Current Multiplier") }}</span>
						<span class="text-green-400 font-bold text-2xl">
							{{ currentMultiplier }}x
						</span>
						<span class="text-gray-400 text-sm block mt-1">
							₹{{ potentialPayout.toFixed(2) }}
						</span>
					</div>

					<button
						v-if="!isPlaying"
						@click="startGame"
						:disabled="betAmount <= 0"
						class="w-full py-3 rounded-lg font-bold text-lg bg-green-600 hover:bg-green-700 text-white transition">
						{{ $t("Start Game") }}
					</button>

					<button
						v-if="isPlaying"
						@click="cashout"
						class="w-full py-3 rounded-lg font-bold text-lg bg-yellow-500 hover:bg-yellow-600 text-black transition">
						{{ $t("Cashout") }} ₹{{ potentialPayout.toFixed(2) }}
					</button>

					<div class="mt-4 text-xs text-gray-500">
						<p>{{ $t("Provably Fair") }} | 3% {{ $t("House Edge") }}</p>
					</div>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
