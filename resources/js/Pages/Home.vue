<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ArrowRight, Gauge, Play, Target, Trophy } from 'lucide-vue-next'
import AppHeader from '../Components/AppHeader.vue'
import type { Statistics } from '../types'

const props = defineProps<{ statistics: Statistics; activeSession: { uuid: string; mode: string } | null }>()
const start = (mode: string) => router.post('/sessions', { mode })
const modeNames: Record<string, string> = { practice: 'практику', exam: 'экзамен', marathon: 'марафон', ticket: 'билет', questions: 'подборку вопросов' }
</script>

<template>
  <AppHeader />
  <main class="shell pb-16 pt-8">
    <section class="grid min-h-[520px] items-center gap-12 lg:grid-cols-[1.2fr_.8fr]">
      <div>
        <p class="mb-5 inline-flex rounded-full border border-[var(--pine)]/20 px-4 py-2 text-sm font-bold text-[var(--pine)]">ПДД без зубрёжки</p>
        <h1 class="max-w-3xl text-5xl font-black leading-[.98] tracking-[-.055em] sm:text-7xl">Понимай дорогу.<br><span class="text-[var(--accent)]">Сдавай уверенно.</span></h1>
        <p class="mt-7 max-w-xl text-lg leading-8 text-[var(--muted)]">Тренажёр категорий A, B и M с официальной логикой экзамена, понятными объяснениями и прогрессом, который не потеряется.</p>
        <div class="mt-9 flex flex-wrap gap-3">
          <button class="lift flex items-center gap-3 rounded-2xl bg-[var(--accent)] px-7 py-4 text-lg font-black text-white" @click="start('practice')"><Play :size="21" fill="currentColor" />Решать</button>
        </div>
        <Link v-if="activeSession" :href="`/sessions/${activeSession.uuid}`" class="mt-5 inline-flex items-center gap-2 font-bold text-[var(--pine)]">Продолжить {{ modeNames[activeSession.mode] ?? 'занятие' }} <ArrowRight :size="18" /></Link>
      </div>
      <div class="card relative overflow-hidden p-7 sm:p-9">
        <div class="absolute -right-16 -top-16 size-52 rounded-full bg-[var(--accent)]/10"></div>
        <div class="flex items-end justify-between"><div><p class="text-sm font-bold uppercase tracking-widest text-[var(--muted)]">Изучено</p><p class="mt-2 text-5xl font-black">{{ statistics.unique_questions }}<span class="text-xl text-[var(--muted)]"> / 800</span></p></div><Target class="text-[var(--accent)]" :size="38" /></div>
        <div class="mt-6 h-3 overflow-hidden rounded-full bg-black/10"><div class="h-full rounded-full bg-[var(--accent)]" :style="{ width: `${statistics.progress}%` }"></div></div>
        <div class="mt-8 grid grid-cols-2 gap-3">
          <div class="rounded-2xl bg-[var(--pine)] p-5 text-white"><Trophy :size="22" /><strong class="mt-5 block text-3xl">{{ statistics.accuracy }}%</strong><span class="text-sm text-white/70">точность</span></div>
          <div class="rounded-2xl border border-black/10 p-5 dark:border-white/10"><Gauge :size="22" /><strong class="mt-5 block text-3xl">{{ statistics.completed_sessions }}</strong><span class="text-sm text-[var(--muted)]">тренировок</span></div>
        </div>
      </div>
    </section>
  </main>
</template>
