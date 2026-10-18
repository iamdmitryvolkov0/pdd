<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { BarChart3, BookOpen, ChevronDown, Clock3, Gauge, Layers3, MoonStar, Sun } from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import type { Component } from 'vue'

type ModeItem = { title: string; icon: Component; note: string; mode?: string; href?: string }

const isDark = ref(false)
const modesOpen = ref(false)
const brandMarkUrl = '/brand-mark.svg'
const modes: ModeItem[] = [
  { title: 'Экзамен', mode: 'exam', icon: Clock3, note: '20 минут · по регламенту' },
  { title: 'Марафон', mode: 'marathon', icon: Gauge, note: 'Все 800 вопросов' },
  { title: 'Билеты', href: '/tickets', icon: Layers3, note: '40 классических билетов' },
  { title: 'Вопросы', href: '/questions', icon: BookOpen, note: 'Практика по номерам' },
]

const applyTheme = (dark: boolean) => {
  isDark.value = dark
  document.documentElement.classList.toggle('dark', dark)
  document.documentElement.classList.toggle('light', !dark)
  localStorage.setItem('theme', dark ? 'dark' : 'light')
}
const toggleTheme = () => applyTheme(!isDark.value)
const startMode = (mode: string) => {
  modesOpen.value = false
  router.post('/sessions', { mode })
}
onMounted(() => { isDark.value = document.documentElement.classList.contains('dark') })
</script>

<template>
  <header class="shell flex items-center justify-between gap-4 py-6">
    <Link href="/" class="flex shrink-0 items-center gap-3 font-black tracking-tight" aria-label="ПДД 24/7 — на главную">
      <img :src="brandMarkUrl" alt="" class="size-11">
      <span class="hidden text-xl sm:inline">ПДД 24/7</span>
    </Link>

    <div class="flex items-center gap-3">
      <nav class="flex items-center rounded-2xl border border-black/10 bg-white/40 p-1 dark:border-white/10 dark:bg-white/5" aria-label="Основная навигация">
        <div class="relative">
          <button class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold hover:bg-black/5 dark:hover:bg-white/5 sm:px-4" :aria-expanded="modesOpen" @click="modesOpen = !modesOpen">Режимы <ChevronDown :size="16"/></button>
          <div v-if="modesOpen" class="card absolute right-0 top-[calc(100%+12px)] z-30 w-[min(320px,calc(100vw-32px))] overflow-hidden p-2 sm:left-0 sm:right-auto">
            <component :is="item.href ? Link : 'button'" v-for="item in modes" :key="item.title" :href="item.href" class="flex w-full items-center gap-3 rounded-2xl p-3 text-left hover:bg-black/5 dark:hover:bg-white/5" @click="item.mode ? startMode(item.mode) : modesOpen = false">
              <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[var(--pine)]/10 text-[var(--pine)]"><component :is="item.icon" :size="19"/></span>
              <span><b class="block">{{ item.title }}</b><small class="text-[var(--muted)]">{{ item.note }}</small></span>
            </component>
          </div>
        </div>
        <Link href="/statistics" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold hover:bg-black/5 dark:hover:bg-white/5 sm:px-4"><BarChart3 :size="17"/><span>Статистика</span></Link>
      </nav>

      <div class="group relative ml-1">
        <button class="grid size-11 place-items-center rounded-2xl border border-black/10 text-[var(--muted)] hover:bg-black/5 dark:border-white/10 dark:hover:bg-white/5" :aria-label="isDark ? 'Включить светлую тему' : 'Включить тёмную тему'" @click="toggleTheme"><Sun v-if="isDark" :size="20"/><MoonStar v-else :size="20"/></button>
        <span class="pointer-events-none absolute right-0 top-[calc(100%+10px)] z-40 hidden whitespace-nowrap rounded-lg bg-[var(--ink)] px-3 py-2 text-xs font-bold text-[var(--paper)] shadow-lg group-hover:block">{{ isDark ? 'Светлая тема' : 'Тёмная тема' }}</span>
      </div>
    </div>
  </header>
</template>
