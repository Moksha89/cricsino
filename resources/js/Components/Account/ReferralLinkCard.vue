<script setup>
import { ref } from "vue";
import { Link2, Copy, Check, Users } from "lucide-vue-next";

const props = defineProps({
	referralUrl: { type: String, required: true },
	directCount: { type: [Number, String], default: 0 },
});

const copied = ref(false);

async function copyLink() {
	try {
		await navigator.clipboard.writeText(props.referralUrl);
	} catch {
		const el = document.createElement("textarea");
		el.value = props.referralUrl;
		document.body.appendChild(el);
		el.select();
		document.execCommand("copy");
		document.body.removeChild(el);
	}
	copied.value = true;
	setTimeout(() => { copied.value = false; }, 2000);
}
</script>

<template>
<div class="bg-gray-800/50 rounded-2xl border border-white/[0.06] p-4 sm:p-6">
	<div class="flex items-center gap-2.5 mb-4">
		<Link2 class="w-5 h-5 text-purple-400" />
		<h3 class="text-lg font-semibold text-white">Your Referral Link</h3>
	</div>
	<div class="flex flex-col sm:flex-row gap-3">
		<div class="flex-1 min-w-0 bg-gray-900/50 rounded-xl border border-white/[0.06] px-4 py-3 flex items-center">
			<span class="text-sm text-gray-300 truncate">{{ referralUrl }}</span>
		</div>
		<button
			@click="copyLink"
			class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold transition-all duration-200 min-h-[44px] flex-shrink-0"
			:class="copied
				? 'bg-green-500/10 text-green-400 border border-green-500/20'
				: 'bg-purple-600 hover:bg-purple-700 text-white'">
			<Check v-if="copied" class="w-4 h-4" />
			<Copy v-else class="w-4 h-4" />
			{{ copied ? 'Copied!' : 'Copy Link' }}
		</button>
	</div>
	<div v-if="directCount !== null" class="mt-3 flex items-center gap-2 text-sm text-gray-400">
		<Users class="w-4 h-4" />
		<span>{{ directCount }} Direct Referrals</span>
	</div>
</div>
</template>
