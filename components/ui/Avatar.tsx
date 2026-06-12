interface AvatarProps {
  initials: string
  size?: number | 'sm' | 'md' | 'lg' | 'xl'
  online?: boolean
  src?: string
  className?: string
}

const WARM_COLORS: Record<string, string> = {
  A: '#6B8F4E', B: '#C2683D', C: '#4A7A9B', D: '#8B6F47', E: '#5C7A9B',
  F: '#A0522D', G: '#6B8F4E', H: '#C2683D', I: '#4A7A9B', J: '#8B6F47',
  K: '#5C7A9B', L: '#A0522D', M: '#6B8F4E', N: '#C2683D', O: '#4A7A9B',
  P: '#8B6F47', Q: '#5C7A9B', R: '#A0522D', S: '#6B8F4E', T: '#C2683D',
  U: '#4A7A9B', V: '#8B6F47', W: '#5C7A9B', X: '#A0522D', Y: '#6B8F4E',
  Z: '#C2683D',
}

const SIZE_MAP = { sm: 28, md: 36, lg: 48, xl: 96 }

export default function Avatar({ initials, size = 'md', online, src, className = '' }: AvatarProps) {
  const px = typeof size === 'number' ? size : (SIZE_MAP[size] ?? 36)
  const bg = WARM_COLORS[initials?.[0]?.toUpperCase() ?? ''] ?? '#C2683D'
  const dotPx = Math.max(8, Math.round(px * 0.22))
  const fontPx = Math.max(9, Math.round(px * 0.34))

  return (
    <div
      className={`relative inline-flex shrink-0 ${className}`}
      style={{ width: px, height: px }}
    >
      {src ? (
        <img
          src={src}
          alt={initials}
          className="w-full h-full rounded-full object-cover"
          style={{ border: '1.5px solid #E6E0D6' }}
        />
      ) : (
        <div
          className="w-full h-full rounded-full flex items-center justify-center select-none font-medium"
          style={{ backgroundColor: bg, color: '#F8F4EC', fontSize: fontPx, fontFamily: 'var(--font-dm-mono), DM Mono, monospace' }}
        >
          {(initials ?? '').slice(0, 2).toUpperCase()}
        </div>
      )}
      {online !== undefined && (
        <span
          className="absolute bottom-0 right-0 rounded-full"
          style={{
            width: dotPx,
            height: dotPx,
            backgroundColor: online ? '#7DC95E' : '#A09A8E',
            border: `2px solid #F6F1E9`,
          }}
        />
      )}
    </div>
  )
}
