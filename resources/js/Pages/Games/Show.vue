<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";

import SportsNavTabs from "@/Components/Sports/SportsNavTabs.vue";
import MarketCard from "@/Components/Sports/MarketCard.vue";
import MarketTabs from "@/Components/Sports/MarketTabs.vue";
import BetSlip from "@/Components/Sports/BetSlip.vue";
import MobileBetSlipSheet from "@/Components/Sports/MobileBetSlipSheet.vue";
import SportsEmptyState from "@/Components/Sports/SportsEmptyState.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import ToggleWatchList from "@/Components/ToggleWatchList.vue";
import EventCard from "@/Components/Cards/EventCard.vue";
import UserLayout from "@/Layouts/UserLayout.vue";

const props = defineProps({
	game: Object,
	markets: Array,
	popular: Array,
	overunders: Object,
	handicaps: Object,
	asianhandicaps: Object,
	categories: Object,
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

const filter = ref("all");
const filters = ["all", "popular", "winner", "teams", "totals", "handicap", "half"];

const filteredMarkets = computed(() => {
	if (filter.value === "all") return props.markets;
	if (filter.value === "popular")
		return props.markets.slice().sort((a, b) => parseFloat(b.traded ?? 0) - parseFloat(a.traded ?? 0));
	return props.markets.filter((m) => m.category === filter.value);
});

const game = ref(props.game);
const listener = ref();

onMounted(() => {
	if (props.game?.uuid)
		listener.value = window.Echo.channel(props.game?.uuid).listen("GameUpdated", (event) => { game.value = event; });
});
onUnmounted(() => { listener.value?.stopListening("GameUpdated"); });

const isLive = computed(() =>
	(game.value.hasStarted && !(game.value.hasEnded || game.value.stateEnded)) ||
	game.value.state === "in_play"
);

const bettingEnded = computed(() =>
	game.value.stateCancelled || game.value.stateEnded || game.value.stateFinished ||
	game.value.closed || game.value.hasEnded ||
	["finished", "postponed", "cancelled", "abandoned", "not_played"].includes(game.value.state)
);
</script>

<template>
	<Head :title="game.name ?? 'Match'" />

	<UserLayout>
		<div class="p-4 sm:p-6 max-w-full">
			<!-- Sports Navigation -->
			<div class="mb-5">
				<SportsNavTabs />
			</div>

			<!-- Breadcrumb -->
			<nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4">
				<Link href="/" class="hover:text-white transition-colors">Home</Link>
				<span>/</span>
				<Link
					v-if="game.league"
					:href="route('sports.index', { sport: game.sport, region: game.league?.slug })"
					class="hover:text-white transition-colors">
					{{ game.league.name }}
				</Link>
				<span v-if="game.league">/</span>
				<span class="text-gray-400">{{ game.name }}</span>
			</nav>

			<!-- Match Header Card -->
			<div class="bg-surface-light rounded-xl border border-white/[0.06] p-4 sm:p-6 mb-6">
				<div class="flex items-start justify-between">
					<div class="flex-1 min-w-0">
						<!-- Teams -->
						<div class="flex items-center gap-4 mb-3">
							<div class="flex-1 min-w-0">
								<div class="flex items-center justify-between mb-2">
									<h1 class="text-lg sm:text-2xl font-bold text-white truncate">
										{{ game.homeTeam?.name }}
									</h1>
									<span v-if="game.scores?.length || isLive" class="text-xl sm:text-2xl font-bold tabular-nums ml-3"
										:class="game.homeScore > game.awayScore ? 'text-green-400' : game.homeScore < game.awayScore ? 'text-red-400' : 'text-white'">
										{{ game.homeScore ?? 0 }}
									</span>
								</div>
								<div class="flex items-center justify-between">
									<h1 class="text-lg sm:text-2xl font-bold text-white truncate">
										{{ game.awayTeam?.name }}
									</h1>
									<span v-if="game.scores?.length || isLive" class="text-xl sm:text-2xl font-bold tabular-nums ml-3"
										:class="game.awayScore > game.homeScore ? 'text-green-400' : game.awayScore < game.homeScore ? 'text-red-400' : 'text-white'">
										{{ game.awayScore ?? 0 }}
									</span>
								</div>
							</div>
						</div>

						<!-- Score unavailable notice -->
						<div v-if="isLive && !game.scores?.length" class="text-[11px] text-gray-500 italic mb-1">
							Score unavailable
						</div>

						<!-- Match meta -->
						<div class="flex flex-wrap items-center gap-3 text-xs">
							<!-- Live badge -->
							<span v-if="isLive" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-500/20 border border-green-500/30">
								<span class="relative flex h-2 w-2">
									<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
									<span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
								</span>
								<span class="text-green-400 font-bold uppercase">
									<template v-if="game.elapsed">{{ game.elapsed }}'</template>
									<template v-else>Live</template>
								</span>
							</span>
							<!-- Ended badge -->
							<span v-else-if="bettingEnded" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 font-bold uppercase">
								{{ game.statusText || 'Ended' }}
							</span>
							<!-- Pre-match -->
							<span v-else class="text-gray-400 font-medium">
								<time :datetime="game.startTime">{{ game.startTimeGmt }}</time>
							</span>

							<span class="text-gray-600">|</span>
							<span class="text-gray-400">{{ game.marketsCount ?? 0 }} markets</span>
							<span class="text-gray-600">|</span>
							<span class="text-gray-400">
								Traded: <MoneyFormat billion :amount="game.traded ?? 0" class="text-primary-light font-semibold" />
							</span>
							<span v-if="game.liquidity" class="text-gray-400">
								LQ: <MoneyFormat billion :amount="game.liquidity" />
							</span>
							<span v-if="game.volume" class="text-gray-400">
								VOL: <MoneyFormat billion :amount="game.volume" />
							</span>
						</div>
					</div>

					<!-- Watchlist -->
					<ToggleWatchList
						:gameId="game.id"
						:isWatched="$page.props.auth?.watchlist?.includes(game.id) ?? false"
						class="ml-3" />
				</div>
			</div>

			<!-- Market filter tabs -->
			<div class="mb-4">
				<MarketTabs v-model="filter" :tabs="filters" />
			</div>

			<!-- Markets -->
			<div v-if="filteredMarkets?.length" class="space-y-3">
				<MarketCard
					v-for="(market, index) in filteredMarkets"
					:key="market.uuid"
					:market="market"
					:game="game"
					:opened="index === 0"
					:showBookie="showBookie"
					:showExchange="showExchange"
					:handicaps="
						market.slug?.includes('handicap')
							? market.slug?.includes('asian') ? asianhandicaps : handicaps
							: market.slug?.includes('overunder') ? overunders : {}
					" />
			</div>

			<!-- Empty -->
			<SportsEmptyState
				v-else
				title="No markets available"
				message="Markets will appear once odds are published for this match."
				:showHomeLink="false" />
		</div>

		<!-- Right sidebar -->
		<template #right-sidebar>
			<div>
				<BetSlip />
				<div class="bg-gray-800 text-white border-b border-white/[0.06] flex items-center px-3 uppercase font-inter text-sm tracking-[1px] font-bold h-11 flex-shrink-0">
					Top Events
				</div>
				<div class="grid">
					<EventCard
						v-for="g in popular"
						:key="g.slug"
						:game="g" />
				</div>
			</div>
		</template>
	</UserLayout>
	<MobileBetSlipSheet />
</template>
