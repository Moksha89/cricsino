<script setup>
	import { useForm } from "@inertiajs/vue3";
	import { Loader2, Award, TrendingUp } from "lucide-vue-next";

	import { Badge } from "@/Components/ui/badge";
	import MoneyFormat from "@/Components/MoneyFormat.vue";
	import UserLayout from "@/Layouts/UserLayout.vue";
	import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
	import AccountSectionCard from "@/Components/Account/AccountSectionCard.vue";
	import LevelOneIcon from "@/Pages/Account/Level/LevelOneIcon.vue";
	import LevelThreeIcon from "@/Pages/Account/Level/LevelThreeIcon.vue";
	import LevelTwoIcon from "@/Pages/Account/Level/LevelTwoIcon.vue";
	defineProps({
		levelOne: Object,
		levelTwo: Object,
		levelThree: Object,
		bets: Object,
		amounts: Object,
		profitLoss: Object,
	});
	const optinForm = useForm({ level: null });
	const optinFor = (level) => {
		optinForm.level = level;
		optinForm.post(window.route("accounts.optin"), {
			preserveScroll: true,
			preserveState: true,
		});
	};
</script>

<template>
	<UserLayout>
		<div class="p-4 sm:p-6 pb-20 sm:pb-6">
			<AccountPageHeader
				title="Commission Tier"
				subtitle="View your commission tier and betting activity this calendar month"
				:icon="Award" />

			<div class="grid gap-5 max-w-4xl">
				<div class="bg-gradient-to-br from-purple-600/20 to-purple-900/10 rounded-2xl border border-purple-500/20 p-5 sm:p-6">
					<div class="flex items-center gap-4">
						<div class="flex-shrink-0">
							<LevelOneIcon v-if="$page.props.auth.user.isLevelOne" class="text-white w-14 h-14" />
							<LevelTwoIcon v-if="$page.props.auth.user.isLevelTwo" class="text-white w-14 h-14" />
							<LevelThreeIcon v-if="$page.props.auth.user.isLevelThree" class="text-white w-14 h-14" />
						</div>
						<div>
							<Badge variant="outline" class="!border-purple-500/40 !text-purple-300 mb-2">Current Tier</Badge>
							<h3 class="text-lg font-semibold text-white">
								{{ $page.props.auth.user.levelConfig.name }}
							</h3>
							<p class="text-sm text-gray-400 mt-0.5">
								{{ $page.props.auth.user.levelConfig.description }}
							</p>
							<p class="text-sm font-medium text-gray-300 mt-1">
								{{ $page.props.auth.user.levelConfig.limits }}
							</p>
						</div>
					</div>
				</div>

				<div class="grid gap-4 sm:grid-cols-3">
					<div v-for="(stat, key) in { bets, amounts, profitLoss }"
						:key="key"
						class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-5">
						<div class="text-xs text-gray-400 font-semibold tracking-wider uppercase mb-2">
							{{ stat.info }}
						</div>
						<div class="text-2xl sm:text-3xl font-bold text-white font-inter">
							<MoneyFormat v-if="key !== 'bets'" :amount="stat.stat" />
							<span v-else>{{ stat.stat }}</span>
						</div>
						<div v-if="stat.last_month != undefined"
							class="text-xs text-gray-500 mt-3 pt-3 border-t border-white/[0.06]">
							Last month:
							<MoneyFormat v-if="key !== 'bets'" :amount="stat.last_month" />
							<span v-else>{{ stat.last_month }}</span>
						</div>
					</div>
				</div>

				<AccountSectionCard title="Other Commission Tiers" description="Opt in to a different tier level" :icon="TrendingUp">
					<div class="space-y-3">
						<div v-for="(level, idx) in [
							{ data: levelOne, num: 1, icon: LevelOneIcon, check: 'isLevelOne' },
							{ data: levelTwo, num: 2, icon: LevelTwoIcon, check: 'isLevelTwo' },
							{ data: levelThree, num: 3, icon: LevelThreeIcon, check: 'isLevelThree' },
						]" :key="idx"
							class="bg-gray-900/30 rounded-xl border border-white/[0.06] p-4 sm:p-5">
							<div class="flex items-center gap-4">
								<component :is="level.icon" class="text-white w-12 h-12 flex-shrink-0" />
								<div class="flex-1 min-w-0">
									<h3 class="text-base font-semibold text-white">
										{{ level.data.name }}
									</h3>
									<p class="text-sm text-gray-400 mt-0.5">{{ level.data.description }}</p>
									<p class="text-sm font-medium text-gray-300 mt-1">{{ level.data.limits }}</p>
									<div class="mt-3">
										<Badge
											v-if="$page.props.auth.user.requested_next_level == level.num"
											variant="outline"
											class="!border-yellow-500/40 !text-yellow-400">
											Request Pending
										</Badge>
										<button
											v-else-if="!$page.props.auth.user[level.check]"
											@click="optinFor(level.num)"
											:disabled="optinForm.processing && optinForm.level == level.num"
											class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-purple-600/20 text-purple-300 border border-purple-500/30 hover:bg-purple-600/30 transition-all min-h-[44px] disabled:opacity-50">
											<Loader2
												v-if="optinForm.processing && optinForm.level == level.num"
												class="w-4 h-4 animate-spin" />
											{{ $t("Opt in for") }} {{ level.data.name }}
										</button>
										<Badge v-else variant="outline" class="!border-green-500/40 !text-green-400">
											Current Tier
										</Badge>
									</div>
								</div>
							</div>
						</div>
					</div>
				</AccountSectionCard>
			</div>
		</div>
	</UserLayout>
</template>
