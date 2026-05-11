<script setup>
import { Head } from "@inertiajs/vue3";
import { User, Lock, Shield, Camera } from "lucide-vue-next";

import UserLayout from "@/Layouts/UserLayout.vue";
import AccountPageHeader from "@/Components/Account/AccountPageHeader.vue";
import AccountSectionCard from "@/Components/Account/AccountSectionCard.vue";
import DangerZoneCard from "@/Components/Account/DangerZoneCard.vue";
import SecurityStatusBadge from "@/Components/Account/SecurityStatusBadge.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import ProfilePhoto from "./Partials/ProfilePhoto.vue";
import TwoFactorAuthenticationForm from "./Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";

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
    <Head title="Profile" />

    <UserLayout>
        <div class="p-4 sm:p-6 pb-20 sm:pb-6">
            <AccountPageHeader
                title="Profile"
                subtitle="Manage your personal information and security settings"
                :icon="User" />

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
        </div>
    </UserLayout>
</template>
