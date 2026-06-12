interface BadgeProps {
  label?: string
  children?: React.ReactNode
  variant?: 'default' | 'accent' | 'sage' | 'info' | 'warning'
  className?: string
}

export default function Badge({ label, children, variant = 'default', className = '' }: BadgeProps) {
  const content = children ?? label ?? ''

  const styles: Record<string, React.CSSProperties> = {
    default: { color: '#7A7870', border: '1px solid #E6E0D6' },
    accent:  { color: '#C2683D', border: '1px solid #E6B89C' },
    sage:    { color: '#5C7A3E', border: '1px solid #C5D6B3' },
    info:    { color: '#4A7A9B', border: '1px solid #B3CDD9' },
    warning: { color: '#8B6F47', border: '1px solid #D9C4A8' },
  }

  return (
    <span
      className={`inline-flex items-center rounded-[3px] px-2 py-1 ${className}`}
      style={{
        fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
        fontSize: '9px',
        letterSpacing: '0.03em',
        ...(styles[variant] ?? styles.default),
      }}
    >
      {content}
    </span>
  )
}
