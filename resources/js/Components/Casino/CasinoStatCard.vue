<script setup>
defineProps({
	label: { type: String, required: true },
	value: { type: [String, Number], required: true },
	icon: { type: Object, default: null },
	color: { type: String, default: "purple" },
});

const colorClasses = {
	purple: "bg-purple-600/15 text-purple-400",
	green: "bg-emerald-600/15 text-emerald-400",
	blue: "bg-blue-600/15 text-blue-400",
	red: "bg-red-600/15 text-red-400",
	yellow: "bg-yellow-600/15 text-yellow-400",
};
</script>

<template>
	<div class="bg-gray-800/60 rounded-xl p-4 border border-white/[0.06]">
		<div class="flex items-center gap-3">
			<div v-if="icon" :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', colorClasses[color] || colorClasses.purple]">
				<component :is="icon" class="w-5 h-5" />
			</div>
			<div class="min-w-0">
				<p class="text-gray-500 text-xs font-medium truncate">{{ label }}</p>
				<p class="text-white text-lg font-bold truncate">{{ value }}</p>
			</div>
		</div>
	</div>
</template>
