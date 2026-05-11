<script setup>
	import { Link, usePage } from "@inertiajs/vue3";
	import { useDark } from "@vueuse/core";

	const isDarkMode = useDark();
	isDarkMode.value = true;

	const appName = usePage().props?.appName ?? 'BetRiver';
</script>
<template>
	<div class="min-h-screen flex items-center overflow-hidden w-full bg-gray-950">
		<div class="flex w-full min-h-screen">
			<!-- Left Panel: Branding -->
			<div class="basis-1/2 hidden relative md:flex items-center justify-center bg-gradient-to-br from-gray-900 via-primary/10 to-gray-950">
				<div class="absolute inset-0 overflow-hidden">
					<div class="absolute -top-40 -left-40 w-80 h-80 bg-primary/10 rounded-full blur-3xl"></div>
					<div class="absolute -bottom-40 -right-40 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
				</div>
				<div class="relative p-8 max-w-md">
					<div class="flex gap-3 items-center mb-6">
						<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center shadow-lg shadow-primary/30">
							<span class="text-white font-bold text-xl">B</span>
						</div>
						<div class="text-3xl font-inter font-bold text-white">
							{{ appName }}
						</div>
					</div>
					<h2 class="text-4xl font-bold mb-3 text-white leading-tight">
						Your Premier<br>Betting Exchange
					</h2>
					<p class="text-gray-400 text-lg mb-8">
						Sports betting, casino games, and more — all in one platform.
					</p>
					<div class="flex gap-4">
						<div class="bg-gray-800/50 rounded-xl p-4 border border-white/[0.06] flex-1 text-center">
							<div class="text-primary font-bold text-2xl">12+</div>
							<div class="text-gray-400 text-xs mt-1">Sports</div>
						</div>
						<div class="bg-gray-800/50 rounded-xl p-4 border border-white/[0.06] flex-1 text-center">
							<div class="text-success font-bold text-2xl">100+</div>
							<div class="text-gray-400 text-xs mt-1">Markets</div>
						</div>
						<div class="bg-gray-800/50 rounded-xl p-4 border border-white/[0.06] flex-1 text-center">
							<div class="text-gold font-bold text-2xl">4</div>
							<div class="text-gray-400 text-xs mt-1">Casino</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Right Panel: Form -->
			<div class="basis-full z-10 md:basis-1/2 flex items-center bg-gray-900 justify-center px-6">
				<div class="w-full max-w-md">
					<Link href="/" class="flex items-center gap-2 mb-6 md:hidden">
						<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center">
							<span class="text-white font-bold text-sm">B</span>
						</div>
						<span class="text-white font-bold text-xl">{{ appName }}</span>
					</Link>
					<slot />
				</div>
			</div>
		</div>
	</div>
</template>
