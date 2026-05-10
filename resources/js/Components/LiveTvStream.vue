<script setup>
import { ref, computed } from "vue";

const props = defineProps({
	streamUrl: {
		type: String,
		default: null,
	},
	game: {
		type: Object,
		default: null,
	},
});

const isPlaying = ref(false);
const isFullscreen = ref(false);
const playerRef = ref(null);

const hasStream = computed(() => !!props.streamUrl);

const gameName = computed(() => {
	if (!props.game) return "Live Stream";
	return `${props.game.homeTeam?.name ?? "Home"} vs ${props.game.awayTeam?.name ?? "Away"}`;
});

function toggleStream() {
	isPlaying.value = !isPlaying.value;
}

function toggleFullscreen() {
	if (!playerRef.value) return;
	if (!document.fullscreenElement) {
		playerRef.value.requestFullscreen();
		isFullscreen.value = true;
	} else {
		document.exitFullscreen();
		isFullscreen.value = false;
	}
}
</script>

<template>
	<div
		v-if="hasStream"
		class="bg-black rounded-lg overflow-hidden"
		ref="playerRef">
		<!-- Stream Header -->
		<div
			class="flex items-center justify-between px-3 py-2 bg-gray-900 text-white">
			<div class="flex items-center space-x-2">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-4 w-4 text-red-500"
					viewBox="0 0 20 20"
					fill="currentColor">
					<path
						d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
					<path
						fill-rule="evenodd"
						d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
						clip-rule="evenodd" />
				</svg>
				<span class="text-sm font-medium">{{ $t("Live TV") }}</span>
				<span class="text-xs text-gray-400">{{ gameName }}</span>
			</div>
			<div class="flex items-center space-x-2">
				<button
					@click="toggleStream"
					class="text-xs px-2 py-1 rounded bg-gray-700 hover:bg-gray-600">
					{{ isPlaying ? $t("Stop") : $t("Watch Live") }}
				</button>
				<button
					v-if="isPlaying"
					@click="toggleFullscreen"
					class="text-xs px-2 py-1 rounded bg-gray-700 hover:bg-gray-600">
					{{ isFullscreen ? $t("Exit Fullscreen") : $t("Fullscreen") }}
				</button>
			</div>
		</div>

		<!-- Stream Content -->
		<div v-if="isPlaying" class="relative">
			<iframe
				:src="streamUrl"
				class="w-full aspect-video"
				frameborder="0"
				allowfullscreen
				allow="autoplay; encrypted-media"></iframe>
		</div>
		<div
			v-else
			class="flex items-center justify-center h-48 bg-gray-900 cursor-pointer"
			@click="toggleStream">
			<div class="text-center">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-12 w-12 text-white mx-auto mb-2"
					viewBox="0 0 20 20"
					fill="currentColor">
					<path
						fill-rule="evenodd"
						d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
						clip-rule="evenodd" />
				</svg>
				<p class="text-gray-400 text-sm">
					{{ $t("Click to watch live stream") }}
				</p>
			</div>
		</div>
	</div>
</template>
