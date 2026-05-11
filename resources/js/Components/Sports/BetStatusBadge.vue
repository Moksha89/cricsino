<script setup>
import { computed } from "vue";

const props = defineProps({
	status: String,
});

const config = computed(() => {
	const s = (props.status ?? "").toLowerCase();
	if (["won", "win", "settled_won"].includes(s)) return { label: "Won", classes: "bg-green-500/20 text-green-400 border-green-500/30" };
	if (["lost", "lose", "settled_lost"].includes(s)) return { label: "Lost", classes: "bg-red-500/20 text-red-400 border-red-500/30" };
	if (["pending", "open", "active"].includes(s)) return { label: "Pending", classes: "bg-primary/20 text-primary-light border-primary/30" };
	if (["matched"].includes(s)) return { label: "Matched", classes: "bg-sky-500/20 text-sky-400 border-sky-500/30" };
	if (["partial", "partially_matched"].includes(s)) return { label: "Partial", classes: "bg-yellow-500/20 text-yellow-400 border-yellow-500/30" };
	if (["unmatched"].includes(s)) return { label: "Unmatched", classes: "bg-gray-500/20 text-gray-400 border-gray-500/30" };
	if (["cancelled", "void", "voided"].includes(s)) return { label: "Void", classes: "bg-gray-500/20 text-gray-500 border-gray-500/30" };
	if (["refund", "refunded"].includes(s)) return { label: "Refund", classes: "bg-blue-500/20 text-blue-400 border-blue-500/30" };
	if (["trade_out", "traded_out"].includes(s)) return { label: "Traded Out", classes: "bg-purple-500/20 text-purple-400 border-purple-500/30" };
	return { label: s || "Unknown", classes: "bg-gray-600/20 text-gray-400 border-gray-600/30" };
});
</script>

<template>
	<span
		class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider border"
		:class="config.classes">
		{{ config.label }}
	</span>
</template>
