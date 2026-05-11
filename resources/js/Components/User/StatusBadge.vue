<script setup>
const props = defineProps({
	status: { type: String, required: true },
});

const colorMap = {
	success: 'bg-success/20 text-success border-success/30',
	completed: 'bg-success/20 text-success border-success/30',
	won: 'bg-success/20 text-success border-success/30',
	approved: 'bg-success/20 text-success border-success/30',
	active: 'bg-success/20 text-success border-success/30',
	pending: 'bg-warning/20 text-warning border-warning/30',
	processing: 'bg-warning/20 text-warning border-warning/30',
	waiting: 'bg-warning/20 text-warning border-warning/30',
	failed: 'bg-danger/20 text-danger border-danger/30',
	lost: 'bg-danger/20 text-danger border-danger/30',
	rejected: 'bg-danger/20 text-danger border-danger/30',
	cancelled: 'bg-danger/20 text-danger border-danger/30',
	error: 'bg-danger/20 text-danger border-danger/30',
	info: 'bg-info/20 text-info border-info/30',
	default: 'bg-gray-600/20 text-gray-400 border-gray-600/30',
};

function getColor(status) {
	const key = status?.toLowerCase?.() ?? 'default';
	return colorMap[key] || colorMap.default;
}
</script>

<template>
<span
	class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
	:class="getColor(status)">
	{{ status }}
</span>
</template>
