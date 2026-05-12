<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { UseTimeAgo } from "@vueuse/components";
import { DateTime } from "luxon";

import OddsButton from "@/Components/Sports/OddsButton.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import TeamName from "@/Components/TeamName.vue";
import ToggleWatchList from "@/Components/ToggleWatchList.vue";
import { useBookieForm, useExchangeForm } from "@/Pages/Games/bettingForm";

const props = defineProps({
	game: Object,
	defaultMarketsCount: Number,
	market: Object,
	showBookie: Boolean,
	showExchange: Boolean,
});

const emit = defineEmits(["select-bet"]);

const game = ref(props.game);
const listener = ref();

onMounted(() => {
	if (props.game?.uuid) {
		listener.value = window.Echo.channel(props.game.uuid).listen(
			"GameChanged",
			(event) => { game.value = event; },
		);
	}
});

onUnmounted(() => {
	listener.value?.stopListening("GameChanged");
});

const { addBet: addExchange } = useExchangeForm();
const { addBet: addBookie } = useBookieForm();

const bettingEnded = computed(() =>
	game.value.stateCancelled ||
	game.value.stateEnded ||
	game.value.stateFinished ||
	game.value.closed ||
	game.value.hasEnded ||
	["finished", "postponed", "cancelled", "abandoned", "not_played"].includes(game.value.state)
);

const isLive = computed(() =>
	(game.value.hasStarted && !(game.value.hasEnded || game.value.stateEnded)) ||
	game.value.state === "in_play"
);

const getName = (name) =>
	`${name}`.replace("{{", "{").replace("}}", "}")
		.replace("{home}", game.value.homeTeam.name)
		.replace("{away}", game.value.awayTeam.name);

function addBet(price, isLay, bet) {
	if (bettingEnded.value || !bet) return;
	const betData = {
		guid: `${game.value.id}-${bet.id}`,
		bet: getName(bet.name),
		market: getName(props.market.name),
		game: getName(game.value.name),
		bet_id: bet.id,
		market_id: props.market.id,
		game_id: game.value.id,
		odd_id: null,
		stake: null,
		returns: null,
		liability: null,
		price: ["BID", "ASK"].includes(price) ? 2.5 : price,
		isLay,
		isAsk: price === "ASK",
		isBid: price === "BID",
	};
	if (props.showBookie) {
		const odds = game.value.odds?.find((n) => n.bet_id === bet.id);
		if (odds) betData.odd_id = odds.id;
		addBookie(betData);
	} else {
		addExchange(betData);
	}
	emit("select-bet", betData);
}
</script>

