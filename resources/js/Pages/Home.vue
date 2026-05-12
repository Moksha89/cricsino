<script setup>
	import { computed } from "vue";

	import { Head } from "@inertiajs/vue3";

	import GameRow from "@/Components/Cards/GameRow.vue";
	import HomeEventCard from "@/Components/Cards/HomeEventCard.vue";
	import HomePageCarousel from "@/Components/Carousel/HomePageCarousel.vue";
	import UserLayout from "@/Layouts/UserLayout.vue";
	import BettingSideBar from "@/Pages/Games/BettingSideBar.vue";
	const props = defineProps({
		slides: Array,
		games: Array,
		top: Array,
		enableExchange: Boolean,
		enableBookie: Boolean,
		defaultMarket: Object,
		multiples: Boolean,
	});

	const showBookie = computed(() => {
		if (!props.enableBookie) return false;
		return props.multiples;
	});
	const showExchange = computed(() => {
		if (!props.enableExchange) return false;
		return !props.multiples;
	});
</script>

<template>
	<Head title="Welcome" />
	<UserLayout>
		<div class="p-4 sm:p-6">
			<div class="pb-4">
				<HomePageCarousel :slides="slides" />
			</div>
			<div class="grid gap-4 mb-12">
				<div>
					<h3 class="text-lg font-bold text-white font-inter">
						{{ $t("Top Football") }}
					</h3>
				</div>
				<div class="grid gap-3">
					<GameRow
						v-for="game in games"
						:key="game.slug"
						:defaultMarketsCount="defaultMarketsCount"
						:market="defaultMarket"
						:showBookie="showBookie"
						:showExchange="showExchange"
						:game="game" />
				</div>
				<div>
					<h3 class="text-lg font-bold text-white font-inter">
						{{ $t("Top Markets") }}
					</h3>
				</div>
				<div class="grid gap-4 sm:grid-cols-2">
					<HomeEventCard
						:game="topgame"
						:showBookie="showBookie"
						:defaultMarket="defaultMarket"
						v-for="topgame in top"
						:key="topgame.id" />
				</div>
			</div>
		</div>
		<template #right-sidebar-top>
			<BettingSideBar :multiples="multiples" />
		</template>
	</UserLayout>
</template>
