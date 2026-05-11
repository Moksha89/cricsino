<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
	title: { type: String, default: "No matches available right now." },
	message: { type: String, default: "Check back later or browse other sports." },
	showHomeLink: { type: Boolean, default: true },
});
</script>

<template>
	<div class="flex flex-col items-center justify-center py-16 px-4 text-center">
		<div class="w-16 h-16 rounded-full bg-surface-light flex items-center justify-center mb-4">
			<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
					d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
			</svg>
		</div>
		<h3 class="text-lg font-semibold text-white mb-1">{{ title }}</h3>
		<p class="text-sm text-gray-400 mb-6 max-w-sm">{{ message }}</p>
		<Link
			v-if="showHomeLink"
			href="/sports"
			class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg transition-colors min-h-[44px]">
			<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
			</svg>
			Back to Sports
		</Link>
	</div>
</template>
