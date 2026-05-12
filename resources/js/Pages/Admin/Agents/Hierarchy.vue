<script setup>
	import { Head, Link } from "@inertiajs/vue3";
	import {
		ArrowLeft,
		GitBranch,
		Users,
		ChevronRight,
	} from "lucide-vue-next";

	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import NoItems from "@/Components/NoItems.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	defineProps({
		agents: Array,
	});

	const roleBadge = (role) => {
		const map = {
			super_admin:
				"bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
			master:
				"bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
			agent: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
		};
		return map[role] || "bg-gray-100 text-gray-800";
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
	<Head title="Agent Hierarchy" />
	<AdminLayout>
		<main class="h-full container">
			<div
				class="relative h-full flex flex-auto flex-col px-4 sm:px-6 py-12 sm:py-6 md:px-8">
				<div class="flex flex-col gap-4 h-full">
					<div class="flex items-center justify-between mb-4">
						<div>
							<div class="flex items-center gap-3 mb-1">
								<Link
									:href="route('admin.agents.index')"
									class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
									<ArrowLeft class="w-5 h-5" />
								</Link>
								<GitBranch
									class="w-6 h-6 text-purple-500" />
								<h3 class="h3">Agent Hierarchy</h3>
							</div>
							<p class="ml-10">
								View parent-child relationships between agents
							</p>
						</div>
					</div>

					<NoItems v-if="!agents || agents.length === 0">
						No top-level agents found. Create an agent to get
						started.
					</NoItems>

					<div v-else class="space-y-4">
						<!-- Top-level agents -->
						<div
							v-for="agent in agents"
							:key="agent.id"
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 overflow-hidden">
							<!-- Top-level agent -->
							<div
								class="flex items-center justify-between p-4 border-b dark:border-gray-700">
								<div class="flex items-center gap-3">
									<div
										class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
										<Users
											class="w-5 h-5 text-purple-600 dark:text-purple-400" />
									</div>
									<div>
										<Link
											:href="
												route(
													'admin.agents.show',
													agent.id,
												)
											"
											class="font-semibold text-gray-900 dark:text-white hover:underline">
											{{
												agent.user?.name ??
												agent.code
											}}
										</Link>
										<div
											class="flex items-center gap-2 mt-0.5">
											<code
												class="text-xs text-gray-500 font-mono">
												{{ agent.code }}
											</code>
											<span
												class="px-1.5 py-0.5 text-xs font-semibold rounded-full"
												:class="
													roleBadge(agent.role)
												">
												{{ roleLabel(agent.role) }}
											</span>
											<span
												class="px-1.5 py-0.5 text-xs font-semibold rounded-full"
												:class="
													agent.active
														? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
														: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
												">
												{{
													agent.active
														? "Active"
														: "Inactive"
												}}
											</span>
										</div>
									</div>
								</div>
								<div class="text-right">
									<p
										class="font-semibold text-gray-900 dark:text-white">
										<MoneyFormat
											:amount="agent.balance" />
									</p>
									<p
										class="text-xs text-gray-500 dark:text-gray-400">
										{{ agent.commission_rate }}%
										commission
									</p>
								</div>
							</div>

							<!-- Children (Level 1) -->
							<div
								v-if="
									agent.children &&
									agent.children.length > 0
								">
								<div
									v-for="child in agent.children"
									:key="child.id"
									class="border-b dark:border-gray-700 last:border-b-0">
									<div
										class="flex items-center justify-between p-4 pl-12 bg-gray-50 dark:bg-gray-800/50">
										<div
											class="flex items-center gap-3">
											<ChevronRight
												class="w-4 h-4 text-gray-400" />
											<div
												class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
												<Users
													class="w-4 h-4 text-blue-600 dark:text-blue-400" />
											</div>
											<div>
												<Link
													:href="
														route(
															'admin.agents.show',
															child.id,
														)
													"
													class="font-medium text-gray-900 dark:text-white hover:underline">
													{{
														child.user?.name ??
														child.code
													}}
												</Link>
												<div
													class="flex items-center gap-2 mt-0.5">
													<code
														class="text-xs text-gray-500 font-mono">
														{{ child.code }}
													</code>
													<span
														class="px-1.5 py-0.5 text-xs font-semibold rounded-full"
														:class="
															roleBadge(
																child.role,
															)
														">
														{{
															roleLabel(
																child.role,
															)
														}}
													</span>
												</div>
											</div>
										</div>
										<div class="text-right">
											<p
												class="font-medium text-gray-900 dark:text-white">
												<MoneyFormat
													:amount="
														child.balance
													" />
											</p>
											<p
												class="text-xs text-gray-500">
												{{
													child.commission_rate
												}}%
											</p>
										</div>
									</div>

									<!-- Grandchildren (Level 2) -->
									<div
										v-if="
											child.children &&
											child.children.length > 0
										">
										<div
											v-for="grandchild in child.children"
											:key="grandchild.id"
											class="flex items-center justify-between p-3 pl-24 bg-gray-100/50 dark:bg-gray-900/30 border-t dark:border-gray-700">
											<div
												class="flex items-center gap-3">
												<ChevronRight
													class="w-3 h-3 text-gray-400" />
												<div
													class="w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
													<Users
														class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
												</div>
												<div>
													<Link
														:href="
															route(
																'admin.agents.show',
																grandchild.id,
															)
														"
														class="font-medium text-sm text-gray-900 dark:text-white hover:underline">
														{{
															grandchild
																.user
																?.name ??
															grandchild.code
														}}
													</Link>
													<div
														class="flex items-center gap-2 mt-0.5">
														<code
															class="text-xs text-gray-500 font-mono">
															{{
																grandchild.code
															}}
														</code>
														<span
															class="px-1.5 py-0.5 text-xs font-semibold rounded-full"
															:class="
																roleBadge(
																	grandchild.role,
																)
															">
															{{
																roleLabel(
																	grandchild.role,
																)
															}}
														</span>
													</div>
												</div>
											</div>
											<div class="text-right">
												<p
													class="font-medium text-sm text-gray-900 dark:text-white">
													<MoneyFormat
														:amount="
															grandchild.balance
														" />
												</p>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div
								v-else
								class="p-4 pl-12 text-sm text-gray-500 dark:text-gray-400 italic">
								No sub-agents
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
	</AdminLayout>
</template>
