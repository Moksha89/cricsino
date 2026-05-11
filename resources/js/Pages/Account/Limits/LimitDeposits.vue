<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { Wallet } from "lucide-vue-next";

	import CurrencySymbol from "@/Components/CurrencySymbol.vue";
	import FormInput from "@/Components/FormInput.vue";
	import LimitCard from "@/Components/Account/LimitCard.vue";

	const props = defineProps({
		personal: Object,
	});
	const form = useForm({
		daily_gross_deposit: props.personal.daily_gross_deposit,
		weekly_gross_deposit: props.personal.weekly_gross_deposit,
		monthly_gross_deposit: props.personal.monthly_gross_deposit,
	});

	const updateGrossLimit = () => {
		form.put(window.route("personal.limit.deposit"), {
			preserveScroll: true,
		});
	};
</script>

<template>
	<LimitCard
		title="Deposit Limits"
		description="Set a limit on the gross amount you can deposit for a period of your choice."
		:icon="Wallet"
		:processing="form.processing"
		:recentlySuccessful="form.recentlySuccessful"
		buttonLabel="Update Deposit Limits"
		@submit="updateGrossLimit">
		<div class="grid gap-4 sm:grid-cols-3">
			<FormInput v-model="form.daily_gross_deposit" label="Daily Limit">
				<template #trail>
					<CurrencySymbol class="text-xs font-semibold" />
				</template>
			</FormInput>
			<FormInput v-model="form.weekly_gross_deposit" label="Weekly Limit">
				<template #trail>
					<CurrencySymbol class="text-xs font-semibold" />
				</template>
			</FormInput>
			<FormInput v-model="form.monthly_gross_deposit" label="Monthly Limit">
				<template #trail>
					<CurrencySymbol class="text-xs font-semibold" />
				</template>
			</FormInput>
		</div>
	</LimitCard>
</template>
