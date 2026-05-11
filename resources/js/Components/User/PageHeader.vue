<script setup>
defineProps({
	title: { type: String, required: true },
	subtitle: { type: String, default: null },
});
</script>

<template>
<div class="mb-6">
	<h1 class="text-2xl sm:text-3xl font-bold text-white font-inter">{{ title }}</h1>
	<p v-if="subtitle" class="text-gray-400 text-sm mt-1">{{ subtitle }}</p>
	<slot />
</div>
</template>
