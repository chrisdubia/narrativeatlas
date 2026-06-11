// Backward-compatible Badge: supports both label+color (old) and children+variant (new)

interface BadgeProps {
  label?: string;
  children?: React.ReactNode;
  color?: string;
  variant?: 'default' | 'accent' | 'success' | 'danger' | 'info' | 'purple' | 'warning';
  size?: 'sm' | 'md';
  className?: string;
}

const VARIANT_STYLES: Record<string, { bg: string; text: string; border: string }> = {
  default:  { bg: 'rgba(30,30,30,1)', text: '#8A8580', border: '#2A2A2A' },
  accent:   { bg: 'rgba(232,168,56,0.12)', text: '#E8A838', border: 'rgba(232,168,56,0.3)' },
  warning:  { bg: 'rgba(232,168,56,0.12)', text: '#E8A838', border: 'rgba(232,168,56,0.3)' },
  success:  { bg: 'rgba(76,175,125,0.12)', text: '#4CAF7D', border: 'rgba(76,175,125,0.3)' },
  danger:   { bg: 'rgba(232,85,85,0.12)', text: '#E85555', border: 'rgba(232,85,85,0.3)' },
  info:     { bg: 'rgba(56,189,248,0.12)', text: '#38BDF8', border: 'rgba(56,189,248,0.3)' },
  purple:   { bg: 'rgba(139,92,246,0.12)', text: '#8B5CF6', border: 'rgba(139,92,246,0.3)' },
};

export default function Badge({ label, children, color, variant = 'default', size = 'sm', className = '' }: BadgeProps) {
  const content = children ?? label ?? '';
  const pad = size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm';

  let styles: { bg: string; text: string; border: string };
  if (color) {
    styles = { bg: `${color}18`, text: color, border: `${color}30` };
  } else {
    styles = VARIANT_STYLES[variant] ?? VARIANT_STYLES.default;
  }

  return (
    <span
      className={`inline-flex items-center font-medium rounded-full ${pad} ${className}`}
      style={{ backgroundColor: styles.bg, color: styles.text, border: `1px solid ${styles.border}` }}
    >
      {content}
    </span>
  );
}
