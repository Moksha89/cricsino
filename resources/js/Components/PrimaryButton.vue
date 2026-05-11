<script setup>
	import { computed } from "vue";

	import { Link } from "@inertiajs/vue3";

	const props = defineProps({
		primary: Boolean,
		default: Boolean,
		secondary: Boolean,
		info: Boolean,
		warning: Boolean,
		error: Boolean,
		success: Boolean,
		rounded: Boolean,
		link: Boolean,
		url: Boolean,
		external: Boolean,
	});
	const colors = computed(() => {
		if (props.default)
			return "bg-gray-700 border-gray-600 text-gray-200 hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-600/80";
		if (props.primary)
			return "bg-primary border-primary text-white hover:bg-primary-dark focus:bg-primary-dark active:bg-primary-dark/90 shadow-lg shadow-primary/20";
		if (props.secondary)
			return "bg-gray-800 text-white border border-gray-600 focus:outline-none hover:bg-gray-700 focus:ring-2 focus:ring-primary/30";
		if (props.success)
			return "bg-success border-success text-white hover:bg-success/80 focus:bg-success/80 active:bg-success/70";
		if (props.warning)
			return "bg-warning border-warning text-white hover:bg-warning/80 focus:bg-warning/80 active:bg-warning/70";
		if (props.error)
			return "bg-error border-error text-white hover:bg-error/80 focus:bg-error/80 active:bg-error/70";
		return "bg-primary border-primary text-white hover:bg-primary-dark focus:bg-primary-dark active:bg-primary-dark/90";
	});
</script>
<template>
	<Link
		v-if="link"
		:class="[rounded ? 'rounded-full' : '', colors]"
		class="btn border font-semibold disabled:opacity-70 disabled:pointer-events-none">
		<slot />
	</Link>
	<a
		v-else-if="url"
		v-bind="
			external
				? { target: '_blank', rel: 'noopener noreferrer nofollow' }
				: {}
		"
		:class="[rounded ? 'rounded-full' : '', colors]"
		class="btn border font-semibold disabled:opacity-70 disabled:pointer-events-none">
		<slot />
	</a>
	<button
		v-else
		:class="[rounded ? 'rounded-full' : '', colors]"
		class="btn border font-semibold disabled:opacity-70 disabled:pointer-events-none">
		<slot />
	</button>
</template>
