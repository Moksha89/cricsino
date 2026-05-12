<script setup>
import { ref } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import { ArrowLeft, Send, Lock, Clock, CheckCircle2, AlertCircle, XCircle, User, Shield } from "lucide-vue-next";

const props = defineProps({
	conversation: Object,
});

const statusConfig = {
	open: { label: 'Open', color: 'text-blue-400 bg-blue-400/10 border-blue-400/20', icon: AlertCircle },
	pending: { label: 'Pending', color: 'text-amber-400 bg-amber-400/10 border-amber-400/20', icon: Clock },
	answered: { label: 'Answered', color: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20', icon: CheckCircle2 },
	closed: { label: 'Closed', color: 'text-gray-400 bg-gray-400/10 border-gray-400/20', icon: XCircle },
};

const categoryLabels = {
	deposit: 'Deposit', withdrawal: 'Withdrawal', betting: 'Betting', casino: 'Casino',
	account: 'Account', kyc: 'KYC', technical: 'Technical', other: 'Other',
};

const replyForm = useForm({ message: '' });

function sendReply() {
	replyForm.post(route('support.reply', props.conversation.id), {
		preserveScroll: true,
		onSuccess: () => replyForm.reset(),
	});
}

function closeConversation() {
	if (confirm('Are you sure you want to close this ticket?')) {
		router.put(route('support.close', props.conversation.id));
	}
}

function timeAgo(date) {
	if (!date) return '';
	const seconds = Math.floor((new Date() - new Date(date)) / 1000);
	if (seconds < 60) return 'just now';
	const minutes = Math.floor(seconds / 60);
	if (minutes < 60) return `${minutes}m ago`;
	const hours = Math.floor(minutes / 60);
	if (hours < 24) return `${hours}h ago`;
	const days = Math.floor(hours / 24);
	return `${days}d ago`;
}

function formatDate(date) {
	return new Date(date).toLocaleString();
}
</script>

<template>
<UserLayout :show-right-sidebar="false">
	<div class="max-w-3xl mx-auto px-4 py-6">
		<!-- Header -->
		<div class="flex items-start gap-3 mb-6">
			<Link
				:href="route('support.index')"
				class="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition mt-1 flex-shrink-0">
				<ArrowLeft class="w-4 h-4 text-gray-400" />
			</Link>
			<div class="flex-1 min-w-0">
				<div class="flex flex-wrap items-center gap-2 mb-1">
					<h1 class="text-xl font-bold text-white">{{ conversation.subject }}</h1>
					<span
						class="px-2.5 py-0.5 text-xs font-semibold rounded-full border"
						:class="statusConfig[conversation.status]?.color">
						{{ statusConfig[conversation.status]?.label || conversation.status }}
					</span>
				</div>
				<div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
					<span class="capitalize">{{ categoryLabels[conversation.category] || conversation.category }}</span>
					<span>Ticket #{{ conversation.id }}</span>
					<span>{{ formatDate(conversation.created_at) }}</span>
				</div>
			</div>
			<button
				v-if="conversation.status !== 'closed'"
				@click="closeConversation"
				class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-red-400 bg-gray-800 hover:bg-red-400/10 rounded-lg transition flex-shrink-0">
				<Lock class="w-3.5 h-3.5" />
				Close
			</button>
		</div>

		<!-- Messages -->
		<div class="space-y-4 mb-6">
			<div
				v-for="msg in conversation.messages"
				:key="msg.id"
				class="flex gap-3"
				:class="msg.sender_type === 'admin' ? '' : 'flex-row-reverse'">
				<div
					class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
					:class="msg.sender_type === 'admin' ? 'bg-emerald-400/10' : 'bg-primary/10'">
					<Shield v-if="msg.sender_type === 'admin'" class="w-4 h-4 text-emerald-400" />
					<User v-else class="w-4 h-4 text-primary-light" />
				</div>
				<div
					class="max-w-[75%] rounded-2xl px-4 py-3"
					:class="msg.sender_type === 'admin'
						? 'bg-emerald-400/[0.06] border border-emerald-400/10'
						: 'bg-primary/[0.06] border border-primary/10'">
					<div class="flex items-center gap-2 mb-1">
						<span class="text-xs font-semibold"
							:class="msg.sender_type === 'admin' ? 'text-emerald-400' : 'text-primary-light'">
							{{ msg.sender_type === 'admin' ? 'Support Team' : 'You' }}
						</span>
						<span class="text-[10px] text-gray-600">{{ timeAgo(msg.created_at) }}</span>
					</div>
					<p class="text-sm text-gray-300 whitespace-pre-wrap break-words">{{ msg.message }}</p>
				</div>
			</div>
		</div>

		<!-- Reply Form -->
		<div v-if="conversation.status !== 'closed'" class="sticky bottom-0 pb-4">
			<form @submit.prevent="sendReply" class="flex gap-3">
				<div class="flex-1 relative">
					<textarea
						v-model="replyForm.message"
						rows="2"
						placeholder="Type your reply..."
						class="w-full px-4 py-3 pr-12 text-sm bg-gray-900/80 border border-white/[0.08] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary/40 transition resize-none"
						@keydown.meta.enter="sendReply"
						@keydown.ctrl.enter="sendReply"></textarea>
					<p v-if="replyForm.errors.message" class="text-xs text-red-400 mt-1">{{ replyForm.errors.message }}</p>
				</div>
				<button
					type="submit"
					:disabled="replyForm.processing || !replyForm.message.trim()"
					class="self-end flex items-center justify-center w-11 h-11 rounded-xl bg-primary hover:bg-primary-light text-white transition disabled:opacity-50">
					<Send class="w-4 h-4" />
				</button>
			</form>
		</div>

		<!-- Closed Notice -->
		<div v-else class="text-center py-6">
			<div class="flex items-center justify-center gap-2 text-sm text-gray-500">
				<Lock class="w-4 h-4" />
				This conversation has been closed
			</div>
		</div>
	</div>
</UserLayout>
</template>
