<script setup>
defineProps({
	name: { type: String, required: true },
	icon: { type: String, default: null },
	color: { type: String, default: "text-gray-400" },
	bgColor: { type: String, default: "bg-gray-500/10 border-gray-500/20" },
	connected: { type: Boolean, default: false },
	processing: { type: Boolean, default: false },
});

defineEmits(["connect", "disconnect"]);
</script>

<template>
<div class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-5 flex items-center justify-between transition-colors hover:border-purple-500/10">
	<div class="flex items-center gap-4">
		<div class="w-11 h-11 rounded-xl flex items-center justify-center border" :class="bgColor">
			<svg v-if="icon" class="w-5 h-5" :class="color" viewBox="0 0 24 24" fill="currentColor">
				<path :d="icon" />
			</svg>
		</div>
		<div>
			<div class="text-white font-medium">{{ name }}</div>
			<div class="text-xs mt-0.5" :class="connected ? 'text-green-400' : 'text-gray-500'">
				{{ connected ? 'Connected' : 'Not connected' }}
			</div>
		</div>
	</div>
	<button
		v-if="connected"
		@click="$emit('disconnect')"
		:disabled="processing"
		class="px-4 py-2.5 text-sm rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors min-h-[44px] disabled:opacity-50">
		Disconnect
	</button>
	<button
		v-else
		@click="$emit('connect')"
		:disabled="processing"
		class="px-4 py-2.5 text-sm rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold transition-colors min-h-[44px] disabled:opacity-50">
		Connect
	</button>
</div>
</template>
