<script setup>
	import { ref } from "vue";

	import { Head, Link, useForm, router as Inertia } from "@inertiajs/vue3";
	import {
		ArrowLeft,
		Users,
		CreditCard,
		Wallet,
		TrendingUp,
		Shield,
		GitBranch,
		Pencil,
		Trash2,
		Plus,
		Minus,
		AlertTriangle,
		Check,
		X,
	} from "lucide-vue-next";

	import ConfirmationModal from "@/Components/ConfirmationModal.vue";
	import InputError from "@/Components/InputError.vue";
	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import PrimaryButton from "@/Components/PrimaryButton.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	const props = defineProps({
		agent: Object,
		stats: Object,
	});

	const showCreditModal = ref(false);
	const showDebitModal = ref(false);
	const showDeleteModal = ref(false);
	const isEditing = ref(false);

	const creditForm = useForm({
		amount: "",
		description: "",
	});

	const debitForm = useForm({
		amount: "",
		description: "",
	});

	const editForm = useForm({
		commission_rate: props.agent.commission_rate,
		credit_limit: props.agent.credit_limit,
		max_users: props.agent.max_users,
		max_sub_agents: props.agent.max_sub_agents,
		active: props.agent.active,
		can_create_users: props.agent.can_create_users,
		can_manage_bets: props.agent.can_manage_bets,
		can_manage_deposits: props.agent.can_manage_deposits,
	});

	const deleteForm = useForm({});
	const toggleForm = useForm({});

	const submitCredit = () => {
		creditForm.post(
			window.route("admin.agents.credit", props.agent.id),
			{
				preserveScroll: true,
				onSuccess: () => {
					showCreditModal.value = false;
					creditForm.reset();
				},
			},
		);
	};

	const submitDebit = () => {
		debitForm.post(
			window.route("admin.agents.debit", props.agent.id),
			{
				preserveScroll: true,
				onSuccess: () => {
					showDebitModal.value = false;
					debitForm.reset();
				},
			},
		);
	};

	const submitEdit = () => {
		editForm.put(
			window.route("admin.agents.update", props.agent.id),
			{
				preserveScroll: true,
				onSuccess: () => (isEditing.value = false),
			},
		);
	};

	const toggleAgent = () => {
		toggleForm.put(
			window.route("admin.agents.toggle", props.agent.id),
			{ preserveScroll: true },
		);
	};

	const deleteAgent = () => {
		deleteForm.delete(
			window.route("admin.agents.destroy", props.agent.id),
			{
				onSuccess: () => (showDeleteModal.value = false),
			},
		);
	};

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

	const txTypeBadge = (type) => {
		const map = {
			credit: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
			debit: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
			commission:
				"bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
			settlement:
				"bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
		};
		return map[type] || "bg-gray-100 text-gray-800";
	};
