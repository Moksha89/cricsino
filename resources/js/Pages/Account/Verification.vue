<script setup>
	import { ShieldCheck, Info } from "lucide-vue-next";
	import UserLayout from "@/Layouts/UserLayout.vue";
	import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
	import AccountSectionCard from "@/Components/Account/AccountSectionCard.vue";
	import VerificationStatusBadge from "@/Components/Account/VerificationStatusBadge.vue";
	import AddressVerify from "@/Pages/Account/Verification/AddressVerify.vue";
	import IdentityVerify from "@/Pages/Account/Verification/IdentityVerify.vue";
	defineProps({
		idTypes: Array,
		addressTypes: Object,
		personal: Object,
	});
</script>

<template>
	<UserLayout>
		<div class="p-4 sm:p-6 pb-20 sm:pb-6">
			<AccountPageHeader
				title="KYC Verification"
				subtitle="Verify your identity to unlock full platform features"
				:icon="ShieldCheck">
				<div class="flex flex-wrap gap-2 mt-3">
					<VerificationStatusBadge
						v-if="!$page.props.enableKyc"
						status="not_required" />
					<template v-else>
						<VerificationStatusBadge
							:status="$page.props.auth.user.isKycVerified ? 'verified' : (personal.proof_of_identity ? 'pending' : 'unverified')" />
					</template>
				</div>
			</AccountPageHeader>

			<div class="grid gap-5 max-w-4xl">
				<IdentityVerify :personal="personal" :idTypes="idTypes" />
				<AddressVerify :personal="personal" :addressTypes="addressTypes" />

				<AccountSectionCard title="Why is KYC required?" :icon="Info">
					<ol class="list-decimal pl-5 space-y-3 text-gray-300 text-sm">
						<li>
							<span class="font-semibold text-white">Protecting You and Us:</span>
							We're required by law to collect this information to prevent financial crimes and protect both you and our platform.
						</li>
						<li>
							<span class="font-semibold text-white">Keeping Your Account Safe:</span>
							By verifying your identity, we can better protect your account from unauthorized access and fraudulent activities.
						</li>
						<li>
							<span class="font-semibold text-white">Enhancing Your Experience:</span>
							With accurate information about you, we can provide more personalized services and recommendations.
						</li>
						<li>
							<span class="font-semibold text-white">Streamlining Future Interactions:</span>
							KYC information helps simplify future transactions and interactions you may have on our platform.
						</li>
						<li>
							<span class="font-semibold text-white">Building Trust Together:</span>
							By participating in our KYC process, you're helping us create a more secure and trustworthy environment for all users.
						</li>
						<li>
							<span class="font-semibold text-white">Ensuring Fair Use:</span>
							KYC helps us maintain a level playing field by ensuring all users are who they claim to be.
						</li>
					</ol>
				</AccountSectionCard>
			</div>
		</div>
	</UserLayout>
</template>
