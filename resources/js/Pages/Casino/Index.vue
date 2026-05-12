<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { Search, Radio } from "lucide-vue-next";
import CasinoHero from "@/Components/Casino/CasinoHero.vue";
import HouseOriginalCard from "@/Components/Casino/HouseOriginalCard.vue";
import CasinoGameCard from "@/Components/Casino/CasinoGameCard.vue";
import CasinoCategoryTabs from "@/Components/Casino/CasinoCategoryTabs.vue";
import CasinoProviderFilter from "@/Components/Casino/CasinoProviderFilter.vue";
import CasinoEmptyState from "@/Components/Casino/CasinoEmptyState.vue";

const props = defineProps({
	games: Object,
	categories: Object,
	providers: Object,
	selectedCategory: String,
	selectedProvider: String,
});

const selectedCat = ref(props.selectedCategory || "");
const selectedProv = ref(props.selectedProvider || "");
const searchQuery = ref("");

function filterGames() {
	const params = {};
	if (selectedCat.value) params.category = selectedCat.value;
	if (selectedProv.value) params.provider = selectedProv.value;
	router.get(route("casino.index"), params, { preserveState: true });
}

function selectCategory(id) {
	selectedCat.value = id;
	filterGames();
}

function selectProvider(val) {
	selectedProv.value = val;
	filterGames();
}

const houseOriginals = [
	{ name: "Crash", desc: "Ride the multiplier before it crashes", route: "games.mini.crash" },
	{ name: "Dice", desc: "Roll over or under your target", route: "games.mini.dice" },
	{ name: "Mines", desc: "Find diamonds, avoid the mines", route: "games.mini.mines" },
	{ name: "Hi-Lo", desc: "Predict higher or lower card", route: "games.mini.hilo" },
];
</script>

<template>
<Head :title="$t('Casino')" />
<UserLayout :showRightSidebar="false">
	<div class="p-4 lg:p-6 space-y-6 pb-24 lg:pb-6">

		<CasinoHero />

		<!-- Live Casino Link -->
		<div class="flex items-center justify-between">
			<div></div>
			<Link
				:href="route('casino.live')"
				class="flex items-center gap-2 bg-red-600/15 text-red-400 text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-red-600/25 transition-all border border-red-500/10">
				<Radio class="w-4 h-4" />
				<span class="relative flex h-2 w-2">
					<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
					<span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
				</span>
				Live Casino
			</Link>
		</div>

		<!-- House Originals -->
		<div id="house-originals">
			<div class="flex items-center gap-2 mb-4">
				<h2 class="text-white font-bold text-lg">House Originals</h2>
				<span class="text-purple-400 text-[10px] font-bold uppercase tracking-widest bg-purple-600/15 px-2 py-0.5 rounded-full">Provably Fair</span>
			</div>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
				<HouseOriginalCard
					v-for="game in houseOriginals"
					:key="game.name"
					:game="game" />
			</div>
		</div>

		<!-- Category Tabs -->
		<CasinoCategoryTabs
			:categories="categories"
			:selected-category="selectedCat"
			@select="selectCategory" />

		<!-- Search + Provider Filter -->
		<div class="flex flex-col sm:flex-row gap-3">
			<div class="relative flex-1">
				<Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
				<input
					v-model="searchQuery"
					type="text"
					placeholder="Search games..."
					class="w-full bg-gray-800/80 text-white text-sm rounded-xl pl-10 pr-4 py-3 border border-white/[0.06] focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/30 outline-none placeholder:text-gray-500 transition" />
			</div>
			<CasinoProviderFilter
				:providers="providers"
				:selected-provider="selectedProv"
				@select="selectProvider" />
		</div>

		<!-- Games Grid -->
		<div v-if="games?.data?.length > 0">
			<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
				<CasinoGameCard
					v-for="game in games.data"
					:key="game.id"
					:game="game" />
			</div>
		</div>

		<!-- Empty State -->
		<CasinoEmptyState
			v-else
			title="No casino games yet"
			description="Try our House Originals above — provably fair games you can play right now. More games coming soon."
			action-label="Play House Originals"
			action-route="#house-originals" />
	</div>
</UserLayout>
</template>
