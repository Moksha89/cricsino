<script setup>
defineProps({
	title: { type: String, default: null },
	description: { type: String, default: null },
	icon: { type: [Object, Function], default: null },
	noPadding: { type: Boolean, default: false },
});
</script>

<template>
<div class="bg-gray-800/50 rounded-2xl border border-white/[0.06] overflow-hidden">
	<div v-if="title || $slots.header" class="px-4 sm:px-6 pt-4 sm:pt-6 pb-2">
		<div v-if="$slots.header"><slot name="header" /></div>
		<template v-else>
			<div class="flex items-center gap-2.5">
				<component v-if="icon" :is="icon" class="w-5 h-5 text-purple-400 flex-shrink-0" />
				<h2 class="text-lg font-semibold text-white">{{ title }}</h2>
			</div>
			<p v-if="description" class="text-sm text-gray-400 mt-1" :class="icon ? 'ml-[30px]' : ''">{{ description }}</p>
		</template>
	</div>
	<div :class="noPadding ? '' : 'px-4 sm:px-6 pb-4 sm:pb-6 pt-2'">
		<slot />
	</div>
</div>
</template>
