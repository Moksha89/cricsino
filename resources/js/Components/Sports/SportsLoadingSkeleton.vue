<script setup>
defineProps({
	count: { type: Number, default: 3 },
});
</script>

<template>
	<div class="space-y-3">
		<div
			v-for="i in count"
			:key="i"
			class="bg-surface-light rounded-xl p-4 animate-pulse">
			<div class="flex items-center justify-between mb-3">
				<div class="flex items-center gap-2">
					<div class="w-16 h-3 bg-gray-700 rounded"></div>
					<div class="w-20 h-3 bg-gray-700 rounded"></div>
				</div>
				<div class="w-6 h-6 bg-gray-700 rounded"></div>
			</div>
			<div class="flex items-center justify-between">
				<div class="space-y-2 flex-1">
					<div class="w-32 h-4 bg-gray-700 rounded"></div>
					<div class="w-28 h-4 bg-gray-700 rounded"></div>
				</div>
				<div class="flex gap-2">
					<div class="w-16 h-12 bg-gray-700 rounded-lg"></div>
					<div class="w-16 h-12 bg-gray-700 rounded-lg"></div>
					<div class="w-16 h-12 bg-gray-700 rounded-lg hidden sm:block"></div>
					<div class="w-16 h-12 bg-gray-700 rounded-lg hidden sm:block"></div>
				</div>
			</div>
		</div>
	</div>
</template>
