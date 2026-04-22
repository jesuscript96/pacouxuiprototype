import type { ReactNode } from 'react'

type Props = {
  children: ReactNode
  contentClass?: string
}

export function StorybookShell({ children, contentClass = '' }: Props) {
  return (
    <div
      className={
        'sb-storybook relative w-full max-w-7xl px-4 pb-10 pt-4 sm:px-6 sm:pb-12 sm:pt-5 lg:px-8 ' +
        contentClass
      }
    >
      <div
        className="pointer-events-none absolute inset-x-0 top-0 h-36 bg-gradient-to-b from-indigo-600/10 via-indigo-500/5 to-transparent"
        aria-hidden
      />
      <div className="relative">{children}</div>
    </div>
  )
}
