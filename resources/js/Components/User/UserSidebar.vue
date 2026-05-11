<script setup>
import { computed } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const appName = computed(() => page.props?.appName ?? 'BetRiver');

const emit = defineEmits(['navigate']);

function navigateTo(routeName) {
	try {
		router.visit(route(routeName));
		emit('navigate');
	} catch(e) {}
}

function isActive(routeName) {
	try {
		return route().current(routeName);
	} catch(e) {
		return false;
	}
}

const mainNav = [
	{ label: 'Home', icon: 'home', route: 'home', match: 'home' },
	{ label: 'Sports', icon: 'sports', route: 'sports.index', match: 'sports.*' },
	{ label: 'In-Play', icon: 'live', route: 'sports.inplay', match: 'sports.inplay' },
	{ label: 'Casino', icon: 'casino', route: 'casino.index', match: 'casino.*' },
];

const casinoNav = [
	{ label: 'Crash', icon: 'crash', route: 'games.mini.crash' },
	{ label: 'Dice', icon: 'dice', route: 'games.mini.dice' },
	{ label: 'Mines', icon: 'mines', route: 'games.mini.mines' },
	{ label: 'Hi-Lo', icon: 'hilo', route: 'games.mini.hilo' },
];

const accountNav = [
	{ label: 'Wallet', icon: 'wallet', route: 'deposits.create' },
	{ label: 'Bet History', icon: 'history', route: 'accounts.statement' },
	{ label: 'Transactions', icon: 'transactions', route: 'accounts.statement' },
	{ label: 'Referrals', icon: 'referrals', route: 'accounts.referrals' },
	{ label: 'Settings', icon: 'settings', route: 'accounts.settings' },
];

const icons = {
	home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
	sports: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z',
	live: 'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z',
	casino: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
	crash: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
	dice: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
	mines: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
	hilo: 'M7 11l5-5m0 0l5 5m-5-5v12',
	wallet: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
	history: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
	transactions: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
	referrals: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
	settings: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
	settings_inner: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
};
</script>

<template>
<div class="p-4">
	<!-- Logo -->
	<Link href="/" class="flex items-center gap-2.5 mb-6">
		<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center shadow-lg shadow-primary/20">
			<span class="text-white font-bold text-sm">B</span>
		</div>
		<span class="text-white font-bold text-xl tracking-tight">{{ appName }}</span>
	</Link>

	<!-- Quick Promo Cards -->
	<div class="grid grid-cols-2 gap-2 mb-6">
		<div class="bg-gradient-to-br from-primary/20 to-primary/5 rounded-xl p-3 cursor-pointer hover:from-primary/30 transition border border-primary/10">
			<div class="text-xs font-semibold text-white">Daily Bonus</div>
			<div class="text-[10px] text-gray-400 mt-0.5">Claim now</div>
		</div>
		<div class="bg-gradient-to-br from-gold/20 to-gold/5 rounded-xl p-3 cursor-pointer hover:from-gold/30 transition border border-gold/10">
			<div class="text-xs font-semibold text-white">VIP</div>
			<div class="text-[10px] text-gray-400 mt-0.5">Rewards</div>
		</div>
		<div class="bg-gradient-to-br from-success/20 to-success/5 rounded-xl p-3 cursor-pointer hover:from-success/30 transition border border-success/10">
			<div class="text-xs font-semibold text-white">Free Bonus</div>
			<div class="text-[10px] text-gray-400 mt-0.5">Get ₹100</div>
		</div>
		<div class="bg-gradient-to-br from-info/20 to-info/5 rounded-xl p-3 cursor-pointer hover:from-info/30 transition border border-info/10">
			<div class="text-xs font-semibold text-white">Reward Hub</div>
			<div class="text-[10px] text-gray-400 mt-0.5">Spin & Win</div>
		</div>
	</div>

	<!-- Main Navigation -->
	<div class="mb-5">
		<div class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-2 px-1">Navigation</div>
		<nav class="space-y-0.5">
			<button
				v-for="item in mainNav"
				:key="item.label"
				@click="navigateTo(item.route)"
				:class="[
					'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
					isActive(item.match)
						? 'bg-primary/20 text-white shadow-lg shadow-primary/10 border border-primary/20'
						: 'text-gray-400 hover:text-white hover:bg-white/[0.04]'
				]">
				<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
					<path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.icon]"/>
				</svg>
				<span>{{ item.label }}</span>
			</button>
		</nav>
	</div>

	<!-- Casino Games -->
	<div class="mb-5">
		<div class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-2 px-1">Casino</div>
		<nav class="space-y-0.5">
			<button
				v-for="item in casinoNav"
				:key="item.label"
				@click="navigateTo(item.route)"
				class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/[0.04] transition-all duration-200">
				<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
					<path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.icon]"/>
				</svg>
				<span>{{ item.label }}</span>
			</button>
		</nav>
	</div>

	<!-- Account -->
	<div class="mb-5" v-if="user">
		<div class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-2 px-1">Account</div>
		<nav class="space-y-0.5">
			<button
				v-for="item in accountNav"
				:key="item.label"
				@click="navigateTo(item.route)"
				class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/[0.04] transition-all duration-200">
				<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
					<path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.icon]"/>
					<path v-if="icons[item.icon + '_inner']" stroke-linecap="round" stroke-linejoin="round" :d="icons[item.icon + '_inner']"/>
				</svg>
				<span>{{ item.label }}</span>
			</button>
		</nav>
	</div>
</div>
</template>
