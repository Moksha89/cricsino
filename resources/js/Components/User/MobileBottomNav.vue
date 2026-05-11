<script setup>
import { Link, router } from "@inertiajs/vue3";

function navigateTo(routeName) {
	try {
		router.visit(route(routeName));
	} catch(e) {}
}

function isActive(routeName) {
	try {
		return route().current(routeName);
	} catch(e) {
		return false;
	}
}

const navItems = [
	{ label: 'Home', route: 'dashboard', match: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
	{ label: 'Sports', route: 'games.index', match: 'games.*', icon: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z' },
	{ label: 'Casino', route: 'casino.index', match: 'casino.*', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z' },
	{ label: 'Wallet', route: 'deposits.create', match: 'deposits.*', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
	{ label: 'Profile', route: 'profile.edit', match: 'profile.*', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];
</script>

<template>
<nav class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-white/[0.06] z-50 px-2 py-1 safe-area-bottom">
	<div class="flex items-center justify-around">
		<button
			v-for="item in navItems"
			:key="item.label"
			@click="navigateTo(item.route)"
			class="flex flex-col items-center py-2 px-3 transition min-w-[56px]"
			:class="isActive(item.match) ? 'text-primary' : 'text-gray-400 hover:text-primary'">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
				<path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
			</svg>
			<span class="text-[10px] mt-0.5 font-medium">{{ item.label }}</span>
		</button>
	</div>
</nav>
</template>

<style scoped>
.safe-area-bottom {
	padding-bottom: max(4px, env(safe-area-inset-bottom));
}
</style>
