<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

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

const houseOriginals = [
	{ name: 'Crash', desc: 'Ride the multiplier!', icon: '📈', gradient: 'from-orange-500 to-red-600', route: 'games.mini.crash' },
	{ name: 'Dice', desc: 'Roll over or under', icon: '🎲', gradient: 'from-blue-500 to-indigo-600', route: 'games.mini.dice' },
	{ name: 'Mines', desc: 'Find the diamonds!', icon: '💎', gradient: 'from-emerald-500 to-teal-600', route: 'games.mini.mines' },
	{ name: 'Hi-Lo', desc: 'Higher or lower?', icon: '🃏', gradient: 'from-purple-500 to-pink-600', route: 'games.mini.hilo' },
];

const gameTabs = [
	{ id: '', label: 'All Games', icon: '🔥' },
	{ id: 'originals', label: 'Originals', icon: '💎' },
	{ id: 'live_casino', label: 'Live Games', icon: '📺' },
	{ id: 'slots', label: 'Slots', icon: '🎰' },
	{ id: 'table_games', label: 'Table Games', icon: '♠️' },
];
</script>

<template>
<Head :title="$t('Casino')" />
<UserLayout :showRightSidebar="false">
	<div class="p-4 lg:p-6 space-y-6 pb-24 lg:pb-6">

		<!-- Header -->
		<div class="flex items-center justify-between">
			<h1 class="text-2xl font-bold text-white">Casino</h1>
			<Link
				:href="route('casino.live')"
				class="flex items-center gap-2 bg-danger/20 text-danger text-sm font-semibold px-4 py-2 rounded-xl hover:bg-danger/30 transition">
				<span class="w-2 h-2 bg-danger rounded-full animate-pulse"></span>
				Live Casino
			</Link>
		</div>

		<!-- House Originals -->
		<div>
			<h2 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
				<span>💎</span> House Originals — Provably Fair
			</h2>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
				<Link
					v-for="game in houseOriginals"
					:key="game.name"
					:href="route(game.route)"
					:class="['group relative overflow-hidden rounded-2xl p-5 transition-all hover:scale-[1.02] hover:shadow-xl bg-gradient-to-br', game.gradient]">
					<div class="text-3xl mb-2">{{ game.icon }}</div>
					<h3 class="text-white font-bold text-base">{{ game.name }}</h3>
					<p class="text-white/60 text-xs mt-1">{{ game.desc }}</p>
					<div class="absolute inset-0 bg-white/0 group-hover:bg-white/5 transition duration-300"></div>
				</Link>
			</div>
		</div>

		<!-- Category Tabs -->
		<div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1">
			<button
				v-for="tab in gameTabs"
				:key="tab.id"
				@click="selectedCat = tab.id; filterGames()"
				:class="[
					'flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all',
					selectedCat === tab.id
						? 'bg-primary text-white shadow-lg shadow-primary/20'
						: 'bg-gray-800 text-gray-400 hover:text-white border border-white/[0.06]'
				]">
				<span>{{ tab.icon }}</span>
				{{ tab.label }}
			</button>
			<button
				v-for="(label, key) in categories"
				:key="key"
				@click="selectedCat = key; filterGames()"
				:class="[
					'flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all',
					selectedCat === key
						? 'bg-primary text-white shadow-lg shadow-primary/20'
						: 'bg-gray-800 text-gray-400 hover:text-white border border-white/[0.06]'
				]">
				{{ label }}
			</button>
		</div>

		<!-- Search + Provider Filter -->
		<div class="flex flex-col sm:flex-row gap-3">
			<div class="relative flex-1">
				<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
				</svg>
				<input
					v-model="searchQuery"
					type="text"
					placeholder="Search for games..."
					class="w-full bg-gray-800 text-white text-sm rounded-2xl pl-10 pr-4 py-3 border border-white/[0.06] focus:border-primary/50 focus:ring-1 focus:ring-primary/30 outline-none placeholder:text-gray-500" />
			</div>
			<select
				v-if="providers && Object.keys(providers).length > 0"
				v-model="selectedProv"
				@change="filterGames()"
				class="bg-gray-800 text-white text-sm rounded-2xl px-4 py-3 border border-white/[0.06] focus:border-primary/50 outline-none appearance-none cursor-pointer">
				<option value="">All Providers</option>
				<option v-for="(label, key) in providers" :key="key" :value="key">{{ label }}</option>
			</select>
		</div>

		<!-- Games Grid -->
		<div v-if="games?.data?.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
			<Link
				v-for="game in games.data"
				:key="game.id"
				:href="route('casino.show', game.uuid)"
				class="group relative overflow-hidden rounded-2xl bg-gray-800 border border-white/[0.06] hover:border-primary/30 transition-all hover:scale-[1.02]">
				<div class="aspect-[3/4] relative">
					<img
						v-if="game.image"
						:src="game.image"
						:alt="game.name"
						class="w-full h-full object-cover" />
					<div
						v-else
						class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary to-accent">
						<span class="text-white text-2xl font-bold">{{ game.name?.charAt(0) }}</span>
					</div>
					<!-- Live Badge -->
					<span
						v-if="game.is_live"
						class="absolute top-2 left-2 px-2 py-0.5 bg-danger text-white text-[10px] font-bold rounded-full flex items-center gap-1">
						<span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> LIVE
					</span>
					<!-- Hover Overlay -->
					<div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-all">
						<span class="opacity-0 group-hover:opacity-100 bg-primary text-white font-bold text-xs px-4 py-2 rounded-xl transition">Play Now</span>
					</div>
				</div>
				<div class="p-3">
					<h3 class="text-sm font-semibold text-white truncate">{{ game.name }}</h3>
					<p class="text-[11px] text-gray-400">{{ game.provider }}</p>
				</div>
			</Link>
		</div>

		<!-- Empty State -->
		<div v-else class="text-center py-20">
			<div class="text-5xl mb-4">🎰</div>
			<p class="text-lg text-gray-300 font-semibold mb-1">No casino games yet</p>
			<p class="text-sm text-gray-500">Try our House Originals above, or games will be added soon.</p>
		</div>
	</div>
</UserLayout>
</template>
