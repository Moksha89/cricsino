<script setup>
import { Plus, Minus } from "lucide-vue-next";
import MoneyFormat from "@/Components/MoneyFormat.vue";

defineProps({
	transaction: { type: Object, required: true },
});
defineEmits(["view"]);
</script>

<template>
<div
	@click="$emit('view', transaction)"
	class="bg-white/[0.03] rounded-xl border border-white/[0.06] p-4 hover:bg-white/[0.05] transition-colors cursor-pointer">
	<div class="flex items-start justify-between gap-3">
		<div class="flex items-center gap-3 min-w-0">
			<div
				class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
				:class="transaction.action === 'credit' ? 'bg-green-500/10' : 'bg-red-500/10'">
				<Plus v-if="transaction.action === 'credit'" class="w-4 h-4 text-green-400" />
				<Minus v-else class="w-4 h-4 text-red-400" />
			</div>
			<div class="min-w-0">
				<p class="text-sm font-medium text-white truncate">{{ transaction.description }}</p>
				<p class="text-xs text-gray-500 mt-0.5">{{ transaction.created_at }}</p>
			</div>
		</div>
		<div class="text-right flex-shrink-0">
			<p
				class="text-sm font-bold"
				:class="transaction.action === 'credit' ? 'text-green-400' : 'text-red-400'">
				<span>{{ transaction.action === 'credit' ? '+' : '-' }}</span>
				<MoneyFormat :amount="transaction.amount" />
			</p>
			<p class="text-xs text-gray-500 uppercase mt-0.5">{{ transaction.uid }}</p>
		</div>
	</div>
</div>
</template>
