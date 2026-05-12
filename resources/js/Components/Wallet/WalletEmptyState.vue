<script setup>
import { Link } from "@inertiajs/vue3";
import { Wallet, ArrowRight } from "lucide-vue-next";

defineProps({
	title: { type: String, default: "No data found" },
	description: { type: String, default: "" },
	actionLabel: { type: String, default: null },
	actionRoute: { type: String, default: null },
	icon: { type: Object, default: null },
});
</script>

<template>
<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
	<div class="w-16 h-16 rounded-2xl bg-purple-500/10 flex items-center justify-center mb-4">
		<component :is="icon || Wallet" class="w-8 h-8 text-purple-400" />
	</div>
	<h3 class="text-lg font-semibold text-white mb-1">{{ title }}</h3>
	<p v-if="description" class="text-sm text-gray-400 max-w-sm mb-6">{{ description }}</p>
	<Link
		v-if="actionRoute && actionLabel"
		:href="actionRoute"
		class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold transition-colors">
		{{ actionLabel }}
		<ArrowRight class="w-4 h-4" />
	</Link>
</div>
</template>
