<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, computed, onUnmounted } from "vue";
import { ArrowLeft, TrendingUp, Zap, RotateCcw } from "lucide-vue-next";
import axios from "axios";
import MiniGameControlPanel from "@/Components/Casino/MiniGameControlPanel.vue";
import ProvablyFairInfoCard from "@/Components/Casino/ProvablyFairInfoCard.vue";

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
	if (crashed.value) return "text-red-500";
	if (currentMultiplier.value >= 2) return "text-emerald-400";
	return "text-white";
});
</script>

<template>
<Head :title="$t('Crash Game')" />
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
				<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center">
					<TrendingUp class="w-5 h-5 text-white" />
				</div>
				<div>
					<h1 class="text-xl font-bold text-white">Crash</h1>
					<span class="text-purple-400 text-[10px] font-bold uppercase tracking-widest">Provably Fair</span>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<!-- Game Display -->
			<div class="lg:col-span-2 space-y-4">
				<div class="relative bg-gray-800/80 rounded-2xl border border-white/[0.06] overflow-hidden min-h-[320px] lg:min-h-[380px] flex flex-col items-center justify-center p-8">
					<!-- Animated background -->
					<div class="absolute inset-0 overflow-hidden">
						<svg class="w-full h-full opacity-[0.04]" viewBox="0 0 400 300" preserveAspectRatio="none">
							<path d="M0 300 Q100 200 200 150 T400 0" stroke="currentColor" stroke-width="2" fill="none" class="text-purple-400" />
							<path d="M0 300 Q150 250 250 200 T400 50" stroke="currentColor" stroke-width="1" fill="none" class="text-purple-300" />
						</svg>
					</div>

					<div class="relative z-10 text-center">
						<div :class="['text-7xl lg:text-8xl font-black transition-all duration-100 tabular-nums', multiplierColor]">
							{{ currentMultiplier.toFixed(2) }}x
						</div>

						<div v-if="crashed" class="mt-4">
							<div class="inline-flex items-center gap-2 bg-red-600/15 text-red-400 px-4 py-2 rounded-xl font-bold text-lg">
								<Zap class="w-5 h-5" />
								CRASHED
							</div>
						</div>

						<div v-if="result?.won" class="mt-3 text-emerald-400 text-xl font-bold">
							Won {{ '\u20B9' }}{{ result.payout }}
						</div>
					</div>
				</div>

				<!-- History -->
				<div v-if="history.length > 0" class="flex flex-wrap gap-2">
					<span
						v-for="(h, i) in history"
						:key="i"
						:class="[
							'px-3 py-1.5 rounded-xl text-xs font-bold transition-all',
							h.point >= 2 ? 'bg-emerald-600/15 text-emerald-400 border border-emerald-500/10' : 'bg-red-600/15 text-red-400 border border-red-500/10'
						]">
						{{ h.point.toFixed(2) }}x
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
					:is-playing="isPlaying">

					<!-- Auto Cashout -->
					<div>
						<label class="text-xs text-gray-400 font-medium uppercase tracking-wider block mb-1.5">Auto Cashout At</label>
						<div class="relative">
							<input
								v-model.number="autoCashout"
								type="number"
								min="1.01"
								step="0.1"
								class="w-full bg-gray-900/80 text-white text-lg font-bold rounded-xl px-4 py-3.5 border border-white/[0.08] focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/30 outline-none"
								:disabled="isPlaying" />
							<span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-semibold">x</span>
						</div>
					</div>

					<!-- Action Button -->
					<button
						@click="startGame"
						:disabled="isPlaying || betAmount <= 0"
						:class="[
							'w-full py-4 rounded-xl font-bold text-base transition-all duration-200 min-h-[52px]',
							isPlaying
								? 'bg-gray-700 cursor-not-allowed text-gray-500'
								: 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg hover:shadow-emerald-600/25 text-white active:scale-[0.98]',
						]">
						<span v-if="isPlaying" class="flex items-center justify-center gap-2">
							<RotateCcw class="w-4 h-4 animate-spin" />
							Running...
						</span>
						<span v-else>Place Bet</span>
					</button>
				</MiniGameControlPanel>
			</div>
		</div>
	</div>
</UserLayout>
</template>
