<script setup>
import { ref } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import { Search, Trophy } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";
import EmptyState from "@/Components/User/EmptyState.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";

const props = defineProps({
	leagues: Object,
});

const search = ref("");

function doSearch() {
	router.get(route("leaguesindex"), { search: search.value }, { preserveState: true });
}
</script>

<template>
	<Head title="Leagues" />
	<UserLayout>
		<div class="p-4 sm:p-6">
			<PageHeader title="Leagues" subtitle="Browse available leagues and competitions" />

			<div class="flex flex-col sm:flex-row gap-3 mb-6">
				<form @submit.prevent="doSearch" class="flex-1 max-w-sm">
					<FormInput v-model="search" placeholder="Search leagues..." @keyup.enter="doSearch">
						<template #lead>
							<Search class="w-4 h-4 text-gray-400" />
						</template>
					</FormInput>
				</form>
			</div>

			<EmptyState
				v-if="!leagues.data?.length"
				title="No leagues available"
				message="Leagues will appear here when competitions are active."
				icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />

			<template v-else>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
					<div
						v-for="league in leagues.data"
						:key="league.id"
						class="bg-surface-light rounded-lg border border-white/10 p-4 hover:border-purple-500/30 transition-colors">
						<div class="flex items-start gap-3">
							<div class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
								<img
									v-if="league.image"
									:src="league.image"
									:alt="league.name"
									class="w-8 h-8 rounded object-cover" />
								<Trophy v-else class="w-5 h-5 text-purple-400" />
							</div>
							<div class="flex-1 min-w-0">
								<h3 class="text-white font-medium text-sm truncate">{{ league.name }}</h3>
								<div class="flex items-center gap-2 mt-1">
									<span v-if="league.sport" class="text-xs text-purple-400 capitalize">{{ league.sport }}</span>
									<span v-if="league.country" class="text-xs text-gray-500">{{ league.country }}</span>
								</div>
								<div v-if="league.games_count" class="text-xs text-gray-400 mt-1">
									{{ league.games_count }} active game{{ league.games_count !== 1 ? 's' : '' }}
								</div>
								<div v-if="league.description" class="text-xs text-gray-500 mt-1 line-clamp-2">{{ league.description }}</div>
							</div>
						</div>
					</div>
				</div>
				<Pagination :meta="leagues.meta" class="mt-4" />
			</template>
		</div>
	</UserLayout>
</template>
