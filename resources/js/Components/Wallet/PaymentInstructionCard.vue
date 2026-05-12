<script setup>
import { Info } from "lucide-vue-next";

defineProps({
	title: { type: String, default: "Payment Instructions" },
	items: { type: Array, default: () => [] },
});
</script>

<template>
<div class="rounded-xl border border-sky-500/20 bg-sky-500/5 p-4">
	<div class="flex items-center gap-2 mb-3">
		<Info class="w-4 h-4 text-sky-400 flex-shrink-0" />
		<h4 class="text-sm font-semibold text-sky-300">{{ title }}</h4>
	</div>
	<ul class="space-y-2">
		<li
			v-for="(item, i) in items"
			:key="i"
			class="flex items-start gap-2 text-sm text-gray-300">
			<span class="w-5 h-5 rounded-full bg-sky-500/10 text-sky-400 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
				{{ i + 1 }}
			</span>
			<span>{{ item }}</span>
		</li>
	</ul>
	<slot />
</div>
</template>
