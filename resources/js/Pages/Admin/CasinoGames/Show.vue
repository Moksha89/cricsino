<script setup>
	import { ref, computed } from "vue";

	import { Head, Link, useForm, router as Inertia } from "@inertiajs/vue3";
	import {
		ArrowLeft,
		Save,
		Gamepad2,
		ToggleLeft,
		ToggleRight,
		Trash2,
		Users,
		DollarSign,
		TrendingUp,
		TrendingDown,
		Activity,
		Clock,
	} from "lucide-vue-next";

	import ConfirmationModal from "@/Components/ConfirmationModal.vue";
	import InputError from "@/Components/InputError.vue";
	import InputLabel from "@/Components/InputLabel.vue";
	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import PrimaryButton from "@/Components/PrimaryButton.vue";
	import TextInput from "@/Components/TextInput.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	defineOptions({ layout: AdminLayout });

	const props = defineProps({
		game: Object,
	});

	const editForm = useForm({
		name: props.game.name ?? "",
		provider: props.game.provider ?? "",
		category: props.game.category ?? "",
		image: props.game.image ?? "",
		description: props.game.description ?? "",
		launch_url: props.game.launch_url ?? "",
		is_live: props.game.is_live ?? false,
	});

	const deleteForm = useForm({});
	const toggleForm = useForm({});
	const showDeleteModal = ref(false);
	const showToggleModal = ref(false);

	const providers = [
		"house_original",
		"evolution",
		"pragmatic_play",
		"ezugi",
		"betsoft",
		"netent",
		"microgaming",
		"playtech",
		"other",
	];

	const categories = [
		"house_original",
		"live_casino",
		"slots",
		"table_games",
		"teen_patti",
		"andar_bahar",
		"roulette",
		"blackjack",
		"baccarat",
		"poker",
		"other",
	];

	const labelFor = (val) => {
		if (!val) return val;
		return val.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
	};

	const submitEdit = () => {
		editForm.put(window.route("admin.casino.update", props.game.id), {
			preserveScroll: true,
		});
	};

	const toggleGame = () => {
		toggleForm.put(window.route("admin.casino.toggle", props.game.id), {
			preserveScroll: true,
			onSuccess: () => (showToggleModal.value = false),
		});
	};

	const deleteGame = () => {
		deleteForm.delete(
			window.route("admin.casino.destroy", props.game.id),
			{
				onSuccess: () => (showDeleteModal.value = false),
			},
		);
	};

	const sessions = computed(() => props.game.sessions ?? []);

	const sessionStats = computed(() => {
		const s = sessions.value;
		const totalSessions = s.length;
		const totalWagered = s.reduce(
			(sum, sess) => sum + parseFloat(sess.bet_amount || 0),
			0,
		);
		const totalWon = s.reduce(
			(sum, sess) => sum + parseFloat(sess.win_amount || 0),
			0,
		);
		const housePnl = totalWagered - totalWon;
		return { totalSessions, totalWagered, totalWon, housePnl };
	});
</script>

