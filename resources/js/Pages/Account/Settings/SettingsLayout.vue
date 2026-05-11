<script setup>
	import { Head } from "@inertiajs/vue3";
	import { Link } from "@inertiajs/vue3";
	import { Settings, Gauge, Bell } from "lucide-vue-next";

	import UserLayout from "@/Layouts/UserLayout.vue";
	import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
	defineProps({
		personal: Object,
	});

	const tabs = [
		{ name: "General", route: "accounts.settings", icon: Settings },
		{ name: "Limits", route: "accounts.limits", icon: Gauge },
		{ name: "Alerts", route: "accounts.alerts", icon: Bell },
	];
</script>

<template>
	<Head title="Settings" />
	<UserLayout>
		<div class="p-4 sm:p-6 pb-20 sm:pb-6">
			<AccountPageHeader
				title="Settings"
				subtitle="Manage your account preferences and security"
				:icon="Settings" />
			<div
				class="mb-6 flex items-center gap-1 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-track-transparent pb-1">
				<Link
					v-for="tab in tabs"
					:key="tab.route"
					:href="route(tab.route)"
					class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 min-h-[44px]"
					:class="route().current(tab.route)
						? 'bg-purple-600/20 text-purple-300 border border-purple-500/30'
						: 'text-gray-400 hover:text-white hover:bg-white/[0.05] border border-transparent'">
					<component :is="tab.icon" class="w-4 h-4" />
					{{ $t(tab.name) }}
				</Link>
			</div>
			<slot />
		</div>
	</UserLayout>
</template>
