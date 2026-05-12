<script setup>
import { ShieldCheck, ShieldAlert, ShieldX } from "lucide-vue-next";

defineProps({
	enabled: { type: Boolean, default: false },
	label: { type: String, default: null },
});
</script>

<template>
<span
	class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
	:class="enabled
		? 'bg-green-500/10 text-green-400 border border-green-500/20'
		: 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20'">
	<ShieldCheck v-if="enabled" class="w-3.5 h-3.5" />
	<ShieldAlert v-else class="w-3.5 h-3.5" />
	{{ label || (enabled ? 'Enabled' : 'Not Enabled') }}
</span>
</template>
