<script setup>
	import { Head, Link, useForm } from "@inertiajs/vue3";
	import { ArrowLeft, Save, Gamepad2 } from "lucide-vue-next";

	import InputError from "@/Components/InputError.vue";
	import InputLabel from "@/Components/InputLabel.vue";
	import PrimaryButton from "@/Components/PrimaryButton.vue";
	import TextInput from "@/Components/TextInput.vue";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	defineOptions({ layout: AdminLayout });

	const form = useForm({
		name: "",
		provider: "",
		category: "",
		game_id: "",
		image: "",
		description: "",
		launch_url: "",
		is_live: false,
	});

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

	const submit = () => {
		form.post(window.route("admin.casino.store"), {
			preserveScroll: true,
		});
	};

	const labelFor = (val) => {
		if (!val) return val;
		return val.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
	};
</script>

<template>
	<Head title="Create Casino Game" />

	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center gap-4">
			<Link
				:href="window.route('admin.casino.index')"
				class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-slate-200"
			>
				<ArrowLeft class="h-5 w-5" />
			</Link>
			<div>
				<h2 class="text-2xl font-bold tracking-tight text-slate-100">
					Create Casino Game
				</h2>
				<p class="mt-1 text-sm text-slate-400">
					Add a new casino game to the platform.
				</p>
			</div>
		</div>

		<!-- Form -->
		<form @submit.prevent="submit" class="space-y-6">
			<!-- Game Info Card -->
			<div
				class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-6 shadow-lg"
			>
				<h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-100">
					<Gamepad2 class="h-5 w-5 text-indigo-400" />
					Game Information
				</h3>

				<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
					<!-- Name -->
					<div>
						<InputLabel for="name" value="Game Name *" />
						<TextInput
							id="name"
							v-model="form.name"
							type="text"
							class="mt-1 block w-full"
							placeholder="e.g. Crash, Lucky Roulette"
							required
						/>
						<InputError :message="form.errors.name" class="mt-1" />
					</div>

					<!-- Game ID -->
					<div>
						<InputLabel for="game_id" value="Game ID / Code *" />
						<TextInput
							id="game_id"
							v-model="form.game_id"
							type="text"
							class="mt-1 block w-full"
							placeholder="e.g. crash, dice, provider_game_123"
							required
						/>
						<InputError
							:message="form.errors.game_id"
							class="mt-1"
						/>
					</div>

					<!-- Provider -->
					<div>
						<InputLabel for="provider" value="Provider *" />
						<select
							id="provider"
							v-model="form.provider"
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
							:message="form.errors.provider"
							class="mt-1"
						/>
					</div>

					<!-- Category -->
					<div>
						<InputLabel for="category" value="Category *" />
						<select
							id="category"
							v-model="form.category"
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
							:message="form.errors.category"
							class="mt-1"
						/>
					</div>

					<!-- Image URL -->
					<div>
						<InputLabel for="image" value="Image URL" />
						<TextInput
							id="image"
							v-model="form.image"
							type="text"
							class="mt-1 block w-full"
							placeholder="https://example.com/game-thumb.png"
						/>
						<InputError
							:message="form.errors.image"
							class="mt-1"
						/>
					</div>

					<!-- Launch URL -->
					<div>
						<InputLabel for="launch_url" value="Launch URL" />
						<TextInput
							id="launch_url"
							v-model="form.launch_url"
							type="text"
							class="mt-1 block w-full"
							placeholder="https://provider.com/launch/game"
						/>
						<InputError
							:message="form.errors.launch_url"
							class="mt-1"
						/>
					</div>

					<!-- Description -->
					<div class="md:col-span-2">
						<InputLabel for="description" value="Description" />
						<textarea
							id="description"
							v-model="form.description"
							rows="3"
							class="mt-1 block w-full rounded-md border-slate-600 bg-slate-700 text-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							placeholder="Brief description of the game..."
						/>
						<InputError
							:message="form.errors.description"
							class="mt-1"
						/>
					</div>

					<!-- Is Live -->
					<div class="flex items-center gap-3">
						<label class="relative inline-flex cursor-pointer items-center">
							<input
								type="checkbox"
								v-model="form.is_live"
								class="peer sr-only"
							/>
							<div
								class="peer h-6 w-11 rounded-full bg-slate-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-red-500 peer-checked:after:translate-x-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300"
							/>
						</label>
						<span class="text-sm text-slate-300">
							Live Casino Game
						</span>
					</div>
				</div>
			</div>

			<!-- Submit -->
			<div class="flex items-center justify-end gap-3">
				<Link
					:href="window.route('admin.casino.index')"
					class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700"
				>
					Cancel
				</Link>
				<PrimaryButton
					class="flex items-center gap-2"
					:class="{ 'opacity-50': form.processing }"
					:disabled="form.processing"
				>
					<Save class="h-4 w-4" />
					Create Game
				</PrimaryButton>
			</div>
		</form>
	</div>
</template>
