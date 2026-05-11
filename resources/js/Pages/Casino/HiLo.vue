<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import axios from "axios";

const page = usePage();
const balance = computed(() => page.props.auth?.user?.balance ?? 0);

const betAmount = ref(10);
const isPlaying = ref(false);
const currentCard = ref(null);
const result = ref(null);
const history = ref([]);

const suits = { hearts: "♥", diamonds: "♦", clubs: "♣", spades: "♠" };
const suitColors = {
	hearts: "text-red-500",
	diamonds: "text-red-500",
	clubs: "text-white",
	spades: "text-white",
};

function getRandomCard() {
	const values = [
		"A",
		"2",
		"3",
		"4",
		"5",
		"6",
		"7",
		"8",
		"9",
		"10",
		"J",
		"Q",
		"K",
	];
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
			alert(
				err.response?.data?.errors?.amount?.[0] ||
					"Error placing bet"
			);
		});
}
</script>

<template>
	<Head :title="$t('Hi-Lo Game')" />
	<UserLayout :showRightSidebar="false">
		<div class="max-w-4xl mx-auto px-4 py-6">
			<h1 class="text-2xl font-bold text-white mb-6">
				{{ $t("Hi-Lo") }}
			</h1>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<div class="md:col-span-2">
					<div
						class="bg-gray-800 rounded-xl p-8 flex flex-col items-center justify-center min-h-[400px]">
						<!-- Cards Display -->
						<div class="flex items-center gap-8">
							<!-- Current Card -->
							<div
								v-if="currentCard"
								class="bg-white rounded-xl w-32 h-48 flex flex-col items-center justify-center shadow-xl">
								<span class="text-4xl font-black text-gray-900">
									{{ currentCard.value }}
								</span>
								<span
									:class="[
										'text-4xl',
										suitColors[currentCard.suit],
									]">
									{{ suits[currentCard.suit] }}
								</span>
							</div>

							<!-- VS Arrow -->
							<div v-if="result" class="text-3xl text-gray-500 font-bold">
								→
							</div>

							<!-- Result Card -->
							<div
								v-if="result?.card"
								:class="[
									'rounded-xl w-32 h-48 flex flex-col items-center justify-center shadow-xl border-4',
									result.won
										? 'bg-white border-green-500'
										: 'bg-white border-red-500',
								]">
								<span class="text-4xl font-black text-gray-900">
									{{ result.card.value }}
								</span>
								<span
									:class="[
										'text-4xl',
										suitColors[result.card.suit],
									]">
									{{ suits[result.card.suit] }}
								</span>
							</div>

							<!-- Hidden Card -->
							<div
								v-else-if="isPlaying"
								class="bg-blue-900 rounded-xl w-32 h-48 flex items-center justify-center shadow-xl border-2 border-blue-700">
								<span class="text-5xl">🂠</span>
							</div>
						</div>

						<!-- Result Banner -->
						<div v-if="result?.won" class="text-green-400 text-xl mt-6 font-bold">
							Higher wins! +₹{{ result.payout }} ({{ result.multiplier }}x)
						</div>
						<div v-else-if="result && !result.won" class="text-red-400 text-xl mt-6 font-bold">
							Wrong guess! -₹{{ betAmount }}
						</div>

						<!-- Guess Buttons -->
						<div v-if="isPlaying" class="flex gap-4 mt-8">
							<button
								@click="guess('higher')"
								class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-xl transition flex items-center gap-2">
								↑ Higher
							</button>
							<button
								@click="guess('lower')"
								class="px-8 py-4 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-xl transition flex items-center gap-2">
								↓ Lower
							</button>
						</div>

						<!-- Provably Fair -->
						<div
							v-if="result?.server_seed"
							class="mt-4 text-xs text-gray-500 text-center">
							<p>
								Server Seed:
								{{ result.server_seed.substring(0, 16) }}...
							</p>
						</div>
					</div>

					<!-- History -->
					<div class="flex flex-wrap gap-2 mt-4">
						<div
							v-for="(h, i) in history"
							:key="i"
							:class="[
								'px-3 py-1 rounded text-xs font-bold flex items-center gap-1',
								h.won
									? 'bg-green-900 text-green-300'
									: 'bg-red-900 text-red-300',
							]">
							{{ h.current.value }}{{ suits[h.current.suit] }}
							→
							{{ h.drawn.value }}{{ suits[h.drawn.suit] }}
						</div>
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

					<button
						v-if="!isPlaying"
						@click="startNewRound"
						:disabled="betAmount <= 0"
						class="w-full py-3 rounded-lg font-bold text-lg bg-blue-600 hover:bg-blue-700 text-white transition">
						{{ $t("Deal Card") }}
					</button>

					<div v-if="isPlaying" class="text-center text-gray-400 text-sm">
						{{ $t("Will the next card be Higher or Lower?") }}
					</div>

					<div class="mt-4 text-xs text-gray-500">
						<p>{{ $t("Provably Fair") }} | 3% {{ $t("House Edge") }}</p>
					</div>
				</div>
			</div>
		</div>
	</UserLayout>
</template>
