<script setup>
import { computed, ref, watch } from "vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import OddsFormat from "@/Components/OddsFormat.vue";

const props = defineProps({
	price: [Number, String],
	amount: [Number, String],
	isLay: { type: Boolean, default: false },
	suspended: { type: Boolean, default: false },
	disabled: { type: Boolean, default: false },
	selected: { type: Boolean, default: false },
	loading: { type: Boolean, default: false },
	blank: { type: Boolean, default: false },
	ask: { type: String, default: null },
});

const emit = defineEmits(["click"]);

const flashing = ref(null);
const prevPrice = ref(props.price);

watch(() => props.price, (newVal, oldVal) => {
	if (newVal && oldVal && newVal !== oldVal) {
		flashing.value = newVal > oldVal ? "up" : "down";
		setTimeout(() => (flashing.value = null), 600);
	}
	prevPrice.value = newVal;
});

const buttonClasses = computed(() => {
	if (props.suspended) return "bg-gray-700/50 cursor-not-allowed opacity-60";
	if (props.disabled) return "bg-gray-700/30 cursor-not-allowed opacity-40";
	if (props.loading) return props.isLay ? "bg-sky-600/70 animate-pulse" : "bg-primary/70 animate-pulse";
	if (props.selected) return props.isLay ? "bg-sky-500 ring-2 ring-sky-400 ring-offset-1 ring-offset-gray-950" : "bg-primary ring-2 ring-primary-light ring-offset-1 ring-offset-gray-950";
	if (flashing.value === "up") return "bg-green-500 animate-pulse";
	if (flashing.value === "down") return "bg-red-500 animate-pulse";
	if (props.isLay) return "bg-sky-600 hover:bg-sky-500 active:bg-sky-400";
	return "bg-primary hover:bg-primary-light active:bg-primary-dark";
});

function handleClick() {
	if (props.suspended || props.disabled || props.loading) return;
	emit("click");
}
</script>

<template>
	<button
		type="button"
		@click="handleClick"
		class="flex flex-col items-center justify-center rounded-lg min-w-[60px] min-h-[44px] px-2 py-1.5 transition-all duration-200 text-white font-inter select-none"
		:class="buttonClasses"
		:disabled="suspended || disabled">
		<!-- Suspended -->
		<template v-if="suspended">
			<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
			</svg>
		</template>
		<!-- Blank -->
		<template v-else-if="blank">
			<span class="text-xs text-gray-500">—</span>
		</template>
		<!-- BID/ASK -->
		<template v-else-if="ask">
			<span class="text-sm font-bold leading-tight">{{ ask }}</span>
		</template>
		<!-- Normal odds -->
		<template v-else>
			<OddsFormat :odds="price * 1" #default="{ odds }">
				<span class="text-sm font-bold leading-tight tabular-nums">{{ odds }}</span>
			</OddsFormat>
			<span v-if="amount" class="text-[10px] text-white/60 leading-tight tabular-nums mt-0.5">
				<MoneyFormat billion :amount="amount * 1" />
			</span>
		</template>
	</button>
</template>
