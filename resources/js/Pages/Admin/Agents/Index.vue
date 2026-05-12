<script setup>
	import { ref } from "vue";

	import { Head, Link, router as Inertia, useForm } from "@inertiajs/vue3";
	import { debouncedWatch, useUrlSearchParams } from "@vueuse/core";
	import {
		Plus,
		Users,
		Eye,
		Pencil,
		Trash2,
		GitBranch,
		ArrowUpDown,
	} from "lucide-vue-next";

	import ConfirmationModal from "@/Components/ConfirmationModal.vue";
	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import NoItems from "@/Components/NoItems.vue";
	import Pagination from "@/Components/Pagination.vue";
	import PrimaryButton from "@/Components/PrimaryButton.vue";
	import SearchInput from "@/Components/SearchInput.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	defineProps({
		agents: Object,
	});

	const params = useUrlSearchParams("history");
	const search = ref(params.search ?? "");
	const deleteForm = useForm({});
	const toggleForm = useForm({});
	const agentBeingDeleted = ref(null);

	const deleteAgent = () => {
		deleteForm.delete(
			window.route("admin.agents.destroy", agentBeingDeleted.value),
			{
				preserveScroll: true,
				preserveState: true,
				onSuccess: () => (agentBeingDeleted.value = null),
			},
		);
	};

	const toggleAgent = (agent) => {
		toggleForm.put(
			window.route("admin.agents.toggle", agent.id),
			{
				preserveScroll: true,
				preserveState: true,
			},
		);
	};

	debouncedWatch(
		[search],
		([search]) => {
			Inertia.get(
				window.route("admin.agents.index"),
				{ ...(!search ? {} : { search }) },
				{
					preserveState: true,
					preserveScroll: true,
				},
			);
		},
		{
			maxWait: 700,
		},
	);

	const roleBadge = (role) => {
		const map = {
			super_admin:
				"bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
			master:
				"bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
			agent: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
		};
		return map[role] || "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200";
	};

	const roleLabel = (role) => {
		const map = {
			super_admin: "Super Admin",
			master: "Master",
			agent: "Agent",
		};
		return map[role] || role;
	};
