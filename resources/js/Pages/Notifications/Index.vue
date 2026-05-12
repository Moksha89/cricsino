<script setup>
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import { Bell, CheckCheck, ArrowDownCircle, ArrowUpCircle, CheckCircle, XCircle, Target, UserPlus, PlusCircle, MinusCircle, ChevronLeft, ChevronRight } from "lucide-vue-next";

const props = defineProps({
	notifications: Object,
});

const iconMap = {
	'arrow-down-circle': ArrowDownCircle,
	'arrow-up-circle': ArrowUpCircle,
	'check-circle': CheckCircle,
	'x-circle': XCircle,
	'target': Target,
	'user-plus': UserPlus,
	'plus-circle': PlusCircle,
	'minus-circle': MinusCircle,
};

const colorMap = {
	blue: 'text-blue-400 bg-blue-400/10',
	green: 'text-emerald-400 bg-emerald-400/10',
	red: 'text-red-400 bg-red-400/10',
	amber: 'text-amber-400 bg-amber-400/10',
};

function getIcon(name) {
	return iconMap[name] || Bell;
}

function getColorClasses(color) {
	return colorMap[color] || 'text-gray-400 bg-gray-400/10';
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
	router.post(window.route('notifications.read', { id }), {}, {
		preserveScroll: true,
	});
}

function markAllAsRead() {
	router.post(window.route('notifications.read.all'), {}, {
		preserveScroll: true,
	});
}

const hasUnread = computed(() => {
	return props.notifications?.data?.some(n => !n.read_at) ?? false;
});
</script>

<template>
<UserLayout :show-right-sidebar="false">
	<div class="max-w-3xl mx-auto px-4 py-6">
		<!-- Header -->
		<div class="flex items-center justify-between mb-6">
			<div>
				<h1 class="text-2xl font-bold text-white">Notifications</h1>
				<p class="text-sm text-gray-400 mt-1">Stay updated on your account activity</p>
			</div>
			<button
				v-if="hasUnread"
				@click="markAllAsRead"
				class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-light hover:text-primary bg-primary/10 hover:bg-primary/20 rounded-xl transition">
				<CheckCheck class="w-4 h-4" />
				Mark all read
			</button>
		</div>

		<!-- Notifications List -->
		<div v-if="notifications?.data?.length > 0" class="space-y-2">
			<div
				v-for="notification in notifications.data"
				:key="notification.id"
				class="flex items-start gap-4 p-4 rounded-2xl border transition"
				:class="notification.read_at
					? 'bg-gray-900/50 border-white/[0.04]'
					: 'bg-primary/[0.04] border-primary/20'">
				<div
					class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center"
					:class="getColorClasses(notification.data?.color)">
					<component :is="getIcon(notification.data?.icon)" class="w-5 h-5" />
				</div>
				<div class="flex-1 min-w-0">
					<div class="flex items-center gap-2">
						<h3 class="text-sm font-semibold text-white">{{ notification.data?.title }}</h3>
						<span v-if="!notification.read_at" class="w-2 h-2 rounded-full bg-primary flex-shrink-0"></span>
					</div>
					<p class="text-sm text-gray-400 mt-0.5">{{ notification.data?.message }}</p>
					<div class="flex items-center gap-3 mt-2">
						<span class="text-xs text-gray-500">{{ timeAgo(notification.created_at) }}</span>
						<span class="text-xs text-gray-600">{{ formatDate(notification.created_at) }}</span>
					</div>
				</div>
				<button
					v-if="!notification.read_at"
					@click="markAsRead(notification.id)"
					class="flex-shrink-0 text-xs text-primary-light hover:text-primary transition mt-1">
					Mark read
				</button>
			</div>
		</div>

		<!-- Empty State -->
		<div v-else class="text-center py-16">
			<div class="w-16 h-16 rounded-2xl bg-gray-800 flex items-center justify-center mx-auto mb-4">
				<Bell class="w-8 h-8 text-gray-600" />
			</div>
			<h3 class="text-lg font-semibold text-white mb-1">No notifications</h3>
			<p class="text-sm text-gray-400">We'll notify you when something important happens</p>
		</div>

		<!-- Pagination -->
		<div v-if="notifications?.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
			<Link
				v-for="link in notifications.links"
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
