<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import { Search, BarChart3 } from "lucide-vue-next";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";
import EmptyState from "@/Components/User/EmptyState.vue";
import StatusBadge from "@/Components/User/StatusBadge.vue";
import ResponsiveTableWrapper from "@/Components/User/ResponsiveTableWrapper.vue";
import Pagination from "@/Components/Pagination.vue";
import FormInput from "@/Components/FormInput.vue";

const props = defineProps({
	markets: Object,
});

const search = ref("");

function doSearch() {
	router.get(route("marketsindex"), { search: search.value }, { preserveState: true });
}
</script>

<template>
	<Head title="Markets" />
	<UserLayout>
		<div class="p-4 sm:p-6">
			<PageHeader title="Markets" subtitle="Browse available betting markets" />

			<div class="flex flex-col sm:flex-row gap-3 mb-6">
				<form @submit.prevent="doSearch" class="flex-1 max-w-sm">
					<FormInput v-model="search" placeholder="Search markets..." @keyup.enter="doSearch">
						<template #lead>
							<Search class="w-4 h-4 text-gray-400" />
						</template>
					</FormInput>
				</form>
			</div>

			<EmptyState
				v-if="!markets.data?.length"
				title="No markets available"
				message="Betting markets will appear here when they are active."
				icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />

			<template v-else>
				<!-- Desktop table -->
				<div class="hidden md:block">
					<ResponsiveTableWrapper>
						<table class="min-w-full">
							<thead>
								<tr class="border-b border-white/10">
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Market</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Sport</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Description</th>
									<th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-white/5">
								<tr v-for="market in markets.data" :key="market.id" class="hover:bg-white/5 transition-colors">
									<td class="px-4 py-3">
										<div class="flex items-center gap-2">
											<BarChart3 class="w-4 h-4 text-purple-400 flex-shrink-0" />
											<span class="text-sm text-white font-medium">{{ market.name }}</span>
										</div>
									</td>
									<td class="px-4 py-3 text-sm text-purple-400 capitalize">{{ market.sport || '—' }}</td>
									<td class="px-4 py-3 text-sm text-gray-400 capitalize">{{ market.category || '—' }}</td>
									<td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ market.description || '—' }}</td>
									<td class="px-4 py-3 text-center">
										<StatusBadge :status="market.active ? 'active' : 'inactive'" />
									</td>
								</tr>
							</tbody>
						</table>
					</ResponsiveTableWrapper>
				</div>

				<!-- Mobile cards -->
				<div class="md:hidden space-y-3">
					<div
						v-for="market in markets.data"
						:key="market.id"
						class="bg-surface-light rounded-lg border border-white/10 p-4">
						<div class="flex items-center justify-between mb-2">
							<div class="flex items-center gap-2">
								<BarChart3 class="w-4 h-4 text-purple-400" />
								<span class="text-sm text-white font-medium">{{ market.name }}</span>
							</div>
							<StatusBadge :status="market.active ? 'active' : 'inactive'" />
						</div>
						<div class="flex items-center gap-2 mt-1">
							<span v-if="market.sport" class="text-xs text-purple-400 capitalize">{{ market.sport }}</span>
							<span v-if="market.category" class="text-xs text-gray-500 capitalize">{{ market.category }}</span>
						</div>
						<div v-if="market.description" class="text-xs text-gray-500 mt-2 line-clamp-2">{{ market.description }}</div>
					</div>
				</div>

				<Pagination :meta="markets.meta" class="mt-4" />
			</template>
		</div>
	</UserLayout>
</template>
