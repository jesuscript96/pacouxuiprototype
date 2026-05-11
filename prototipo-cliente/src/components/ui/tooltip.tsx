import * as React from 'react'
import { Tooltip as TooltipPrimitives } from 'radix-ui'

import { cn } from '@/lib/utils'

function TooltipProvider({
  delayDuration = 200,
  skipDelayDuration = 150,
  ...props
}: React.ComponentProps<typeof TooltipPrimitives.Provider>) {
  return (
    <TooltipPrimitives.Provider
      delayDuration={delayDuration}
      skipDelayDuration={skipDelayDuration}
      {...props}
    />
  )
}

type UiTooltipProps = {
  content: React.ReactNode
  children: React.ReactElement
  contentClassName?: string
  side?: React.ComponentProps<typeof TooltipPrimitives.Content>['side']
} & Omit<React.ComponentProps<typeof TooltipPrimitives.Root>, 'children'>

/**
 * Tooltip accesible con texto visible al hover/focus (Radix).
 * Requiere {@link TooltipProvider} en un ancestro (p. ej. shell del cliente).
 */
function UiTooltip({
  content,
  children,
  contentClassName,
  side = 'top',
  delayDuration = 200,
  ...rootProps
}: UiTooltipProps) {
  return (
    <TooltipPrimitives.Root delayDuration={delayDuration} {...rootProps}>
      <TooltipPrimitives.Trigger asChild>{children}</TooltipPrimitives.Trigger>
      <TooltipPrimitives.Portal>
        <TooltipPrimitives.Content
          side={side}
          sideOffset={6}
          className={cn(
            'z-[100] max-w-[min(18rem,calc(100vw-1rem))] rounded-md border border-slate-700/90 bg-slate-900 px-2.5 py-1.5 text-xs font-medium leading-snug text-white shadow-lg select-none',
            contentClassName,
          )}
        >
          {content}
        </TooltipPrimitives.Content>
      </TooltipPrimitives.Portal>
    </TooltipPrimitives.Root>
  )
}

export { TooltipProvider, UiTooltip }
