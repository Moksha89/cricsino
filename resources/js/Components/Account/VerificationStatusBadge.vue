<script setup>
import { CheckCircle, Clock, XCircle, MinusCircle } from "lucide-vue-next";

const props = defineProps({
	status: { type: String, default: "unverified" },
});

const config = {
	complete: { icon: CheckCircle, text: "Verified", classes: "bg-green-500/10 text-green-400 border-green-500/20" },
	verified: { icon: CheckCircle, text: "Verified", classes: "bg-green-500/10 text-green-400 border-green-500/20" },
	pending: { icon: Clock, text: "Pending", classes: "bg-yellow-500/10 text-yellow-400 border-yellow-500/20" },
	unverified: { icon: XCircle, text: "Unverified", classes: "bg-red-500/10 text-red-400 border-red-500/20" },
	not_required: { icon: MinusCircle, text: "Not Required", classes: "bg-gray-500/10 text-gray-400 border-gray-500/20" },
};
</script>

<template>
<span
	class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
	:class="(config[status] || config.unverified).classes">
	<component :is="(config[status] || config.unverified).icon" class="w-3.5 h-3.5" />
	{{ (config[status] || config.unverified).text }}
</span>
</template>
