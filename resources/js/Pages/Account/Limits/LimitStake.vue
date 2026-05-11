<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { CircleDollarSign } from "lucide-vue-next";

	import CurrencySymbol from "@/Components/CurrencySymbol.vue";
	import FormInput from "@/Components/FormInput.vue";
	import LimitCard from "@/Components/Account/LimitCard.vue";

	const props = defineProps({
		personal: Object,
	});
	const form = useForm({
		stake_limit: props.personal.stake_limit,
	});

	const updateGrossLimit = () => {
		form.put(window.route("personal.limit.stake"), {
			preserveScroll: true,
		});
	};
</script>

<template>
	<LimitCard
		title="Stake Limit"
		description="Set a limit on the maximum amount you can stake on a bet. To remove or increase your stake limit, a 7-day cooling off period applies."
		:icon="CircleDollarSign"
		:processing="form.processing"
		:disabled="!personal.canUpdateStake"
		:recentlySuccessful="form.recentlySuccessful"
		buttonLabel="Update Stake Limit"
		@submit="updateGrossLimit">
		<FormInput
			class="max-w-xs"
			v-model="form.stake_limit"
			:error="form.errors.stake_limit"
			:disabled="form.processing || !personal.canUpdateStake"
			:help="personal.nextStakeLimitAt ? `Can change ${personal.nextStakeLimitAt}` : null"
			label="Maximum per stake">
			<template #trail>
				<CurrencySymbol class="text-xs font-semibold" />
			</template>
		</FormInput>
	</LimitCard>
</template>