<template>
	<div class="bg-surface-light rounded-xl border border-white/[0.06] overflow-hidden hover:border-white/[0.12] transition-all duration-200">
		<!-- Header row: league + time + watchlist -->
		<div class="flex items-center justify-between px-4 py-2 border-b border-white/[0.04]">
			<div class="flex items-center gap-2 min-w-0 flex-1">
				<!-- Live badge -->
				<span v-if="isLive" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-500/20 border border-green-500/30">
					<span class="relative flex h-1.5 w-1.5">
						<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
						<span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
					</span>
					<span class="text-[10px] font-bold text-green-400 uppercase tracking-wider">Live</span>
				</span>
				<!-- League name -->
				<span v-if="game.league" class="text-[11px] font-medium text-gray-400 truncate">
					{{ game.league.name }}
				</span>
				<!-- Time / status -->
				<span class="text-[11px] text-gray-500 flex items-center gap-1 shrink-0">
					<template v-if="isLive">
						<span v-if="game.elapsed" class="text-sky-400 font-semibold">{{ game.elapsed }}'</span>
						<span v-else-if="game.statusText && game.statusText !== 'TBD'" class="text-sky-400 font-semibold">{{ game.statusText }}</span>
					</template>
					<template v-else-if="bettingEnded">
						<span class="text-red-400 font-semibold uppercase text-[10px]">{{ game.statusText || 'Ended' }}</span>
					</template>
					<template v-else>
						<UseTimeAgo
							v-if="game.startTimeTs"
							:time="DateTime.fromSeconds(game.startTimeTs).toJSDate()"
							v-slot="time">
							<span>{{ time?.timeAgo }}</span>
						</UseTimeAgo>
					</template>
				</span>
			</div>
			<ToggleWatchList
				:gameId="game.id"
				:isWatched="$page.props.auth?.watchlist?.includes(game.id) ?? false" />
		</div>

		<!-- Match body -->
		<div class="px-4 py-3">
			<div class="flex items-start gap-3">
				<!-- Teams + score -->
				<Link
					:href="route('sports.show', { game: game.slug })"
					class="flex-1 min-w-0 group">
					<div class="flex items-center justify-between mb-1.5">
						<span class="text-sm font-semibold text-white group-hover:text-primary-light transition-colors truncate">
							{{ game.homeTeam?.name }}
						</span>
						<span v-if="game.scores?.length" class="text-sm font-bold tabular-nums ml-2"
							:class="game.homeScore > game.awayScore ? 'text-green-400' : game.homeScore < game.awayScore ? 'text-red-400' : 'text-white'">
							{{ game.homeScore }}
						</span>
						<span v-else-if="isLive" class="text-[10px] text-gray-500 ml-2">—</span>
					</div>
					<div class="flex items-center justify-between">
						<span class="text-sm font-semibold text-white group-hover:text-primary-light transition-colors truncate">
							{{ game.awayTeam?.name }}
						</span>
						<span v-if="game.scores?.length" class="text-sm font-bold tabular-nums ml-2"
							:class="game.awayScore > game.homeScore ? 'text-green-400' : game.awayScore < game.homeScore ? 'text-red-400' : 'text-white'">
							{{ game.awayScore }}
						</span>
						<span v-else-if="isLive" class="text-[10px] text-gray-500 ml-2">—</span>
					</div>
				</Link>

				<!-- Odds buttons (desktop) -->
				<div v-if="market && !bettingEnded" class="hidden sm:flex items-center gap-1">
					<template v-for="bet in market.bets" :key="bet.id">
						<div class="flex flex-col items-center gap-0.5">
							<span class="text-[9px] text-gray-500 font-medium uppercase truncate max-w-[60px] text-center leading-tight">
								<TeamName :name="bet.name" :game="game" />
							</span>
							<div class="flex gap-1">
								<!-- Back (lay in exchange = green/amber) -->
								<OddsButton
									:price="game.lays?.find(n => n.bet_id === bet.id)?.price"
									:amount="game.lays?.find(n => n.bet_id === bet.id)?.amount"
									:blank="!game.lays?.find(n => n.bet_id === bet.id)"
									:ask="!game.lays?.find(n => n.bet_id === bet.id) ? 'BID' : null"
									@click="addBet(game.lays?.find(n => n.bet_id === bet.id)?.price ?? 'BID', false, bet)" />
								<!-- Lay (back in exchange = blue) -->
								<OddsButton
									:price="game.backs?.find(n => n.bet_id === bet.id)?.price"
									:amount="game.backs?.find(n => n.bet_id === bet.id)?.amount"
									:blank="!game.backs?.find(n => n.bet_id === bet.id)"
									:ask="!game.backs?.find(n => n.bet_id === bet.id) ? 'ASK' : null"
									is-lay
									@click="addBet(game.backs?.find(n => n.bet_id === bet.id)?.price ?? 'ASK', true, bet)" />
							</div>
						</div>
					</template>
				</div>

				<!-- Bookie odds -->
				<div v-if="showBookie && market && !bettingEnded" class="hidden sm:flex items-center gap-1">
					<template v-for="bet in market.bets" :key="bet.id">
						<OddsButton
							:price="game.odds?.find(n => n.bet_id === bet.id)?.odd"
							:blank="!game.odds?.find(n => n.bet_id === bet.id)"
							@click="addBet(game.odds?.find(n => n.bet_id === bet.id)?.odd, false, bet)" />
					</template>
				</div>

				<!-- Settled -->
				<div v-if="bettingEnded" class="hidden sm:flex items-center">
					<span class="text-xs font-semibold text-gray-500 uppercase bg-gray-800 px-3 py-2 rounded-lg">Settled</span>
				</div>
			</div>

			<!-- Mobile odds row -->
			<div v-if="market && !bettingEnded" class="sm:hidden mt-3 pt-3 border-t border-white/[0.04]">
				<!-- Market name -->
				<div v-if="market.name" class="text-[10px] text-gray-500 font-medium uppercase mb-2">
					<TeamName :name="market.name" :game="game" />
				</div>
				<div class="grid gap-2" :class="showBookie ? `grid-cols-${market.bets?.length || 3}` : `grid-cols-${(market.bets?.length || 3) * 2}`">
					<template v-if="showBookie">
						<template v-for="bet in market.bets" :key="bet.id">
							<OddsButton
								:price="game.odds?.find(n => n.bet_id === bet.id)?.odd"
								:blank="!game.odds?.find(n => n.bet_id === bet.id)"
								@click="addBet(game.odds?.find(n => n.bet_id === bet.id)?.odd, false, bet)" />
						</template>
					</template>
					<template v-else>
						<template v-for="bet in market.bets" :key="bet.id">
							<OddsButton
								:price="game.lays?.find(n => n.bet_id === bet.id)?.price"
								:amount="game.lays?.find(n => n.bet_id === bet.id)?.amount"
								:blank="!game.lays?.find(n => n.bet_id === bet.id)"
								:ask="!game.lays?.find(n => n.bet_id === bet.id) ? 'BID' : null"
								@click="addBet(game.lays?.find(n => n.bet_id === bet.id)?.price ?? 'BID', false, bet)" />
							<OddsButton
								:price="game.backs?.find(n => n.bet_id === bet.id)?.price"
								:amount="game.backs?.find(n => n.bet_id === bet.id)?.amount"
								:blank="!game.backs?.find(n => n.bet_id === bet.id)"
								:ask="!game.backs?.find(n => n.bet_id === bet.id) ? 'ASK' : null"
								is-lay
								@click="addBet(game.backs?.find(n => n.bet_id === bet.id)?.price ?? 'ASK', true, bet)" />
						</template>
					</template>
				</div>
			</div>

			<!-- Footer stats -->
			<div class="flex items-center gap-3 mt-2 text-[10px] text-gray-500 font-medium">
				<span>
					Traded: <span class="text-primary-light"><MoneyFormat :amount="game.traded ?? 0" /></span>
				</span>
				<span>
					{{ game.marketsCount ?? defaultMarketsCount ?? 0 }} markets
				</span>
			</div>
		</div>
	</div>
</template>
