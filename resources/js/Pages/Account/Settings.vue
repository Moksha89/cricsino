<script setup>
	import AccountSectionCard from "@/Components/Account/AccountSectionCard.vue";
	import DangerZoneCard from "@/Components/Account/DangerZoneCard.vue";
	import SecurityStatusBadge from "@/Components/Account/SecurityStatusBadge.vue";
	import DeleteUserForm from "@/Pages/Account/Settings/DeleteUserForm.vue";
	import ProfilePhoto from "@/Pages/Account/Settings/ProfilePhoto.vue";
	import SettingsLayout from "@/Pages/Account/Settings/SettingsLayout.vue";
	import TwoFactorAuthenticationForm from "@/Pages/Account/Settings/TwoFactorAuthenticationForm.vue";
	import UpdatePasswordForm from "@/Pages/Account/Settings/UpdatePasswordForm.vue";
	import UpdateProfileInformationForm from "@/Pages/Account/Settings/UpdateProfileInformationForm.vue";
	import { User, Lock, Shield, Camera } from "lucide-vue-next";

	defineProps({
		mustVerifyEmail: {
			type: Boolean,
		},
		status: {
			type: String,
		},
	});
</script>

<template>
	<SettingsLayout>
		<div class="grid gap-5 max-w-4xl">
			<AccountSectionCard title="Profile Information" description="Update your name and email address" :icon="User">
				<UpdateProfileInformationForm
					:must-verify-email="mustVerifyEmail"
					:status="status"
					class="max-w-xl" />
			</AccountSectionCard>

			<AccountSectionCard title="Profile Photo" description="Upload or change your profile picture" :icon="Camera">
				<ProfilePhoto
					:photo="$page.props.auth.user.profile_photo_url"
					class="max-w-xl" />
			</AccountSectionCard>

			<AccountSectionCard title="Password" description="Update your password to keep your account secure" :icon="Lock">
				<UpdatePasswordForm class="max-w-xl" />
			</AccountSectionCard>

			<AccountSectionCard :icon="Shield">
				<template #header>
					<div class="flex items-center justify-between flex-wrap gap-2">
						<div class="flex items-center gap-2.5">
							<Shield class="w-5 h-5 text-purple-400 flex-shrink-0" />
							<h2 class="text-lg font-semibold text-white">Two-Factor Authentication</h2>
						</div>
						<SecurityStatusBadge :enabled="$page.props.auth.user?.two_factor_enabled" />
					</div>
					<p class="text-sm text-gray-400 mt-1 ml-[30px]">Add an extra layer of security to your account</p>
				</template>
				<TwoFactorAuthenticationForm
					:requiresConfirmation="$page.props.twoFactorRequiresConfirmation ?? false" />
			</AccountSectionCard>

			<DangerZoneCard
				title="Delete Account"
				description="Permanently delete your account and all associated data. This action cannot be undone.">
				<DeleteUserForm class="max-w-xl" />
			</DangerZoneCard>
		</div>
	</SettingsLayout>
</template>
