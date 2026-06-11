export default function Badge({ label, color = '#E8A838' }: { label: string; color?: string }) {
  return (
    <span
      className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
      style={{ background: `${color}18`, color: color, border: `1px solid ${color}30` }}
    >
      {label}
    </span>
  );
}
