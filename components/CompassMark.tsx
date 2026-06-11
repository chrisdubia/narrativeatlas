'use client'

import { motion } from 'framer-motion'

interface CompassMarkProps {
  size?: number
  animate?: boolean
  className?: string
}

export default function CompassMark({ size = 48, animate = false, className = '' }: CompassMarkProps) {
  return (
    <motion.svg
      width={size}
      height={size}
      viewBox="0 0 48 48"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={className}
      animate={animate ? { rotate: 360 } : {}}
      transition={animate ? { duration: 30, repeat: Infinity, ease: 'linear' } : {}}
      whileHover={{ rotate: 45 }}
    >
      {/* Outer ring */}
      <circle cx="24" cy="24" r="22" stroke="#2A2A2A" strokeWidth="1.5" />
      <circle cx="24" cy="24" r="18" stroke="#2A2A2A" strokeWidth="0.5" />
      
      {/* Cardinal tick marks */}
      <line x1="24" y1="2" x2="24" y2="7" stroke="#8A8580" strokeWidth="1.5" strokeLinecap="round" />
      <line x1="46" y1="24" x2="41" y2="24" stroke="#8A8580" strokeWidth="1" strokeLinecap="round" />
      <line x1="24" y1="46" x2="24" y2="41" stroke="#8A8580" strokeWidth="1" strokeLinecap="round" />
      <line x1="2" y1="24" x2="7" y2="24" stroke="#8A8580" strokeWidth="1" strokeLinecap="round" />
      
      {/* North needle (amber) */}
      <motion.path
        d="M24 6 L26.5 24 L24 22 L21.5 24 Z"
        fill="#E8A838"
        whileHover={{ scale: 1.1 }}
      />
      {/* South needle (dark) */}
      <path d="M24 42 L26.5 24 L24 26 L21.5 24 Z" fill="#3A3A3A" />
      
      {/* Center dot */}
      <circle cx="24" cy="24" r="2.5" fill="#E8A838" />
      <circle cx="24" cy="24" r="1" fill="#0D0D0D" />
      
      {/* Ordinal ticks */}
      <line x1="39.6" y1="8.4" x2="36.4" y2="11.6" stroke="#2A2A2A" strokeWidth="0.75" strokeLinecap="round" />
      <line x1="39.6" y1="39.6" x2="36.4" y2="36.4" stroke="#2A2A2A" strokeWidth="0.75" strokeLinecap="round" />
      <line x1="8.4" y1="39.6" x2="11.6" y2="36.4" stroke="#2A2A2A" strokeWidth="0.75" strokeLinecap="round" />
      <line x1="8.4" y1="8.4" x2="11.6" y2="11.6" stroke="#2A2A2A" strokeWidth="0.75" strokeLinecap="round" />
    </motion.svg>
  )
}
