<script setup>
defineProps({
	label: { type: String, required: true },
	value: { type: [String, Number], default: null },
});
</script>

<template>
<div class="space-y-1">
	<p class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ label }}</p>
	<div class="text-sm font-semibold text-white">
		<slot>{{ value ?? '—' }}</slot>
	</div>
</div>
</template>
