<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { useDark } from "@vueuse/core";
import WhatsAppWidget from "@/Components/WhatsAppWidget.vue";

const isDarkMode = useDark();
isDarkMode.value = true; // Always dark for premium theme

const page = usePage();
const user = computed(() => page.props.auth?.user);
const balance = computed(() => user.value?.balance ?? 0);
const appName = computed(() => page.props?.appName ?? 'BetRiver');

const sidebarOpen = ref(true);
const chatOpen = ref(false);
const mobileMenuOpen = ref(false);
const userDropdownOpen = ref(false);
const searchQuery = ref('');

const isMobile = ref(false);
function checkMobile() {
	isMobile.value = window.innerWidth < 1024;
	if (isMobile.value) {
		sidebarOpen.value = false;
		chatOpen.value = false;
	}
}
onMounted(() => {
	checkMobile();
	window.addEventListener('resize', checkMobile);
});
onUnmounted(() => {
	window.removeEventListener('resize', checkMobile);
});

const menuItems = [
	{ label: 'Home', icon: '🏠', route: 'dashboard', routeName: 'dashboard' },
	{ label: 'Sports', icon: '⚽', route: 'games.index', routeName: 'games.*' },
	{ label: 'Casino', icon: '🎰', route: 'casino.index', routeName: 'casino.*' },
	{ label: 'Live Casino', icon: '🎲', route: 'casino.live', routeName: 'casino.live' },
];

const gameCategories = [
	{ label: 'Originals', icon: '💎', route: 'casino.index' },
	{ label: 'Top Slots', icon: '🎰', route: 'casino.index' },
	{ label: 'Live Games', icon: '📺', route: 'casino.live' },
	{ label: 'Crash Games', icon: '📈', route: 'games.mini.crash' },
];

const accountItems = [
	{ label: 'Wallet', icon: '💰', route: 'deposits.index' },
	{ label: 'Bet History', icon: '📋', route: 'accounts.statement' },
	{ label: 'VIP Club', icon: '👑', route: 'accounts.level' },
	{ label: 'Referrals', icon: '🤝', route: 'accounts.referrals' },
];

function navigateTo(routeName) {
	try {
		router.visit(route(routeName));
		mobileMenuOpen.value = false;
	} catch(e) {}
}

function isActive(routeName) {
	try {
		return route().current(routeName);
	} catch(e) {
		return false;
	}
}
</script>

