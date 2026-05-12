<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
	logs: Array,
	sportKeys: Array,
	hasApiKey: Boolean,
	gamesWithScores: Number,
	liveGames: Number,
});

const refreshForm = useForm({
	sport_key: "cricket_ipl",
});

const refreshing = ref(false);

function runRefresh() {
	refreshing.value = true;
	refreshForm.post(route("admin.live-scores.refresh"), {
		preserveScroll: true,
		onFinish: () => (refreshing.value = false),
	});
}

function statusColor(status) {
	if (status === "completed") return "text-green-400";
	if (status === "failed") return "text-red-400";
	if (status === "partial") return "text-yellow-400";
	if (status === "running") return "text-sky-400";
	return "text-gray-400";
}

function formatDate(dateStr) {
	if (!dateStr) return "—";
	return new Date(dateStr).toLocaleString();
}
</script>
<template>
	<AdminLayout>
		<Head title="Live Scores" />
		<div class="p-4 md:p-6 space-y-6">
			<h1 class="text-2xl font-bold text-white">Live Scores</h1>
			<p class="text-sm text-gray-400">
				Fetch and display live scores from TheOddsApi. Score refresh is display-only — it does not settle bets, change wallet balances, or trigger any financial actions.
			</p>

			<!-- Stats Cards -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
				<div class="bg-gray-800 rounded-lg p-4">
					<div class="text-sm text-gray-400">API Key</div>
					<div class="text-lg font-semibold" :class="hasApiKey ? 'text-green-400' : 'text-red-400'">
						{{ hasApiKey ? "Configured" : "Not Set" }}
					</div>
				</div>
				<div class="bg-gray-800 rounded-lg p-4">
					<div class="text-sm text-gray-400">Games with Scores</div>
					<div class="text-lg font-semibold text-white">{{ gamesWithScores }}</div>
				</div>
				<div class="bg-gray-800 rounded-lg p-4">
					<div class="text-sm text-gray-400">Live Games</div>
					<div class="text-lg font-semibold text-green-400">{{ liveGames }}</div>
				</div>
			</div>

			<!-- Manual Score Refresh -->
			<div class="bg-gray-800 rounded-lg p-4">
				<h2 class="text-lg font-semibold text-white mb-3">Manual Score Refresh</h2>
				<div v-if="!hasApiKey" class="text-red-400 text-sm mb-3">
					No API key configured. Set THEODDSAPI_APIKEY in .env or in Odds Import settings.
				</div>
				<div class="flex items-end gap-3">
					<div class="flex-1">
						<label class="text-sm text-gray-400 block mb-1">Sport</label>
						<select
							v-model="refreshForm.sport_key"
							class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600"
						>
							<option v-for="key in sportKeys" :key="key" :value="key">
								{{ key }}
							</option>
						</select>
					</div>
					<button
						@click="runRefresh"
						:disabled="refreshing || refreshForm.processing || !hasApiKey"
						class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm rounded whitespace-nowrap"
					>
						<span v-if="refreshing || refreshForm.processing">Refreshing...</span>
						<span v-else>Refresh Scores</span>
					</button>
				</div>
				<div v-if="refreshForm.errors.sport_key" class="text-red-400 text-xs mt-1">
					{{ refreshForm.errors.sport_key }}
				</div>
			</div>

			<!-- Refresh Logs -->
			<div class="bg-gray-800 rounded-lg p-4">
				<h2 class="text-lg font-semibold text-white mb-3">Refresh History</h2>
				<div v-if="!logs || logs.length === 0" class="text-gray-500 text-sm">
					No score refreshes yet.
				</div>
				<div v-else class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="text-gray-400 border-b border-gray-700">
								<th class="text-left py-2 px-2">Time</th>
								<th class="text-left py-2 px-2">Sport</th>
								<th class="text-left py-2 px-2">Status</th>
								<th class="text-right py-2 px-2">Updated</th>
								<th class="text-right py-2 px-2">Skipped</th>
								<th class="text-right py-2 px-2">Errors</th>
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
									{{ log.games_updated ?? 0 }}
								</td>
								<td class="py-2 px-2 text-right text-gray-300">
									{{ log.games_skipped ?? 0 }}
								</td>
								<td class="py-2 px-2 text-right text-gray-300">
									{{ log.errors_count ?? 0 }}
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
