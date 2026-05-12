<script setup>
defineProps({
	title: { type: String, default: 'No data found' },
	message: { type: String, default: null },
	icon: { type: String, default: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' },
});
</script>

<template>
<div class="text-center py-12">
	<svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
		<path stroke-linecap="round" stroke-linejoin="round" :d="icon"/>
	</svg>
	<h3 class="mt-3 text-sm font-semibold text-gray-300">{{ title }}</h3>
	<p v-if="message" class="mt-1 text-sm text-gray-500">{{ message }}</p>
	<div v-if="$slots.action" class="mt-4">
		<slot name="action" />
	</div>
</div>
</template>