<template>
<div class="flex h-screen overflow-hidden bg-gray-950 text-white font-inter">
	<!-- Sidebar (Desktop) -->
	<aside
		v-show="sidebarOpen && !isMobile"
		class="w-[250px] flex-shrink-0 bg-gray-900 border-r border-white/[0.06] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
		<div class="p-4">
			<!-- Logo -->
			<Link href="/" class="flex items-center gap-2 mb-6">
				<div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-primary-light flex items-center justify-center">
					<span class="text-white font-bold text-sm">B</span>
				</div>
				<span class="text-white font-bold text-xl">{{ appName }}</span>
			</Link>

			<!-- Quick Promo Cards -->
			<div class="grid grid-cols-2 gap-2 mb-6">
				<div class="bg-gradient-to-br from-primary/20 to-primary/5 rounded-xl p-3 cursor-pointer hover:from-primary/30 transition">
					<div class="text-xs font-semibold text-white">Daily Bonus</div>
					<div class="text-[10px] text-gray-400 mt-0.5">Claim now</div>
				</div>
				<div class="bg-gradient-to-br from-gold/20 to-gold/5 rounded-xl p-3 cursor-pointer hover:from-gold/30 transition">
					<div class="text-xs font-semibold text-white">VIP</div>
					<div class="text-[10px] text-gray-400 mt-0.5">Rewards</div>
				</div>
				<div class="bg-gradient-to-br from-success/20 to-success/5 rounded-xl p-3 cursor-pointer hover:from-success/30 transition">
					<div class="text-xs font-semibold text-white">Free Bonus</div>
					<div class="text-[10px] text-gray-400 mt-0.5">Get ₹100</div>
				</div>
				<div class="bg-gradient-to-br from-info/20 to-info/5 rounded-xl p-3 cursor-pointer hover:from-info/30 transition">
					<div class="text-xs font-semibold text-white">Reward Hub</div>
					<div class="text-[10px] text-gray-400 mt-0.5">Spin & Win</div>
				</div>
			</div>

			<!-- Games Section -->
			<div class="mb-6">
				<div class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-3 px-1">Games</div>
				<nav class="space-y-1">
					<button
						v-for="item in menuItems"
						:key="item.label"
						@click="navigateTo(item.route)"
						:class="[
							'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
							isActive(item.routeName)
								? 'bg-primary/20 text-white shadow-lg shadow-primary/10'
								: 'text-gray-400 hover:text-white hover:bg-white/[0.04]'
						]">
						<span class="text-base">{{ item.icon }}</span>
						<span>{{ item.label }}</span>
					</button>
				</nav>
			</div>

			<!-- Game Categories -->
			<div class="mb-6">
				<nav class="space-y-1">
					<button
						v-for="cat in gameCategories"
						:key="cat.label"
						@click="navigateTo(cat.route)"
						class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/[0.04] transition-all duration-200">
						<span class="text-base">{{ cat.icon }}</span>
						<span>{{ cat.label }}</span>
					</button>
				</nav>
			</div>

			<!-- Account Section -->
			<div class="mb-6">
				<div class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-3 px-1">Account</div>
				<nav class="space-y-1">
					<button
						v-for="item in accountItems"
						:key="item.label"
						@click="navigateTo(item.route)"
						class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/[0.04] transition-all duration-200">
						<span class="text-base">{{ item.icon }}</span>
						<span>{{ item.label }}</span>
					</button>
				</nav>
			</div>
		</div>
	</aside>

	<!-- Main Content Area -->
	<div class="flex-1 flex flex-col min-w-0">
		<!-- Top Bar -->
		<header class="h-14 bg-gray-900 border-b border-white/[0.06] flex items-center justify-between px-4 flex-shrink-0 z-50">
			<!-- Left: Menu + Logo + Search -->
			<div class="flex items-center gap-3">
				<button
					@click="isMobile ? (mobileMenuOpen = !mobileMenuOpen) : (sidebarOpen = !sidebarOpen)"
					class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/[0.06] transition">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
					</svg>
				</button>
				<Link v-if="isMobile" href="/" class="flex items-center gap-2">
					<div class="w-7 h-7 rounded-lg bg-gradient-to-br from-primary to-primary-light flex items-center justify-center">
						<span class="text-white font-bold text-xs">B</span>
					</div>
					<span class="text-white font-bold">{{ appName }}</span>
				</Link>
				<!-- Search (Desktop) -->
				<div v-if="!isMobile" class="relative">
					<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
					</svg>
					<input
						v-model="searchQuery"
						type="text"
						placeholder="Search games..."
						class="w-64 bg-gray-800 text-white text-sm rounded-xl pl-10 pr-4 py-2 border border-white/[0.06] focus:border-primary/50 focus:ring-1 focus:ring-primary/30 outline-none placeholder:text-gray-500 transition" />
				</div>
			</div>

			<!-- Right: Wallet + Actions + User -->
			<div class="flex items-center gap-2">
				<!-- Wallet Balance -->
				<template v-if="user">
					<div class="flex items-center gap-1 bg-gray-800 rounded-xl px-3 py-1.5 border border-white/[0.06]">
						<span class="text-success font-bold text-sm">₹{{ Number(balance).toLocaleString() }}</span>
					</div>
					<!-- Deposit Button -->
					<Link
						:href="route('deposits.create')"
						class="bg-gradient-to-r from-primary to-primary-light text-white text-sm font-semibold px-4 py-1.5 rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-200">
						+ Deposit
					</Link>
					<!-- User Menu -->
					<div class="relative">
						<button
							@click="userDropdownOpen = !userDropdownOpen"
							class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-white/[0.06] transition">
							<div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center">
								<span class="text-white text-xs font-bold">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
							</div>
							<svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
							</svg>
						</button>
						<!-- Dropdown -->
						<div
							v-show="userDropdownOpen"
							@click="userDropdownOpen = false"
							class="absolute right-0 top-full mt-2 w-48 bg-gray-800 rounded-2xl border border-white/[0.08] shadow-2xl py-2 z-50">
							<div class="px-4 py-2 border-b border-white/[0.06]">
								<div class="text-sm font-semibold text-white">{{ user.name }}</div>
								<div class="text-xs text-gray-400">{{ user.email }}</div>
							</div>
							<Link :href="route('profile.edit')" class="block px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/[0.04] transition">Profile</Link>
							<Link :href="route('accounts.settings')" class="block px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/[0.04] transition">Settings</Link>
							<Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-2.5 text-sm text-danger hover:bg-white/[0.04] transition">Log Out</Link>
						</div>
					</div>
				</template>
				<template v-else>
					<Link href="/login" class="text-sm font-semibold text-gray-300 hover:text-white px-3 py-1.5 transition">Log In</Link>
					<Link href="/register" class="bg-gradient-to-r from-primary to-primary-light text-white text-sm font-semibold px-4 py-1.5 rounded-xl transition">Register</Link>
				</template>
			</div>
		</header>

		<!-- Page Content -->
		<main class="flex-1 overflow-y-auto bg-gray-950 scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
			<slot />
		</main>
	</div>

	<!-- Chat Panel (Desktop) -->
	<aside
		v-if="chatOpen && !isMobile"
		class="w-[320px] flex-shrink-0 bg-gray-900 border-l border-white/[0.06] flex flex-col">
		<div class="h-14 flex items-center justify-between px-4 border-b border-white/[0.06]">
			<span class="font-semibold text-white">Chat</span>
			<button @click="chatOpen = false" class="text-gray-400 hover:text-white p-1">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>
		</div>
		<div class="flex-1 overflow-y-auto p-4">
			<div class="text-center text-gray-500 text-sm py-8">
				Chat coming soon...
			</div>
		</div>
		<div class="p-3 border-t border-white/[0.06]">
			<div class="flex gap-2">
				<input type="text" placeholder="Send a message..." class="flex-1 bg-gray-800 text-white text-sm rounded-xl px-4 py-2.5 border border-white/[0.06] focus:border-primary/50 outline-none placeholder:text-gray-500" />
				<button class="bg-primary text-white px-4 rounded-xl text-sm font-semibold hover:bg-primary-light transition">Send</button>
			</div>
		</div>
	</aside>

	<!-- Mobile Bottom Navigation -->
	<nav v-if="isMobile" class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-white/[0.06] z-50 px-2 py-1 safe-area-bottom">
		<div class="flex items-center justify-around">
			<Link href="/" class="flex flex-col items-center py-2 px-3 text-gray-400 hover:text-primary transition" :class="{ 'text-primary': isActive('dashboard') }">
				<span class="text-lg">🏠</span>
				<span class="text-[10px] mt-0.5">Home</span>
			</Link>
			<button @click="navigateTo('games.index')" class="flex flex-col items-center py-2 px-3 text-gray-400 hover:text-primary transition" :class="{ 'text-primary': isActive('games.*') }">
				<span class="text-lg">⚽</span>
				<span class="text-[10px] mt-0.5">Sports</span>
			</button>
			<button @click="navigateTo('casino.index')" class="flex flex-col items-center py-2 px-3 text-gray-400 hover:text-primary transition" :class="{ 'text-primary': isActive('casino.*') }">
				<span class="text-lg">🎰</span>
				<span class="text-[10px] mt-0.5">Casino</span>
			</button>
			<button @click="navigateTo('deposits.index')" class="flex flex-col items-center py-2 px-3 text-gray-400 hover:text-primary transition">
				<span class="text-lg">💰</span>
				<span class="text-[10px] mt-0.5">Wallet</span>
			</button>
			<button @click="navigateTo('profile.edit')" class="flex flex-col items-center py-2 px-3 text-gray-400 hover:text-primary transition">
				<span class="text-lg">👤</span>
				<span class="text-[10px] mt-0.5">Profile</span>
			</button>
		</div>
	</nav>

	<!-- Mobile Sidebar Overlay -->
	<Teleport to="body">
		<div v-if="mobileMenuOpen && isMobile" class="fixed inset-0 z-[100]">
			<div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
			<aside class="absolute left-0 top-0 bottom-0 w-[280px] bg-gray-900 overflow-y-auto shadow-2xl">
				<div class="p-4">
					<div class="flex items-center justify-between mb-6">
						<Link href="/" class="flex items-center gap-2">
							<div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-primary-light flex items-center justify-center">
								<span class="text-white font-bold text-sm">B</span>
							</div>
							<span class="text-white font-bold text-xl">{{ appName }}</span>
						</Link>
						<button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-white p-2">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
							</svg>
						</button>
					</div>
					<!-- Same menu items as desktop sidebar -->
					<div class="space-y-1 mb-6">
						<button
							v-for="item in [...menuItems, ...gameCategories, ...accountItems]"
							:key="item.label"
							@click="navigateTo(item.route)"
							class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/[0.04] transition">
							<span class="text-base">{{ item.icon }}</span>
							<span>{{ item.label }}</span>
						</button>
					</div>
				</div>
			</aside>
		</div>
	</Teleport>

	<!-- WhatsApp Widget -->
	<WhatsAppWidget />
</div>
</template>

<style scoped>
.safe-area-bottom {
	padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
