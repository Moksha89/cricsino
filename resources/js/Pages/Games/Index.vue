<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";

import SportsNavTabs from "@/Components/Sports/SportsNavTabs.vue";
import MatchCard from "@/Components/Sports/MatchCard.vue";
import BetSlip from "@/Components/Sports/BetSlip.vue";
import MobileBetSlipSheet from "@/Components/Sports/MobileBetSlipSheet.vue";
import SportsEmptyState from "@/Components/Sports/SportsEmptyState.vue";
import Pagination from "@/Components/Pagination.vue";
import { toTitleCase } from "@/Layouts/FontendLayout/useMenu";
import UserLayout from "@/Layouts/UserLayout.vue";

const props = defineProps({
	league: Object,
	sport: String,
	region: String,
	popular: Array,
	games: Object,
	defaultMarketsCount: Number,
	defaultMarket: Object,
	enableExchange: Boolean,
	enableBookie: Boolean,
});

const multiples = computed(() => usePage().props.multiples);
const showBookie = computed(() => {
	if (!props.enableBookie) return false;
	return multiples.value;
});
const showExchange = computed(() => {
	if (!props.enableExchange) return false;
	return !multiples.value;
});

const pageTitle = computed(() => {
	if (props.league?.name) return toTitleCase(props.league.name);
	return toTitleCase(props.sport ?? "Sports") + (props.region ? " " + toTitleCase(props.region) : "");
});
</script>

<template>
	<Head :title="pageTitle" />

	<UserLayout>
		<div class="p-4 sm:p-6 max-w-full">
			<!-- Sports Navigation -->
			<div class="mb-5">
				<SportsNavTabs />
			</div>

			<!-- Page header -->
			<div class="mb-5">
				<h1 class="text-xl sm:text-2xl font-bold text-white">
					{{ pageTitle }}
				</h1>
				<p v-if="games.data?.length" class="text-sm text-gray-400 mt-1">
					{{ games.meta?.total ?? games.data.length }} matches available
				</p>
			</div>

			<!-- Match list -->
			<div v-if="games.data?.length" class="space-y-3">
				<MatchCard
					v-for="game in games.data"
					:key="game.slug"
					:game="game"
					:defaultMarketsCount="defaultMarketsCount"
					:market="defaultMarket"
					:showBookie="showBookie"
					:showExchange="showExchange" />
			</div>

			<!-- Empty state -->
			<SportsEmptyState
				v-else
				:title="'No ' + pageTitle.toLowerCase() + ' matches right now.'"
				message="Check back later or browse other sports and leagues." />

			<!-- Pagination -->
			<Pagination v-if="games.meta" :meta="games.meta" class="mt-6" />
		</div>

		<!-- Right sidebar: Bet Slip -->
		<template #right-sidebar-top>
			<BetSlip />
		</template>
	</UserLayout>
	<MobileBetSlipSheet />
</template>
