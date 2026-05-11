<script setup>
import { Head } from "@inertiajs/vue3";

import UserLayout from "@/Layouts/UserLayout.vue";
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
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-white font-inter">Profile</h1>
            </div>
            <div class="max-w-7xl grid sm:grid-cols-2 gap-6">
                <div class="p-4 sm:p-6 bg-gray-800/50 rounded-2xl border border-white/[0.06]">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-6 bg-gray-800/50 rounded-2xl border border-white/[0.06]">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="grid gap-4">
                    <div class="p-4 sm:p-6 bg-gray-800/50 rounded-2xl border border-white/[0.06]">
                        <ProfilePhoto
                            :photo="$page.props.auth.user.profile_photo_url"
                            class="max-w-xl"
                        />
                    </div>
                    <div class="p-4 sm:p-6 bg-gray-800/50 rounded-2xl border border-white/[0.06]">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
                <div class="p-4 sm:p-6 bg-gray-800/50 rounded-2xl border border-white/[0.06]">
                    <TwoFactorAuthenticationForm
                        :requiresConfirmation="
                            $page.props.twoFactorRequiresConfirmation ?? false
                        "
                    />
                </div>
            </div>
        </div>
    </UserLayout>
</template>
