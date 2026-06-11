'use client';
import { motion } from 'framer-motion';

export default function CompassMark({ size = 32, animated = false }: { size?: number; animated?: boolean }) {
  return (
    <motion.svg
      width={size}
      height={size}
      viewBox="0 0 40 40"
      fill="none"
      whileHover={animated ? { rotate: 15 } : undefined}
      transition={{ type: 'spring', stiffness: 300, damping: 20 }}
    >
      <circle cx="20" cy="20" r="18" stroke="#2A2A2A" strokeWidth="1.5" />
      <circle cx="20" cy="20" r="14" stroke="#1E1E1E" strokeWidth="0.75" />
      {/* North needle - amber */}
      <motion.path
        d="M20 20 L20 6"
        stroke="#E8A838"
        strokeWidth="2"
        strokeLinecap="round"
        animate={animated ? { rotate: [0, 360] } : undefined}
        transition={animated ? { duration: 20, repeat: Infinity, ease: 'linear' } : undefined}
        style={{ transformOrigin: '20px 20px' }}
      />
      {/* South needle - muted */}
      <path d="M20 20 L20 34" stroke="#4A4845" strokeWidth="1.5" strokeLinecap="round" />
      {/* East/West ticks */}
      <path d="M6 20 L8 20 M32 20 L34 20" stroke="#2A2A2A" strokeWidth="1" strokeLinecap="round" />
      {/* Center dot */}
      <circle cx="20" cy="20" r="2" fill="#E8A838" />
      {/* Cardinal labels */}
      <text x="20" y="4.5" textAnchor="middle" fill="#E8A838" fontSize="4" fontFamily="Inter" fontWeight="600">N</text>
      <text x="20" y="38" textAnchor="middle" fill="#4A4845" fontSize="4" fontFamily="Inter" fontWeight="500">S</text>
    </motion.svg>
  );
}
