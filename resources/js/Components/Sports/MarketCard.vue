<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { ChevronDown } from "lucide-vue-next";

import OddsButton from "@/Components/Sports/OddsButton.vue";
import MoneyFormat from "@/Components/MoneyFormat.vue";
import TeamName from "@/Components/TeamName.vue";
import { useBookieForm, useExchangeForm } from "@/Pages/Games/bettingForm";

const props = defineProps({
	game: Object,
	market: Object,
	showBookie: Boolean,
	showExchange: Boolean,
	opened: { type: Boolean, default: false },
	handicaps: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["select-bet"]);

const show = ref(props.opened);
const marketItem = ref(props.market);
const handicap = ref(Object.keys(props.handicaps ?? {})[0] ?? null);

const { addBet: addExchange, exchangeForm } = useExchangeForm();
const { addBet: addBookie, bookieForm } = useBookieForm();

const bettingEnded = computed(() =>
	props.game.stateCancelled || props.game.stateEnded || props.game.stateFinished ||
	props.game.closed || props.game.hasEnded ||
	["finished", "postponed", "cancelled", "abandoned", "not_played"].includes(props.game.state)
);

const bets = computed(() => {
	if (!handicap.value) return marketItem.value.bets;
	const caps = props.handicaps[handicap.value] ?? [];
	if (!caps.length) return marketItem.value.bets;
	return marketItem.value.bets.filter((bet) => caps.includes(bet.result));
});

const isWinner = (betId) => {
	const wins = usePage().props.winBets ?? [];
	return wins.includes(betId);
};

const getName = (name) =>
	`${name}`.replace("{{", "{").replace("}}", "}")
		.replace("{home}", props.game.homeTeam?.name ?? "")
		.replace("{away}", props.game.awayTeam?.name ?? "");

function addBet(price, isLay, bet) {
	if (bettingEnded.value || !bet) return;
	const betData = {
		guid: `${props.game.id}-${bet.id}`,
		bet: getName(bet.name),
		market: getName(props.market.name),
		game: getName(props.game.name),
		bet_id: bet.id,
		market_id: props.market.id,
		game_id: props.game.id,
		stake: null,
		returns: null,
		liability: null,
		price: ["BID", "ASK"].includes(price) ? 2.5 : price,
		isLay,
		isAsk: price === "ASK",
		isBid: price === "BID",
	};
	if (props.showBookie) addBookie(betData);
	else addExchange(betData);
	emit("select-bet", betData);
}

const listener = ref();
onMounted(() => {
	if (props.market.gameMarket?.uuid)
		listener.value = window.Echo.channel(props.market.gameMarket?.uuid)
			.listen("PriceChanged", (event) => { marketItem.value = event.market; });
});
onUnmounted(() => { listener.value?.stopListening("PriceChanged"); });
</script>

<template>
	<div class="bg-surface-light rounded-xl border border-white/[0.06] overflow-hidden">
		<!-- Market header -->
		<button
			type="button"
			@click="show = !show"
			class="w-full flex items-center justify-between px-4 py-3 hover:bg-white/[0.03] transition-colors min-h-[44px]">
			<div class="flex-1 min-w-0">
				<h3 class="text-sm font-semibold text-white text-left truncate">
					<TeamName :name="market.name" :game="game" />
				</h3>
				<div class="flex items-center gap-3 mt-0.5">
					<span v-if="market.traded" class="text-[10px] text-gray-500 font-medium">
						Traded: <MoneyFormat :amount="market.traded ?? 0" class="text-primary-light" />
					</span>
					<span v-if="market.liquidity" class="text-[10px] text-gray-500 font-medium">
						LQ: <MoneyFormat :amount="market.liquidity" />
					</span>
					<span v-if="game.state === 'in_play'" class="text-[10px] text-sky-400 font-semibold">
						In-play delay: 2 min
					</span>
				</div>
			</div>
			<!-- Handicap tabs -->
			<div v-if="Object.keys(handicaps ?? {}).length > 1" class="flex items-center gap-1 mr-3" @click.stop>
				<button
					v-for="(_, hcap) in handicaps"
					:key="hcap"
					@click="handicap = hcap; if (!show) show = true;"
					class="px-2 py-1 text-[10px] font-bold rounded transition-colors min-h-[28px]"
					:class="handicap === hcap ? 'bg-primary text-white' : 'bg-gray-800 text-gray-400 hover:text-white'">
					{{ hcap }}
				</button>
			</div>
			<ChevronDown class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': show }" />
		</button>

		<!-- Market body -->
		<div v-show="show" class="border-t border-white/[0.04]">
			<!-- Column headers (desktop) -->
			<div v-if="showExchange" class="hidden sm:grid grid-cols-[1fr_4rem_repeat(6,minmax(60px,1fr))] gap-1 px-4 py-2 text-[10px] text-gray-500 font-medium uppercase">
				<span>Selection</span>
				<span class="text-center">Last</span>
				<span class="text-center text-primary-light col-span-3">Back</span>
				<span class="text-center text-sky-400 col-span-3">Lay</span>
			</div>

			<!-- Bets -->
			<ul class="divide-y divide-white/[0.04]">
				<li v-for="bet in bets" :key="bet.id" class="px-4 py-2.5">
					<!-- Settled view -->
					<template v-if="bettingEnded">
						<div class="flex items-center justify-between">
							<span class="text-sm text-white font-medium">
								<TeamName :name="bet.name" :game="game" />
							</span>
							<span v-if="isWinner(bet.id)" class="text-xs font-bold text-green-400 uppercase">Winner</span>
							<span v-else class="text-xs font-bold text-gray-500 uppercase">Loser</span>
						</div>
					</template>

					<!-- Active exchange view -->
					<template v-else-if="showExchange">
						<!-- Desktop grid -->
						<div class="hidden sm:grid grid-cols-[1fr_4rem_repeat(6,minmax(60px,1fr))] gap-1 items-center">
							<span class="text-sm text-white font-medium truncate">
								<TeamName :name="bet.name" :game="game" />
							</span>
							<span class="text-center text-xs font-semibold tabular-nums text-gray-400">
								{{ bet.last_trade?.price ?? '—' }}
							</span>
							<!-- 3 Back levels (lay orders in the book) -->
							<OddsButton
								v-for="(lay, i) in (bet.lays ?? []).slice(0, 3)"
								:key="'lay-' + i"
								:price="lay.price"
								:amount="lay.amount"
								@click="addBet(lay.price * 1, false, bet)" />
							<OddsButton v-for="i in Math.max(0, 3 - (bet.lays?.length ?? 0))" :key="'lay-blank-' + i" blank :ask="'BID'" @click="addBet('BID', false, bet)" />
							<!-- 3 Lay levels (back orders in the book) -->
							<OddsButton
								v-for="(back, i) in (bet.backs ?? []).slice(0, 3)"
								:key="'back-' + i"
								:price="back.price"
								:amount="back.amount"
								is-lay
								@click="addBet(back.price * 1, true, bet)" />
							<OddsButton v-for="i in Math.max(0, 3 - (bet.backs?.length ?? 0))" :key="'back-blank-' + i" blank is-lay :ask="'ASK'" @click="addBet('ASK', true, bet)" />
						</div>

						<!-- Mobile -->
						<div class="sm:hidden">
							<div class="flex items-center justify-between mb-2">
								<span class="text-sm text-white font-medium truncate">
									<TeamName :name="bet.name" :game="game" />
								</span>
								<span class="text-xs text-gray-400 tabular-nums">
									Last: {{ bet.last_trade?.price ?? '—' }}
								</span>
							</div>
							<div class="grid grid-cols-2 gap-2">
								<OddsButton
									:price="bet.lays?.[0]?.price"
									:amount="bet.lays?.[0]?.amount"
									:blank="!bet.lays?.[0]"
									:ask="!bet.lays?.[0] ? 'BID' : null"
									@click="addBet(bet.lays?.[0]?.price ?? 'BID', false, bet)" />
								<OddsButton
									:price="bet.backs?.[0]?.price"
									:amount="bet.backs?.[0]?.amount"
									:blank="!bet.backs?.[0]"
									:ask="!bet.backs?.[0] ? 'ASK' : null"
									is-lay
									@click="addBet(bet.backs?.[0]?.price ?? 'ASK', true, bet)" />
							</div>
						</div>
					</template>

					<!-- Bookie view -->
					<template v-else-if="showBookie">
						<div class="flex items-center justify-between">
							<span class="text-sm text-white font-medium truncate flex-1">
								<TeamName :name="bet.name" :game="game" />
							</span>
							<OddsButton
								v-if="bet.odds?.[0]"
								:price="bet.odds[0].odd"
								@click="addBet(bet.odds[0].odd * 1, false, bet)" />
							<OddsButton v-else blank />
						</div>
					</template>
				</li>
			</ul>

			<!-- Empty state -->
			<div v-if="!bets?.length" class="px-4 py-6 text-center text-sm text-gray-500">
				No selections available for this market.
			</div>
		</div>
	</div>
</template>
