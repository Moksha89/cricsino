<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const currentUrl = computed(() => page.url);

const tabs = [
	{ name: "In-Play", href: "/sport/in-play", icon: "play" },
	{ name: "Cricket", href: "/sports/cricket", icon: "cricket" },
	{ name: "Football", href: "/sports/football", icon: "football" },
	{ name: "Tennis", href: "/sports/tennis", icon: "tennis" },
	{ name: "Basketball", href: "/sports/basketball", icon: "basketball" },
	{ name: "Watchlist", href: "/sport/watchlist", icon: "star" },
];

function isActive(href) {
	return currentUrl.value.startsWith(href);
}
</script>

<template>
	<nav class="flex items-center gap-1 overflow-x-auto scrollbar-hide pb-1 -mx-1 px-1">
		<Link
			v-for="tab in tabs"
			:key="tab.href"
			:href="tab.href"
			class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap transition-all duration-200 min-h-[44px]"
			:class="isActive(tab.href)
				? 'bg-primary text-white shadow-lg shadow-primary/25'
				: 'bg-surface-light text-gray-400 hover:text-white hover:bg-white/[0.08]'
			">
			<!-- In-Play -->
			<svg v-if="tab.icon === 'play'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<!-- Cricket -->
			<svg v-else-if="tab.icon === 'cricket'" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.5" fill="none"/>
				<line x1="7" y1="12" x2="17" y2="12" stroke="currentColor" stroke-width="1"/>
				<line x1="12" y1="7" x2="12" y2="17" stroke="currentColor" stroke-width="1"/>
			</svg>
			<!-- Football -->
			<svg v-else-if="tab.icon === 'football'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="9" stroke-width="2"/>
				<path stroke-width="1.5" d="M12 3l2 6h5l-4 3 2 6-5-4-5 4 2-6-4-3h5z"/>
			</svg>
			<!-- Tennis -->
			<svg v-else-if="tab.icon === 'tennis'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="9" stroke-width="2"/>
				<path stroke-width="1.5" d="M5 12c0-4 3-7 7-7M19 12c0 4-3 7-7 7"/>
			</svg>
			<!-- Basketball -->
			<svg v-else-if="tab.icon === 'basketball'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="9" stroke-width="2"/>
				<path stroke-width="1.5" d="M3 12h18M12 3v18"/>
			</svg>
			<!-- Star/Watchlist -->
			<svg v-else-if="tab.icon === 'star'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
			</svg>
			<span class="hidden sm:inline">{{ tab.name }}</span>
			<!-- Live badge for in-play -->
			<span v-if="tab.icon === 'play' && isActive(tab.href)" class="relative flex h-2 w-2">
				<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
				<span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
			</span>
		</Link>
	</nav>
</template>
