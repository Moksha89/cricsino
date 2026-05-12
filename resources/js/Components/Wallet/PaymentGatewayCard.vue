<script setup>
defineProps({
	gateway: { type: Object, required: true },
	selected: { type: Boolean, default: false },
});
defineEmits(["select"]);
</script>

<template>
<button
	@click="$emit('select', gateway.value)"
	class="flex items-center gap-3 p-4 rounded-xl border transition-all duration-200 text-left w-full"
	:class="selected
		? 'border-purple-500 bg-purple-500/10 ring-1 ring-purple-500/50'
		: 'border-white/10 bg-white/[0.03] hover:bg-white/[0.06] hover:border-white/20'">
	<img
		v-if="gateway.img"
		:src="gateway.img"
		:alt="gateway.label"
		class="w-10 h-10 rounded-lg object-contain bg-white/10 p-1" />
	<div v-else class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
		<span class="text-purple-400 font-bold text-sm">{{ gateway.label?.charAt(0) }}</span>
	</div>
	<div class="min-w-0 flex-1">
		<p class="text-sm font-semibold text-white truncate">{{ gateway.label }}</p>
		<p v-if="gateway.subtitle" class="text-xs text-gray-400 truncate">{{ gateway.subtitle }}</p>
	</div>
	<div v-if="selected" class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0">
		<svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
			<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
		</svg>
	</div>
</button>
</template>
