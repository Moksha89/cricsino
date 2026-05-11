<script setup>
	import { onMounted } from "vue";

	import { Link } from "@inertiajs/vue3";
	import { ArrowLeftCircle } from "lucide-vue-next";

	import WeCopy from "@/Components/WeCopy.vue";
	import UserLayout from "@/Layouts/UserLayout.vue";
	import CryptoDeposit from "@/Pages/Deposits/Show/CryptoDeposit.vue";
	import Transaction from "@/Pages/Deposits/Show/Transaction.vue";
	const props = defineProps({
		deposit: Object,
		redirect: String,
	});
	const isValidUrl = (string) => {
		try {
			// eslint-disable-next-line no-new
			new URL(string);
			return true;
		} catch (_) {
			return false;
		}
	};
	onMounted(() => {
		if (props.redirect && isValidUrl(props.redirect))
			setTimeout(() => {
				window.location.href = props.redirect;
			}, 1500);
	});
</script>

<template>
	<UserLayout>
		<div class="p-4 sm:p-6 mb-12">
			<div class="flex space-x-4 items-center">
				<Link
					class="text-gray-400 hover:text-white transition-colors duration-300"
					:href="route('deposits.create')">
					<ArrowLeftCircle class="w-10 h-10 stroke-[1px]" />
				</Link>
				<div class="grid">
					<h1 class="text-2xl sm:text-3xl text-white font-inter font-semibold">
						{{ $t("Deposit money") }}
					</h1>
					<WeCopy after :text="deposit.uuid">
						<p>{{ deposit.uuid }}</p>
					</WeCopy>
				</div>
			</div>
			<template v-if="deposit.status === 'processing'">
				<CryptoDeposit
					v-if="
						['coinpayments', 'nowpayments'].includes(
							deposit.gateway.gid,
						)
					"
					:deposit="deposit" />
				<p v-else-if="redirect">
					Please wait as your are redirected in a moment
				</p>
			</template>
			<template v-else>
				<Transaction :deposit="deposit" />
			</template>
		</div>
	</UserLayout>
</template>
