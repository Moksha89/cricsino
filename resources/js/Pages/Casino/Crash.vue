<script setup>
import PremiumLayout from "@/Layouts/PremiumLayout.vue";
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

	axios.post(route("games.mini.crash.bet"), {
		amount: betAmount.value,
		auto_cashout: autoCashout.value,
	}).then((res) => {
		crashPoint = res.data.crash_point;
		result.value = res.data;
		page.props.auth.user.balance = res.data.balance;
		startTime = Date.now();
		animateCrash();
	}).catch((err) => {
		isPlaying.value = false;
		alert(err.response?.data?.errors?.amount?.[0] || "Error placing bet");
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
	if (crashed.value) return "text-danger";
	if (currentMultiplier.value >= 2) return "text-success";
	return "text-white";
});
</script>

<template>
<Head :title="$t('Crash Game')" />
<PremiumLayout>
	<div class="p-4 lg:p-6 pb-24 lg:pb-6">
		<div class="flex items-center gap-3 mb-6">
			<span class="text-2xl">📈</span>
			<h1 class="text-2xl font-bold text-white">Crash</h1>
			<span class="text-xs bg-primary/20 text-primary px-2 py-0.5 rounded-full font-semibold">Provably Fair</span>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<!-- Game Display -->
			<div class="lg:col-span-2 space-y-4">
				<div class="bg-gray-800 rounded-2xl p-8 flex flex-col items-center justify-center min-h-[320px] border border-white/[0.06] relative overflow-hidden">
					<!-- Background graph lines -->
					<div class="absolute inset-0 opacity-5">
						<svg class="w-full h-full" viewBox="0 0 400 300">
							<line x1="0" y1="300" x2="400" y2="0" stroke="currentColor" stroke-width="1"/>
							<line x1="0" y1="300" x2="200" y2="50" stroke="currentColor" stroke-width="0.5"/>
						</svg>
					</div>
					<div :class="['text-7xl font-black transition-all duration-100 z-10', multiplierColor]">
						{{ currentMultiplier.toFixed(2) }}x
					</div>
					<div v-if="crashed" class="text-danger text-xl mt-4 font-bold z-10 animate-pulse">CRASHED!</div>
					<div v-if="result?.won" class="text-success text-xl mt-2 font-bold z-10">Won ₹{{ result.payout }}</div>

					<div v-if="result?.server_seed" class="mt-4 text-xs text-gray-500 text-center z-10">
						<p>Seed: {{ result.server_seed.substring(0, 16) }}...</p>
						<p>Nonce: {{ result.nonce }}</p>
					</div>
				</div>

				<!-- History -->
				<div class="flex flex-wrap gap-2">
					<span
						v-for="(h, i) in history"
						:key="i"
						:class="[
							'px-3 py-1.5 rounded-xl text-xs font-bold',
							h.point >= 2 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'
						]">
						{{ h.point.toFixed(2) }}x
					</span>
				</div>
			</div>

			<!-- Controls -->
			<div class="bg-gray-800 rounded-2xl p-5 border border-white/[0.06]">
				<div class="mb-4">
					<label class="text-xs text-gray-400 mb-1.5 block font-medium">Bet Amount (₹)</label>
					<input
						v-model.number="betAmount"
						type="number"
						min="1"
						class="w-full bg-gray-900 text-white rounded-xl px-4 py-3 border border-white/[0.06] focus:border-primary/50 focus:ring-1 focus:ring-primary/30 outline-none text-sm"
						:disabled="isPlaying" />
					<div class="flex gap-1.5 mt-2">
						<button
							v-for="amt in [10, 50, 100, 500]"
							:key="amt"
							@click="betAmount = amt"
							class="flex-1 bg-gray-900 text-gray-300 text-xs py-2 rounded-xl hover:bg-gray-700 hover:text-white transition border border-white/[0.06]"
							:disabled="isPlaying">
							₹{{ amt }}
						</button>
					</div>
				</div>

				<div class="mb-5">
					<label class="text-xs text-gray-400 mb-1.5 block font-medium">Auto Cashout</label>
					<input
						v-model.number="autoCashout"
						type="number"
						min="1.01"
						step="0.1"
						class="w-full bg-gray-900 text-white rounded-xl px-4 py-3 border border-white/[0.06] focus:border-primary/50 focus:ring-1 focus:ring-primary/30 outline-none text-sm"
						:disabled="isPlaying" />
				</div>

				<button
					@click="startGame"
					:disabled="isPlaying || betAmount <= 0"
					:class="[
						'w-full py-3.5 rounded-xl font-bold text-base transition-all duration-200',
						isPlaying
							? 'bg-gray-600 cursor-not-allowed text-gray-400'
							: 'bg-gradient-to-r from-success to-emerald-600 hover:shadow-lg hover:shadow-success/30 text-white',
					]">
					{{ isPlaying ? 'Running...' : 'Place Bet' }}
				</button>

				<div class="mt-4 flex items-center justify-between text-xs text-gray-500">
					<span>Provably Fair</span>
					<span>3% House Edge</span>
				</div>
			</div>
		</div>
	</div>
</PremiumLayout>
</template>
