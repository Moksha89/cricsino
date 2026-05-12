<script setup>
import UserLayout from "@/Layouts/UserLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Radio, ArrowLeft } from "lucide-vue-next";
import CasinoGameCard from "@/Components/Casino/CasinoGameCard.vue";
import CasinoEmptyState from "@/Components/Casino/CasinoEmptyState.vue";

defineProps({
	games: Object,
});
</script>

<template>
<Head :title="$t('Live Casino')" />
<UserLayout :showRightSidebar="false">
	<div class="p-4 lg:p-6 space-y-6 pb-24 lg:pb-6">

		<!-- Header -->
		<div class="flex items-center justify-between">
			<div class="flex items-center gap-3">
				<Link
					:href="route('casino.index')"
					class="w-9 h-9 rounded-xl bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition border border-white/[0.06]">
					<ArrowLeft class="w-4 h-4 text-gray-400" />
				</Link>
				<div>
					<div class="flex items-center gap-2">
						<h1 class="text-2xl font-bold text-white">Live Casino</h1>
						<span class="relative flex h-2.5 w-2.5">
							<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
							<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
						</span>
					</div>
					<p class="text-sm text-gray-500 mt-0.5">Real dealers, real-time games</p>
				</div>
			</div>
			<div class="flex items-center gap-1.5 text-red-400 text-xs font-semibold bg-red-600/10 px-3 py-1.5 rounded-full">
				<Radio class="w-3.5 h-3.5" />
				LIVE
			</div>
		</div>

		<!-- Banner -->
		<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-red-900/40 via-purple-900/40 to-indigo-900/40 p-6 border border-red-500/10">
			<div class="absolute inset-0 bg-gradient-to-r from-red-600/5 to-transparent"></div>
			<div class="relative z-10">
				<h2 class="text-white font-bold text-lg mb-1">Live Dealer Experience</h2>
				<p class="text-gray-400 text-sm">Play with real dealers in real time. Premium tables, immersive experience.</p>
			</div>
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

		<CasinoEmptyState
			v-else
			title="No live games available"
			description="Live casino games will be added soon. Try our House Originals in the meantime."
			:action-label="'Back to Casino'"
			:action-route="route('casino.index')" />
	</div>
</UserLayout>
</template>