</script>
<template>
	<Head :title="`Agent: ${agent.user?.name ?? agent.code}`" />
	<AdminLayout>
		<main class="h-full container">
			<div
				class="relative h-full flex flex-auto flex-col px-4 sm:px-6 py-12 sm:py-6 md:px-8">
				<div class="flex flex-col gap-6">
					<!-- Header -->
					<div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
						<div>
							<div class="flex items-center gap-3 mb-1">
								<Link
									:href="route('admin.agents.index')"
									class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
									<ArrowLeft class="w-5 h-5" />
								</Link>
								<h3 class="h3">
									{{ agent.user?.name ?? "Agent" }}
								</h3>
								<span
									class="px-2 py-1 text-xs font-semibold rounded-full"
									:class="roleBadge(agent.role)">
									{{ roleLabel(agent.role) }}
								</span>
								<span
									class="px-2 py-1 text-xs font-semibold rounded-full"
									:class="
										agent.active
											? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
											: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
									">
									{{ agent.active ? "Active" : "Inactive" }}
								</span>
							</div>
							<p class="ml-10 text-gray-500">
								{{ agent.user?.email }} | Code:
								<code
									class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded font-mono">
									{{ agent.code }}
								</code>
							</p>
						</div>
						<div class="flex gap-2 flex-wrap">
							<button
								@click="showCreditModal = true"
								class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition">
								<Plus class="w-4 h-4" />
								Credit
							</button>
							<button
								@click="showDebitModal = true"
								class="inline-flex items-center gap-1.5 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm font-medium transition">
								<Minus class="w-4 h-4" />
								Debit
							</button>
							<button
								@click="toggleAgent"
								:disabled="toggleForm.processing"
								class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition"
								:class="
									agent.active
										? 'bg-yellow-600 hover:bg-yellow-700 text-white'
										: 'bg-emerald-600 hover:bg-emerald-700 text-white'
								">
								{{ agent.active ? "Deactivate" : "Activate" }}
							</button>
							<button
								@click="isEditing = !isEditing"
								class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition">
								<Pencil class="w-4 h-4" />
								{{ isEditing ? "Cancel Edit" : "Edit" }}
							</button>
							<button
								v-if="agent.balance == 0"
								@click="showDeleteModal = true"
								class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition">
								<Trash2 class="w-4 h-4" />
								Delete
							</button>
						</div>
					</div>

					<!-- Stats Grid -->
					<div
						class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-5">
							<div
								class="flex items-center justify-between mb-2">
								<span
									class="text-sm text-gray-500 dark:text-gray-400">
									Balance
								</span>
								<Wallet
									class="w-5 h-5 text-emerald-500" />
							</div>
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								<MoneyFormat :amount="agent.balance" />
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-5">
							<div
								class="flex items-center justify-between mb-2">
								<span
									class="text-sm text-gray-500 dark:text-gray-400">
									Credit Limit
								</span>
								<CreditCard
									class="w-5 h-5 text-blue-500" />
							</div>
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								<MoneyFormat :amount="agent.credit_limit" />
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-5">
							<div
								class="flex items-center justify-between mb-2">
								<span
									class="text-sm text-gray-500 dark:text-gray-400">
									Commission Rate
								</span>
								<TrendingUp
									class="w-5 h-5 text-purple-500" />
							</div>
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								{{ agent.commission_rate }}%
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-5">
							<div
								class="flex items-center justify-between mb-2">
								<span
									class="text-sm text-gray-500 dark:text-gray-400">
									Exposure
								</span>
								<Shield
									class="w-5 h-5 text-orange-500" />
							</div>
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								<MoneyFormat :amount="agent.exposure" />
							</p>
						</div>
					</div>

					<!-- Summary Stats -->
					<div
						class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-4 text-center">
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								{{ stats.total_users }}
							</p>
							<p
								class="text-xs text-gray-500 dark:text-gray-400">
								Total Users
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-4 text-center">
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								{{ stats.direct_users }}
							</p>
							<p
								class="text-xs text-gray-500 dark:text-gray-400">
								Direct Users
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-4 text-center">
							<p
								class="text-2xl font-bold text-gray-900 dark:text-white">
								{{ stats.sub_agents }}
							</p>
							<p
								class="text-xs text-gray-500 dark:text-gray-400">
								Sub-Agents
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-4 text-center">
							<p
								class="text-2xl font-bold text-green-600">
								<MoneyFormat
									:amount="stats.total_credited" />
							</p>
							<p
								class="text-xs text-gray-500 dark:text-gray-400">
								Total Credited
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-4 text-center">
							<p
								class="text-2xl font-bold text-red-600">
								<MoneyFormat
									:amount="stats.total_debited" />
							</p>
							<p
								class="text-xs text-gray-500 dark:text-gray-400">
								Total Debited
							</p>
						</div>
						<div
							class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-4 text-center">
							<p
								class="text-2xl font-bold text-purple-600">
								<MoneyFormat
									:amount="stats.total_commission" />
							</p>
							<p
								class="text-xs text-gray-500 dark:text-gray-400">
								Commission Earned
							</p>
						</div>
					</div>

					<!-- Edit Form -->
					<div
						v-if="isEditing"
						class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-6">
						<h4
							class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
							Edit Agent Settings
						</h4>
						<form @submit.prevent="submitEdit">
							<div
								class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div>
									<label
										class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
										Commission Rate (%)
									</label>
									<input
										v-model="editForm.commission_rate"
										type="number"
										step="0.01"
										min="0"
										max="100"
										class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
									<InputError
										class="mt-1"
										:message="
											editForm.errors.commission_rate
										" />
								</div>
								<div>
									<label
										class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
										Credit Limit (INR)
									</label>
									<input
										v-model="editForm.credit_limit"
										type="number"
										step="0.01"
										min="0"
										class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
									<InputError
										class="mt-1"
										:message="
											editForm.errors.credit_limit
										" />
								</div>
								<div>
									<label
										class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
										Max Users
									</label>
									<input
										v-model="editForm.max_users"
										type="number"
										min="1"
										class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
									<InputError
										class="mt-1"
										:message="editForm.errors.max_users" />
								</div>
								<div>
									<label
										class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
										Max Sub-Agents
									</label>
									<input
										v-model="editForm.max_sub_agents"
										type="number"
										min="0"
										class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
									<InputError
										class="mt-1"
										:message="
											editForm.errors.max_sub_agents
										" />
								</div>
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
													editForm.can_create_users
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
													editForm.can_manage_bets
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
													editForm.can_manage_deposits
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
								class="flex justify-end gap-3 mt-6 pt-4 border-t dark:border-gray-600">
								<button
									type="button"
									@click="isEditing = false"
									class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
									Cancel
								</button>
								<PrimaryButton
									:disabled="editForm.processing"
									class="bg-emerald-600 hover:bg-emerald-700">
									Save Changes
								</PrimaryButton>
							</div>
						</form>
					</div>

					<!-- Hierarchy -->
					<div
						v-if="
							agent.parent || (agent.children && agent.children.length > 0)
						"
						class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-6">
						<div class="flex items-center gap-2 mb-4">
							<GitBranch
								class="w-5 h-5 text-purple-500" />
							<h4
								class="text-lg font-semibold text-gray-900 dark:text-white">
								Agent Hierarchy
							</h4>
						</div>
						<div class="space-y-3">
							<div v-if="agent.parent" class="flex items-center gap-3">
								<span
									class="text-xs uppercase tracking-wider text-gray-500 w-16">
									Parent
								</span>
								<Link
									:href="
										route(
											'admin.agents.show',
											agent.parent.id,
										)
									"
									class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900/50">
									<span class="font-medium text-blue-700 dark:text-blue-300">
										{{ agent.parent.user?.name ?? agent.parent.code }}
									</span>
									<code
										class="text-xs text-blue-500">
										{{ agent.parent.code }}
									</code>
								</Link>
							</div>
							<div class="flex items-center gap-3">
								<span
									class="text-xs uppercase tracking-wider text-gray-500 w-16">
									Current
								</span>
								<span
									class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-md font-medium text-emerald-700 dark:text-emerald-300">
									{{ agent.user?.name ?? agent.code }}
									<code class="text-xs text-emerald-500">
										{{ agent.code }}
									</code>
								</span>
							</div>
							<div
								v-if="agent.children && agent.children.length > 0">
								<div class="flex items-center gap-3 mb-2">
									<span
										class="text-xs uppercase tracking-wider text-gray-500 w-16">
										Children
									</span>
								</div>
								<div
									class="ml-[76px] flex flex-wrap gap-2">
									<Link
										v-for="child in agent.children"
										:key="child.id"
										:href="
											route(
												'admin.agents.show',
												child.id,
											)
										"
										class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600">
										<span
											class="font-medium text-gray-700 dark:text-gray-300">
											{{ child.user?.name ?? child.code }}
										</span>
										<code
											class="text-xs text-gray-500">
											{{ child.code }}
										</code>
									</Link>
								</div>
							</div>
						</div>
					</div>

					<!-- Users -->
					<div
						v-if="agent.users && agent.users.length > 0"
						class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-6">
						<div class="flex items-center gap-2 mb-4">
							<Users class="w-5 h-5 text-blue-500" />
							<h4
								class="text-lg font-semibold text-gray-900 dark:text-white">
								Assigned Users ({{ agent.users.length }})
							</h4>
						</div>
						<div class="overflow-x-auto">
							<table class="table-default table-hover">
								<thead>
									<tr>
										<th>User</th>
										<th>Email</th>
										<th>Joined</th>
									</tr>
								</thead>
								<tbody>
									<tr
										v-for="user in agent.users"
										:key="user.id">
										<td>{{ user.name }}</td>
										<td>{{ user.email }}</td>
										<td>
											{{
												new Date(
													user.created_at,
												).toLocaleDateString()
											}}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Transaction History -->
					<div
						class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-600 p-6">
						<h4
							class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
							Recent Transactions
						</h4>
						<div
							v-if="
								!agent.transactions ||
								agent.transactions.length === 0
							"
							class="text-center py-8 text-gray-500 dark:text-gray-400">
							No transactions yet
						</div>
						<div v-else class="overflow-x-auto">
							<table class="table-default table-hover">
								<thead>
									<tr>
										<th>Date</th>
										<th>Type</th>
										<th>Amount</th>
										<th>Before</th>
										<th>After</th>
										<th>Description</th>
									</tr>
								</thead>
								<tbody>
									<tr
										v-for="tx in agent.transactions"
										:key="tx.id">
										<td class="whitespace-nowrap">
											{{
												new Date(
													tx.created_at,
												).toLocaleString()
											}}
										</td>
										<td>
											<span
												class="px-2 py-0.5 text-xs font-semibold rounded-full"
												:class="
													txTypeBadge(tx.type)
												">
												{{ tx.type }}
											</span>
										</td>
										<td
											class="font-mono"
											:class="
												tx.type === 'credit' || tx.type === 'commission'
													? 'text-green-600'
													: 'text-red-600'
											">
											<MoneyFormat
												:amount="tx.amount" />
										</td>
										<td class="font-mono text-gray-500">
											<MoneyFormat
												:amount="
													tx.balance_before
												" />
										</td>
										<td class="font-mono">
											<MoneyFormat
												:amount="
													tx.balance_after
												" />
										</td>
										<td
											class="text-sm text-gray-500 max-w-xs truncate">
											{{ tx.description ?? "&#8212;" }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</main>

		<!-- Credit Modal -->
		<ConfirmationModal
			:show="showCreditModal"
			@close="showCreditModal = false">
			<template #icon>
				<div
					class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
					<Plus class="h-6 w-6 text-green-600" />
				</div>
			</template>
			<template #title>Credit Agent Balance</template>
			<template #content>
				<div class="space-y-4 mt-2">
					<p class="text-sm text-gray-500 dark:text-gray-400">
						Add funds to
						<strong>{{ agent.user?.name ?? agent.code }}</strong>.
						Current balance:
						<MoneyFormat :amount="agent.balance" />.
					</p>
					<div>
						<label
							class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
							Amount (INR)
						</label>
						<input
							v-model="creditForm.amount"
							type="number"
							step="0.01"
							min="0.01"
							class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
							placeholder="0.00" />
						<InputError
							class="mt-1"
							:message="creditForm.errors.amount" />
					</div>
					<div>
						<label
							class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
							Description (optional)
						</label>
						<input
							v-model="creditForm.description"
							type="text"
							class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
							placeholder="Reason for credit" />
						<InputError
							class="mt-1"
							:message="creditForm.errors.description" />
					</div>
				</div>
			</template>
			<template #footer>
				<button
					class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md mr-3"
					@click="showCreditModal = false">
					Cancel
				</button>
				<button
					class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md"
					:disabled="creditForm.processing"
					@click="submitCredit">
					Confirm Credit
				</button>
			</template>
		</ConfirmationModal>

		<!-- Debit Modal -->
		<ConfirmationModal
			:show="showDebitModal"
			@close="showDebitModal = false">
			<template #icon>
				<div
					class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
					<Minus class="h-6 w-6 text-orange-600" />
				</div>
			</template>
			<template #title>Debit Agent Balance</template>
			<template #content>
				<div class="space-y-4 mt-2">
					<div
						class="flex items-start gap-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-md">
						<AlertTriangle
							class="w-5 h-5 text-yellow-600 mt-0.5 shrink-0" />
						<p
							class="text-sm text-yellow-700 dark:text-yellow-300">
							This will deduct funds from the agent's balance.
							Current balance:
							<MoneyFormat :amount="agent.balance" />.
						</p>
					</div>
					<div>
						<label
							class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
							Amount (INR)
						</label>
						<input
							v-model="debitForm.amount"
							type="number"
							step="0.01"
							min="0.01"
							class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500"
							placeholder="0.00" />
						<InputError
							class="mt-1"
							:message="debitForm.errors.amount" />
					</div>
					<div>
						<label
							class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
							Description (optional)
						</label>
						<input
							v-model="debitForm.description"
							type="text"
							class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500"
							placeholder="Reason for debit" />
						<InputError
							class="mt-1"
							:message="debitForm.errors.description" />
					</div>
				</div>
			</template>
			<template #footer>
				<button
					class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md mr-3"
					@click="showDebitModal = false">
					Cancel
				</button>
				<button
					class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md"
					:disabled="debitForm.processing"
					@click="submitDebit">
					Confirm Debit
				</button>
			</template>
		</ConfirmationModal>

		<!-- Delete Modal -->
		<ConfirmationModal
			:show="showDeleteModal"
			@close="showDeleteModal = false">
			<template #title>Delete Agent</template>
			<template #content>
				Are you sure you want to delete this agent? This action cannot be
				undone. Only agents with zero balance can be deleted.
			</template>
			<template #footer>
				<button
					class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md mr-3"
					@click="showDeleteModal = false">
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
