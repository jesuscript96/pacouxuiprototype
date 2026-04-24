import { useCallback, useEffect, useState, type Dispatch, type SetStateAction } from 'react'

import type { EmpresaWizardFormState } from './empresaWizardTypes'

export type WizardDraftPayload = {
  form: EmpresaWizardFormState
  currentStep: number
  savedAt: string
}

const DEFAULT_DEBOUNCE_MS = 1000

type Args = {
  draftKey: string
  /** En modo solo lectura no se hidrata ni persiste borrador. */
  draftEnabled?: boolean
  form: EmpresaWizardFormState
  setForm: Dispatch<SetStateAction<EmpresaWizardFormState>>
  currentStep: number
  setCurrentStep: (step: number) => void
  /** Si no hay borrador en localStorage, aplicar una sola vez (p. ej. datos del catálogo al editar). */
  seedWhenNoDraft: EmpresaWizardFormState | null
  debounceMs?: number
}

/**
 * Hidrata desde localStorage una sola vez; persiste form + paso con debounce.
 */
export function useEmpresaWizardDraft({
  draftKey,
  draftEnabled = true,
  form,
  setForm,
  currentStep,
  setCurrentStep,
  seedWhenNoDraft,
  debounceMs = DEFAULT_DEBOUNCE_MS,
}: Args): {
  lastSavedAt: Date | null
  clearDraft: () => void
  restoredFromDraft: boolean
  dismissRestoredNotice: () => void
} {
  const [lastSavedAt, setLastSavedAt] = useState<Date | null>(null)
  const [restoredFromDraft, setRestoredFromDraft] = useState(false)
  const [hydrateDone, setHydrateDone] = useState(false)

  useEffect(() => {
    if (!draftEnabled) {
      if (seedWhenNoDraft) {
        setForm(seedWhenNoDraft)
      }
      setHydrateDone(true)
      return
    }
    try {
      const raw = localStorage.getItem(draftKey)
      if (raw) {
        const p = JSON.parse(raw) as WizardDraftPayload
        if (p?.form) {
          setForm(p.form)
          setCurrentStep(typeof p.currentStep === 'number' ? p.currentStep : 0)
          if (p.savedAt) {
            setLastSavedAt(new Date(p.savedAt))
          }
          setRestoredFromDraft(true)
          setHydrateDone(true)
          return
        }
      }
    } catch {
      // ignorar borrador corrupto
    }
    if (seedWhenNoDraft) {
      setForm(seedWhenNoDraft)
    }
    setHydrateDone(true)
  }, [draftEnabled, draftKey, seedWhenNoDraft, setForm, setCurrentStep])

  useEffect(() => {
    if (!draftEnabled || !hydrateDone) {
      return
    }
    const handle = window.setTimeout(() => {
      try {
        const payload: WizardDraftPayload = {
          form,
          currentStep,
          savedAt: new Date().toISOString(),
        }
        localStorage.setItem(draftKey, JSON.stringify(payload))
        setLastSavedAt(new Date())
      } catch {
        // quota exceeded, etc.
      }
    }, debounceMs)
    return () => window.clearTimeout(handle)
  }, [draftEnabled, hydrateDone, form, currentStep, draftKey, debounceMs])

  const clearDraft = useCallback(() => {
    if (!draftEnabled) {
      return
    }
    try {
      localStorage.removeItem(draftKey)
    } catch {
      // noop
    }
    setLastSavedAt(null)
  }, [draftEnabled, draftKey])

  const dismissRestoredNotice = useCallback(() => {
    setRestoredFromDraft(false)
  }, [])

  return { lastSavedAt, clearDraft, restoredFromDraft, dismissRestoredNotice }
}
