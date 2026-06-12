'use client'

interface CompassMarkProps {
  size?: number
  className?: string
}

export default function CompassMark({ size = 20, className = '' }: CompassMarkProps) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 20 20"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={className}
    >
      <circle cx="10" cy="10" r="9" stroke="#1C1C1A" strokeWidth="1.5" />
      <path d="M7 10l2 2 4-4" stroke="#1C1C1A" strokeWidth="1.1" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  )
}
