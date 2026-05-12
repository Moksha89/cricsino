<script setup>
	import { Head, Link, useForm } from "@inertiajs/vue3";
	import { ArrowLeft, UserPlus } from "lucide-vue-next";

	import InputError from "@/Components/InputError.vue";
	import PrimaryButton from "@/Components/PrimaryButton.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	const props = defineProps({
		users: Array,
		parentAgents: Array,
	});

	const form = useForm({
		user_id: "",
		parent_id: "",
		role: "agent",
		commission_rate: 5,
		credit_limit: 0,
		max_users: 100,
		max_sub_agents: 10,
		can_create_users: true,
		can_manage_bets: false,
		can_manage_deposits: false,
	});

	const submit = () => {
		form.post(window.route("admin.agents.store"), {
			preserveScroll: true,
		});
	};
</script>
<template>
	<Head title="Create Agent" />
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
								<h3 class="h3">Create Agent</h3>
							</div>
							<p class="ml-10">
								Add a new agent to the platform
							</p>
						</div>
					</div>

					<div
						class="card border-0 card-border max-w-3xl">
						<div class="card-body p-6">
							<form @submit.prevent="submit">
								<div
									class="grid grid-cols-1 md:grid-cols-2 gap-6">
									<!-- User Selection -->
									<div class="md:col-span-2">
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											User Account
										</label>
										<select
											v-model="form.user_id"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
											<option value="">
												Select a user...
											</option>
											<option
												v-for="user in users"
												:key="user.id"
												:value="user.id">
												{{ user.name }} ({{
													user.email
												}})
											</option>
										</select>
										<InputError
											class="mt-1"
											:message="form.errors.user_id" />
									</div>

									<!-- Role -->
									<div>
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											Role
										</label>
										<select
											v-model="form.role"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
											<option value="super_admin">
												Super Admin
											</option>
											<option value="master">
												Master
											</option>
											<option value="agent">
												Agent
											</option>
										</select>
										<InputError
											class="mt-1"
											:message="form.errors.role" />
									</div>

									<!-- Parent Agent -->
									<div>
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											Parent Agent (optional)
										</label>
										<select
											v-model="form.parent_id"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
											<option value="">
												No parent (top level)
											</option>
											<option
												v-for="agent in parentAgents"
												:key="agent.id"
												:value="agent.id">
												{{ agent.user?.name ?? agent.code }} ({{ agent.code }}) -
												{{ agent.role }}
											</option>
										</select>
										<InputError
											class="mt-1"
											:message="
												form.errors.parent_id
											" />
									</div>

									<!-- Commission Rate -->
									<div>
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											Commission Rate (%)
										</label>
										<input
											v-model="form.commission_rate"
											type="number"
											step="0.01"
											min="0"
											max="100"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
										<InputError
											class="mt-1"
											:message="
												form.errors.commission_rate
											" />
									</div>

									<!-- Credit Limit -->
									<div>
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											Credit Limit (INR)
										</label>
										<input
											v-model="form.credit_limit"
											type="number"
											step="0.01"
											min="0"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
										<InputError
											class="mt-1"
											:message="
												form.errors.credit_limit
											" />
									</div>

									<!-- Max Users -->
									<div>
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											Max Users
										</label>
										<input
											v-model="form.max_users"
											type="number"
											min="1"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
										<InputError
											class="mt-1"
											:message="
												form.errors.max_users
											" />
									</div>

									<!-- Max Sub-Agents -->
									<div>
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
											Max Sub-Agents
										</label>
										<input
											v-model="form.max_sub_agents"
											type="number"
											min="0"
											class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
										<InputError
											class="mt-1"
											:message="
												form.errors.max_sub_agents
											" />
									</div>

									<!-- Permissions -->
									<div class="md:col-span-2">
										<label
											class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
											Permissions
										</label>
										<div class="flex flex-wrap gap-6">
											<label
												class="inline-flex items-center gap-2 cursor-pointer">
												<input
													v-model="
														form.can_create_users
													"
													type="checkbox"
													class="rounded border-gray-300 dark:border-gray-600 text-emerald-600 shadow-sm focus:ring-emerald-500" />
												<span
													class="text-sm text-gray-700 dark:text-gray-300">
													Can Create Users
												</span>
											</label>
											<label
												class="inline-flex items-center gap-2 cursor-pointer">
												<input
													v-model="
														form.can_manage_bets
													"
													type="checkbox"
													class="rounded border-gray-300 dark:border-gray-600 text-emerald-600 shadow-sm focus:ring-emerald-500" />
												<span
													class="text-sm text-gray-700 dark:text-gray-300">
													Can Manage Bets
												</span>
											</label>
											<label
												class="inline-flex items-center gap-2 cursor-pointer">
												<input
													v-model="
														form.can_manage_deposits
													"
													type="checkbox"
													class="rounded border-gray-300 dark:border-gray-600 text-emerald-600 shadow-sm focus:ring-emerald-500" />
												<span
													class="text-sm text-gray-700 dark:text-gray-300">
													Can Manage Deposits
												</span>
											</label>
										</div>
									</div>
								</div>

								<div
									class="flex justify-end gap-3 mt-8 pt-6 border-t dark:border-gray-600">
									<Link
										:href="route('admin.agents.index')"
										class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
										Cancel
									</Link>
									<PrimaryButton
										:disabled="form.processing"
										class="bg-emerald-600 hover:bg-emerald-700">
										<UserPlus class="w-4 h-4 mr-2" />
										Create Agent
									</PrimaryButton>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</main>
	</AdminLayout>
</template>
