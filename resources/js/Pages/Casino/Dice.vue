<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import axios from "axios";

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
			alert(
				err.response?.data?.errors?.amount?.[0] ||
					"Error placing bet"
			);
		})
		.finally(() => {
			isRolling.value = false;
		});
}
</script>

<template>
	<Head :title="$t('Dice Game')" />
	<AuthenticatedLayout>
		<div class="max-w-4xl mx-auto px-4 py-6">
			<h1 class="text-2xl font-bold text-white mb-6">
				{{ $t("Dice") }}
			</h1>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<div class="md:col-span-2">
					<div
						class="bg-gray-800 rounded-xl p-8 flex flex-col items-center justify-center min-h-[300px]">
						<!-- Result Display -->
						<div
							v-if="result"
							:class="[
								'text-7xl font-black',
								result.won ? 'text-green-400' : 'text-red-400',
							]">
							{{ result.roll.toFixed(2) }}
						</div>
						<div v-else class="text-7xl font-black text-gray-600">??.??</div>

						<div v-if="result?.won" class="text-green-400 text-xl mt-2 font-bold">
							Won ₹{{ result.payout }} ({{ result.multiplier }}x)
						</div>
						<div v-else-if="result" class="text-red-400 text-xl mt-2 font-bold">
							Lost
						</div>

						<!-- Slider -->
						<div class="w-full mt-8 px-4">
							<div class="relative">
								<input
									type="range"
									v-model.number="target"
									min="1"
									max="98"
									class="w-full h-2 rounded-lg appearance-none cursor-pointer"
									:style="{
										background: `linear-gradient(to right, ${direction === 'under' ? '#22c55e' : '#ef4444'} ${target}%, ${direction === 'over' ? '#22c55e' : '#ef4444'} ${target}%)`,
									}" />
								<div class="flex justify-between text-xs text-gray-500 mt-1">
									<span>0</span>
									<span class="text-white font-bold">{{ target }}</span>
									<span>100</span>
								</div>
							</div>
						</div>

						<!-- Provably Fair Info -->
						<div v-if="result?.server_seed" class="mt-4 text-xs text-gray-500 text-center">
							<p>
								Server Seed:
								{{ result.server_seed.substring(0, 16) }}...
							</p>
							<p>Nonce: {{ result.nonce }}</p>
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
							{{ h.roll.toFixed(2) }}
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
							class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 border border-gray-600" />
						<div class="flex gap-1 mt-2">
							<button
								v-for="amt in [10, 50, 100, 500]"
								:key="amt"
								@click="betAmount = amt"
								class="flex-1 bg-gray-700 text-white text-xs py-1 rounded hover:bg-gray-600">
								₹{{ amt }}
							</button>
						</div>
					</div>

					<div class="mb-4">
						<label class="text-sm text-gray-400 mb-1 block">
							{{ $t("Direction") }}
						</label>
						<div class="flex gap-2">
							<button
								@click="direction = 'over'"
								:class="[
									'flex-1 py-2 rounded-lg font-bold text-sm',
									direction === 'over'
										? 'bg-green-600 text-white'
										: 'bg-gray-700 text-gray-300',
								]">
								Over {{ target }}
							</button>
							<button
								@click="direction = 'under'"
								:class="[
									'flex-1 py-2 rounded-lg font-bold text-sm',
									direction === 'under'
										? 'bg-green-600 text-white'
										: 'bg-gray-700 text-gray-300',
								]">
								Under {{ target }}
							</button>
						</div>
					</div>

					<div class="grid grid-cols-2 gap-3 mb-4 text-sm">
						<div class="bg-gray-700 rounded-lg p-3">
							<span class="text-gray-400 block">
								{{ $t("Win Chance") }}
							</span>
							<span class="text-white font-bold">{{ winChance }}%</span>
						</div>
						<div class="bg-gray-700 rounded-lg p-3">
							<span class="text-gray-400 block">
								{{ $t("Multiplier") }}
							</span>
							<span class="text-white font-bold">{{ multiplier }}x</span>
						</div>
					</div>

					<div class="mb-4 bg-gray-700 rounded-lg p-3 text-sm">
						<span class="text-gray-400 block">
							{{ $t("Potential Win") }}
						</span>
						<span class="text-green-400 font-bold text-lg">₹{{ potentialWin }}</span>
					</div>

					<button
						@click="roll"
						:disabled="isRolling || betAmount <= 0"
						:class="[
							'w-full py-3 rounded-lg font-bold text-lg transition',
							isRolling
								? 'bg-gray-600 cursor-not-allowed'
								: 'bg-green-600 hover:bg-green-700 text-white',
						]">
						{{ isRolling ? $t("Rolling...") : $t("Roll Dice") }}
					</button>

					<div class="mt-4 text-xs text-gray-500">
						<p>{{ $t("Provably Fair") }} | 3% {{ $t("House Edge") }}</p>
					</div>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
