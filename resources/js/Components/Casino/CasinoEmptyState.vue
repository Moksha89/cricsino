<script setup>
import { Link } from "@inertiajs/vue3";
import { Gamepad2 } from "lucide-vue-next";

defineProps({
	title: { type: String, default: "No games available" },
	description: { type: String, default: "Check back soon for new games." },
	actionLabel: { type: String, default: null },
	actionRoute: { type: String, default: null },
	icon: { type: Object, default: null },
});
</script>

<template>
	<div class="text-center py-16 lg:py-20">
		<div class="w-16 h-16 rounded-2xl bg-purple-600/10 flex items-center justify-center mx-auto mb-4">
			<component :is="icon || Gamepad2" class="w-8 h-8 text-purple-400" />
		</div>
		<h3 class="text-lg font-semibold text-white mb-1">{{ title }}</h3>
		<p class="text-sm text-gray-500 max-w-sm mx-auto">{{ description }}</p>
		<Link
			v-if="actionLabel && actionRoute"
			:href="actionRoute"
			class="inline-flex items-center gap-2 mt-5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
			{{ actionLabel }}
		</Link>
	</div>
</template>
