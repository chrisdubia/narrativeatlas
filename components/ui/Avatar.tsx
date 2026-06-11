export default function Avatar({
  initials, color = '#E8A838', size = 36, online
}: { initials: string; color?: string; size?: number; online?: boolean }) {
  return (
    <div className="relative inline-flex shrink-0" style={{ width: size, height: size }}>
      <div
        className="flex items-center justify-center rounded-full font-semibold text-[#0D0D0D] select-none"
        style={{
          width: size, height: size,
          background: color,
          fontSize: size * 0.32,
        }}
      >
        {initials}
      </div>
      {online && (
        <span className="absolute bottom-0 right-0 w-2.5 h-2.5 bg-[#4CAF7D] rounded-full border-2 border-[#0D0D0D]" />
      )}
    </div>
  );
}
