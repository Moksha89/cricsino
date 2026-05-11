<script setup>
	import { Head } from "@inertiajs/vue3";

	import UserLayout from "@/Layouts/UserLayout.vue";
	import MenuLink from "@/Pages/Account/Settings/MenuLink.vue";
	defineProps({
		personal: Object,
	});
</script>

<template>
	<Head title="Settings" />
	<UserLayout>
		<div class="p-4 sm:p-6 mb-12">
			<div class="mb-6">
				<h1 class="text-2xl sm:text-3xl text-white font-inter font-semibold">
					{{ $t("Settings") }}
				</h1>
			</div>
			<div
				class="mb-6 flex items-center space-x-2 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-track-transparent pb-1">
				<MenuLink
					:active="route().current('accounts.settings')"
					:href="route('accounts.settings')">
					{{ $t("General") }}
				</MenuLink>
				<MenuLink
					:active="route().current('accounts.limits')"
					:href="route('accounts.limits')">
					{{ $t("Limits") }}
				</MenuLink>
				<MenuLink
					:active="route().current('accounts.alerts')"
					:href="route('accounts.alerts')">
					{{ $t("Alerts") }}
				</MenuLink>
			</div>
			<slot></slot>
		</div>
	</UserLayout>
</template>
