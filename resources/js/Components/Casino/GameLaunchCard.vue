<script setup>
import { Link } from "@inertiajs/vue3";
import { Play, Star } from "lucide-vue-next";

defineProps({
	game: { type: Object, required: true },
	featured: { type: Boolean, default: false },
});
</script>

<template>
	<Link
		:href="route('casino.show', game.uuid)"
		:class="[
			'group relative overflow-hidden rounded-2xl border transition-all duration-300',
			featured
				? 'bg-gradient-to-br from-purple-900/50 to-indigo-900/50 border-purple-500/20 hover:border-purple-500/40 col-span-2 row-span-2'
				: 'bg-gray-800/80 border-white/[0.06] hover:border-purple-500/30',
			'hover:shadow-lg hover:shadow-purple-500/10'
		]">
		<div :class="['relative overflow-hidden', featured ? 'aspect-square' : 'aspect-[3/4]']">
			<img
				v-if="game.image"
				:src="game.image"
				:alt="game.name"
				class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
			<div
				v-else
				:class="['w-full h-full flex flex-col items-center justify-center', featured ? 'bg-gradient-to-br from-purple-800 to-indigo-800' : 'bg-gradient-to-br from-gray-800 to-gray-900']">
				<div :class="['rounded-2xl bg-white/10 flex items-center justify-center', featured ? 'w-20 h-20 mb-3' : 'w-12 h-12 mb-2']">
					<span :class="['text-white font-black', featured ? 'text-4xl' : 'text-xl']">{{ game.name?.charAt(0) }}</span>
				</div>
			</div>

			<div v-if="featured" class="absolute top-3 left-3 flex items-center gap-1 bg-yellow-500/90 text-black text-[10px] font-bold px-2 py-0.5 rounded-full">
				<Star class="w-3 h-3" />
				Featured
			</div>

			<div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-5">
				<span class="inline-flex items-center gap-1.5 bg-purple-600 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-lg translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
					<Play class="w-4 h-4" />
					Launch Game
				</span>
			</div>
		</div>
		<div class="p-3">
			<h3 :class="['font-semibold text-white truncate', featured ? 'text-base' : 'text-sm']">{{ game.name }}</h3>
			<p v-if="game.provider" class="text-[11px] text-gray-500 mt-0.5">{{ game.provider }}</p>
		</div>
	</Link>
</template>
