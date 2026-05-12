<script setup>
import { ref } from "vue";
import { Copy, Check } from "lucide-vue-next";

const props = defineProps({
	text: { type: String, required: true },
});

const copied = ref(false);

async function copyToClipboard() {
	try {
		await navigator.clipboard.writeText(props.text);
		copied.value = true;
		setTimeout(() => { copied.value = false; }, 2000);
	} catch {
		const el = document.createElement("textarea");
		el.value = props.text;
		document.body.appendChild(el);
		el.select();
		document.execCommand("copy");
		document.body.removeChild(el);
		copied.value = true;
		setTimeout(() => { copied.value = false; }, 2000);
	}
}
</script>

<template>
<button
	@click.stop="copyToClipboard"
	class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-200"
	:class="copied
		? 'bg-green-500/10 text-green-400 border border-green-500/20'
		: 'bg-white/[0.05] text-gray-400 border border-white/10 hover:bg-white/[0.08] hover:text-white'">
	<Check v-if="copied" class="w-3.5 h-3.5" />
	<Copy v-else class="w-3.5 h-3.5" />
	{{ copied ? 'Copied' : 'Copy' }}
</button>
</template>
