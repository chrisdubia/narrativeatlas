'use client'

import { ButtonHTMLAttributes, ReactNode } from 'react'

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'outline' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  children: ReactNode
  fullWidth?: boolean
}

export default function Button({
  variant = 'primary',
  size = 'md',
  children,
  fullWidth = false,
  className = '',
  ...props
}: ButtonProps) {
  const base = 'inline-flex items-center justify-center cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed'

  const variants = {
    primary: 'bg-[#1C1C1A] text-[#F2F0EB] hover:bg-[#C2683D] border-none',
    outline: 'bg-transparent text-[#1C1C1A] border border-[#1C1C1A] hover:bg-[#ECEAE4]',
    ghost: 'bg-transparent text-[#1C1C1A] border border-[#D8D5CE] hover:border-[#1C1C1A]',
  }

  const sizes = {
    sm: 'px-3 py-2 text-[10px] tracking-[0.1em] uppercase rounded-[3px]',
    md: 'px-5 py-3 text-[10px] tracking-[0.1em] uppercase rounded-[3px]',
    lg: 'px-6 py-3.5 text-[11px] tracking-[0.1em] uppercase rounded-[3px]',
  }

  return (
    <button
      style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace' }}
      className={`${base} ${variants[variant]} ${sizes[size]} ${fullWidth ? 'w-full' : ''} ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}
