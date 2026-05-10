<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
	games: Object,
	categories: Object,
	providers: Object,
	selectedCategory: String,
	selectedProvider: String,
});

const selectedCat = ref(props.selectedCategory || "");
const selectedProv = ref(props.selectedProvider || "");

function filterGames() {
	const params = {};
	if (selectedCat.value) params.category = selectedCat.value;
	if (selectedProv.value) params.provider = selectedProv.value;
	router.get(route("casino.index"), params, { preserveState: true });
}

function clearFilters() {
	selectedCat.value = "";
	selectedProv.value = "";
	router.get(route("casino.index"));
}
</script>

<template>
	<Head :title="$t('Casino')" />
	<AuthenticatedLayout>
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
			<!-- Header -->
			<div class="flex items-center justify-between mb-6">
				<h1
					class="text-2xl font-bold text-gray-900 dark:text-white">
					{{ $t("Casino Games") }}
				</h1>
				<Link
					:href="route('casino.live')"
					class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center space-x-2">
					<span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
					<span>{{ $t("Live Casino") }}</span>
				</Link>
			</div>

			<!-- Category Tabs -->
			<div class="flex flex-wrap gap-2 mb-6">
				<button
					@click="selectedCat = ''; filterGames()"
					:class="[
						'px-4 py-2 rounded-full text-sm font-medium transition',
						!selectedCat
							? 'bg-blue-600 text-white'
							: 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
					]">
					{{ $t("All Games") }}
				</button>
				<button
					v-for="(label, key) in categories"
					:key="key"
					@click="selectedCat = key; filterGames()"
					:class="[
						'px-4 py-2 rounded-full text-sm font-medium transition',
						selectedCat === key
							? 'bg-blue-600 text-white'
							: 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
					]">
					{{ label }}
				</button>
			</div>

			<!-- Games Grid -->
			<div
				v-if="games.data && games.data.length > 0"
				class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
				<div
					v-for="game in games.data"
					:key="game.id"
					class="group relative rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 shadow-sm hover:shadow-lg transition-shadow">
					<div class="aspect-[3/4] relative">
						<img
							v-if="game.image"
							:src="game.image"
							:alt="game.name"
							class="w-full h-full object-cover" />
						<div
							v-else
							class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-600 to-blue-600">
							<span class="text-white text-lg font-bold">
								{{ game.name.charAt(0) }}
							</span>
						</div>
						<!-- Live Badge -->
						<span
							v-if="game.is_live"
							class="absolute top-2 left-2 px-2 py-0.5 bg-red-600 text-white text-xs rounded-full flex items-center">
							<span class="w-1.5 h-1.5 bg-white rounded-full mr-1 animate-pulse"></span>
							LIVE
						</span>
						<!-- Hover Overlay -->
						<div
							class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 flex items-center justify-center transition-all">
							<Link
								:href="route('casino.show', game.uuid)"
								class="opacity-0 group-hover:opacity-100 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium transition">
								{{ $t("Play Now") }}
							</Link>
						</div>
					</div>
					<div class="p-2">
						<h3
							class="text-sm font-medium text-gray-900 dark:text-white truncate">
							{{ game.name }}
						</h3>
						<p class="text-xs text-gray-500 dark:text-gray-400">
							{{ game.provider }}
						</p>
					</div>
				</div>
			</div>

			<!-- Empty State -->
			<div
				v-else
				class="text-center py-16 text-gray-500 dark:text-gray-400">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="h-16 w-16 mx-auto mb-4"
					fill="none"
					viewBox="0 0 24 24"
					stroke="currentColor">
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
				</svg>
				<p class="text-lg">{{ $t("No casino games available yet.") }}</p>
				<p class="text-sm mt-1">
					{{ $t("Casino games will be added by the admin.") }}
				</p>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
