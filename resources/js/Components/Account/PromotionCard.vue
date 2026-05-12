<script setup>
import { Tag, Calendar } from "lucide-vue-next";

defineProps({
	title: { type: String, required: true },
	description: { type: String, default: null },
	validUntil: { type: String, default: null },
	code: { type: String, default: null },
	active: { type: Boolean, default: true },
});
</script>

<template>
<div class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-5 transition-colors hover:border-purple-500/20">
	<div class="flex items-start justify-between gap-3">
		<div class="flex items-start gap-3 min-w-0">
			<div class="w-10 h-10 rounded-xl bg-purple-600/10 flex items-center justify-center flex-shrink-0">
				<Tag class="w-5 h-5 text-purple-400" />
			</div>
			<div class="min-w-0">
				<h3 class="text-white font-semibold truncate">{{ title }}</h3>
				<p v-if="description" class="text-sm text-gray-400 mt-0.5 line-clamp-2">{{ description }}</p>
			</div>
		</div>
		<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0"
			:class="active
				? 'bg-green-500/10 text-green-400 border border-green-500/20'
				: 'bg-gray-500/10 text-gray-400 border border-gray-500/20'">
			{{ active ? 'Active' : 'Expired' }}
		</span>
	</div>
	<div v-if="code || validUntil" class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500">
		<span v-if="code" class="inline-flex items-center gap-1 bg-gray-900/50 px-2.5 py-1 rounded-lg border border-white/[0.06]">
			Code: <span class="text-gray-300 font-mono">{{ code }}</span>
		</span>
		<span v-if="validUntil" class="inline-flex items-center gap-1">
			<Calendar class="w-3.5 h-3.5" /> {{ validUntil }}
		</span>
	</div>
</div>
</template>
