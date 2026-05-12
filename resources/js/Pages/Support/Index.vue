<script setup>
import { ref, computed, watch } from "vue";
import { router, Link } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import { MessageSquare, Plus, Filter, Clock, CheckCircle2, AlertCircle, XCircle, ChevronRight } from "lucide-vue-next";

const props = defineProps({
	conversations: Object,
	filters: Object,
	categories: Array,
	statuses: Array,
});

const selectedStatus = ref(props.filters?.status || '');
const selectedCategory = ref(props.filters?.category || '');

function applyFilters() {
	const params = {};
	if (selectedStatus.value) params.status = selectedStatus.value;
	if (selectedCategory.value) params.category = selectedCategory.value;
	router.get(route('support.index'), params, { preserveState: true });
}

watch([selectedStatus, selectedCategory], applyFilters);

const statusConfig = {
	open: { label: 'Open', color: 'text-blue-400 bg-blue-400/10 border-blue-400/20', icon: AlertCircle },
	pending: { label: 'Pending', color: 'text-amber-400 bg-amber-400/10 border-amber-400/20', icon: Clock },
	answered: { label: 'Answered', color: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20', icon: CheckCircle2 },
	closed: { label: 'Closed', color: 'text-gray-400 bg-gray-400/10 border-gray-400/20', icon: XCircle },
};

const categoryLabels = {
	deposit: 'Deposit',
	withdrawal: 'Withdrawal',
	betting: 'Betting',
	casino: 'Casino',
	account: 'Account',
	kyc: 'KYC',
	technical: 'Technical',
	other: 'Other',
};

function timeAgo(date) {
	if (!date) return '';
	const seconds = Math.floor((new Date() - new Date(date)) / 1000);
	if (seconds < 60) return 'just now';
	const minutes = Math.floor(seconds / 60);
	if (minutes < 60) return `${minutes}m ago`;
	const hours = Math.floor(minutes / 60);
	if (hours < 24) return `${hours}h ago`;
	const days = Math.floor(hours / 24);
	if (days < 7) return `${days}d ago`;
	return new Date(date).toLocaleDateString();
}
</script>

<template>
<UserLayout :show-right-sidebar="false">
	<div class="max-w-4xl mx-auto px-4 py-6">
		<!-- Header -->
		<div class="flex items-center justify-between mb-6">
			<div>
				<h1 class="text-2xl font-bold text-white">Support</h1>
				<p class="text-sm text-gray-400 mt-1">Get help with your account</p>
			</div>
			<Link
				:href="route('support.create')"
				class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary-light rounded-xl transition shadow-lg shadow-primary/20">
				<Plus class="w-4 h-4" />
				New Ticket
			</Link>
		</div>

		<!-- Filters -->
		<div class="flex flex-wrap gap-3 mb-6">
			<select
				v-model="selectedStatus"
				class="px-3 py-2 text-sm bg-gray-900/80 border border-white/[0.08] rounded-xl text-gray-300 focus:outline-none focus:border-primary/40">
				<option value="">All Statuses</option>
				<option v-for="s in statuses" :key="s" :value="s">{{ statusConfig[s]?.label || s }}</option>
			</select>
			<select
				v-model="selectedCategory"
				class="px-3 py-2 text-sm bg-gray-900/80 border border-white/[0.08] rounded-xl text-gray-300 focus:outline-none focus:border-primary/40">
				<option value="">All Categories</option>
				<option v-for="c in categories" :key="c" :value="c">{{ categoryLabels[c] || c }}</option>
			</select>
		</div>

		<!-- Conversations List -->
		<div v-if="conversations?.data?.length > 0" class="space-y-2">
			<Link
				v-for="conv in conversations.data"
				:key="conv.id"
				:href="route('support.show', conv.id)"
				class="flex items-center gap-4 p-4 rounded-2xl border bg-gray-900/50 border-white/[0.04] hover:border-primary/20 hover:bg-primary/[0.04] transition group">
				<div class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
					<MessageSquare class="w-5 h-5 text-primary-light" />
				</div>
				<div class="flex-1 min-w-0">
					<div class="flex items-center gap-2 mb-1">
						<h3 class="text-sm font-semibold text-white truncate">{{ conv.subject }}</h3>
						<span
							class="flex-shrink-0 px-2 py-0.5 text-[10px] font-semibold rounded-full border"
							:class="statusConfig[conv.status]?.color || 'text-gray-400 bg-gray-400/10'">
							{{ statusConfig[conv.status]?.label || conv.status }}
						</span>
					</div>
					<div class="flex items-center gap-3">
						<span class="text-xs text-gray-500 capitalize">{{ categoryLabels[conv.category] || conv.category }}</span>
						<span class="text-xs text-gray-600">{{ conv.messages_count }} messages</span>
						<span class="text-xs text-gray-600">{{ timeAgo(conv.last_message_at) }}</span>
					</div>
				</div>
				<ChevronRight class="w-4 h-4 text-gray-600 group-hover:text-primary-light transition flex-shrink-0" />
			</Link>
		</div>

		<!-- Empty State -->
		<div v-else class="text-center py-16">
			<div class="w-16 h-16 rounded-2xl bg-gray-800 flex items-center justify-center mx-auto mb-4">
				<MessageSquare class="w-8 h-8 text-gray-600" />
			</div>
			<h3 class="text-lg font-semibold text-white mb-1">No support tickets</h3>
			<p class="text-sm text-gray-400 mb-4">Create a ticket and our support team will help you</p>
			<Link
				:href="route('support.create')"
				class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-light rounded-xl transition">
				<Plus class="w-4 h-4" />
				Create Ticket
			</Link>
		</div>

		<!-- Pagination -->
		<div v-if="conversations?.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
			<Link
				v-for="link in conversations.links"
				:key="link.label"
				:href="link.url"
				:class="[
					'px-3 py-1.5 text-sm rounded-lg transition',
					link.active
						? 'bg-primary text-white font-semibold'
						: link.url
							? 'text-gray-400 hover:text-white hover:bg-white/[0.06]'
							: 'text-gray-600 cursor-not-allowed',
				]"
				v-html="link.label"
				:disabled="!link.url" />
		</div>
	</div>
</UserLayout>
</template>
