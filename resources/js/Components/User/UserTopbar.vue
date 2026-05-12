<script setup>
import { ref, computed } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import NotificationBell from "@/Components/User/NotificationBell.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const balance = computed(() => user.value?.balance ?? 0);
const appName = computed(() => page.props?.appName ?? 'BetRiver');

const props = defineProps({
	isMobile: Boolean,
});

const emit = defineEmits(['toggleSidebar', 'toggleMobileMenu']);

const userDropdownOpen = ref(false);
const searchQuery = ref('');

function handleToggle() {
	if (props.isMobile) {
		emit('toggleMobileMenu');
	} else {
		emit('toggleSidebar');
	}
}
</script>

<template>
<header class="h-14 bg-gray-900 border-b border-white/[0.06] flex items-center justify-between px-4 flex-shrink-0 z-50">
	<!-- Left: Menu + Logo + Search -->
	<div class="flex items-center gap-3">
		<button
			@click="handleToggle"
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
		<template v-if="user">
			<!-- Wallet Balance -->
			<div class="flex items-center gap-1 bg-gray-800 rounded-xl px-3 py-1.5 border border-white/[0.06]">
				<span class="text-success font-bold text-sm">₹{{ Number(balance).toLocaleString() }}</span>
			</div>
			<!-- Notifications -->
			<NotificationBell />
			<!-- Deposit Button -->
			<Link
				:href="route('deposits.create')"
				class="bg-gradient-to-r from-primary to-primary-light text-white text-sm font-semibold px-4 py-1.5 rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-200 hidden sm:inline-flex">
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
					<Link :href="route('accounts.statement')" class="block px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/[0.04] transition">Bet History</Link>
					<Link :href="route('deposits.create')" class="block px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/[0.04] transition">Deposit</Link>
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
</template>
