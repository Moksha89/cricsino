<script setup>
import { Trophy, XCircle } from "lucide-vue-next";

defineProps({
	won: { type: Boolean, default: false },
	amount: { type: [Number, String], default: 0 },
	multiplier: { type: [Number, String], default: null },
	label: { type: String, default: null },
});
</script>

<template>
	<div
		:class="[
			'flex items-center gap-3 p-3.5 rounded-xl border transition-all',
			won
				? 'bg-emerald-600/10 border-emerald-500/20 text-emerald-400'
				: 'bg-red-600/10 border-red-500/20 text-red-400'
		]">
		<div :class="['w-9 h-9 rounded-lg flex items-center justify-center shrink-0', won ? 'bg-emerald-600/20' : 'bg-red-600/20']">
			<component :is="won ? Trophy : XCircle" class="w-4.5 h-4.5" />
		</div>
		<div class="min-w-0 flex-1">
			<p class="text-sm font-bold">
				{{ won ? 'Won' : 'Lost' }}
				<span v-if="won" class="text-white"> {{ '\u20B9' }}{{ amount }}</span>
			</p>
			<p v-if="label" class="text-xs opacity-70 truncate">{{ label }}</p>
		</div>
		<span v-if="multiplier" :class="['text-sm font-bold px-2 py-0.5 rounded-lg', won ? 'bg-emerald-600/20' : 'bg-red-600/20']">
			{{ multiplier }}x
		</span>
	</div>
</template>
