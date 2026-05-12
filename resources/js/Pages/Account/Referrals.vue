<script setup>
	import { Users } from "lucide-vue-next";
	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import UserLayout from "@/Layouts/UserLayout.vue";
	import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
	import AccountSectionCard from "@/Components/Account/AccountSectionCard.vue";
	import ReferralLinkCard from "@/Components/Account/ReferralLinkCard.vue";
	defineProps({
		referrals: Object,
		earnings: Object,
		lifeTime: Object,
		levels: Object,
		direct: Object,
	});
</script>

<template>
	<UserLayout>
		<div class="p-4 sm:p-6 pb-20 sm:pb-6">
			<AccountPageHeader
				title="Referral Earnings"
				subtitle="View and monitor your referral activity and earnings"
				:icon="Users" />

			<div class="grid gap-5 max-w-4xl">
				<ReferralLinkCard
					:referralUrl="route('home', { ref: $page.props.auth.user.refId })"
					:directCount="direct" />

				<div class="grid gap-4 sm:grid-cols-3">
					<div v-for="(stat, key) in { referrals, earnings, lifeTime }"
						:key="key"
						class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-5">
						<div class="text-xs text-gray-400 font-semibold tracking-wider uppercase mb-2">
							{{ stat.info }}
						</div>
						<div class="text-2xl sm:text-3xl font-bold text-white font-inter">
							<MoneyFormat v-if="key !== 'referrals'" :amount="stat.stat" />
							<span v-else>{{ stat.stat }}</span>
						</div>
						<div v-if="stat.last_month != undefined"
							class="text-xs text-gray-500 mt-3 pt-3 border-t border-white/[0.06]">
							Last month:
							<MoneyFormat v-if="key !== 'referrals'" :amount="stat.last_month" />
							<span v-else>{{ stat.last_month }}</span>
						</div>
					</div>
				</div>

				<AccountSectionCard title="Referral Commission Rates" description="As a percentage of admin fees, depending on user tier">
					<div class="overflow-x-auto -mx-4 sm:-mx-6">
						<table class="w-full text-sm" role="table">
							<tbody role="rowgroup">
								<tr
									v-for="(level, i) in levels"
									:key="i"
									role="row"
									class="border-b border-white/[0.06] last:border-0">
									<td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-sm font-medium text-white">
										Level #{{ level[0].level }}
									</td>
									<td
										v-for="(lvl, j) in level"
										:key="j"
										class="px-4 sm:px-6 py-3.5 uppercase text-xs whitespace-nowrap font-medium text-gray-400">
										{{ lvl.type }}:
										<span class="font-semibold text-white ml-1">
											{{ lvl.percent * 1 }}%
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</AccountSectionCard>
			</div>
		</div>
	</UserLayout>
</template>
