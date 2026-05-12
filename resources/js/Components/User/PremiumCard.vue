<script setup>
defineProps({
	noPadding: { type: Boolean, default: false },
});
</script>

<template>
<div
	class="bg-gray-800/50 rounded-2xl border border-white/[0.06] backdrop-blur-sm"
	:class="noPadding ? '' : 'p-4 sm:p-6'">
	<slot />
</div>
</template>
