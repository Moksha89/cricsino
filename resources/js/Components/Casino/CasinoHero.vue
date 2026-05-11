<script setup>
import { Link } from "@inertiajs/vue3";
import { Sparkles, Play, Shield } from "lucide-vue-next";

defineProps({
	title: { type: String, default: "Casino" },
	subtitle: { type: String, default: "Provably fair House Originals & premium casino games" },
});
</script>

<template>
	<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 p-6 lg:p-10">
		<div class="absolute inset-0 opacity-10">
			<svg class="w-full h-full" viewBox="0 0 800 400" fill="none">
				<circle cx="700" cy="50" r="200" fill="currentColor" class="text-purple-400" opacity="0.3" />
				<circle cx="100" cy="350" r="150" fill="currentColor" class="text-indigo-400" opacity="0.2" />
				<rect x="300" y="100" width="200" height="200" rx="20" fill="currentColor" class="text-purple-300" opacity="0.1" transform="rotate(15 400 200)" />
			</svg>
		</div>

		<div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
			<div>
				<div class="flex items-center gap-2 mb-2">
					<Sparkles class="w-5 h-5 text-yellow-400" />
					<span class="text-yellow-400 text-xs font-bold uppercase tracking-wider">Provably Fair</span>
				</div>
				<h1 class="text-3xl lg:text-4xl font-black text-white mb-2">{{ title }}</h1>
				<p class="text-purple-200 text-sm lg:text-base max-w-lg">{{ subtitle }}</p>
			</div>
			<div class="flex items-center gap-3">
				<a href="#house-originals" class="flex items-center gap-2 bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white px-5 py-3 rounded-xl font-semibold text-sm transition-all">
					<Play class="w-4 h-4" />
					Play Now
				</a>
				<div class="flex items-center gap-1.5 text-purple-300 text-xs">
					<Shield class="w-3.5 h-3.5" />
					<span>Verified Fair</span>
				</div>
			</div>
		</div>
	</div>
</template>
