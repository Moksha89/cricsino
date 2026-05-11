<script setup>
	import { Gift } from "lucide-vue-next";
	import UserLayout from "@/Layouts/UserLayout.vue";
	import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
	import AccountEmptyState from "@/Components/Account/AccountEmptyState.vue";
</script>

<template>
	<UserLayout>
		<div class="p-4 sm:p-6 pb-20 sm:pb-6">
			<AccountPageHeader
				title="Promotions"
				subtitle="View available promotions and bonus offers"
				:icon="Gift" />

			<div class="max-w-4xl">
				<AccountEmptyState
					title="No Promotions Available"
					description="There are no active promotions at this time. Check back later for new bonus offers and special deals."
					:icon="Gift" />
			</div>
		</div>
	</UserLayout>
</template>
