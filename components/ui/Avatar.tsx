// Backward-compatible Avatar — accepts size as number or string

interface AvatarProps {
  initials: string;
  size?: number | 'sm' | 'md' | 'lg' | 'xl';
  online?: boolean;
  color?: string;
  className?: string;
}

const INITIAL_COLORS: Record<string, string> = {
  A: '#4CAF7D', B: '#E8A838', C: '#8B5CF6', D: '#38BDF8', E: '#F97316',
  F: '#EC4899', G: '#4CAF7D', H: '#E8A838', I: '#8B5CF6', J: '#38BDF8',
  K: '#F97316', L: '#EC4899', M: '#4CAF7D', N: '#E8A838', O: '#8B5CF6',
  P: '#38BDF8', Q: '#F97316', R: '#EC4899', S: '#4CAF7D', T: '#E8A838',
  U: '#8B5CF6', V: '#38BDF8', W: '#F97316', X: '#EC4899', Y: '#4CAF7D',
  Z: '#E8A838',
};

const SIZE_MAP = { sm: 28, md: 36, lg: 48, xl: 80 };

export default function Avatar({ initials, size = 'md', online, color, className = '' }: AvatarProps) {
  const px = typeof size === 'number' ? size : (SIZE_MAP[size] ?? 36);
  const bg = color ?? INITIAL_COLORS[initials?.[0]?.toUpperCase() ?? ''] ?? '#E8A838';
  const dotPx = Math.max(8, Math.round(px * 0.26));
  const fontPx = Math.max(9, Math.round(px * 0.38));

  return (
    <div
      className={`relative inline-flex shrink-0 ${className}`}
      style={{ width: px, height: px }}
    >
      <div
        className="w-full h-full rounded-full flex items-center justify-center font-semibold select-none"
        style={{ backgroundColor: bg, color: '#0D0D0D', fontSize: fontPx }}
      >
        {(initials ?? '').slice(0, 2)}
      </div>
      {online !== undefined && (
        <span
          className="absolute bottom-0 right-0 rounded-full"
          style={{
            width: dotPx,
            height: dotPx,
            backgroundColor: online ? '#4CAF7D' : '#8A8580',
            border: `2px solid #0D0D0D`,
          }}
        />
      )}
    </div>
  );
}
