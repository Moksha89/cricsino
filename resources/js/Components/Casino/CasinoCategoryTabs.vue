<script setup>
import { Flame, Gem, MonitorPlay, CircleDot, LayoutGrid } from "lucide-vue-next";

const props = defineProps({
	categories: { type: Object, default: () => ({}) },
	selectedCategory: { type: String, default: "" },
});

const emit = defineEmits(["select"]);

const defaultTabs = [
	{ id: "", label: "All Games", icon: Flame },
	{ id: "originals", label: "Originals", icon: Gem },
	{ id: "live_casino", label: "Live", icon: MonitorPlay },
	{ id: "slots", label: "Slots", icon: CircleDot },
	{ id: "table_games", label: "Tables", icon: LayoutGrid },
];
</script>

<template>
	<div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 -mx-1 px-1">
		<button
			v-for="tab in defaultTabs"
			:key="tab.id"
			@click="emit('select', tab.id)"
			:class="[
				'flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap transition-all duration-200 shrink-0',
				selectedCategory === tab.id
					? 'bg-purple-600 text-white shadow-lg shadow-purple-600/25'
					: 'bg-gray-800/80 text-gray-400 hover:text-white hover:bg-gray-700/80 border border-white/[0.06]'
			]">
			<component :is="tab.icon" class="w-4 h-4" />
			{{ tab.label }}
		</button>
		<button
			v-for="(label, key) in categories"
			:key="key"
			@click="emit('select', key)"
			:class="[
				'px-4 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap transition-all duration-200 shrink-0',
				selectedCategory === key
					? 'bg-purple-600 text-white shadow-lg shadow-purple-600/25'
					: 'bg-gray-800/80 text-gray-400 hover:text-white hover:bg-gray-700/80 border border-white/[0.06]'
			]">
			{{ label }}
		</button>
	</div>
</template>
