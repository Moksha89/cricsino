<script setup>
import { Head } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";
import EmptyState from "@/Components/User/EmptyState.vue";
import GameRow from "@/Components/Cards/GameRow.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
	games: Object,
});
</script>

<template>
	<Head title="In-Play" />
	<UserLayout>
		<div class="p-4 sm:p-6">
			<PageHeader title="In-Play" subtitle="Live matches happening right now">
				<div class="flex items-center gap-2 mt-2">
					<span class="relative flex h-3 w-3">
						<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
						<span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
					</span>
					<span class="text-sm text-green-400">Live</span>
				</div>
			</PageHeader>

			<EmptyState
				v-if="!games.data?.length"
				title="No in-play matches right now"
				message="Check back later for live matches or browse upcoming events."
				icon="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />

			<template v-else>
				<div class="grid gap-3">
					<GameRow
						v-for="game in games.data"
						:key="game.slug"
						:game="game" />
				</div>
				<Pagination :meta="games.meta" class="mt-4" />
			</template>
		</div>
	</UserLayout>
</template>
