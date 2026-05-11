<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
	modelValue: { type: [Number, String], default: 0 },
	label: { type: String, default: "" },
	error: { type: String, default: null },
	min: { type: Number, default: null },
	max: { type: Number, default: null },
	disabled: { type: Boolean, default: false },
	help: { type: String, default: null },
});

const emit = defineEmits(["update:modelValue"]);

const page = usePage();
const currencyCode = computed(() => page.props.currency?.currency_code ?? 'INR');
const currencySymbol = computed(() => page.props.currency?.currency_symbol ?? '₹');

const quickAmounts = [100, 500, 1000, 5000, 10000];
</script>

<template>
<div class="space-y-3">
	<label v-if="label" class="block text-sm font-medium text-gray-300">{{ label }}</label>
	<div class="relative">
		<span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-purple-400">
			{{ currencySymbol }}
		</span>
		<input
			type="number"
			:value="modelValue"
			@input="emit('update:modelValue', $event.target.value)"
			:disabled="disabled"
			:min="min"
			:max="max"
			class="w-full pl-10 pr-16 py-3.5 bg-white/[0.05] border border-white/10 rounded-xl text-white text-lg font-bold
				   placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500/50
				   disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
			placeholder="0.00" />
		<span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">
			{{ currencyCode }}
		</span>
	</div>

	<!-- Quick amount buttons -->
	<div class="flex flex-wrap gap-2">
		<button
			v-for="amt in quickAmounts"
			:key="amt"
			@click="emit('update:modelValue', amt)"
			:disabled="disabled"
			class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200
				   bg-white/[0.05] border border-white/10 text-gray-300
				   hover:bg-purple-500/10 hover:border-purple-500/30 hover:text-purple-300
				   disabled:opacity-50 disabled:cursor-not-allowed">
			{{ currencySymbol }}{{ amt.toLocaleString() }}
		</button>
	</div>

	<!-- Min/Max info -->
	<div v-if="min || max" class="flex gap-3 text-xs text-gray-400">
		<span v-if="min">Min: {{ currencySymbol }}{{ min.toLocaleString() }}</span>
		<span v-if="max">Max: {{ currencySymbol }}{{ max.toLocaleString() }}</span>
	</div>

	<p v-if="help" class="text-xs text-gray-400">{{ help }}</p>
	<p v-if="error" class="text-xs text-red-400 font-medium">{{ error }}</p>
</div>
</template>
