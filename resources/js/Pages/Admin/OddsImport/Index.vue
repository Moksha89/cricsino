<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
	logs: Array,
	sportKeys: Array,
	hasApiKey: Boolean,
	maskedKey: String,
	oddsCount: Number,
	gamesWithOdds: Number,
});

const importForm = useForm({
	sport_key: "cricket_ipl",
});

const apiKeyForm = useForm({
	api_key: "",
});

const importing = ref(false);

function runImport() {
	importing.value = true;
	importForm.post(route("admin.odds-import.import"), {
		preserveScroll: true,
		onFinish: () => (importing.value = false),
	});
}

function updateApiKey() {
	apiKeyForm.post(route("admin.odds-import.api-key"), {
		preserveScroll: true,
		onSuccess: () => (apiKeyForm.api_key = ""),
	});
}

function statusColor(status) {
	if (status === "completed") return "text-green-400";
	if (status === "failed") return "text-red-400";
	if (status === "running") return "text-yellow-400";
	return "text-gray-400";
}

function formatDate(dateStr) {
	if (!dateStr) return "—";
	return new Date(dateStr).toLocaleString();
}
</script>
<template>
	<AdminLayout>
		<Head title="Odds Import" />
		<div class="p-4 md:p-6 space-y-6">
			<h1 class="text-2xl font-bold text-white">Odds Import</h1>

			<!-- Stats Cards -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
				<div class="bg-gray-800 rounded-lg p-4">
					<div class="text-sm text-gray-400">API Key Status</div>
					<div class="text-lg font-semibold" :class="hasApiKey ? 'text-green-400' : 'text-red-400'">
						{{ hasApiKey ? "Configured" : "Not Set" }}
					</div>
					<div v-if="maskedKey" class="text-xs text-gray-500 mt-1 font-mono">
						{{ maskedKey }}
					</div>
				</div>
				<div class="bg-gray-800 rounded-lg p-4">
					<div class="text-sm text-gray-400">Total Odds Records</div>
					<div class="text-lg font-semibold text-white">{{ oddsCount }}</div>
				</div>
				<div class="bg-gray-800 rounded-lg p-4">
					<div class="text-sm text-gray-400">Games with Odds</div>
					<div class="text-lg font-semibold text-white">{{ gamesWithOdds }}</div>
				</div>
			</div>

			<!-- API Key Update (collapsed by default) -->
			<div class="bg-gray-800 rounded-lg p-4">
				<h2 class="text-lg font-semibold text-white mb-3">Update API Key</h2>
				<p class="text-sm text-gray-400 mb-3">
					Key is read from <code class="text-yellow-300">.env</code> first, then from admin settings.
					Only update here if you need to override the .env value.
				</p>
				<div class="flex items-end gap-3">
					<div class="flex-1">
						<input
							type="password"
							v-model="apiKeyForm.api_key"
							placeholder="Enter new API key"
							class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600 focus:border-purple-500 focus:outline-none"
						/>
						<div v-if="apiKeyForm.errors.api_key" class="text-red-400 text-xs mt-1">
							{{ apiKeyForm.errors.api_key }}
						</div>
					</div>
					<button
						@click="updateApiKey"
						:disabled="apiKeyForm.processing || !apiKeyForm.api_key"
						class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white text-sm rounded whitespace-nowrap"
					>
						{{ apiKeyForm.processing ? "Saving..." : "Save Key" }}
					</button>
				</div>
			</div>

			<!-- Manual Import -->
			<div class="bg-gray-800 rounded-lg p-4">
				<h2 class="text-lg font-semibold text-white mb-3">Manual Import</h2>
				<div v-if="!hasApiKey" class="text-red-400 text-sm mb-3">
					No API key configured. Set THEODDSAPI_APIKEY in .env or update the key above.
				</div>
				<div class="flex items-end gap-3">
					<div class="flex-1">
						<label class="text-sm text-gray-400 block mb-1">Sport</label>
						<select
							v-model="importForm.sport_key"
							class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600"
						>
							<option v-for="key in sportKeys" :key="key" :value="key">
								{{ key }}
							</option>
						</select>
					</div>
					<button
						@click="runImport"
						:disabled="importing || importForm.processing || !hasApiKey"
						class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm rounded whitespace-nowrap"
					>
						<span v-if="importing || importForm.processing">Importing...</span>
						<span v-else>Import Odds</span>
					</button>
				</div>
				<div v-if="importForm.errors.sport_key" class="text-red-400 text-xs mt-1">
					{{ importForm.errors.sport_key }}
				</div>
			</div>

			<!-- Import Logs -->
			<div class="bg-gray-800 rounded-lg p-4">
				<h2 class="text-lg font-semibold text-white mb-3">Import History</h2>
				<div v-if="!logs || logs.length === 0" class="text-gray-500 text-sm">
					No imports yet.
				</div>
				<div v-else class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="text-gray-400 border-b border-gray-700">
								<th class="text-left py-2 px-2">Time</th>
								<th class="text-left py-2 px-2">Sport</th>
								<th class="text-left py-2 px-2">Status</th>
								<th class="text-right py-2 px-2">Games</th>
								<th class="text-right py-2 px-2">Odds</th>
								<th class="text-left py-2 px-2">Source</th>
								<th class="text-left py-2 px-2">Error</th>
							</tr>
						</thead>
						<tbody>
							<tr
								v-for="log in logs"
								:key="log.id"
								class="border-b border-gray-700/50"
							>
								<td class="py-2 px-2 text-gray-300 whitespace-nowrap">
									{{ formatDate(log.created_at) }}
								</td>
								<td class="py-2 px-2 text-white font-mono">
									{{ log.sport_key }}
								</td>
								<td class="py-2 px-2">
									<span :class="statusColor(log.status)" class="font-semibold capitalize">
										{{ log.status }}
									</span>
								</td>
								<td class="py-2 px-2 text-right text-gray-300">
									{{ log.games_imported }}
								</td>
								<td class="py-2 px-2 text-right text-gray-300">
									{{ log.odds_imported }}
								</td>
								<td class="py-2 px-2 text-gray-400">
									{{ log.trigger_source }}
								</td>
								<td class="py-2 px-2 text-red-400 text-xs max-w-xs truncate">
									{{ log.error_message || "—" }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</AdminLayout>
</template>
