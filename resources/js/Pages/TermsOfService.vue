<script setup>
	import { Head } from "@inertiajs/vue3";
	import VueMarkdown from "vue-markdown-render";

	import UserLayout from "@/Layouts/UserLayout.vue";

	defineProps({
		terms: String,
	});
</script>

<template>
	<Head title="Terms of service" />
	<UserLayout>
		<main class="p-4 sm:p-6">
			<div class="mark w-full bg-gray-800/50 rounded-2xl border border-white/[0.06] p-6 sm:p-10">
				<VueMarkdown :source="terms" />
			</div>
		</main>
	</UserLayout>
</template>

<style>
	.mark li {
		@apply text-gray-500 dark:text-gray-400;
	}
	.mark p {
		@apply text-gray-300;
	}
	.mark li > p {
		@apply text-gray-800 dark:text-gray-100 font-semibold my-6 text-xl;
	}
	.mark h1 {
		@apply text-xl md:text-2xl font-bold text-gray-200 mb-4;
	}
</style>
