<script setup>
import { Shield, ChevronDown, ChevronUp, Copy, Check } from "lucide-vue-next";
import { ref } from "vue";

defineProps({
	serverSeed: { type: String, default: null },
	clientSeed: { type: String, default: null },
	nonce: { type: [String, Number], default: null },
	houseEdge: { type: String, default: "3%" },
	expanded: { type: Boolean, default: false },
});

const isExpanded = ref(false);
const copiedField = ref(null);

function copyText(text, field) {
	navigator.clipboard.writeText(text);
	copiedField.value = field;
	setTimeout(() => (copiedField.value = null), 2000);
}
</script>

<template>
	<div class="bg-gray-800/60 rounded-2xl border border-white/[0.06] overflow-hidden">
		<button
			@click="isExpanded = !isExpanded"
			class="w-full flex items-center justify-between p-4 hover:bg-white/[0.02] transition">
			<div class="flex items-center gap-2.5">
				<div class="w-8 h-8 rounded-lg bg-purple-600/20 flex items-center justify-center">
					<Shield class="w-4 h-4 text-purple-400" />
				</div>
				<div class="text-left">
					<span class="text-white text-sm font-semibold block">Provably Fair</span>
					<span class="text-gray-500 text-xs">{{ houseEdge }} House Edge</span>
				</div>
			</div>
			<component :is="isExpanded ? ChevronUp : ChevronDown" class="w-4 h-4 text-gray-500" />
		</button>

		<div v-if="isExpanded || expanded" class="px-4 pb-4 space-y-3 border-t border-white/[0.04]">
			<p class="text-gray-400 text-xs pt-3 leading-relaxed">
				Each game result is determined by a combination of server seed, client seed, and nonce. You can verify any round independently.
			</p>

			<div v-if="serverSeed" class="space-y-2">
				<div class="bg-gray-900/60 rounded-xl p-3">
					<div class="flex items-center justify-between mb-1">
						<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider">Server Seed</span>
						<button @click="copyText(serverSeed, 'server')" class="text-gray-500 hover:text-purple-400 transition">
							<component :is="copiedField === 'server' ? Check : Copy" class="w-3.5 h-3.5" />
						</button>
					</div>
					<p class="text-white text-xs font-mono break-all">{{ serverSeed.substring(0, 32) }}{{ serverSeed.length > 32 ? '...' : '' }}</p>
				</div>

				<div v-if="clientSeed" class="bg-gray-900/60 rounded-xl p-3">
					<div class="flex items-center justify-between mb-1">
						<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider">Client Seed</span>
						<button @click="copyText(clientSeed, 'client')" class="text-gray-500 hover:text-purple-400 transition">
							<component :is="copiedField === 'client' ? Check : Copy" class="w-3.5 h-3.5" />
						</button>
					</div>
					<p class="text-white text-xs font-mono break-all">{{ clientSeed }}</p>
				</div>

				<div v-if="nonce !== null" class="bg-gray-900/60 rounded-xl p-3">
					<span class="text-gray-500 text-[10px] font-medium uppercase tracking-wider block mb-1">Nonce</span>
					<p class="text-white text-xs font-mono">{{ nonce }}</p>
				</div>
			</div>

			<div v-else class="bg-gray-900/40 rounded-xl p-3 text-center">
				<p class="text-gray-500 text-xs">Play a round to see verification data</p>
			</div>
		</div>
	</div>
</template>
