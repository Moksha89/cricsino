<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { Clock, AlertTriangle } from "lucide-vue-next";

	import FormLabel from "@/Components/FormLabel.vue";
	import RadioSelect from "@/Components/RadioSelect.vue";
	import Switch from "@/Components/Switch.vue";
	import LimitCard from "@/Components/Account/LimitCard.vue";

	defineProps({
		personal: Object,
	});
	const form = useForm({
		days: 1,
		confirm: false,
	});
	const timePeriods = [
		{ value: 1, label: "1 Day" },
		{ value: 7, label: "7 Days" },
		{ value: 31, label: "31 Days" },
		{ value: 42, label: "6 Weeks" },
	];
	const updateGrossLimit = () => {
		form.put(window.route("personal.timeout"), {
			preserveScroll: true,
		});
	};
</script>

<template>
	<LimitCard
		title="Time Out"
		description="Take a voluntary break from betting. During your timeout, you won't be able to place bets or use the website."
		:icon="Clock"
		:processing="form.processing"
		:disabled="!form.confirm"
		:recentlySuccessful="form.recentlySuccessful"
		buttonLabel="Initiate Timeout"
		danger
		@submit="updateGrossLimit">
		<div class="space-y-5">
			<div class="text-sm text-gray-400 space-y-2">
				<ul class="list-disc pl-5 space-y-1.5">
					<li>You can choose to activate a timeout for: 1 day, 7 days, 31 days, 42 days</li>
					<li>During your chosen timeout period, you won't be able to place any bets or use the website</li>
					<li>This feature is entirely optional and under your control</li>
					<li>When the timeout expires your account will be automatically reopened</li>
				</ul>
				<p class="font-medium text-gray-300">
					Taking a break can be a positive step in maintaining a healthy relationship with betting.
				</p>
			</div>

			<div>
				<FormLabel class="mb-2">Choose a timeout period</FormLabel>
				<RadioSelect
					v-model="form.days"
					:options="timePeriods"
					class="gap-3 mt-2" />
			</div>

			<div>
				<FormLabel class="mb-2">Confirm</FormLabel>
				<Switch v-model="form.confirm">
					I confirm that I want a timeout from betting
				</Switch>
			</div>

			<div class="flex items-start gap-3 p-3 rounded-xl bg-red-950/30 border border-red-500/20">
				<AlertTriangle class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" />
				<div>
					<p class="text-sm font-medium text-red-400">Please NOTE:</p>
					<p class="text-sm text-gray-400 mt-0.5">After you submit the form you will be locked out of your account.</p>
				</div>
			</div>
		</div>
	</LimitCard>
</template>
