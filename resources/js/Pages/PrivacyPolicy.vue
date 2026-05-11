<script setup>
	import { Head } from "@inertiajs/vue3";
	import VueMarkdown from "vue-markdown-render";

	import UserLayout from "@/Layouts/UserLayout.vue";
	defineProps({
		policy: String,
	});
</script>

<template>
	<Head title="Privacy Policy" />
	<UserLayout>
		<main class="p-4 sm:p-6">
			<div class="bg-gray-800/50 border border-white/[0.06] rounded-2xl p-6 sm:p-10">
				<div class="mark">
					<VueMarkdown :source="policy" />
				</div>
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
	.mark h2 {
		@apply text-gray-800 dark:text-gray-100 font-semibold my-6 text-xl;
	}
	.mark h1 {
		@apply text-xl md:text-2xl font-bold text-gray-200 mb-4;
	}
</style>
