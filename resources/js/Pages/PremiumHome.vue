<script setup>
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import PremiumLayout from "@/Layouts/PremiumLayout.vue";

const props = defineProps({
	slides: Array,
	games: Array,
	top: Array,
	enableExchange: Boolean,
	enableBookie: Boolean,
	defaultMarket: Object,
	multiples: Boolean,
});

const activeTab = ref('originals');
const searchQuery = ref('');

const tabs = [
	{ id: 'originals', label: 'Originals', icon: '💎' },
	{ id: 'sports', label: 'Sports', icon: '⚽' },
	{ id: 'live', label: 'Live Casino', icon: '📺' },
	{ id: 'crash', label: 'Crash', icon: '📈' },
	{ id: 'slots', label: 'Slots', icon: '🎰' },
];

const categoryCards = [
	{ label: 'CASINO', icon: '🎰', color: 'from-blue-600 to-blue-800', badge: '290', route: 'casino.index' },
	{ label: 'SPORTS', icon: '🏏', color: 'from-green-600 to-green-800', badge: '290', route: 'games.index' },
	{ label: 'LOTTERY', icon: '🎫', color: 'from-red-500 to-pink-600', badge: '290', route: 'casino.index' },
	{ label: 'REFER', icon: '🎁', color: 'from-yellow-500 to-orange-500', badge: '290', route: 'accounts.referrals' },
];

const houseOriginals = [
	{ name: 'Crash', desc: 'Ride the multiplier', icon: '📈', gradient: 'from-orange-500 to-red-600', route: 'games.mini.crash' },
	{ name: 'Dice', desc: 'Roll over/under', icon: '🎲', gradient: 'from-blue-500 to-indigo-600', route: 'games.mini.dice' },
	{ name: 'Mines', desc: 'Find diamonds', icon: '💎', gradient: 'from-emerald-500 to-teal-600', route: 'games.mini.mines' },
	{ name: 'Hi-Lo', desc: 'Higher or lower', icon: '🃏', gradient: 'from-purple-500 to-pink-600', route: 'games.mini.hilo' },
];

const recentWins = [
	{ game: 'Crash', user: '@player1', amount: '₹2,450', multiplier: '3.2x' },
	{ game: 'Dice', user: '@lucky7', amount: '₹890', multiplier: '1.8x' },
	{ game: 'Mines', user: '@winner99', amount: '₹5,200', multiplier: '8.5x' },
	{ game: 'Hi-Lo', user: '@betking', amount: '₹1,100', multiplier: '2.0x' },
	{ game: 'Crash', user: '@rocket', amount: '₹15,000', multiplier: '25.0x' },
	{ game: 'Dice', user: '@ace', amount: '₹430', multiplier: '1.5x' },
	{ game: 'Mines', user: '@diamond', amount: '₹3,700', multiplier: '5.2x' },
	{ game: 'Crash', user: '@moon', amount: '₹8,900', multiplier: '12.1x' },
];

function goTo(routeName) {
	try { router.visit(route(routeName)); } catch(e) {}
}

const upcomingGames = computed(() => {
	return (props.games || []).slice(0, 6);
});
</script>

