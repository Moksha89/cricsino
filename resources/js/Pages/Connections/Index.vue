<script setup>
import { Head, router } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import PageHeader from "@/Components/User/PageHeader.vue";

const props = defineProps({
	connections: Object,
});

const providers = [
	{
		id: "google",
		name: "Google",
		icon: "M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z",
		color: "text-red-400",
		bgColor: "bg-red-500/10 border-red-500/20",
	},
	{
		id: "facebook",
		name: "Facebook",
		icon: "M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z",
		color: "text-blue-400",
		bgColor: "bg-blue-500/10 border-blue-500/20",
	},
	{
		id: "github",
		name: "GitHub",
		icon: "M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12",
		color: "text-gray-300",
		bgColor: "bg-gray-500/10 border-gray-500/20",
	},
];

function isConnected(providerId) {
	if (!props.connections?.data) return false;
	return props.connections.data.some((c) => c.provider === providerId);
}

function getConnection(providerId) {
	if (!props.connections?.data) return null;
	return props.connections.data.find((c) => c.provider === providerId);
}

function connect(providerId) {
	router.get(route("connections.connect", { provider: providerId }));
}

function disconnect(connection) {
	if (!connection) return;
	router.delete(route("connections.destroy", { connection: connection.userId }));
}
</script>

<template>
	<Head title="Connected Accounts" />
	<UserLayout>
		<div class="p-4 sm:p-6">
			<PageHeader title="Connected Accounts" subtitle="Manage your social login connections" />

			<div class="space-y-4 max-w-2xl">
				<div
					v-for="provider in providers"
					:key="provider.id"
					class="bg-surface-light rounded-lg border border-white/10 p-4 sm:p-5 flex items-center justify-between">
					<div class="flex items-center gap-4">
						<div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="provider.bgColor">
							<svg class="w-5 h-5" :class="provider.color" viewBox="0 0 24 24" fill="currentColor">
								<path :d="provider.icon" />
							</svg>
						</div>
						<div>
							<div class="text-white font-medium">{{ provider.name }}</div>
							<div v-if="isConnected(provider.id)" class="text-xs text-green-400">Connected</div>
							<div v-else class="text-xs text-gray-500">Not connected</div>
						</div>
					</div>
					<button
						v-if="isConnected(provider.id)"
						@click="disconnect(getConnection(provider.id))"
						class="px-4 py-2 text-sm rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors">
						Disconnect
					</button>
					<button
						v-else
						@click="connect(provider.id)"
						class="px-4 py-2 text-sm rounded-lg border border-purple-500/30 text-purple-400 hover:bg-purple-500/10 transition-colors">
						Connect
					</button>
				</div>
			</div>
		</div>
	</UserLayout>
</template>
