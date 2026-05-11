<script setup>
import { Loader2 } from "lucide-vue-next";
import CollapseTransition from "@/Components/CollapseTransition.vue";

defineProps({
	title: { type: String, required: true },
	description: { type: String, default: null },
	icon: { type: [Object, Function], default: null },
	processing: { type: Boolean, default: false },
	disabled: { type: Boolean, default: false },
	recentlySuccessful: { type: Boolean, default: false },
	buttonLabel: { type: String, default: "Update" },
	danger: { type: Boolean, default: false },
});

defineEmits(["submit"]);
</script>

<template>
<div class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-6"
	:class="danger ? 'border-red-500/20' : ''">
	<div class="flex items-start gap-3 mb-4">
		<div v-if="icon" class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
			:class="danger ? 'bg-red-500/10' : 'bg-purple-600/10'">
			<component :is="icon" class="w-4.5 h-4.5" :class="danger ? 'text-red-400' : 'text-purple-400'" />
		</div>
		<div>
			<h3 class="text-base font-semibold text-white">{{ title }}</h3>
			<p v-if="description" class="text-sm text-gray-400 mt-0.5">{{ description }}</p>
		</div>
	</div>
	<slot />
	<div class="mt-5">
		<CollapseTransition>
			<p v-show="recentlySuccessful" class="mb-3 text-green-400 text-sm">Saved successfully</p>
		</CollapseTransition>
		<button
			@click="$emit('submit')"
			:disabled="processing || disabled"
			class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 min-h-[44px] disabled:opacity-50"
			:class="danger
				? 'bg-red-600 hover:bg-red-700 text-white'
				: 'bg-purple-600 hover:bg-purple-700 text-white'">
			<Loader2 v-if="processing" class="w-4 h-4 animate-spin" />
			{{ buttonLabel }}
		</button>
	</div>
</div>
</template>
