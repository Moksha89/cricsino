<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Wallet, TrendingUp, TrendingDown, Clock, Shield } from "lucide-vue-next";
import MoneyFormat from "@/Components/MoneyFormat.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const balance = computed(() => parseFloat(user.value?.balance ?? 0));
const bonus = computed(() => parseFloat(user.value?.bonus_balance ?? 0));
const exposure = computed(() => parseFloat(page.props.exposure ?? 0));
const potentialWinnings = computed(() => parseFloat(page.props.potentialWinnings ?? 0));

const cards = computed(() => [
	{
		label: "Available Balance",
		amount: balance.value,
		icon: Wallet,
		color: "text-green-400",
		bgColor: "bg-green-500/10",
		borderColor: "border-green-500/20",
	},
	{
		label: "Exposure",
		amount: exposure.value,
		icon: TrendingDown,
		color: "text-amber-400",
		bgColor: "bg-amber-500/10",
		borderColor: "border-amber-500/20",
		show: exposure.value > 0,
	},
	{
		label: "Potential Winnings",
		amount: potentialWinnings.value,
		icon: TrendingUp,
		color: "text-purple-400",
		bgColor: "bg-purple-500/10",
		borderColor: "border-purple-500/20",
		show: potentialWinnings.value > 0,
	},
	{
		label: "Bonus Balance",
		amount: bonus.value,
		icon: Shield,
		color: "text-sky-400",
		bgColor: "bg-sky-500/10",
		borderColor: "border-sky-500/20",
		show: bonus.value > 0,
	},
].filter(c => c.show !== false));
</script>

<template>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
	<div
		v-for="card in cards"
		:key="card.label"
		class="rounded-xl border p-4"
		:class="[card.bgColor, card.borderColor]">
		<div class="flex items-center gap-3">
			<div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="card.bgColor">
				<component :is="card.icon" class="w-5 h-5" :class="card.color" />
			</div>
			<div class="min-w-0">
				<p class="text-xs text-gray-400 font-medium">{{ card.label }}</p>
				<p class="text-lg font-bold text-white truncate">
					<MoneyFormat :amount="card.amount" />
				</p>
			</div>
		</div>
	</div>
</div>
</template>
