<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { BarChart3, MoonStar, Route } from 'lucide-vue-next'
</script>

<template>
  <header class="shell flex items-center justify-between py-6">
    <Link href="/" class="flex items-center gap-3 font-black tracking-tight" aria-label="Вектор — на главную">
      <span class="grid size-10 place-items-center rounded-2xl bg-[var(--pine)] text-white"><Route :size="21" /></span>
      <span class="text-xl">Вектор</span>
    </Link>
    <nav class="flex items-center gap-2" aria-label="Основная навигация">
      <Link href="/statistics" class="rounded-xl p-3 text-[var(--muted)] hover:bg-black/5" aria-label="Статистика"><BarChart3 :size="20" /></Link>
      <span class="rounded-xl p-3 text-[var(--muted)]" title="Тема следует настройкам системы"><MoonStar :size="20" /></span>
    </nav>
  </header>
</template>
