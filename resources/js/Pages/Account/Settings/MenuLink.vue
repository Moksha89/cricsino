<script setup>
	import { Link } from "@inertiajs/vue3";

	defineProps({
		active: Boolean,
		btn: Boolean,
	});
</script>
<template>
	<button
		v-if="btn"
		:class="
			active
				? 'text-white bg-primary/20 border-primary'
				: 'text-gray-400 border-transparent hover:text-white hover:border-primary/50'
		"
		class="font-semibold font-inter border-b-2 inline-block text-xs tracking-wide py-2 px-4 uppercase transition-colors duration-200">
		<slot />
	</button>
	<Link
		v-else
		:class="
			active
				? 'text-white bg-primary/20 border-primary'
				: 'text-gray-400 border-transparent hover:text-white hover:border-primary/50'
		"
		class="font-semibold font-inter border-b-2 inline-block text-xs tracking-wide py-2 px-4 uppercase transition-colors duration-200 rounded-t-lg">
		<slot />
	</Link>
</template>
