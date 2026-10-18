<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { CheckCircle2 } from 'lucide-vue-next'
import AppHeader from '../Components/AppHeader.vue'
defineProps<{ results: Record<number, { last: number; best: number; errors: number }> }>()
const start = (ticket: number) => router.post('/sessions', { mode: 'ticket', ticket })
</script>
<template><AppHeader/><main class="shell pb-16"><div class="my-10"><p class="font-bold text-[var(--accent)]">Классический режим</p><h1 class="mt-2 text-5xl font-black tracking-tight">40 билетов</h1><p class="mt-3 text-[var(--muted)]">По 20 вопросов, без ограничения времени.</p></div><div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5"><button v-for="ticket in 40" :key="ticket" class="card lift p-5 text-left" @click="start(ticket)"><div class="flex justify-between"><span class="text-xs font-bold uppercase tracking-wider text-[var(--muted)]">Билет</span><CheckCircle2 v-if="results[ticket]" :size="18" class="text-emerald-600"/></div><strong class="my-5 block text-4xl">{{ ticket }}</strong><small v-if="results[ticket]" class="text-[var(--muted)]">Лучший: {{ results[ticket].best }}/20</small><small v-else class="text-[var(--muted)]">Не начат</small></button></div></main></template>
