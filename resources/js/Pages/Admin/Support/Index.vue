<script setup>
	import { ref } from "vue";
	import { Head, Link, router as Inertia } from "@inertiajs/vue3";
	import { debouncedWatch, useUrlSearchParams } from "@vueuse/core";
	import { MessageSquare, Eye, Filter } from "lucide-vue-next";

	import NoItems from "@/Components/NoItems.vue";
	import Pagination from "@/Components/Pagination.vue";
	import SearchInput from "@/Components/SearchInput.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	const props = defineProps({
		conversations: Object,
		filters: Object,
		categories: Array,
		statuses: Array,
	});

	const params = useUrlSearchParams("history");
	const search = ref(params.search ?? "");
	const selectedStatus = ref(params.status ?? "");
	const selectedCategory = ref(params.category ?? "");

	function applyFilters() {
		const p = {};
		if (search.value) p.search = search.value;
		if (selectedStatus.value) p.status = selectedStatus.value;
		if (selectedCategory.value) p.category = selectedCategory.value;
		Inertia.get(window.route("admin.support.index"), p, { preserveState: true });
	}

	debouncedWatch([search], applyFilters, { debounce: 300 });

	const statusColors = {
		open: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
		pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
		answered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
		closed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
	};

	const categoryLabels = {
		deposit: 'Deposit', withdrawal: 'Withdrawal', betting: 'Betting', casino: 'Casino',
		account: 'Account', kyc: 'KYC', technical: 'Technical', other: 'Other',
	};

	function formatDate(date) {
		if (!date) return '-';
		return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
	}

	function timeAgo(date) {
		if (!date) return '-';
		const seconds = Math.floor((new Date() - new Date(date)) / 1000);
		if (seconds < 60) return 'just now';
		const minutes = Math.floor(seconds / 60);
		if (minutes < 60) return `${minutes}m ago`;
		const hours = Math.floor(minutes / 60);
		if (hours < 24) return `${hours}h ago`;
		const days = Math.floor(hours / 24);
		return `${days}d ago`;
	}
</script>

<template>
	<AdminLayout>
		<Head title="Support Tickets" />
		<div class="p-4 bg-white dark:bg-gray-800 block sm:flex items-center justify-between border-b border-gray-200 dark:border-gray-700 lg:mt-1.5">
			<div class="mb-1 w-full">
				<div class="mb-4">
					<h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Support Tickets</h1>
				</div>
				<div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
					<SearchInput v-model="search" placeholder="Search by ticket ID, subject, or user..." />
					<select
						v-model="selectedStatus"
						@change="applyFilters"
						class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
						<option value="">All Statuses</option>
						<option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
					</select>
					<select
						v-model="selectedCategory"
						@change="applyFilters"
						class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
						<option value="">All Categories</option>
						<option v-for="c in categories" :key="c" :value="c">{{ categoryLabels[c] || c }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="flex flex-col">
			<div class="overflow-x-auto">
				<div class="inline-block min-w-full align-middle">
					<div class="overflow-hidden shadow">
						<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 table-fixed">
							<thead class="bg-gray-100 dark:bg-gray-700">
								<tr>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">ID</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">User</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Subject</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Category</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Status</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Messages</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Last Message</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Created</th>
									<th class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Actions</th>
								</tr>
							</thead>
							<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
								<tr v-for="conv in conversations?.data" :key="conv.id" class="hover:bg-gray-100 dark:hover:bg-gray-700">
									<td class="p-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">#{{ conv.id }}</td>
									<td class="p-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
										<div>{{ conv.user?.name || 'Unknown' }}</div>
										<div class="text-xs text-gray-400">{{ conv.user?.email || '' }}</div>
									</td>
									<td class="p-4 text-sm text-gray-900 dark:text-white max-w-[200px] truncate">{{ conv.subject }}</td>
									<td class="p-4 whitespace-nowrap">
										<span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 capitalize">
											{{ categoryLabels[conv.category] || conv.category }}
										</span>
									</td>
									<td class="p-4 whitespace-nowrap">
										<span
											class="px-2.5 py-0.5 text-xs font-medium rounded-full capitalize"
											:class="statusColors[conv.status] || 'bg-gray-100 text-gray-800'">
											{{ conv.status }}
										</span>
									</td>
									<td class="p-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ conv.messages_count }}</td>
									<td class="p-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ timeAgo(conv.last_message_at) }}</td>
									<td class="p-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ formatDate(conv.created_at) }}</td>
									<td class="p-4 whitespace-nowrap">
										<Link
											:href="window.route('admin.support.show', conv.id)"
											class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
											<Eye class="w-3.5 h-3.5" />
											View
										</Link>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<NoItems v-if="!conversations?.data?.length" title="No support tickets" message="No support tickets found matching your filters." />

		<Pagination :meta="conversations" />
	</AdminLayout>
</template>
