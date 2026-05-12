<script setup>
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Bell, CheckCheck, ArrowDownCircle, ArrowUpCircle, CheckCircle, XCircle, UserPlus, PlusCircle, MinusCircle } from "lucide-vue-next";

const props = defineProps({
	notifications: Object,
});

const iconMap = {
	'arrow-down-circle': ArrowDownCircle,
	'arrow-up-circle': ArrowUpCircle,
	'check-circle': CheckCircle,
	'x-circle': XCircle,
	'user-plus': UserPlus,
	'plus-circle': PlusCircle,
	'minus-circle': MinusCircle,
};

const colorMap = {
	blue: 'text-blue-500 bg-blue-100 dark:text-blue-400 dark:bg-blue-900/20',
	green: 'text-emerald-500 bg-emerald-100 dark:text-emerald-400 dark:bg-emerald-900/20',
	red: 'text-red-500 bg-red-100 dark:text-red-400 dark:bg-red-900/20',
	amber: 'text-amber-500 bg-amber-100 dark:text-amber-400 dark:bg-amber-900/20',
};

function getIcon(name) {
	return iconMap[name] || Bell;
}

function getColorClasses(color) {
	return colorMap[color] || 'text-gray-500 bg-gray-100 dark:text-gray-400 dark:bg-gray-800';
}

function timeAgo(date) {
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

function formatDate(date) {
	return new Date(date).toLocaleString();
}

function markAsRead(id) {
	router.post(window.route('admin.notifications.read', { id }), {}, {
		preserveScroll: true,
	});
}

function markAllAsRead() {
	router.post(window.route('admin.notifications.read.all'), {}, {
		preserveScroll: true,
	});
}

const hasUnread = computed(() => {
	return props.notifications?.data?.some(n => !n.read_at) ?? false;
});
</script>

<template>
<AdminLayout>
	<div class="p-6">
		<!-- Header -->
		<div class="flex items-center justify-between mb-6">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Notifications</h1>
				<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">System events and alerts</p>
			</div>
			<button
				v-if="hasUnread"
				@click="markAllAsRead"
				class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition">
				<CheckCheck class="w-4 h-4" />
				Mark all read
			</button>
		</div>

		<!-- Notifications List -->
		<div v-if="notifications?.data?.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
			<div
				v-for="(notification, index) in notifications.data"
				:key="notification.id"
				class="flex items-start gap-4 p-4 transition"
				:class="[
					notification.read_at ? '' : 'bg-blue-50/50 dark:bg-blue-900/5',
					index < notifications.data.length - 1 ? 'border-b border-gray-100 dark:border-gray-700' : '',
				]">
				<div
					class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
					:class="getColorClasses(notification.data?.color)">
					<component :is="getIcon(notification.data?.icon)" class="w-5 h-5" />
				</div>
				<div class="flex-1 min-w-0">
					<div class="flex items-center gap-2">
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ notification.data?.title }}</h3>
						<span v-if="!notification.read_at" class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
					</div>
					<p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ notification.data?.message }}</p>
					<div class="flex items-center gap-3 mt-2">
						<span class="text-xs text-gray-400 dark:text-gray-500">{{ timeAgo(notification.created_at) }}</span>
						<span class="text-xs text-gray-300 dark:text-gray-600">{{ formatDate(notification.created_at) }}</span>
					</div>
				</div>
				<button
					v-if="!notification.read_at"
					@click="markAsRead(notification.id)"
					class="flex-shrink-0 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition mt-1">
					Mark read
				</button>
			</div>
		</div>

		<!-- Empty State -->
		<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 text-center py-16">
			<div class="w-16 h-16 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
				<Bell class="w-8 h-8 text-gray-400" />
			</div>
			<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No notifications</h3>
			<p class="text-sm text-gray-500 dark:text-gray-400">Admin events will appear here</p>
		</div>

		<!-- Pagination -->
		<div v-if="notifications?.last_page > 1" class="flex items-center justify-center gap-1 mt-6">
			<Link
				v-for="link in notifications.links"
				:key="link.label"
				:href="link.url"
				:class="[
					'px-3 py-1.5 text-sm rounded-lg transition',
					link.active
						? 'bg-blue-600 text-white font-semibold'
						: link.url
							? 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
							: 'text-gray-300 dark:text-gray-600 cursor-not-allowed',
				]"
				v-html="link.label"
				:disabled="!link.url" />
		</div>
	</div>
</AdminLayout>
</template>
