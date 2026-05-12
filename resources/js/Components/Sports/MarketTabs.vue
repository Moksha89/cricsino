<script setup>
const props = defineProps({
	modelValue: { type: String, default: "all" },
	tabs: { type: Array, default: () => ["all", "popular", "winner", "teams", "totals", "handicap", "half"] },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
	<nav class="flex items-center gap-1 overflow-x-auto scrollbar-hide pb-1 -mx-1 px-1">
		<button
			v-for="tab in tabs"
			:key="tab"
			@click="emit('update:modelValue', tab)"
			type="button"
			class="px-3 py-2 rounded-lg text-xs font-semibold capitalize whitespace-nowrap transition-all duration-200 min-h-[36px]"
			:class="modelValue === tab
				? 'bg-primary text-white shadow-lg shadow-primary/25'
				: 'bg-surface-light text-gray-400 hover:text-white hover:bg-white/[0.08]'
			">
			{{ tab }}
		</button>
	</nav>
</template>