</script>
<template>
	<Head title="Agents" />
	<AdminLayout>
		<main class="h-full container">
			<div
				class="relative h-full flex flex-auto flex-col px-4 sm:px-6 py-12 sm:py-6 md:px-8">
				<div class="flex flex-col gap-4 h-full">
					<div
						class="lg:flex items-center justify-between mb-4 gap-3">
						<div class="mb-4 lg:mb-0">
							<h3 class="h3">Agents</h3>
							<p>Manage agents, masters, and sub-agents</p>
						</div>
						<div class="flex gap-3">
							<Link
								:href="route('admin.agents.hierarchy')"
								class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-medium transition">
								<GitBranch class="w-4 h-4" />
								Hierarchy
							</Link>
							<Link
								:href="route('admin.agents.create')"
								class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-sm font-medium transition">
								<Plus class="w-4 h-4" />
								Add Agent
							</Link>
						</div>
					</div>
					<div class="card border-0 card-border">
						<div class="card-body px-0 card-gutterless h-full">
							<div
								class="lg:flex items-center justify-between mb-4 px-6">
								<h3 class="mb-4 lg:mb-0"></h3>
								<div class="flex justify-end gap-x-3 w-1/2">
									<SearchInput
										class="max-w-xs"
										v-model="search" />
								</div>
							</div>
							<NoItems
								v-if="agents.data.length == 0"
								class="border-t dark:border-gray-600">
								No Agents Found
							</NoItems>
							<div v-else>
								<div class="overflow-x-auto">
									<table
										class="table-default table-hover"
										role="table">
										<thead>
											<tr role="row">
												<th role="columnheader">
													Agent
												</th>
												<th role="columnheader">
													Code
												</th>
												<th role="columnheader">
													Role
												</th>
												<th role="columnheader">
													Parent
												</th>
												<th role="columnheader">
													Balance
												</th>
												<th role="columnheader">
													Credit Limit
												</th>
												<th role="columnheader">
													Commission
												</th>
												<th role="columnheader">
													Sub-Agents
												</th>
												<th role="columnheader">
													Status
												</th>
												<th role="columnheader">
													Created
												</th>
												<td role="columnheader"></td>
											</tr>
										</thead>

										<tbody>
											<tr
												v-for="agent in agents.data"
												:key="agent.id"
												role="row">
												<td role="cell">
													<div class="flex flex-col">
														<Link
															class="underline font-medium"
															:href="
																route(
																	'admin.agents.show',
																	agent.id,
																)
															">
															{{
																agent.user
																	?.name ??
																"Unknown"
															}}
														</Link>
														<span
															class="text-xs text-gray-500">
															{{
																agent.user
																	?.email ??
																""
															}}
														</span>
													</div>
												</td>
												<td role="cell">
													<code
														class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-sm font-mono">
														{{ agent.code }}
													</code>
												</td>
												<td role="cell">
													<span
														class="px-2 py-1 text-xs font-semibold rounded-full"
														:class="
															roleBadge(
																agent.role,
															)
														">
														{{
															roleLabel(
																agent.role,
															)
														}}
													</span>
												</td>
												<td role="cell">
													<template
														v-if="agent.parent">
														<Link
															class="underline text-sm"
															:href="
																route(
																	'admin.agents.show',
																	agent
																		.parent
																		.id,
																)
															">
															{{
																agent.parent
																	.user
																	?.name ??
																agent.parent
																	.code
															}}
														</Link>
													</template>
													<span
														v-else
														class="text-gray-400"
														>&#8212;</span
													>
												</td>
												<td role="cell">
													<MoneyFormat
														:amount="
															agent.balance
														" />
												</td>
												<td role="cell">
													<MoneyFormat
														:amount="
															agent.credit_limit
														" />
												</td>
												<td role="cell">
													{{
														agent.commission_rate
													}}%
												</td>
												<td role="cell">
													{{
														agent.children
															?.length ?? 0
													}}
												</td>
												<td role="cell">
													<label
														class="inline-flex items-center space-x-2">
														<input
															@change="
																toggleAgent(
																	agent,
																)
															"
															v-model="
																agent.active
															"
															class="form-switch h-5 w-10 rounded-full bg-red-500 before:rounded-full before:bg-gray-50 checked:!bg-green-500 checked:before:bg-white dark:bg-red-500 dark:before:bg-navy-300 dark:checked:before:bg-white"
															type="checkbox" />
														<span
															class="text-green-500"
															v-if="agent.active">
															Active
														</span>
														<span
															class="text-red-500"
															v-else>
															Inactive
														</span>
													</label>
												</td>
												<td role="cell">
													{{
														new Date(
															agent.created_at,
														).toLocaleDateString()
													}}
												</td>
												<td role="cell">
													<div
														class="flex items-center gap-2">
														<Link
															:href="
																route(
																	'admin.agents.show',
																	agent.id,
																)
															"
															class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-emerald-600"
															v-tippy="'View'">
															<Eye
																class="w-4 h-4" />
														</Link>
														<button
															v-if="
																agent.balance ==
																0
															"
															@click="
																agentBeingDeleted =
																	agent.id
															"
															class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-red-600"
															v-tippy="'Delete'">
															<Trash2
																class="w-4 h-4" />
														</button>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
								<Pagination :meta="agents" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<ConfirmationModal
			:show="agentBeingDeleted !== null"
			@close="agentBeingDeleted = null">
			<template #title> Delete Agent </template>
			<template #content>
				Are you sure you want to delete this agent? This action cannot be
				undone.
			</template>
			<template #footer>
				<button
					class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md mr-3"
					@click="agentBeingDeleted = null">
					Cancel
				</button>
				<button
					class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md"
					:disabled="deleteForm.processing"
					@click="deleteAgent">
					Delete Agent
				</button>
			</template>
		</ConfirmationModal>
	</AdminLayout>
</template>
