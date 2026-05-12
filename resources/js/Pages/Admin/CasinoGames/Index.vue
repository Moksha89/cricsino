<script setup>
	import { ref, computed } from "vue";

	import { Head, Link, router as Inertia, useForm } from "@inertiajs/vue3";
	import { debouncedWatch, useUrlSearchParams } from "@vueuse/core";
	import {
		Plus,
		Eye,
		Pencil,
		Trash2,
		Search,
		Gamepad2,
		ToggleLeft,
		ToggleRight,
	} from "lucide-vue-next";

	import ConfirmationModal from "@/Components/ConfirmationModal.vue";
	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import NoItems from "@/Components/NoItems.vue";
	import Pagination from "@/Components/Pagination.vue";
	import PrimaryButton from "@/Components/PrimaryButton.vue";
	import SearchInput from "@/Components/SearchInput.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	defineOptions({ layout: AdminLayout });

	const props = defineProps({
		games: Object,
	});

	const params = useUrlSearchParams("history");
	const search = ref(params.search ?? "");
	const deleteForm = useForm({});
	const toggleForm = useForm({});
	const gameBeingDeleted = ref(null);
	const gameBeingToggled = ref(null);

	debouncedWatch(
		search,
		(value) => {
			Inertia.get(
				window.route("admin.casino.index"),
				{ search: value || undefined },
				{ preserveState: true, replace: true },
			);
		},
		{ debounce: 300 },
	);

	const confirmDelete = (game) => {
		gameBeingDeleted.value = game;
	};

	const deleteGame = () => {
		deleteForm.delete(
			window.route("admin.casino.destroy", gameBeingDeleted.value?.id),
			{
				preserveScroll: true,
				preserveState: true,
				onSuccess: () => (gameBeingDeleted.value = null),
			},
		);
	};

	const confirmToggle = (game) => {
		gameBeingToggled.value = game;
	};

	const toggleGame = () => {
		toggleForm.put(
			window.route("admin.casino.toggle", gameBeingToggled.value?.id),
			{
				preserveScroll: true,
				preserveState: true,
				onSuccess: () => (gameBeingToggled.value = null),
			},
		);
	};

	const categoryLabel = (cat) => {
		if (!cat) return cat;
		return cat.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
	};
</script>

