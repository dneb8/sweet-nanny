import { h } from 'vue'
import { toast } from 'vue-sonner'
import { Icon } from '@iconify/vue'

const ICONS = {
  success: 'mdi:check-circle',
  info: 'mdi:information-outline',
  warning: 'mdi:alert',
  error: 'mdi:close-circle',
}

export type NotifyVariant = 'success' | 'info' | 'warning' | 'error'

export interface NotifyOptions {
  variant: NotifyVariant
  title: string
  description?: string
  icon?: string
  duration?: number
}

const LIGHT_BASE = {
  success: 'border-emerald-200 text-emerald-800 bg-emerald-100/10',
  info: 'border-sky-200 text-sky-800 bg-sky-100/10',
  warning: 'border-amber-200 text-amber-800 bg-amber-100/10',
  error: 'border-rose-200 text-rose-800 bg-rose-100/10',
}

const ICON_COLORS = {
  success: 'text-emerald-600 dark:text-emerald-300',
  info: 'text-sky-600 dark:text-sky-300',
  warning: 'text-amber-600 dark:text-amber-300',
  error: 'text-rose-600 dark:text-rose-300',
}

const DARK_TINT = {
  success: 'border-emerald-300 dark:bg-emerald-400/10',
  info: 'border-emerald-300 dark:bg-sky-400/10',
  warning: 'border-emerald-300 dark:bg-amber-400/10',
  error: 'border-emerald-300 dark:bg-rose-400/10',
}

const DARK_OVERLAY = {
  success: 'dark:bg-emerald-500/20',
  info: 'dark:bg-sky-500/20',
  warning: 'dark:bg-amber-500/20',
  error: 'dark:bg-rose-500/20',
}

function wrapClasses(variant: NotifyVariant) {
  const light = LIGHT_BASE[variant]
  const darkTint = DARK_TINT[variant]
  return [
    'relative overflow-hidden',
    'rounded-lg border shadow-lg',
    'min-w-[320px] max-w-[420px]',
    'backdrop-blur-md',
    light,
    darkTint,
    'dark:text-white/90 dark:border-white/10',
  ].join(' ')
}

function overlayClasses(variant: NotifyVariant) {
  const overlay = DARK_OVERLAY[variant]
  return [
    'pointer-events-none absolute inset-0',
    'opacity-100 mix-blend-normal',
    overlay,
    'dark:backdrop-blur-md',
  ].join(' ')
}

export function useNotify() {
  const notify = (options: NotifyOptions) => {
    const {
      variant,
      title,
      description,
      icon = ICONS[variant],
      duration = 5000,
    } = options

    const containerClass = wrapClasses(variant)
    const iconColor = ICON_COLORS[variant]
    const overlay = overlayClasses(variant)

    toast.custom(
      () =>
        h(
          'div',
          {
            class: containerClass,
            role: 'alert',
          },
          [
            h('div', { class: overlay }),
            h(
              'div',
              { class: 'relative z-[1] flex items-start gap-3 p-4' },
              [
                h(Icon, {
                  icon,
                  class: `w-5 h-5 flex-shrink-0 mt-0.5 ${iconColor}`,
                }),
                h('div', { class: 'flex-1 min-w-0' }, [
                  h('div', { class: 'font-semibold text-sm mb-0.5' }, title),
                  description &&
                    h('div', { class: 'text-xs opacity-90 mt-1' }, description),
                ]),
              ],
            ),
          ],
        ),
      { duration },
    )
  }

  const notifySuccess = (title: string, description?: string, icon?: string, duration?: number) => {
    notify({ variant: 'success', title, description, icon, duration })
  }

  const notifyInfo = (title: string, description?: string, icon?: string, duration?: number) => {
    notify({ variant: 'info', title, description, icon, duration })
  }

  const notifyWarning = (title: string, description?: string, icon?: string, duration?: number) => {
    notify({ variant: 'warning', title, description, icon, duration })
  }

  const notifyError = (title: string, description?: string, icon?: string, duration?: number) => {
    notify({ variant: 'error', title, description, icon, duration })
  }

  return {
    notify,
    notifySuccess,
    notifyInfo,
    notifyWarning,
    notifyError,
  }
}
