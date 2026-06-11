'use client';
import { useState } from 'react';
import { motion } from 'framer-motion';
import Link from 'next/link';
import CompassMark from '@/components/CompassMark';
import Button from '@/components/ui/Button';

const quotes = [
  { text: 'Working with students in Lviv changed how I see everything.', name: 'Maya', location: 'Omaha, Nebraska' },
  { text: 'We mapped our neighborhood and sent it to city hall. They actually listened.', name: 'Aisha', location: 'Nairobi, Kenya' },
  { text: 'For the first time, I felt like my voice crossed borders.', name: 'Dmytro', location: 'Lviv, Ukraine' },
];

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPass, setShowPass] = useState(false);

  return (
    <div className="min-h-screen flex">
      {/* Left panel */}
      <div className="hidden lg:flex flex-col w-1/2 relative overflow-hidden map-grid" style={{ background: '#0A0A0A' }}>
        {/* Amber glow */}
        <div className="absolute top-1/3 left-1/4 w-96 h-96 rounded-full opacity-10 blur-3xl" style={{ background: '#E8A838' }} />
        <div className="absolute bottom-1/4 right-1/4 w-64 h-64 rounded-full opacity-5 blur-3xl" style={{ background: '#7B68EE' }} />

        <div className="relative z-10 flex flex-col h-full p-16">
          {/* Logo */}
          <div className="flex items-center gap-3 mb-auto">
            <CompassMark size={36} animated />
            <span className="font-display text-[#F0EDE8] text-xl italic">Narrative Atlas</span>
          </div>

          {/* Hero text */}
          <div className="mb-12">
            <motion.h1
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="font-display text-5xl text-[#F0EDE8] leading-tight mb-4"
            >
              Where young<br />
              <span style={{ color: '#E8A838' }}>changemakers</span><br />
              connect, collaborate,<br />
              and act.
            </motion.h1>
            <motion.p
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.8, delay: 0.5 }}
              className="text-[#8A8580] text-lg"
            >
              A platform for virtual exchange and civic action — built for the youth of today and the teachers who believe in them.
            </motion.p>
          </div>

          {/* Quote cards */}
          <div className="flex flex-col gap-3">
            {quotes.map((q, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.6 + i * 0.15, duration: 0.5 }}
                className="float-animate p-4 rounded-xl border border-[#2A2A2A] bg-[#161616]/60 backdrop-blur-sm"
                style={{ animationDelay: `${i * 1.3}s` }}
              >
                <p className="text-[#F0EDE8] text-sm italic mb-2">&ldquo;{q.text}&rdquo;</p>
                <p className="text-xs text-[#E8A838]">— {q.name}, <span className="text-[#4A4845]">{q.location}</span></p>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* Right panel — login form */}
      <div className="flex-1 flex items-center justify-center p-8" style={{ background: '#111111' }}>
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          className="w-full max-w-sm"
        >
          {/* Mobile logo */}
          <div className="flex items-center gap-2 mb-8 lg:hidden">
            <CompassMark size={28} />
            <span className="font-display text-[#F0EDE8] italic">Narrative Atlas</span>
          </div>

          {/* Compass centered for desktop */}
          <div className="hidden lg:flex justify-center mb-8">
            <CompassMark size={48} animated />
          </div>

          <h2 className="font-display text-3xl text-[#F0EDE8] mb-2">Welcome back.</h2>
          <p className="text-[#8A8580] text-sm mb-8">Sign in to continue your work.</p>

          <form className="flex flex-col gap-5" onSubmit={e => e.preventDefault()}>
            {/* Email */}
            <div>
              <label className="block text-xs text-[#8A8580] mb-2 uppercase tracking-wider">Email</label>
              <input
                type="email"
                value={email}
                onChange={e => setEmail(e.target.value)}
                placeholder="you@example.com"
                className="w-full bg-[#1A1A1A] border border-[#2A2A2A] rounded-lg px-4 py-3 text-sm text-[#F0EDE8] placeholder-[#3A3A3A] focus:border-[#E8A838] transition-colors"
              />
            </div>

            {/* Password */}
            <div>
              <label className="block text-xs text-[#8A8580] mb-2 uppercase tracking-wider">Password</label>
              <div className="relative">
                <input
                  type={showPass ? 'text' : 'password'}
                  value={password}
                  onChange={e => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full bg-[#1A1A1A] border border-[#2A2A2A] rounded-lg px-4 py-3 text-sm text-[#F0EDE8] placeholder-[#3A3A3A] focus:border-[#E8A838] transition-colors pr-10"
                />
                <button
                  type="button"
                  onClick={() => setShowPass(!showPass)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-[#4A4845] hover:text-[#8A8580] text-xs"
                >
                  {showPass ? 'Hide' : 'Show'}
                </button>
              </div>
              <div className="flex justify-end mt-1.5">
                <a href="#" className="text-xs text-[#4A4845] hover:text-[#E8A838] transition-colors">Forgot password?</a>
              </div>
            </div>

            <Link href="/">
              <Button variant="primary" size="lg" fullWidth type="submit">
                Sign in
              </Button>
            </Link>

            <div className="flex items-center gap-3">
              <div className="flex-1 h-px bg-[#2A2A2A]" />
              <span className="text-xs text-[#4A4845]">or</span>
              <div className="flex-1 h-px bg-[#2A2A2A]" />
            </div>

            <Button variant="ghost" size="lg" fullWidth>
              <svg width="16" height="16" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Continue with Google
            </Button>
          </form>

          <p className="text-center text-xs text-[#4A4845] mt-8">
            New to Narrative Atlas?{' '}
            <a href="#" className="text-[#E8A838] hover:underline">Request access</a>
          </p>
        </motion.div>
      </div>
    </div>
  );
}
