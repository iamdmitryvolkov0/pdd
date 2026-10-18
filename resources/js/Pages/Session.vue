<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { ArrowLeft, ArrowRight, Check, Clock3, Flag, X } from 'lucide-vue-next'
import AppHeader from '../Components/AppHeader.vue'
import type { Session } from '../types'

const props = defineProps<{ session: Session }>()
const currentIndex = ref(Math.max(0, props.session.questions.findIndex(question => !question.answer)))
const submitting = ref(false)
const timeoutTriggered = ref(false)
const now = ref(Date.now())
const questionStartedAt = ref(Date.now())
let timer = 0
const question = computed(() => props.session.questions[currentIndex.value])
const answeredCount = computed(() => props.session.questions.filter(item => item.answer).length)
const title = computed(() => ({ practice:'Тренировка', exam:'Экзамен', marathon:'Марафон', ticket:'Билет', questions:'Вопросы' }[props.session.mode] || props.session.mode))
const time = computed(() => {
  const seconds = props.session.expiresAt ? Math.max(0, Math.floor((new Date(props.session.expiresAt).getTime() - now.value) / 1000)) : Math.max(0, Math.floor((now.value - new Date(props.session.startedAt).getTime()) / 1000))
  return `${Math.floor(seconds / 60).toString().padStart(2,'0')}:${(seconds % 60).toString().padStart(2,'0')}`
})
const select = async (optionId:number) => {
  if (question.value.answer || submitting.value || props.session.status !== 'active') return
  submitting.value = true
  try {
    const response = await fetch(`/sessions/${props.session.uuid}/answers`, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || ''}, body:JSON.stringify({question_id:question.value.id,answer_option_id:optionId,response_time_ms:Date.now()-questionStartedAt.value}) })
    if (!response.ok) throw new Error('Не удалось сохранить ответ')
    const result = await response.json() as { isCorrect: boolean | null }
    router.reload({ only:['session'] })
    if (result.isCorrect === true && currentIndex.value < props.session.questions.length - 1) window.setTimeout(() => move(1), 650)
  } finally { submitting.value = false }
}
const move = (step:number) => { currentIndex.value = Math.min(props.session.questions.length - 1, Math.max(0,currentIndex.value + step)); questionStartedAt.value = Date.now() }
const finish = () => router.post(`/sessions/${props.session.uuid}/finish`)
const keydown = (event:KeyboardEvent) => {
  if (/^[1-5]$/.test(event.key)) { const option = question.value.options[Number(event.key)-1]; if (option) select(option.id) }
  if (event.key === 'ArrowLeft') move(-1)
  if (event.key === 'ArrowRight' || event.key === 'Enter') move(1)
}
onMounted(() => {
  timer = window.setInterval(() => {
    now.value = Date.now()
    if (props.session.expiresAt && now.value >= new Date(props.session.expiresAt).getTime() && props.session.status === 'active' && !timeoutTriggered.value) {
      timeoutTriggered.value = true
      finish()
    }
  }, 1000)
  window.addEventListener('keydown',keydown)
})
onBeforeUnmount(() => { clearInterval(timer); window.removeEventListener('keydown',keydown) })
</script>

<template>
  <Head :title="`${title} — ПДД 24/7`" />
  <AppHeader />
  <main class="shell pb-20">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-black/10 px-5 py-3 dark:border-white/10">
      <div><strong>{{ title }}</strong><span v-if="question.stage" class="ml-2 rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-900">дополнительный блок</span></div>
      <div class="flex items-center gap-5 text-sm font-bold"><span>{{ answeredCount }} / {{ session.questions.length }}</span><span class="flex items-center gap-2"><Clock3 :size="17" />{{ time }}</span></div>
    </div>
    <div v-if="session.status !== 'active'" class="card mb-6 flex items-center justify-between gap-5 p-6"><div><p class="text-sm font-bold uppercase tracking-wide text-[var(--muted)]">Сессия завершена</p><h2 class="mt-1 text-3xl font-black">{{ session.status === 'passed' ? 'Экзамен сдан' : session.status === 'failed' ? 'Экзамен не сдан' : 'Готово' }}</h2></div><Flag :size="36" class="text-[var(--accent)]"/></div>
    <section class="grid gap-6 lg:grid-cols-[1fr_310px]">
      <article class="card p-5 sm:p-9">
        <div class="mb-5 flex items-center justify-between"><p class="text-sm font-bold uppercase tracking-widest text-[var(--muted)]">Вопрос {{ question.position }} · Блок {{ question.block }}</p><span class="text-sm text-[var(--muted)]">Билет {{ question.ticket }}</span></div>
        <img v-if="question.image" :src="question.image" :alt="`Дорожная ситуация к вопросу ${question.position}`" class="mb-7 max-h-[400px] w-full rounded-2xl object-contain" loading="lazy">
        <h1 class="text-2xl font-black leading-snug sm:text-3xl">{{ question.text }}</h1>
        <div class="mt-7 grid gap-3">
          <button v-for="option in question.options" :key="option.id" class="flex items-start gap-4 rounded-2xl border p-4 text-left disabled:cursor-default" :class="option.is_correct ? 'border-emerald-500 bg-emerald-500/10' : question.answer?.answer_option_id === option.id && !question.answer.is_correct ? 'border-red-500 bg-red-500/10' : 'border-black/10 dark:border-white/10'" :disabled="!!question.answer || submitting" @click="select(option.id)">
            <span class="grid size-8 shrink-0 place-items-center rounded-lg border border-current font-black">{{ option.position }}</span><span class="pt-1 leading-6">{{ option.text }}</span><Check v-if="option.is_correct" class="ml-auto text-emerald-600"/><X v-else-if="question.answer?.answer_option_id === option.id && !question.answer.is_correct" class="ml-auto text-red-600"/>
          </button>
        </div>
        <div v-if="question.explanation" class="explanation mt-6 rounded-2xl p-5 sm:p-6"><strong class="text-lg">Почему так?</strong><p class="mt-2 text-base leading-7">{{ question.explanation }}</p></div>
        <div class="mt-7 flex justify-between"><button class="flex items-center gap-2 rounded-xl px-4 py-3 font-bold disabled:opacity-30" :disabled="currentIndex === 0" @click="move(-1)"><ArrowLeft :size="18"/>Назад</button><button v-if="currentIndex < session.questions.length-1" class="flex items-center gap-2 rounded-xl bg-[var(--ink)] px-5 py-3 font-bold text-[var(--paper)]" @click="move(1)">Дальше<ArrowRight :size="18"/></button><button v-else-if="session.status === 'active'" class="rounded-xl bg-[var(--accent)] px-5 py-3 font-black text-white" @click="finish">Завершить</button></div>
      </article>
      <aside class="card h-fit p-5"><p class="mb-4 text-sm font-bold uppercase tracking-widest text-[var(--muted)]">Навигация</p><div class="grid grid-cols-5 gap-2"><button v-for="(item,index) in session.questions" :key="item.id" class="aspect-square rounded-xl border text-sm font-bold" :class="index === currentIndex ? 'border-[var(--accent)] bg-[var(--accent)] text-white' : item.answer ? 'border-[var(--pine)]/30 bg-[var(--pine)]/10' : 'border-black/10 dark:border-white/10'" :aria-label="`Перейти к вопросу ${index+1}${item.answer ? ', отвечен' : ''}`" @click="currentIndex=index">{{ index+1 }}</button></div></aside>
    </section>
  </main>
</template>
