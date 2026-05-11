<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { Search, Users } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";
import EmptyState from "@/Components/User/EmptyState.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";

const props = defineProps({
	teams: Object,
});

const search = ref("");

function doSearch() {
	router.get(route("teamsindex"), { search: search.value }, { preserveState: true });
}
</script>

<template>
	<Head title="Teams" />
	<UserLayout>
		<div class="p-4 sm:p-6">
			<PageHeader title="Teams" subtitle="Browse teams across all sports" />

			<div class="flex flex-col sm:flex-row gap-3 mb-6">
				<form @submit.prevent="doSearch" class="flex-1 max-w-sm">
					<FormInput v-model="search" placeholder="Search teams..." @keyup.enter="doSearch">
						<template #lead>
							<Search class="w-4 h-4 text-gray-400" />
						</template>
					</FormInput>
				</form>
			</div>

			<EmptyState
				v-if="!teams.data?.length"
				title="No teams available"
				message="Teams will appear here once they are added to the system."
				icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />

			<template v-else>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
					<div
						v-for="team in teams.data"
						:key="team.id"
						class="bg-surface-light rounded-lg border border-white/10 p-4 hover:border-purple-500/30 transition-colors">
						<div class="flex items-start gap-3">
							<div class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
								<img
									v-if="team.image"
									:src="team.image"
									:alt="team.name"
									class="w-8 h-8 rounded object-cover" />
								<Users v-else class="w-5 h-5 text-purple-400" />
							</div>
							<div class="flex-1 min-w-0">
								<h3 class="text-white font-medium text-sm truncate">{{ team.name }}</h3>
								<div class="flex items-center gap-2 mt-1">
									<span v-if="team.sport" class="text-xs text-purple-400 capitalize">{{ team.sport }}</span>
									<span v-if="team.country" class="text-xs text-gray-500">{{ team.country }}</span>
								</div>
								<div v-if="team.code" class="text-xs text-gray-500 mt-1">Code: {{ team.code }}</div>
							</div>
						</div>
					</div>
				</div>
				<Pagination :meta="teams.meta" class="mt-4" />
			</template>
		</div>
	</UserLayout>
</template>