<template>
	<Head title="Casino Games" />

	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h2
					class="text-2xl font-bold tracking-tight text-slate-100"
				>
					Casino Games
				</h2>
				<p class="mt-1 text-sm text-slate-400">
					Manage casino games, providers, and categories.
				</p>
			</div>
			<Link :href="window.route('admin.casino.create')">
				<PrimaryButton class="flex items-center gap-2">
					<Plus class="h-4 w-4" />
					Add Game
				</PrimaryButton>
			</Link>
		</div>

		<!-- Search -->
		<div class="max-w-md">
			<SearchInput
				v-model="search"
				placeholder="Search by name, provider, or category..."
			/>
		</div>

		<!-- Table -->
		<div
			class="overflow-hidden rounded-xl border border-slate-700/50 bg-slate-800/50 shadow-lg"
		>
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-slate-700/50">
					<thead>
						<tr class="bg-slate-800/80">
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Game
							</th>
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Provider
							</th>
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Category
							</th>
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Game ID
							</th>
							<th
								class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Live
							</th>
							<th
								class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Status
							</th>
							<th
								class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Created
							</th>
							<th
								class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-400"
							>
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-700/30">
						<tr
							v-for="game in games.data"
							:key="game.id"
							class="transition-colors hover:bg-slate-700/20"
						>
							<!-- Game Name -->
							<td class="whitespace-nowrap px-4 py-3">
								<div class="flex items-center gap-3">
									<div
										v-if="game.image"
										class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg border border-slate-600/50"
									>
										<img
											:src="game.image"
											:alt="game.name"
											class="h-full w-full object-cover"
										/>
									</div>
									<div
										v-else
										class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-slate-600/50 bg-slate-700/50"
									>
										<Gamepad2
											class="h-5 w-5 text-slate-400"
										/>
									</div>
									<div>
										<div
											class="font-medium text-slate-100"
										>
											{{ game.name }}
										</div>
										<div
											v-if="game.slug"
											class="text-xs text-slate-500"
										>
											{{ game.slug }}
										</div>
									</div>
								</div>
							</td>

							<!-- Provider -->
							<td class="whitespace-nowrap px-4 py-3">
								<span
									class="inline-flex items-center rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-xs font-medium text-indigo-400"
								>
									{{ game.provider }}
								</span>
							</td>

							<!-- Category -->
							<td class="whitespace-nowrap px-4 py-3">
								<span
									class="inline-flex items-center rounded-full bg-purple-500/10 px-2.5 py-0.5 text-xs font-medium text-purple-400"
								>
									{{ categoryLabel(game.category) }}
								</span>
							</td>

							<!-- Game ID -->
							<td class="whitespace-nowrap px-4 py-3">
								<code
									class="rounded bg-slate-700/50 px-1.5 py-0.5 text-xs text-slate-300"
								>
									{{ game.game_id }}
								</code>
							</td>

							<!-- Live -->
							<td
								class="whitespace-nowrap px-4 py-3 text-center"
							>
								<span
									v-if="game.is_live"
									class="inline-flex items-center rounded-full bg-red-500/10 px-2 py-0.5 text-xs font-medium text-red-400"
								>
									LIVE
								</span>
								<span
									v-else
									class="text-xs text-slate-500"
								>
									-
								</span>
							</td>

							<!-- Status -->
							<td
								class="whitespace-nowrap px-4 py-3 text-center"
							>
								<span
									:class="[
										'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
										game.active
											? 'bg-green-500/10 text-green-400'
											: 'bg-red-500/10 text-red-400',
									]"
								>
									{{
										game.active ? "Active" : "Inactive"
									}}
								</span>
							</td>

							<!-- Created -->
							<td
								class="whitespace-nowrap px-4 py-3 text-sm text-slate-400"
							>
								{{
									new Date(
										game.created_at,
									).toLocaleDateString()
								}}
							</td>

							<!-- Actions -->
							<td
								class="whitespace-nowrap px-4 py-3 text-right"
							>
								<div
									class="flex items-center justify-end gap-2"
								>
									<Link
										:href="
											window.route(
												'admin.casino.show',
												game.id,
											)
										"
										class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-blue-400"
										title="View"
									>
										<Eye class="h-4 w-4" />
									</Link>
									<button
										class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-amber-400"
										title="Toggle Status"
										@click="confirmToggle(game)"
									>
										<ToggleLeft
											v-if="!game.active"
											class="h-4 w-4"
										/>
										<ToggleRight
											v-else
											class="h-4 w-4"
										/>
									</button>
									<button
										class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-red-400"
										title="Delete"
										@click="confirmDelete(game)"
									>
										<Trash2 class="h-4 w-4" />
									</button>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Empty State -->
			<div
				v-if="!games.data || games.data.length === 0"
				class="flex flex-col items-center justify-center py-16 text-center"
			>
				<Gamepad2 class="mb-4 h-12 w-12 text-slate-500" />
				<h3 class="text-lg font-medium text-slate-300">
					No Casino Games Found
				</h3>
				<p class="mt-1 text-sm text-slate-500">
					{{
						search
							? "No games match your search."
							: "Get started by adding your first casino game."
					}}
				</p>
				<Link
					v-if="!search"
					:href="window.route('admin.casino.create')"
					class="mt-4"
				>
					<PrimaryButton class="flex items-center gap-2">
						<Plus class="h-4 w-4" />
						Add Game
					</PrimaryButton>
				</Link>
			</div>
		</div>

		<!-- Pagination -->
		<Pagination :meta="games" />
	</div>

	<!-- Delete Confirmation Modal -->
	<ConfirmationModal
		:show="gameBeingDeleted !== null"
		@close="gameBeingDeleted = null"
	>
		<template #title>Delete Casino Game</template>
		<template #content>
			Are you sure you want to delete
			<strong>{{ gameBeingDeleted?.name }}</strong
			>? This action cannot be undone. All associated session data will
			also be removed.
		</template>
		<template #footer>
			<button
				class="mr-3 rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700"
				@click="gameBeingDeleted = null"
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

	<!-- Toggle Confirmation Modal -->
	<ConfirmationModal
		:show="gameBeingToggled !== null"
		@close="gameBeingToggled = null"
	>
		<template #title>Toggle Game Status</template>
		<template #content>
			Are you sure you want to
			<strong>{{
				gameBeingToggled?.active ? "deactivate" : "activate"
			}}</strong>
			<strong>{{ gameBeingToggled?.name }}</strong
			>?
			<span v-if="gameBeingToggled?.active" class="text-amber-400">
				Players will no longer be able to access this game.
			</span>
		</template>
		<template #footer>
			<button
				class="mr-3 rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700"
				@click="gameBeingToggled = null"
			>
				Cancel
			</button>
			<button
				:class="[
					'rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors',
					gameBeingToggled?.active
						? 'bg-amber-600 hover:bg-amber-700'
						: 'bg-green-600 hover:bg-green-700',
					{ 'opacity-50': toggleForm.processing },
				]"
				:disabled="toggleForm.processing"
				@click="toggleGame"
			>
				{{
					gameBeingToggled?.active
						? "Deactivate"
						: "Activate"
				}}
			</button>
		</template>
	</ConfirmationModal>
</template>