<template>
	<Head :title="game.name" />

	<div class="space-y-6">
		<!-- Header -->
		<div class="flex flex-wrap items-start justify-between gap-4">
			<div class="flex items-center gap-4">
				<Link
					:href="window.route('admin.casino.index')"
					class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-slate-200"
				>
					<ArrowLeft class="h-5 w-5" />
				</Link>
				<div>
					<h2
						class="flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-100"
					>
						{{ game.name }}
						<span
							v-if="game.is_live"
							class="inline-flex items-center rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-medium text-red-400"
						>
							LIVE
						</span>
						<span
							:class="[
								'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
								game.active
									? 'bg-green-500/10 text-green-400'
									: 'bg-red-500/10 text-red-400',
							]"
						>
							{{ game.active ? "Active" : "Inactive" }}
						</span>
					</h2>
					<p class="mt-1 text-sm text-slate-400">
						{{ labelFor(game.provider) }} &middot;
						{{ labelFor(game.category) }}
						<span v-if="game.game_id">
							&middot; ID:
							<code
								class="rounded bg-slate-700/50 px-1.5 py-0.5 text-xs"
							>
								{{ game.game_id }}
							</code>
						</span>
					</p>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="flex items-center gap-2">
				<button
					@click="showToggleModal = true"
					:class="[
						'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors',
						game.active
							? 'bg-amber-600 hover:bg-amber-700'
							: 'bg-green-600 hover:bg-green-700',
					]"
				>
					<ToggleLeft v-if="!game.active" class="h-4 w-4" />
					<ToggleRight v-else class="h-4 w-4" />
					{{ game.active ? "Deactivate" : "Activate" }}
				</button>
				<button
					@click="showDeleteModal = true"
					class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
				>
					<Trash2 class="h-4 w-4" />
					Delete
				</button>
			</div>
		</div>

		<!-- Stats Cards -->
		<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
			<div
				class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-4 shadow-lg"
			>
				<div class="flex items-center justify-between">
					<span class="text-sm text-slate-400">Total Sessions</span>
					<Activity class="h-5 w-5 text-blue-400" />
				</div>
				<p class="mt-2 text-2xl font-bold text-slate-100">
					{{ sessionStats.totalSessions }}
				</p>
			</div>
			<div
				class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-4 shadow-lg"
			>
				<div class="flex items-center justify-between">
					<span class="text-sm text-slate-400">Total Wagered</span>
					<DollarSign class="h-5 w-5 text-indigo-400" />
				</div>
				<p class="mt-2 text-2xl font-bold text-slate-100">
					<MoneyFormat :money="sessionStats.totalWagered" />
				</p>
			</div>
			<div
				class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-4 shadow-lg"
			>
				<div class="flex items-center justify-between">
					<span class="text-sm text-slate-400">Total Won</span>
					<TrendingUp class="h-5 w-5 text-green-400" />
				</div>
				<p class="mt-2 text-2xl font-bold text-green-400">
					<MoneyFormat :money="sessionStats.totalWon" />
				</p>
			</div>
			<div
				class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-4 shadow-lg"
			>
				<div class="flex items-center justify-between">
					<span class="text-sm text-slate-400">House P&L</span>
					<TrendingDown class="h-5 w-5 text-amber-400" />
				</div>
				<p
					class="mt-2 text-2xl font-bold"
					:class="
						sessionStats.housePnl >= 0
							? 'text-green-400'
							: 'text-red-400'
					"
				>
					<MoneyFormat :money="sessionStats.housePnl" />
				</p>
			</div>
		</div>

		<!-- Edit Form -->
		<div
			class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-6 shadow-lg"
		>
			<h3
				class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-100"
			>
				<Gamepad2 class="h-5 w-5 text-indigo-400" />
				Edit Game Details
			</h3>

			<form @submit.prevent="submitEdit" class="space-y-6">
				<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
					<!-- Name -->
					<div>
						<InputLabel for="edit_name" value="Game Name *" />
						<TextInput
							id="edit_name"
							v-model="editForm.name"
							type="text"
							class="mt-1 block w-full"
							required
						/>
						<InputError
							:message="editForm.errors.name"
							class="mt-1"
						/>
					</div>

					<!-- Provider -->
					<div>
						<InputLabel for="edit_provider" value="Provider *" />
						<select
							id="edit_provider"
							v-model="editForm.provider"
							class="mt-1 block w-full rounded-md border-slate-600 bg-slate-700 text-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						>
							<option value="" disabled>Select provider</option>
							<option
								v-for="p in providers"
								:key="p"
								:value="p"
							>
								{{ labelFor(p) }}
							</option>
						</select>
						<InputError
							:message="editForm.errors.provider"
							class="mt-1"
						/>
					</div>

					<!-- Category -->
					<div>
						<InputLabel for="edit_category" value="Category *" />
						<select
							id="edit_category"
							v-model="editForm.category"
							class="mt-1 block w-full rounded-md border-slate-600 bg-slate-700 text-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						>
							<option value="" disabled>Select category</option>
							<option
								v-for="c in categories"
								:key="c"
								:value="c"
							>
								{{ labelFor(c) }}
							</option>
						</select>
						<InputError
							:message="editForm.errors.category"
							class="mt-1"
						/>
					</div>

					<!-- Image URL -->
					<div>
						<InputLabel for="edit_image" value="Image URL" />
						<TextInput
							id="edit_image"
							v-model="editForm.image"
							type="text"
							class="mt-1 block w-full"
							placeholder="https://example.com/thumb.png"
						/>
						<InputError
							:message="editForm.errors.image"
							class="mt-1"
						/>
					</div>

					<!-- Launch URL -->
					<div>
						<InputLabel
							for="edit_launch_url"
							value="Launch URL"
						/>
						<TextInput
							id="edit_launch_url"
							v-model="editForm.launch_url"
							type="text"
							class="mt-1 block w-full"
							placeholder="https://provider.com/launch/game"
						/>
						<InputError
							:message="editForm.errors.launch_url"
							class="mt-1"
						/>
					</div>

					<!-- Is Live -->
					<div class="flex items-center gap-3 pt-6">
						<label
							class="relative inline-flex cursor-pointer items-center"
						>
							<input
								type="checkbox"
								v-model="editForm.is_live"
								class="peer sr-only"
							/>
							<div
								class="peer h-6 w-11 rounded-full bg-slate-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-red-500 peer-checked:after:translate-x-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300"
							/>
						</label>
						<span class="text-sm text-slate-300"
							>Live Casino Game</span
						>
					</div>

					<!-- Description -->
					<div class="md:col-span-2">
						<InputLabel
							for="edit_description"
							value="Description"
						/>
						<textarea
							id="edit_description"
							v-model="editForm.description"
							rows="3"
							class="mt-1 block w-full rounded-md border-slate-600 bg-slate-700 text-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							placeholder="Brief description..."
						/>
						<InputError
							:message="editForm.errors.description"
							class="mt-1"
						/>
					</div>
				</div>

				<div class="flex justify-end">
					<PrimaryButton
						class="flex items-center gap-2"
						:class="{ 'opacity-50': editForm.processing }"
						:disabled="editForm.processing"
					>
						<Save class="h-4 w-4" />
						Save Changes
					</PrimaryButton>
				</div>
			</form>
		</div>

		<!-- Recent Sessions -->
		<div
			class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-6 shadow-lg"
		>
			<h3
				class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-100"
			>
				<Clock class="h-5 w-5 text-indigo-400" />
				Recent Sessions
			</h3>

			<div v-if="sessions.length > 0" class="overflow-x-auto">
				<table class="min-w-full divide-y divide-slate-700/50">
					<thead>
						<tr class="bg-slate-800/80">
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								User
							</th>
							<th
								class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Bet Amount
							</th>
							<th
								class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Win Amount
							</th>
							<th
								class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								P/L
							</th>
							<th
								class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Status
							</th>
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Date
							</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-700/30">
						<tr
							v-for="session in sessions"
							:key="session.id"
							class="transition-colors hover:bg-slate-700/20"
						>
							<td class="whitespace-nowrap px-4 py-3">
								<div
									v-if="session.user"
									class="text-sm text-slate-200"
								>
									{{ session.user.name }}
									<div class="text-xs text-slate-500">
										{{ session.user.email }}
									</div>
								</div>
								<span v-else class="text-sm text-slate-500"
									>-</span
								>
							</td>
							<td
								class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-200"
							>
								<MoneyFormat :money="session.bet_amount" />
							</td>
							<td
								class="whitespace-nowrap px-4 py-3 text-right text-sm text-green-400"
							>
								<MoneyFormat :money="session.win_amount" />
							</td>
							<td class="whitespace-nowrap px-4 py-3 text-right">
								<span
									class="text-sm"
									:class="
										parseFloat(session.win_amount) -
											parseFloat(session.bet_amount) >=
										0
											? 'text-green-400'
											: 'text-red-400'
									"
								>
									<MoneyFormat
										:money="
											parseFloat(session.win_amount) -
											parseFloat(session.bet_amount)
										"
									/>
								</span>
							</td>
							<td
								class="whitespace-nowrap px-4 py-3 text-center"
							>
								<span
									:class="[
										'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
										session.status === 'completed'
											? 'bg-green-500/10 text-green-400'
											: session.status === 'active'
												? 'bg-blue-500/10 text-blue-400'
												: 'bg-slate-500/10 text-slate-400',
									]"
								>
									{{ session.status }}
								</span>
							</td>
							<td
								class="whitespace-nowrap px-4 py-3 text-sm text-slate-400"
							>
								{{
									new Date(
										session.created_at,
									).toLocaleString()
								}}
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Empty Sessions -->
			<div
				v-else
				class="flex flex-col items-center justify-center py-12 text-center"
			>
				<Users class="mb-3 h-10 w-10 text-slate-500" />
				<h4 class="text-base font-medium text-slate-300">
					No Sessions Yet
				</h4>
				<p class="mt-1 text-sm text-slate-500">
					No players have played this game yet.
				</p>
			</div>
		</div>
	</div>

	<!-- Toggle Confirmation Modal -->
	<ConfirmationModal
		:show="showToggleModal"
		@close="showToggleModal = false"
	>
		<template #title>Toggle Game Status</template>
		<template #content>
			Are you sure you want to
			<strong>{{ game.active ? "deactivate" : "activate" }}</strong>
			<strong>{{ game.name }}</strong
			>?
			<span v-if="game.active" class="text-amber-400">
				Players will no longer be able to access this game.
			</span>
		</template>
		<template #footer>
			<button
				class="mr-3 rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700"
				@click="showToggleModal = false"
			>
				Cancel
			</button>
			<button
				:class="[
					'rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors',
					game.active
						? 'bg-amber-600 hover:bg-amber-700'
						: 'bg-green-600 hover:bg-green-700',
					{ 'opacity-50': toggleForm.processing },
				]"
				:disabled="toggleForm.processing"
				@click="toggleGame"
			>
				{{ game.active ? "Deactivate" : "Activate" }}
			</button>
		</template>
	</ConfirmationModal>

	<!-- Delete Confirmation Modal -->
	<ConfirmationModal
		:show="showDeleteModal"
		@close="showDeleteModal = false"
	>
		<template #title>Delete Casino Game</template>
		<template #content>
			Are you sure you want to delete
			<strong>{{ game.name }}</strong
			>? This action cannot be undone. All associated session data will
			also be removed.
		</template>
		<template #footer>
			<button
				class="mr-3 rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700"
				@click="showDeleteModal = false"
			>
				Cancel
			</button>
			<button
				class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
				:class="{ 'opacity-50': deleteForm.processing }"
				:disabled="deleteForm.processing"
				@click="deleteGame"
			>
				Delete Game
			</button>
		</template>
	</ConfirmationModal>
</template>
