<script setup>
import { Link } from "@inertiajs/vue3";
import { Play } from "lucide-vue-next";

defineProps({
	game: { type: Object, required: true },
});
</script>

<template>
	<Link
		:href="route('casino.show', game.uuid)"
		class="group relative overflow-hidden rounded-2xl bg-gray-800/80 border border-white/[0.06] hover:border-purple-500/30 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
		<div class="aspect-[3/4] relative overflow-hidden">
			<img
				v-if="game.image"
				:src="game.image"
				:alt="game.name"
				class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
			<div
				v-else
				class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-purple-900/80 to-indigo-900/80">
				<div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mb-2">
					<span class="text-white text-2xl font-black">{{ game.name?.charAt(0) }}</span>
				</div>
				<span class="text-white/40 text-xs">{{ game.provider || 'Casino' }}</span>
			</div>

			<span
				v-if="game.is_live"
				class="absolute top-2.5 left-2.5 px-2 py-0.5 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center gap-1 shadow-lg shadow-red-600/30">
				<span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
				LIVE
			</span>

			<div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
				<span class="inline-flex items-center gap-1.5 bg-purple-600 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-lg shadow-purple-600/30 translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
					<Play class="w-3.5 h-3.5" />
					Play Now
				</span>
			</div>
		</div>
		<div class="p-3">
			<h3 class="text-sm font-semibold text-white truncate">{{ game.name }}</h3>
			<p v-if="game.provider" class="text-[11px] text-gray-500 mt-0.5">{{ game.provider }}</p>
		</div>
	</Link>
</template>
