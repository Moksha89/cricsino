<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";
import { Bell, CheckCheck, ArrowDownCircle, ArrowUpCircle, CheckCircle, XCircle, UserPlus, PlusCircle, MinusCircle } from "lucide-vue-next";

const page = usePage();
const unreadCount = computed(() => page.props.adminNotificationCount ?? 0);

function getCsrfToken() {
	const meta = document.querySelector('meta[name="csrf-token"]');
	if (meta) return meta.content;
	const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
	return match ? decodeURIComponent(match[1]) : '';
}
const dropdownOpen = ref(false);
const notifications = ref([]);
const loading = ref(false);

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
	blue: 'text-blue-400',
	green: 'text-emerald-400',
	red: 'text-red-400',
	amber: 'text-amber-400',
};

function getIcon(name) {
	return iconMap[name] || Bell;
}

function getColor(color) {
	return colorMap[color] || 'text-gray-400';
}

function timeAgo(date) {
	const seconds = Math.floor((new Date() - new Date(date)) / 1000);
	if (seconds < 60) return 'just now';
	const minutes = Math.floor(seconds / 60);
	if (minutes < 60) return `${minutes}m ago`;
	const hours = Math.floor(minutes / 60);
	if (hours < 24) return `${hours}h ago`;
	const days = Math.floor(hours / 24);
	return `${days}d ago`;
}

async function fetchNotifications() {
	if (loading.value) return;
	loading.value = true;
	try {
		const response = await fetch(window.route('admin.notifications.latest'), {
			headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
		});
		const data = await response.json();
		notifications.value = data.notifications || [];
	} catch (e) {
		// silently fail
	} finally {
		loading.value = false;
	}
}

function toggleDropdown() {
	dropdownOpen.value = !dropdownOpen.value;
	if (dropdownOpen.value) {
		fetchNotifications();
	}
}

async function markAsRead(id) {
	try {
		await fetch(window.route('admin.notifications.read', { id }), {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-XSRF-TOKEN': getCsrfToken(),
			},
		});
		const n = notifications.value.find(n => n.id === id);
		if (n) n.read_at = new Date().toISOString();
		router.reload({ only: ['adminNotificationCount'] });
	} catch (e) {
		// silently fail
	}
}

async function markAllAsRead() {
	try {
		await fetch(window.route('admin.notifications.read.all'), {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-XSRF-TOKEN': getCsrfToken(),
			},
		});
		notifications.value.forEach(n => n.read_at = new Date().toISOString());
		router.reload({ only: ['adminNotificationCount'] });
	} catch (e) {
		// silently fail
	}
}

function handleClickOutside(e) {
	const el = document.getElementById('admin-notification-bell');
	if (el && !el.contains(e.target)) {
		dropdownOpen.value = false;
	}
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
<div id="admin-notification-bell" class="relative">
	<button
		@click.stop="toggleDropdown"
		class="btn relative h-8 w-8 rounded-full p-0 bg-slate-300/20 hover:bg-slate-300/40 focus:bg-slate-300/40 active:bg-slate-300/30 dark:bg-navy-300/20 dark:hover:bg-navy-300/40 dark:focus:bg-navy-300/40 dark:active:bg-navy-300/30">
		<Bell class="w-5 h-5 text-white" />
		<span
			v-if="unreadCount > 0"
			class="absolute -top-1 -right-1 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold px-1">
			{{ unreadCount > 99 ? '99+' : unreadCount }}
		</span>
	</button>

	<transition
		enter-active-class="transition ease-out duration-150"
		enter-from-class="opacity-0 scale-95 -translate-y-1"
		enter-to-class="opacity-100 scale-100 translate-y-0"
		leave-active-class="transition ease-in duration-100"
		leave-from-class="opacity-100 scale-100 translate-y-0"
		leave-to-class="opacity-0 scale-95 -translate-y-1">
		<div
			v-show="dropdownOpen"
			class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-2xl z-50 overflow-hidden">
			<!-- Header -->
			<div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
				<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Admin Notifications</h3>
				<button
					v-if="unreadCount > 0"
					@click="markAllAsRead"
					class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition">
					<CheckCheck class="w-3.5 h-3.5" />
					Mark all read
				</button>
			</div>

			<!-- Notifications List -->
			<div class="max-h-80 overflow-y-auto">
				<div v-if="loading && notifications.length === 0" class="px-4 py-8 text-center">
					<div class="w-6 h-6 border-2 border-blue-300 border-t-blue-600 rounded-full animate-spin mx-auto"></div>
					<p class="text-xs text-gray-500 mt-2">Loading...</p>
				</div>

				<div v-else-if="notifications.length === 0" class="px-4 py-8 text-center">
					<Bell class="w-8 h-8 text-gray-400 mx-auto mb-2" />
					<p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
					<p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Admin events will appear here</p>
				</div>

				<button
					v-for="notification in notifications"
					:key="notification.id"
					@click="!notification.read_at && markAsRead(notification.id)"
					class="w-full text-left flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition border-b border-gray-100 dark:border-gray-600/50 last:border-0"
					:class="{ 'bg-blue-50 dark:bg-blue-900/10': !notification.read_at }">
					<div class="flex-shrink-0 mt-0.5">
						<component
							:is="getIcon(notification.data?.icon)"
							class="w-5 h-5"
							:class="getColor(notification.data?.color)" />
					</div>
					<div class="flex-1 min-w-0">
						<div class="flex items-center gap-2">
							<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ notification.data?.title }}</p>
							<span v-if="!notification.read_at" class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
						</div>
						<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ notification.data?.message }}</p>
						<p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">{{ timeAgo(notification.created_at) }}</p>
					</div>
				</button>
			</div>

			<!-- Footer -->
			<div class="border-t border-gray-200 dark:border-gray-600 px-4 py-2 bg-gray-50 dark:bg-gray-800">
				<Link
					:href="route('admin.notifications.index')"
					@click="dropdownOpen = false"
					class="block text-center text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition py-1">
					View all notifications
				</Link>
			</div>
		</div>
	</transition>
</div>
</template>
