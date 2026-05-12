<script setup>
	import { ref } from "vue";
	import { Head, Link, useForm, router } from "@inertiajs/vue3";
	import { ArrowLeft, Send, User, Shield, Lock, Unlock, MessageSquare } from "lucide-vue-next";
	import AdminLayout from "@/Layouts/AdminLayout.vue";

	const props = defineProps({
		conversation: Object,
		statuses: Array,
	});

	const statusColors = {
		open: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
		pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
		answered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
		closed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
	};

	const categoryLabels = {
		deposit: 'Deposit', withdrawal: 'Withdrawal', betting: 'Betting', casino: 'Casino',
		account: 'Account', kyc: 'KYC', technical: 'Technical', other: 'Other',
	};

	const replyForm = useForm({ message: '' });
	const statusForm = useForm({ status: props.conversation.status });

	function sendReply() {
		replyForm.post(window.route('admin.support.reply', props.conversation.id), {
			preserveScroll: true,
			onSuccess: () => replyForm.reset(),
		});
	}

	function updateStatus() {
		statusForm.put(window.route('admin.support.status', props.conversation.id), {
			preserveScroll: true,
		});
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
	<AdminLayout>
		<Head :title="'Ticket #' + conversation.id" />
		<div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
			<div class="flex items-center gap-3 mb-4">
				<Link
					:href="route('admin.support.index')"
					class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
					<ArrowLeft class="w-4 h-4" />
					Back to Tickets
				</Link>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
				<!-- User Info Card -->
				<div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">User Information</h3>
					<div class="space-y-2 text-sm">
						<div class="flex justify-between">
							<span class="text-gray-500 dark:text-gray-400">Name</span>
							<span class="text-gray-900 dark:text-white font-medium">{{ conversation.user?.name }}</span>
						</div>
						<div class="flex justify-between">
							<span class="text-gray-500 dark:text-gray-400">Email</span>
							<span class="text-gray-900 dark:text-white">{{ conversation.user?.email }}</span>
						</div>
						<div class="flex justify-between">
							<span class="text-gray-500 dark:text-gray-400">User ID</span>
							<span class="text-gray-900 dark:text-white">#{{ conversation.user?.id }}</span>
						</div>
					</div>
				</div>

				<!-- Ticket Info Card -->
				<div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Ticket Details</h3>
					<div class="space-y-2 text-sm">
						<div class="flex justify-between">
							<span class="text-gray-500 dark:text-gray-400">Ticket ID</span>
							<span class="text-gray-900 dark:text-white font-medium">#{{ conversation.id }}</span>
						</div>
						<div class="flex justify-between">
							<span class="text-gray-500 dark:text-gray-400">Category</span>
							<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 capitalize">
								{{ categoryLabels[conversation.category] || conversation.category }}
							</span>
						</div>
						<div class="flex justify-between items-center">
							<span class="text-gray-500 dark:text-gray-400">Status</span>
							<span class="px-2 py-0.5 text-xs font-medium rounded-full capitalize" :class="statusColors[conversation.status]">
								{{ conversation.status }}
							</span>
						</div>
						<div class="flex justify-between">
							<span class="text-gray-500 dark:text-gray-400">Created</span>
							<span class="text-gray-900 dark:text-white text-xs">{{ formatDate(conversation.created_at) }}</span>
						</div>
					</div>
				</div>

				<!-- Status Update Card -->
				<div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Update Status</h3>
					<form @submit.prevent="updateStatus" class="space-y-3">
						<select
							v-model="statusForm.status"
							class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
							<option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
						</select>
						<button
							type="submit"
							:disabled="statusForm.processing || statusForm.status === conversation.status"
							class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50">
							Update Status
						</button>
					</form>
				</div>
			</div>
		</div>

		<!-- Subject header -->
		<div class="px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
			<h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ conversation.subject }}</h2>
			<span class="text-sm text-gray-500 dark:text-gray-400">{{ conversation.messages?.length || 0 }} messages</span>
		</div>

		<!-- Messages -->
		<div class="p-4 space-y-4 max-h-[500px] overflow-y-auto" style="min-height: 200px;">
			<div
				v-for="msg in conversation.messages"
				:key="msg.id"
				class="flex gap-3"
				:class="msg.sender_type === 'user' ? '' : 'flex-row-reverse'">
				<div
					class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
					:class="msg.sender_type === 'admin' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-700'">
					<Shield v-if="msg.sender_type === 'admin'" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
					<User v-else class="w-4 h-4 text-gray-600 dark:text-gray-400" />
				</div>
				<div
					class="max-w-[75%] rounded-lg px-4 py-3"
					:class="msg.sender_type === 'admin'
						? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/40'
						: 'bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600'">
					<div class="flex items-center gap-2 mb-1">
						<span class="text-xs font-semibold"
							:class="msg.sender_type === 'admin' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'">
							{{ msg.sender_type === 'admin' ? (msg.sender?.name || 'Admin') : (msg.sender?.name || conversation.user?.name || 'User') }}
						</span>
						<span class="text-[10px] text-gray-400">{{ timeAgo(msg.created_at) }}</span>
					</div>
					<p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ msg.message }}</p>
				</div>
			</div>

			<div v-if="!conversation.messages?.length" class="text-center py-8 text-gray-500 dark:text-gray-400">
				<MessageSquare class="w-8 h-8 mx-auto mb-2 opacity-50" />
				<p class="text-sm">No messages yet</p>
			</div>
		</div>

		<!-- Reply Form -->
		<div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
			<form @submit.prevent="sendReply" class="flex gap-3">
				<div class="flex-1">
					<textarea
						v-model="replyForm.message"
						rows="3"
						placeholder="Type your reply as admin..."
						class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-primary focus:border-primary resize-none"
						@keydown.meta.enter="sendReply"
						@keydown.ctrl.enter="sendReply"></textarea>
					<p v-if="replyForm.errors.message" class="text-xs text-red-500 mt-1">{{ replyForm.errors.message }}</p>
				</div>
				<button
					type="submit"
					:disabled="replyForm.processing || !replyForm.message.trim()"
					class="self-end px-4 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50 flex items-center gap-2">
					<Send class="w-4 h-4" />
					Reply
				</button>
			</form>
		</div>
	</AdminLayout>
</template>
