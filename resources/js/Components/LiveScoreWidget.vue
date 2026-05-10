<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";

const props = defineProps({
	game: {
		type: Object,
		required: true,
	},
	scorecardUrl: {
		type: String,
		default: null,
	},
});

const isExpanded = ref(false);
const scores = ref(props.game.scores || []);
const status = ref(props.game.status || "NS");

const homeScore = computed(() => {
	const total = scores.value.find(
		(s) => s.type === "total" && s.team === "home"
	);
	return total?.score ?? 0;
});

const awayScore = computed(() => {
	const total = scores.value.find(
		(s) => s.type === "total" && s.team === "away"
	);
	return total?.score ?? 0;
});

const isLive = computed(() => {
	return ["LIVE", "1ST", "2ND", "HT", "ET", "BRK"].includes(status.value);
});

let listener = null;

onMounted(() => {
	if (props.game.uuid) {
		listener = window.Echo?.channel(props.game.uuid);
		listener?.listen("GameUpdated", (data) => {
			if (data.scores) scores.value = data.scores;
			if (data.status) status.value = data.status;
		});
	}
});

onUnmounted(() => {
	if (listener) {
		listener.stopListening("GameUpdated");
	}
});
</script>

<template>
	<div
		class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 mb-2"
		v-if="isLive">
		<div class="flex items-center justify-between">
			<div class="flex items-center space-x-2">
				<span
					class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
					<span class="w-2 h-2 bg-red-500 rounded-full mr-1 animate-pulse"></span>
					LIVE
				</span>
				<span class="text-sm text-gray-600 dark:text-gray-400">
					{{ status }}
				</span>
			</div>
			<button
				v-if="scorecardUrl"
				@click="isExpanded = !isExpanded"
				class="text-xs text-blue-500 hover:text-blue-700">
				{{ isExpanded ? $t("Hide Scorecard") : $t("Show Scorecard") }}
			</button>
		</div>

		<div class="flex items-center justify-center mt-2 space-x-6">
			<div class="text-center">
				<div class="text-lg font-bold dark:text-white">
					{{ game.homeTeam?.name || "Home" }}
				</div>
				<div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
					{{ homeScore }}
				</div>
			</div>
			<div class="text-gray-400 text-lg">vs</div>
			<div class="text-center">
				<div class="text-lg font-bold dark:text-white">
					{{ game.awayTeam?.name || "Away" }}
				</div>
				<div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
					{{ awayScore }}
				</div>
			</div>
		</div>

		<!-- Embedded Scorecard -->
		<div v-if="isExpanded && scorecardUrl" class="mt-3">
			<iframe
				:src="scorecardUrl"
				class="w-full h-64 rounded border border-gray-300 dark:border-gray-700"
				frameborder="0"
				allowfullscreen></iframe>
		</div>
	</div>
</template>
