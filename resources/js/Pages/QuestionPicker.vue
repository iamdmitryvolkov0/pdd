<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppHeader from '../Components/AppHeader.vue'
const selected = ref<number[]>([])
const count = computed(() => selected.value.length * 40)
const toggle = (position:number) => selected.value.includes(position) ? selected.value = selected.value.filter(item => item !== position) : selected.value.push(position)
const start = () => router.post('/sessions', { mode:'questions', positions:selected.value })
</script>
<template><AppHeader/><main class="shell max-w-4xl pb-16"><div class="my-10"><p class="font-bold text-[var(--accent)]">Точечная практика</p><h1 class="mt-2 text-5xl font-black tracking-tight">Выберите номера</h1><p class="mt-3 text-[var(--muted)]">Каждый номер добавляет вопрос из всех 40 билетов.</p></div><div class="card p-6"><div class="grid grid-cols-4 gap-3 sm:grid-cols-10"><button v-for="position in 20" :key="position" class="aspect-square rounded-2xl border text-lg font-black" :class="selected.includes(position) ? 'border-[var(--accent)] bg-[var(--accent)] text-white':'border-black/10 dark:border-white/10'" :aria-pressed="selected.includes(position)" @click="toggle(position)">{{ position }}</button></div><div class="mt-7 flex items-center justify-between border-t border-black/10 pt-6 dark:border-white/10"><p><strong class="text-2xl">{{ count }}</strong><br><span class="text-sm text-[var(--muted)]">вопросов без повторений</span></p><button class="rounded-2xl bg-[var(--accent)] px-6 py-4 font-black text-white disabled:opacity-40" :disabled="!selected.length" @click="start">Начать</button></div></div></main></template>
