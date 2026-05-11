<script setup>
import { Link } from "@inertiajs/vue3";
import { Inbox } from "lucide-vue-next";

defineProps({
	title: { type: String, default: "Nothing here yet" },
	description: { type: String, default: "Check back later." },
	actionLabel: { type: String, default: null },
	actionRoute: { type: String, default: null },
	icon: { type: [Object, Function], default: null },
});
</script>

<template>
<div class="text-center py-12 lg:py-16">
	<div class="w-14 h-14 rounded-2xl bg-purple-600/10 flex items-center justify-center mx-auto mb-4">
		<component :is="icon || Inbox" class="w-7 h-7 text-purple-400" />
	</div>
	<h3 class="text-base font-semibold text-white mb-1">{{ title }}</h3>
	<p class="text-sm text-gray-500 max-w-sm mx-auto">{{ description }}</p>
	<Link
		v-if="actionLabel && actionRoute"
		:href="actionRoute"
		class="inline-flex items-center gap-2 mt-5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition min-h-[44px]">
		{{ actionLabel }}
	</Link>
	<slot />
</div>
</template>
