<script setup>
import { Link } from "@inertiajs/vue3";
import { TrendingUp, Dices, Grid3x3, ArrowUpDown } from "lucide-vue-next";

const props = defineProps({
	game: { type: Object, required: true },
});

const iconMap = {
	Crash: TrendingUp,
	Dice: Dices,
	Mines: Grid3x3,
	"Hi-Lo": ArrowUpDown,
};

const gradientMap = {
	Crash: "from-orange-600 via-red-600 to-rose-700",
	Dice: "from-blue-600 via-indigo-600 to-violet-700",
	Mines: "from-emerald-600 via-teal-600 to-cyan-700",
	"Hi-Lo": "from-purple-600 via-fuchsia-600 to-pink-700",
};
</script>

<template>
	<Link
		:href="route(game.route)"
		class="group relative overflow-hidden rounded-2xl transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl hover:shadow-purple-500/20">
		<div :class="['relative p-5 lg:p-6 min-h-[140px] lg:min-h-[160px] flex flex-col justify-between bg-gradient-to-br', gradientMap[game.name] || 'from-purple-600 to-indigo-700']">
			<div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -translate-y-8 translate-x-8 group-hover:scale-150 transition-transform duration-500"></div>
			<div class="absolute bottom-0 left-0 w-16 h-16 bg-black/10 rounded-full translate-y-6 -translate-x-6"></div>

			<div class="relative z-10">
				<div class="flex items-center gap-2 mb-1">
					<component :is="iconMap[game.name] || TrendingUp" class="w-5 h-5 text-white/80" />
					<span class="text-white/60 text-[10px] font-bold uppercase tracking-widest">House Original</span>
				</div>
				<h3 class="text-white font-black text-xl lg:text-2xl">{{ game.name }}</h3>
				<p class="text-white/60 text-xs mt-1">{{ game.desc }}</p>
			</div>

			<div class="relative z-10 flex items-center justify-between mt-3">
				<span class="inline-flex items-center gap-1 bg-white/10 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
					Provably Fair
				</span>
				<span class="text-white/0 group-hover:text-white/90 text-xs font-bold transition-all duration-300 flex items-center gap-1">
					Play
					<svg class="w-3 h-3 -translate-x-2 group-hover:translate-x-0 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
				</span>
			</div>
		</div>
	</Link>
</template>
