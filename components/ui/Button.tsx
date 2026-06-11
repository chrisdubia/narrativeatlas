'use client';
import { motion } from 'framer-motion';
import { ReactNode } from 'react';

interface ButtonProps {
  children: ReactNode;
  variant?: 'primary' | 'ghost' | 'danger' | 'subtle';
  size?: 'sm' | 'md' | 'lg';
  onClick?: () => void;
  disabled?: boolean;
  fullWidth?: boolean;
  type?: 'button' | 'submit';
  className?: string;
}

export default function Button({
  children, variant = 'primary', size = 'md', onClick, disabled, fullWidth, type = 'button', className = ''
}: ButtonProps) {
  const base = 'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-200 cursor-pointer border-0 select-none';

  const variants = {
    primary: 'bg-[#E8A838] text-[#0D0D0D] hover:bg-[#F0B84A] active:scale-[0.98]',
    ghost: 'bg-transparent text-[#F0EDE8] border border-[#2A2A2A] hover:border-[#E8A838] hover:text-[#E8A838] active:scale-[0.98]',
    danger: 'bg-[#E85555] text-white hover:bg-[#f06666] active:scale-[0.98]',
    subtle: 'bg-[#1E1E1E] text-[#8A8580] hover:bg-[#2A2A2A] hover:text-[#F0EDE8] active:scale-[0.98]',
  };

  const sizes = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
  };

  return (
    <motion.button
      whileTap={{ scale: 0.97 }}
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={`${base} ${variants[variant]} ${sizes[size]} ${fullWidth ? 'w-full' : ''} ${disabled ? 'opacity-40 cursor-not-allowed' : ''} ${className}`}
    >
      {children}
    </motion.button>
  );
}
