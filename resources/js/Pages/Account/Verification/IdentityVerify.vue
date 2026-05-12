<script setup>
	import { ref } from "vue";

	import { useForm } from "@inertiajs/vue3";
	import { ScanEye } from "lucide-vue-next";
	import { uid } from "uid";

	import CollapseTransition from "@/Components/CollapseTransition.vue";
	import FileUploader from "@/Components/FileUploader.vue";
	import FileUploaderLocal from "@/Components/FileUploaderLocal.vue";
	import FormLabel from "@/Components/FormLabel.vue";
	import Loading from "@/Components/Loading.vue";
	import RadioSelect from "@/Components/RadioSelect.vue";
	import VerificationStatusBadge from "@/Components/Account/VerificationStatusBadge.vue";
	defineProps({
		idTypes: Array,
		personal: Object,
	});

	const form = useForm({
		type: "idcard",
		image_uri: null,
		image_path: null,
		image_upload: true,
	});
	const showForm = ref(false);
	const refreshId = ref(uid());
	const approveKyc = () => {
		form.put(window.route("accounts.verify.identity"), {
			preserveScroll: true,
			preserveState: true,
			onFinish() {
				showForm.value = false;
				refreshId.value = uid();
				form.reset();
			},
		});
	};
</script>

<template>
	<div class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-6">
		<div class="flex items-center gap-4">
			<div class="w-12 h-12 rounded-xl bg-purple-600/10 flex items-center justify-center flex-shrink-0">
				<ScanEye class="w-6 h-6 text-purple-400" />
			</div>
			<div class="flex-1 min-w-0">
				<h3 class="text-base font-semibold text-white">
					{{ $t("Proof of Identity") }}
				</h3>
				<p class="text-sm text-gray-400 mt-0.5">
					{{ $t("Submit clear and high resolution documents to complete KYC faster") }}
				</p>
				<div class="mt-2">
					<VerificationStatusBadge
						v-if="!$page.props.enableKyc"
						status="not_required" />
					<VerificationStatusBadge
						v-else-if="$page.props.auth.user.isKycVerified"
						status="complete" />
					<template v-else-if="personal.proof_of_identity">
						<div class="flex items-center gap-2">
							<VerificationStatusBadge status="pending" />
							<button @click.prevent="showForm = !showForm"
								class="text-xs text-red-400 hover:text-red-300 underline transition-colors">
								{{ $t("Resubmit") }}
							</button>
						</div>
					</template>
					<VerificationStatusBadge v-else status="unverified" />
				</div>
			</div>
		</div>
		<CollapseTransition>
			<div
				v-show="
					$page.props.enableKyc &&
					!$page.props.auth.user.isKycVerified &&
					(showForm || !personal.proof_of_identity)
				">
				<div class="mt-6 pt-4 border-t border-white/[0.06]">
					<FormLabel class="mb-2">
						{{ $t("Type of Identification") }}
					</FormLabel>
					<RadioSelect
						v-model="form.type"
						:options="Object.values(idTypes)" />
				</div>
				<div class="w-full mt-6">
					<FormLabel class="mb-4">
						{{ $t("Upload Your Document") }}
					</FormLabel>
					<FileUploader
						class="mb-1 h-32 sm:max-w-sm w-full"
						v-if="$page.props.s3"
						v-model="form.image_uri"
						v-model:file="form.image_path"
						:key="refreshId"
						auto />
					<FileUploaderLocal
						v-else
						class="mb-1 sm:max-w-sm w-full"
						v-model="form.image_uri"
						:key="`u-${refreshId}`"
						v-model:file="form.image_path" />
					<p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
						{{ form.errors.image }}
					</p>
					<p v-else class="text-xs text-gray-500 mt-1">
						{{ $t("This will overwrite any previous uploads") }}
					</p>
				</div>
				<div class="flex w-full mt-5">
					<button
						@click="approveKyc"
						:disabled="form.processing"
						class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-purple-600 hover:bg-purple-700 text-white transition-all duration-200 min-h-[44px] disabled:opacity-50">
						<Loading v-if="form.processing" class="!w-4 !h-4" />
						Submit KYC
					</button>
				</div>
			</div>
		</CollapseTransition>
	</div>
</template>
