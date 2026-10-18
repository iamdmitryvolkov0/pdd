export type Statistics = { total_answers: number; correct_answers: number; errors: number; accuracy: number; unique_questions: number; progress: number; completed_sessions: number; exam_pass_rate: number; average_response_seconds: number }
export type Option = { id: number; position: number; text: string; is_correct?: boolean }
export type Question = { id: number; position: number; stage: number; block: number; ticket: number; text: string; image: string | null; explanation: string | null; options: Option[]; answer: { answer_option_id: number; is_correct: boolean } | null }
export type Session = { uuid: string; mode: string; status: string; startedAt: string; expiresAt: string | null; questions: Question[] }
