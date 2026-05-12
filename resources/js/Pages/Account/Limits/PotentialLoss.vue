<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { TrendingDown } from "lucide-vue-next";

	import CurrencySymbol from "@/Components/CurrencySymbol.vue";
	import FormInput from "@/Components/FormInput.vue";
	import FormLabel from "@/Components/FormLabel.vue";
	import RadioSelect from "@/Components/RadioSelect.vue";
	import LimitCard from "@/Components/Account/LimitCard.vue";

	const props = defineProps({
		personal: Object,
		lossOptions: Object,
	});
	const form = useForm({
		loss_limit_interval: props.personal.loss_limit_interval,
		loss_limit: props.personal.loss_limit,
	});

	const updateGrossLimit = () => {
		form.put(window.route("personal.limit.loss"), {
			preserveScroll: true,
		});
	};
</script>

<template>
	<LimitCard
		title="Loss Limits"
		description="Set a limit on the amount you can lose for a period of your choice."
		:icon="TrendingDown"
		:processing="form.processing"
		:recentlySuccessful="form.recentlySuccessful"
		buttonLabel="Update Loss Limit"
		@submit="updateGrossLimit">
		<div class="space-y-5">
			<div>
				<FormLabel class="mb-2">Loss Interval</FormLabel>
				<RadioSelect
					v-model="form.loss_limit_interval"
					:options="lossOptions"
					class="gap-3" />
			</div>
			<FormInput
				class="max-w-xs"
				v-model="form.loss_limit"
				label="Loss Limit">
				<template #trail>
					<CurrencySymbol class="text-xs font-semibold" />
				</template>
			</FormInput>
		</div>
	</LimitCard>
</template>
