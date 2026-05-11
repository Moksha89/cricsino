<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { Bell, Shield, Check } from "lucide-vue-next";

	import CollapseTransition from "@/Components/CollapseTransition.vue";
	import FormLabel from "@/Components/FormLabel.vue";
	import Loading from "@/Components/Loading.vue";
	import Switch from "@/Components/Switch.vue";
	import AccountSectionCard from "@/Components/Account/AccountSectionCard.vue";
	import {
		Select,
		SelectContent,
		SelectGroup,
		SelectItem,
		SelectTrigger,
		SelectValue,
	} from "@/Components/ui/select";
	import SettingsLayout from "@/Pages/Account/Settings/SettingsLayout.vue";

	const props = defineProps({
		personal: Object,
		mailOptions: Object,
	});
	const form = useForm({
		bet_emails: props.personal.bet_emails,
		mailing_list: props.personal.mailing_list,
		confirm_bets: props.personal.confirm_bets,
	});

	const updateGrossLimit = () => {
		form.put(window.route("personal.alerts"), {
			preserveScroll: true,
		});
	};
</script>

<template>
	<SettingsLayout>
		<div class="grid gap-5 max-w-4xl">
			<AccountSectionCard title="Notification Preferences" description="Control how and when you receive notifications" :icon="Bell">
				<div class="space-y-6">
					<div>
						<FormLabel class="mb-2">Bet Result Emails</FormLabel>
						<Select v-model="form.bet_emails">
							<SelectTrigger class="max-w-sm bg-gray-900/50 border-white/[0.06] rounded-xl min-h-[44px]">
								<SelectValue
									class="text-gray-300 font-medium"
									placeholder="Method of notification" />
							</SelectTrigger>
							<SelectContent>
								<SelectGroup>
									<SelectItem
										v-for="option in mailOptions"
										:key="option.value"
										:value="option.value">
										{{ option.label }}
									</SelectItem>
								</SelectGroup>
							</SelectContent>
						</Select>
					</div>

					<div class="space-y-4">
						<div>
							<FormLabel class="mb-2">Mailing List</FormLabel>
							<Switch v-model="form.mailing_list">
								I'd like to receive updates and special offers.
							</Switch>
						</div>

						<div>
							<FormLabel class="mb-2">Skip bet confirmations</FormLabel>
							<Switch v-model="form.confirm_bets">
								Tick to place bets immediately without any confirmation.
							</Switch>
						</div>
					</div>
				</div>

				<div class="mt-6">
					<CollapseTransition>
						<p v-show="form.recentlySuccessful" class="mb-3 text-green-400 text-sm">
							Saved successfully
						</p>
					</CollapseTransition>
					<button
						@click="updateGrossLimit"
						:disabled="form.processing"
						class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-purple-600 hover:bg-purple-700 text-white transition-all duration-200 min-h-[44px] disabled:opacity-50">
						<Loading v-if="form.processing" class="!w-4 !h-4" />
						Update Notification Settings
					</button>
				</div>
			</AccountSectionCard>

			<AccountSectionCard title="Privacy Protection" description="How we protect your contact information" :icon="Shield">
				<ul class="space-y-3 text-gray-400 text-sm">
					<li class="flex items-start gap-3">
						<div class="w-5 h-5 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
							<Check class="w-3 h-3 text-green-400" />
						</div>
						<span>We will never spam you or send unsolicited emails.</span>
					</li>
					<li class="flex items-start gap-3">
						<div class="w-5 h-5 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
							<Check class="w-3 h-3 text-green-400" />
						</div>
						<span>Your contact information will never be shared with third-party advertisers or spammers.</span>
					</li>
					<li class="flex items-start gap-3">
						<div class="w-5 h-5 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
							<Check class="w-3 h-3 text-green-400" />
						</div>
						<span>We use industry-standard encryption to protect your data during transmission and storage.</span>
					</li>
					<li class="flex items-start gap-3">
						<div class="w-5 h-5 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
							<Check class="w-3 h-3 text-green-400" />
						</div>
						<span>You can opt-out of non-essential communications at any time.</span>
					</li>
					<li class="flex items-start gap-3">
						<div class="w-5 h-5 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
							<Check class="w-3 h-3 text-green-400" />
						</div>
						<span>We regularly review and update our privacy practices to ensure your information remains secure.</span>
					</li>
				</ul>
			</AccountSectionCard>
		</div>
	</SettingsLayout>
</template>
