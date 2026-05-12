<script setup>
import { computed } from "vue";
import { Check, Clock, AlertTriangle, Loader, X, RefreshCcw } from "lucide-vue-next";

const props = defineProps({
	status: { type: String, required: true },
});

const statusConfig = computed(() => {
	const s = props.status?.toLowerCase();
	if (["approved", "complete", "confirmed", "success"].includes(s)) {
		return { icon: Check, label: s, classes: "bg-green-500/10 text-green-400 border-green-500/20" };
	}
	if (["pending", "review"].includes(s)) {
		return { icon: Clock, label: s, classes: "bg-amber-500/10 text-amber-400 border-amber-500/20" };
	}
	if (["processing"].includes(s)) {
		return { icon: Loader, label: s, classes: "bg-sky-500/10 text-sky-400 border-sky-500/20" };
	}
	if (["rejected", "failed", "cancelled"].includes(s)) {
		return { icon: X, label: s, classes: "bg-red-500/10 text-red-400 border-red-500/20" };
	}
	if (["reversed", "refunded"].includes(s)) {
		return { icon: RefreshCcw, label: s, classes: "bg-orange-500/10 text-orange-400 border-orange-500/20" };
	}
	return { icon: AlertTriangle, label: s, classes: "bg-gray-500/10 text-gray-400 border-gray-500/20" };
});
</script>

<template>
<span
	class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold uppercase border"
	:class="statusConfig.classes">
	<component :is="statusConfig.icon" class="w-3.5 h-3.5" />
	{{ statusConfig.label }}
</span>
</template>
