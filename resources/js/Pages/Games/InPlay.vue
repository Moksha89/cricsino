<script setup>
import { Head } from "@inertiajs/vue3";

import SportsNavTabs from "@/Components/Sports/SportsNavTabs.vue";
import MatchCard from "@/Components/Sports/MatchCard.vue";
import BetSlip from "@/Components/Sports/BetSlip.vue";
import MobileBetSlipSheet from "@/Components/Sports/MobileBetSlipSheet.vue";
import SportsEmptyState from "@/Components/Sports/SportsEmptyState.vue";
import Pagination from "@/Components/Pagination.vue";
import UserLayout from "@/Layouts/UserLayout.vue";

const props = defineProps({
	games: Object,
});
</script>

<template>
	<Head title="In-Play" />
	<UserLayout>
		<div class="p-4 sm:p-6 max-w-full">
			<!-- Sports Navigation -->
			<div class="mb-5">
				<SportsNavTabs />
			</div>

			<!-- Page header -->
			<div class="flex items-center gap-3 mb-5">
				<h1 class="text-xl sm:text-2xl font-bold text-white">In-Play</h1>
				<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-500/20 border border-green-500/30">
					<span class="relative flex h-2 w-2">
						<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
						<span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
					</span>
					<span class="text-[11px] font-bold text-green-400 uppercase tracking-wider">Live</span>
				</span>
				<span v-if="games.data?.length" class="text-sm text-gray-400 ml-auto">
					{{ games.meta?.total ?? games.data.length }} live matches
				</span>
			</div>

			<!-- Match list -->
			<div v-if="games.data?.length" class="space-y-3">
				<MatchCard
					v-for="game in games.data"
					:key="game.slug"
					:game="game" />
			</div>

			<!-- Empty state -->
			<SportsEmptyState
				v-else
				title="No in-play matches right now"
				message="Check back later for live matches or browse upcoming events." />

			<!-- Pagination -->
			<Pagination v-if="games.meta" :meta="games.meta" class="mt-6" />
		</div>

		<template #right-sidebar-top>
			<BetSlip />
		</template>
	</UserLayout>
	<MobileBetSlipSheet />
</template>
