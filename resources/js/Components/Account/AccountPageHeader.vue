<script setup>
defineProps({
	title: { type: String, required: true },
	subtitle: { type: String, default: null },
	icon: { type: Object, default: null },
});
</script>

<template>
<div class="mb-6">
	<div class="flex items-center gap-3">
		<div v-if="icon" class="w-10 h-10 rounded-xl bg-purple-600/10 flex items-center justify-center flex-shrink-0">
			<component :is="icon" class="w-5 h-5 text-purple-400" />
		</div>
		<div>
			<h1 class="text-2xl sm:text-3xl font-bold text-white font-inter">{{ title }}</h1>
			<p v-if="subtitle" class="text-gray-400 text-sm mt-0.5">{{ subtitle }}</p>
		</div>
	</div>
	<slot />
</div>
</template>
