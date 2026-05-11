<script setup>
import { Filter } from "lucide-vue-next";

defineProps({
	providers: { type: Object, default: () => ({}) },
	selectedProvider: { type: String, default: "" },
});

const emit = defineEmits(["select"]);
</script>

<template>
	<div v-if="providers && Object.keys(providers).length > 0" class="relative">
		<Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" />
		<select
			:value="selectedProvider"
			@change="emit('select', $event.target.value)"
			class="w-full sm:w-auto bg-gray-800/80 text-white text-sm rounded-xl pl-9 pr-8 py-2.5 border border-white/[0.06] focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/30 outline-none appearance-none cursor-pointer hover:border-white/10 transition">
			<option value="">All Providers</option>
			<option v-for="(label, key) in providers" :key="key" :value="key">{{ label }}</option>
		</select>
	</div>
</template>