<template>
<Head title="Home" />
<PremiumLayout>
	<div class="p-4 lg:p-6 space-y-6 pb-24 lg:pb-6">

		<!-- Hero Banner + Category Cards -->
		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			<!-- Hero Banner -->
			<div class="lg:col-span-2 relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-dark via-primary to-primary-light p-6 lg:p-8 min-h-[200px]">
				<div class="relative z-10">
					<h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight mb-2">
						GET LUCKY<br/>BONUSES DAILY.
					</h1>
					<p class="text-white/70 text-sm mb-4">Spin, win, and claim your rewards every single day!</p>
					<button class="bg-white text-primary font-bold text-sm px-6 py-2.5 rounded-xl hover:bg-gray-100 transition shadow-lg">
						Claim Bonus
					</button>
				</div>
				<!-- Decorative elements -->
				<div class="absolute right-4 top-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
				<div class="absolute right-16 bottom-4 w-20 h-20 bg-white/5 rounded-full blur-xl"></div>
			</div>

			<!-- Category Cards -->
			<div class="grid grid-cols-2 gap-3">
				<button
					v-for="cat in categoryCards"
					:key="cat.label"
					@click="goTo(cat.route)"
					:class="['relative overflow-hidden rounded-2xl p-4 transition-transform hover:scale-[1.02] bg-gradient-to-br', cat.color]">
					<div class="text-2xl mb-1">{{ cat.icon }}</div>
					<div class="text-white font-bold text-sm">{{ cat.label }}</div>
					<div class="absolute bottom-2 left-4 flex items-center gap-1 bg-black/20 rounded-full px-2 py-0.5">
						<span class="w-2 h-2 rounded-full bg-success"></span>
						<span class="text-[10px] text-white/80">{{ cat.badge }}</span>
					</div>
				</button>
			</div>
		</div>

		<!-- Recent Wins Carousel -->
		<div>
			<h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
				<span class="text-gold">🏆</span> Recent Wins
			</h3>
			<div class="flex gap-3 overflow-x-auto scrollbar-hide pb-2">
				<div
					v-for="(win, i) in recentWins"
					:key="i"
					class="flex-shrink-0 bg-gray-800 rounded-2xl p-3 min-w-[140px] border border-white/[0.06] hover:border-primary/30 transition">
					<div class="flex items-center gap-2 mb-2">
						<div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-xs">
							{{ win.game === 'Crash' ? '📈' : win.game === 'Dice' ? '🎲' : win.game === 'Mines' ? '💎' : '🃏' }}
						</div>
						<div>
							<div class="text-xs font-bold text-white">{{ win.game }}</div>
							<div class="text-[10px] text-gray-400">{{ win.user }}</div>
						</div>
					</div>
					<div class="text-success font-bold text-sm">{{ win.amount }}</div>
					<div class="text-[10px] text-gray-400">{{ win.multiplier }}</div>
				</div>
			</div>
		</div>

		<!-- House Originals -->
		<div>
			<h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
				<span>💎</span> House Originals
			</h3>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
				<button
					v-for="game in houseOriginals"
					:key="game.name"
					@click="goTo(game.route)"
					:class="['group relative overflow-hidden rounded-2xl p-5 transition-all hover:scale-[1.02] hover:shadow-xl bg-gradient-to-br', game.gradient]">
					<div class="text-3xl mb-2">{{ game.icon }}</div>
					<div class="text-white font-bold text-base">{{ game.name }}</div>
					<div class="text-white/60 text-xs">{{ game.desc }}</div>
					<!-- Hover glow -->
					<div class="absolute inset-0 bg-white/0 group-hover:bg-white/5 transition-all duration-300"></div>
				</button>
			</div>
		</div>

		<!-- Live / Upcoming Matches -->
		<div v-if="upcomingGames.length > 0">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-white font-bold text-lg flex items-center gap-2">
					<span>⚡</span> Live & Upcoming
				</h3>
				<Link :href="route('games.index')" class="text-primary text-sm font-semibold hover:text-primary-light transition">View all →</Link>
			</div>
			<div class="grid gap-3">
				<div
					v-for="game in upcomingGames"
					:key="game.id"
					class="bg-gray-800 rounded-2xl p-4 border border-white/[0.06] hover:border-primary/20 transition">
					<div class="flex items-center justify-between">
						<div class="flex-1">
							<div class="flex items-center gap-2 mb-1">
								<span v-if="game.is_live" class="inline-flex items-center gap-1 bg-danger/20 text-danger text-[10px] font-bold px-2 py-0.5 rounded-full">
									<span class="w-1.5 h-1.5 rounded-full bg-danger animate-pulse"></span> LIVE
								</span>
								<span v-else class="text-[10px] text-gray-400">{{ game.startTime }}</span>
							</div>
							<div class="text-white font-semibold text-sm">{{ game.name }}</div>
							<div class="text-xs text-gray-400 mt-0.5">{{ game.sport }}</div>
						</div>
						<Link
							:href="route('games.show', game.slug || game.id)"
							class="bg-primary/20 text-primary text-xs font-bold px-4 py-2 rounded-xl hover:bg-primary/30 transition">
							Bet Now
						</Link>
					</div>
				</div>
			</div>
		</div>

		<!-- All Games Section -->
		<div>
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-white font-bold text-lg flex items-center gap-2">
					<span>🔥</span> All Games
				</h3>
				<Link :href="route('casino.index')" class="text-primary text-sm font-semibold hover:text-primary-light transition">View all →</Link>
			</div>

			<!-- Tabs -->
			<div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide pb-1">
				<button
					v-for="tab in tabs"
					:key="tab.id"
					@click="activeTab = tab.id"
					:class="[
						'flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all',
						activeTab === tab.id
							? 'bg-primary text-white shadow-lg shadow-primary/20'
							: 'bg-gray-800 text-gray-400 hover:text-white border border-white/[0.06]'
					]">
					<span>{{ tab.icon }}</span>
					{{ tab.label }}
				</button>
			</div>

			<!-- Search -->
			<div class="relative mb-4">
				<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
				</svg>
				<input
					v-model="searchQuery"
					type="text"
					placeholder="Search for games..."
					class="w-full bg-gray-800 text-white text-sm rounded-2xl pl-10 pr-4 py-3 border border-white/[0.06] focus:border-primary/50 focus:ring-1 focus:ring-primary/30 outline-none placeholder:text-gray-500" />
			</div>

			<!-- Game Grid -->
			<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
				<button
					v-for="game in houseOriginals"
					:key="'grid-'+game.name"
					@click="goTo(game.route)"
					:class="['group relative overflow-hidden rounded-2xl aspect-[4/5] transition-all hover:scale-[1.02] bg-gradient-to-br', game.gradient]">
					<div class="absolute inset-0 flex flex-col items-center justify-center p-3">
						<div class="text-4xl mb-2">{{ game.icon }}</div>
						<div class="text-white font-bold text-sm text-center">{{ game.name }}</div>
						<div class="text-white/50 text-xs text-center">{{ game.desc }}</div>
					</div>
					<div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
						<span class="bg-white/20 backdrop-blur-sm text-white font-bold text-xs px-4 py-2 rounded-xl">Play Now</span>
					</div>
				</button>
			</div>
		</div>

		<!-- Deposit Promo Banner -->
		<div class="bg-gradient-to-r from-gold/20 via-gold/10 to-transparent rounded-2xl p-6 border border-gold/20">
			<div class="flex items-center justify-between">
				<div>
					<h3 class="text-gold font-bold text-lg mb-1">GET HUGE DEPOSIT BONUS</h3>
					<p class="text-gray-400 text-sm">First deposit gets 100% bonus up to ₹10,000</p>
				</div>
				<Link :href="route('deposits.create')" class="bg-gold text-gray-900 font-bold text-sm px-6 py-2.5 rounded-xl hover:bg-gold/90 transition shadow-lg">
					Claim
				</Link>
			</div>
		</div>

	</div>
</PremiumLayout>
</template>
