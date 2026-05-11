<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";

import SportsNavTabs from "@/Components/Sports/SportsNavTabs.vue";
import MatchCard from "@/Components/Sports/MatchCard.vue";
import BetSlip from "@/Components/Sports/BetSlip.vue";
import MobileBetSlipSheet from "@/Components/Sports/MobileBetSlipSheet.vue";
import SportsEmptyState from "@/Components/Sports/SportsEmptyState.vue";
import UserLayout from "@/Layouts/UserLayout.vue";

const props = defineProps({
	league: Object,
	sport: String,
	region: String,
	popular: Array,
	games: Object,
	defaultMarketsCounts: Array,
	defaultMarkets: Object,
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
</script>

<template>
	<Head title="Watchlist" />

	<UserLayout>
		<div class="p-4 sm:p-6 max-w-full">
			<!-- Sports Navigation -->
			<div class="mb-5">
				<SportsNavTabs />
			</div>

			<!-- Page header -->
			<div class="flex items-center gap-3 mb-5">
				<h1 class="text-xl sm:text-2xl font-bold text-white">Watchlist</h1>
				<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
					<path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
				</svg>
				<span v-if="games?.length || games?.data?.length" class="text-sm text-gray-400 ml-auto">
					{{ games?.length ?? games?.data?.length ?? 0 }} watched matches
				</span>
			</div>

			<!-- Match list -->
			<template v-if="Array.isArray(games) ? games.length : games?.data?.length">
				<div class="space-y-3">
					<MatchCard
						v-for="game in (Array.isArray(games) ? games : games.data)"
						:key="game.slug"
						:game="game"
						:defaultMarketsCount="defaultMarketsCounts?.[game.sport]"
						:market="defaultMarkets?.[game.sport]"
						:showBookie="showBookie"
						:showExchange="showExchange" />
				</div>
			</template>

			<!-- Empty state -->
			<SportsEmptyState
				v-else
				title="Your watchlist is empty"
				message="Add matches to your watchlist by clicking the star icon on any match." />
		</div>

		<template #right-sidebar-top>
			<BetSlip />
		</template>
	</UserLayout>
	<MobileBetSlipSheet />
</template>
