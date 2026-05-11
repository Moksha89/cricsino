<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed, onUnmounted } from "vue";
import axios from "axios";

const page = usePage();
const balance = computed(() => page.props.auth?.user?.balance ?? 0);

const betAmount = ref(10);
const autoCashout = ref(2.0);
const isPlaying = ref(false);
const currentMultiplier = ref(1.0);
const crashed = ref(false);
const result = ref(null);
const history = ref([]);
const animationId = ref(null);

let startTime = null;
let crashPoint = null;

function startGame() {
	if (betAmount.value <= 0 || betAmount.value > balance.value) return;

	isPlaying.value = true;
	crashed.value = false;
	result.value = null;
	currentMultiplier.value = 1.0;

	axios
		.post(route("games.mini.crash.bet"), {
			amount: betAmount.value,
			auto_cashout: autoCashout.value,
		})
		.then((res) => {
			crashPoint = res.data.crash_point;
			result.value = res.data;
			page.props.auth.user.balance = res.data.balance;

			// Animate the crash
			startTime = Date.now();
			animateCrash();
		})
		.catch((err) => {
			isPlaying.value = false;
			alert(
				err.response?.data?.errors?.amount?.[0] ||
					"Error placing bet"
			);
		});
}

function animateCrash() {
	const elapsed = (Date.now() - startTime) / 1000;
	currentMultiplier.value = Math.round(Math.pow(Math.E, 0.6 * elapsed) * 100) / 100;

	if (currentMultiplier.value >= crashPoint) {
		currentMultiplier.value = crashPoint;
		crashed.value = true;
		isPlaying.value = false;
		history.value.unshift({
			point: crashPoint,
			won: result.value?.won,
			payout: result.value?.payout,
			amount: betAmount.value,
		});
		if (history.value.length > 20) history.value.pop();
		return;
	}

	animationId.value = requestAnimationFrame(animateCrash);
}

onUnmounted(() => {
	if (animationId.value) cancelAnimationFrame(animationId.value);
});

const multiplierColor = computed(() => {
	if (crashed.value) return "text-red-500";
	if (currentMultiplier.value >= 2) return "text-green-400";
	return "text-white";
});
</script>

<template>
	<Head :title="$t('Crash Game')" />
	<AuthenticatedLayout>
		<div class="max-w-4xl mx-auto px-4 py-6">
			<h1 class="text-2xl font-bold text-white mb-6">
				{{ $t("Crash") }}
			</h1>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
				<!-- Game Display -->
				<div class="md:col-span-2">
					<div
						class="bg-gray-800 rounded-xl p-8 flex flex-col items-center justify-center min-h-[300px]">
						<div
							:class="[
								'text-7xl font-black transition-all duration-100',
								multiplierColor,
							]">
							{{ currentMultiplier.toFixed(2) }}x
						</div>
						<div v-if="crashed" class="text-red-400 text-xl mt-4 font-bold">
							CRASHED!
						</div>
						<div
							v-if="result?.won"
							class="text-green-400 text-xl mt-2 font-bold">
							Won ₹{{ result.payout }}
						</div>

						<!-- Provably Fair Info -->
						<div v-if="result?.server_seed" class="mt-4 text-xs text-gray-500 text-center">
							<p>Server Seed: {{ result.server_seed.substring(0, 16) }}...</p>
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
								h.point >= 2
									? 'bg-green-900 text-green-300'
									: 'bg-red-900 text-red-300',
							]">
							{{ h.point.toFixed(2) }}x
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
							class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 border border-gray-600 focus:border-blue-500"
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
							{{ $t("Auto Cashout") }}
						</label>
						<input
							v-model.number="autoCashout"
							type="number"
							min="1.01"
							step="0.1"
							class="w-full bg-gray-700 text-white rounded-lg px-3 py-2 border border-gray-600 focus:border-blue-500"
							:disabled="isPlaying" />
					</div>

					<button
						@click="startGame"
						:disabled="isPlaying || betAmount <= 0"
						:class="[
							'w-full py-3 rounded-lg font-bold text-lg transition',
							isPlaying
								? 'bg-gray-600 cursor-not-allowed'
								: 'bg-green-600 hover:bg-green-700 text-white',
						]">
						{{ isPlaying ? $t("Running...") : $t("Place Bet") }}
					</button>

					<div class="mt-4 text-xs text-gray-500">
						<p>{{ $t("Provably Fair") }} | 3% {{ $t("House Edge") }}</p>
					</div>
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
