<script setup>
import { AlertTriangle } from "lucide-vue-next";

defineProps({
	title: { type: String, default: "Danger Zone" },
	description: { type: String, default: "This action is irreversible." },
});
</script>

<template>
<div class="bg-red-950/20 rounded-2xl border border-red-500/20 p-4 sm:p-6">
	<div class="flex items-start gap-3 mb-4">
		<div class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0">
			<AlertTriangle class="w-4.5 h-4.5 text-red-400" />
		</div>
		<div>
			<h3 class="text-base font-semibold text-red-400">{{ title }}</h3>
			<p class="text-sm text-gray-400 mt-0.5">{{ description }}</p>
		</div>
	</div>
	<slot />
</div>
</template>
