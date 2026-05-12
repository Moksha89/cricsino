<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import UserLayout from "@/Layouts/UserLayout.vue";
import { MessageSquare, ArrowLeft, Send } from "lucide-vue-next";

const props = defineProps({
	categories: Array,
});

const categoryLabels = {
	deposit: 'Deposit',
	withdrawal: 'Withdrawal',
	betting: 'Betting',
	casino: 'Casino',
	account: 'Account',
	kyc: 'KYC',
	technical: 'Technical',
	other: 'Other',
};

const form = useForm({
	subject: '',
	category: '',
	message: '',
});

function submit() {
	form.post(route('support.store'));
}
</script>

<template>
<UserLayout :show-right-sidebar="false">
	<div class="max-w-2xl mx-auto px-4 py-6">
		<!-- Header -->
		<div class="flex items-center gap-3 mb-6">
			<Link
				:href="route('support.index')"
				class="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition">
				<ArrowLeft class="w-4 h-4 text-gray-400" />
			</Link>
			<div>
				<h1 class="text-2xl font-bold text-white">New Support Ticket</h1>
				<p class="text-sm text-gray-400 mt-0.5">Describe your issue and our team will help</p>
			</div>
		</div>

		<!-- Form -->
		<form @submit.prevent="submit" class="space-y-5">
			<!-- Category -->
			<div>
				<label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
				<select
					v-model="form.category"
					class="w-full px-4 py-3 text-sm bg-gray-900/80 border border-white/[0.08] rounded-xl text-white focus:outline-none focus:border-primary/40 transition">
					<option value="" disabled>Select a category</option>
					<option v-for="c in categories" :key="c" :value="c">{{ categoryLabels[c] || c }}</option>
				</select>
				<p v-if="form.errors.category" class="text-xs text-red-400 mt-1">{{ form.errors.category }}</p>
			</div>

			<!-- Subject -->
			<div>
				<label class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
				<input
					v-model="form.subject"
					type="text"
					placeholder="Brief description of your issue"
					class="w-full px-4 py-3 text-sm bg-gray-900/80 border border-white/[0.08] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary/40 transition" />
				<p v-if="form.errors.subject" class="text-xs text-red-400 mt-1">{{ form.errors.subject }}</p>
			</div>

			<!-- Message -->
			<div>
				<label class="block text-sm font-medium text-gray-300 mb-2">Message</label>
				<textarea
					v-model="form.message"
					rows="6"
					placeholder="Describe your issue in detail..."
					class="w-full px-4 py-3 text-sm bg-gray-900/80 border border-white/[0.08] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary/40 transition resize-none"></textarea>
				<div class="flex items-center justify-between mt-1">
					<p v-if="form.errors.message" class="text-xs text-red-400">{{ form.errors.message }}</p>
					<span class="text-xs text-gray-600 ml-auto">{{ form.message.length }} / 5000</span>
				</div>
			</div>

			<!-- Submit -->
			<div class="flex items-center gap-3 pt-2">
				<button
					type="submit"
					:disabled="form.processing"
					class="flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-primary hover:bg-primary-light rounded-xl transition shadow-lg shadow-primary/20 disabled:opacity-50">
					<Send class="w-4 h-4" />
					{{ form.processing ? 'Submitting...' : 'Submit Ticket' }}
				</button>
				<Link
					:href="route('support.index')"
					class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-white transition">
					Cancel
				</Link>
			</div>
		</form>
	</div>
</UserLayout>
</template>
