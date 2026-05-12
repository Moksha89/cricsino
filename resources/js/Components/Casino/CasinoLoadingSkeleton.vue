<script setup>
defineProps({
	count: { type: Number, default: 8 },
	variant: { type: String, default: "card" },
});
</script>

<template>
	<div v-if="variant === 'card'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
		<div v-for="i in count" :key="i" class="rounded-2xl bg-gray-800/60 border border-white/[0.04] overflow-hidden animate-pulse">
			<div class="aspect-[3/4] bg-gray-700/50"></div>
			<div class="p-3 space-y-2">
				<div class="h-3.5 bg-gray-700/50 rounded-full w-3/4"></div>
				<div class="h-2.5 bg-gray-700/30 rounded-full w-1/2"></div>
			</div>
		</div>
	</div>

	<div v-else-if="variant === 'hero'" class="rounded-2xl bg-gray-800/60 border border-white/[0.04] p-8 animate-pulse">
		<div class="h-4 bg-gray-700/50 rounded-full w-24 mb-3"></div>
		<div class="h-8 bg-gray-700/50 rounded-full w-48 mb-2"></div>
		<div class="h-4 bg-gray-700/30 rounded-full w-64"></div>
	</div>

	<div v-else-if="variant === 'originals'" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
		<div v-for="i in 4" :key="i" class="rounded-2xl bg-gray-800/60 border border-white/[0.04] p-5 animate-pulse min-h-[140px]">
			<div class="h-4 bg-gray-700/50 rounded-full w-8 mb-3"></div>
			<div class="h-5 bg-gray-700/50 rounded-full w-16 mb-2"></div>
			<div class="h-3 bg-gray-700/30 rounded-full w-24"></div>
		</div>
	</div>
</template>
