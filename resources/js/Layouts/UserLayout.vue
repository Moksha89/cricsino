<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { useDark } from "@vueuse/core";
import { ChevronDown } from "lucide-vue-next";

import UserSidebar from "@/Components/User/UserSidebar.vue";
import UserTopbar from "@/Components/User/UserTopbar.vue";
import MobileBottomNav from "@/Components/User/MobileBottomNav.vue";
import AlertMessages from "@/Layouts/AlertMessages.vue";
import WhatsAppWidget from "@/Components/WhatsAppWidget.vue";
import EventCard from "@/Components/Cards/EventCard.vue";
import StakeSidebarCard from "@/Components/Cards/StakeSidebarCard.vue";
import TicketSidebarCard from "@/Components/Cards/TicketSidebarCard.vue";
import CollapseTransition from "@/Components/CollapseTransition.vue";

const isDarkMode = useDark();
isDarkMode.value = true;

const page = usePage();

defineProps({
	showRightSidebar: { type: Boolean, default: true },
});

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);
const isMobile = ref(false);
const rightSidebarOpen = ref(true);

function checkMobile() {
	isMobile.value = window.innerWidth < 1024;
	if (isMobile.value) {
		sidebarOpen.value = false;
		rightSidebarOpen.value = false;
	}
}

onMounted(() => {
	checkMobile();
	window.addEventListener('resize', checkMobile);
});
onUnmounted(() => {
	window.removeEventListener('resize', checkMobile);
});

const showBets = ref(false);
const showTickets = ref(false);
watch(showBets, (val) => { if (val) showTickets.value = false; });
watch(showTickets, (val) => { if (val) showBets.value = false; });
</script>

<template>
<div class="flex h-screen overflow-hidden bg-gray-950 text-white font-inter">
	<AlertMessages />

	<!-- Sidebar (Desktop) -->
	<aside
		v-show="sidebarOpen && !isMobile"
		class="w-[250px] flex-shrink-0 bg-gray-900 border-r border-white/[0.06] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
		<UserSidebar @navigate="mobileMenuOpen = false" />
	</aside>

	<!-- Main Content Area -->
	<div class="flex-1 flex flex-col min-w-0">
		<!-- Top Bar -->
		<UserTopbar
			:is-mobile="isMobile"
			@toggle-sidebar="sidebarOpen = !sidebarOpen"
			@toggle-mobile-menu="mobileMenuOpen = !mobileMenuOpen" />

		<!-- Content + Right Sidebar -->
		<div class="flex-1 flex overflow-hidden">
			<!-- Page Content -->
			<main class="flex-1 overflow-y-auto bg-gray-950 scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent"
				:class="{ 'pb-16 lg:pb-0': isMobile }">
				<slot />
			</main>

			<!-- Right Sidebar (Desktop) — bet slip, events -->
			<aside
				v-if="showRightSidebar && !isMobile && rightSidebarOpen"
				class="w-[320px] 2xl:w-[380px] flex-shrink-0 bg-gray-900 border-l border-white/[0.06] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
				<div>
					<slot name="right-sidebar-top" />

					<!-- User Bets -->
					<template v-if="(page.props.auth?.user?.stakes ?? []).length > 0">
						<div
							class="bg-gray-800 text-white text-xs border-b border-white/[0.06] flex items-center justify-between px-3 uppercase font-inter tracking-[1px] font-bold h-11 flex-shrink-0">
							<div @click="showBets = !showBets" class="flex-grow cursor-pointer">
								Your Bets
							</div>
							<div class="flex items-center">
								<Link
									:href="route('accounts.statement', { types: 'bet' })"
									v-show="showBets"
									class="text-primary-light text-xs hover:text-primary hover:underline">
									See All
								</Link>
								<a class="h-full px-3" href="#" @click.prevent="showBets = !showBets">
									<ChevronDown
										:class="{ 'rotate-180': showBets }"
										class="w-4 h-4 ml-2 transition-transform duration-300" />
								</a>
							</div>
						</div>
						<CollapseTransition>
							<div v-show="showBets">
								<StakeSidebarCard
									v-for="stake in page.props.auth?.user?.stakes ?? []"
									:key="stake.id"
									:stake="stake" />
							</div>
						</CollapseTransition>
					</template>

					<!-- User Tickets -->
					<template v-if="(page.props.auth?.user?.tickets ?? []).length > 0">
						<div
							class="bg-gray-800 text-white border-b border-white/[0.06] flex items-center justify-between px-3 uppercase font-inter text-xs tracking-[1px] font-bold h-11 flex-shrink-0">
							<div @click="showTickets = !showTickets" class="flex-grow cursor-pointer">
								Your Tickets
							</div>
							<div class="flex items-center">
								<Link
									:href="route('accounts.statement', { types: 'ticket' })"
									v-show="showTickets"
									class="text-primary-light text-xs hover:text-primary hover:underline">
									See All
								</Link>
								<a class="h-full px-3" href="#" @click.prevent="showTickets = !showTickets">
									<ChevronDown
										:class="{ 'rotate-180': showTickets }"
										class="w-4 h-4 ml-2 transition-transform duration-300" />
								</a>
							</div>
						</div>
						<CollapseTransition>
							<div v-show="showTickets">
								<TicketSidebarCard
									v-for="ticket in page.props.auth?.user?.tickets ?? []"
									:key="ticket.id"
									:ticket="ticket" />
							</div>
						</CollapseTransition>
					</template>

					<!-- Default: Top Events or custom right sidebar -->
					<slot name="right-sidebar">
						<div
							class="bg-gray-800 text-white border-b border-white/[0.06] flex items-center px-3 uppercase font-inter text-sm tracking-[1px] font-bold h-11 flex-shrink-0">
							Top Events
						</div>
						<div class="grid">
							<EventCard
								v-for="game in page.props.popular"
								:key="game.slug"
								:game="game" />
						</div>
					</slot>
				</div>
			</aside>
		</div>
	</div>

	<!-- Mobile Bottom Navigation -->
	<MobileBottomNav v-if="isMobile" />

	<!-- Mobile Sidebar Overlay -->
	<Teleport to="body">
		<div v-if="mobileMenuOpen && isMobile" class="fixed inset-0 z-[100]">
			<div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
			<aside class="absolute left-0 top-0 bottom-0 w-[280px] bg-gray-900 overflow-y-auto shadow-2xl">
				<div class="flex items-center justify-end p-4">
					<button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-white/[0.06]">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
						</svg>
					</button>
				</div>
				<UserSidebar @navigate="mobileMenuOpen = false" />
			</aside>
		</div>
	</Teleport>

	<!-- WhatsApp Widget -->
	<WhatsAppWidget />
</div>
</template>
