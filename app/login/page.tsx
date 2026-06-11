'use client'

import { useState } from 'react'
import { motion } from 'framer-motion'
import { Eye, EyeOff } from 'lucide-react'
import CompassMark from '@/components/CompassMark'
import Button from '@/components/ui/Button'

const quotes = [
  { text: "This platform helped me connect with students in Kenya who face the same climate challenges we do in Copenhagen.", author: "Maja H., Denmark, 17" },
  { text: "I never imagined I'd be co-writing legislation with someone from another continent. Narrative Atlas made it real.", author: "Oluwaseun A., Nigeria, 19" },
  { text: "Our group map shows how our stories are connected across borders. It's the most meaningful project I've done.", author: "Chen Wei, Taiwan, 16" },
]

export default function LoginPage() {
  const [showPassword, setShowPassword] = useState(false)
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  return (
    <div className="min-h-screen flex">
      {/* Left panel */}
      <div className="hidden lg:flex lg:w-1/2 relative bg-[#0A0A0A] flex-col items-center justify-center px-12 overflow-hidden">
        {/* Background texture */}
        <div className="absolute inset-0 opacity-20"
          style={{
            backgroundImage: 'radial-gradient(circle at 25% 25%, #E8A83820 0%, transparent 50%), radial-gradient(circle at 75% 75%, #4CAF7D10 0%, transparent 50%)',
          }}
        />
        
        {/* Animated compass */}
        <motion.div
          animate={{ rotate: 360 }}
          transition={{ duration: 60, repeat: Infinity, ease: 'linear' }}
          className="absolute top-1/4 right-1/4 opacity-10"
        >
          <CompassMark size={300} />
        </motion.div>
        
        <div className="relative z-10 max-w-md">
          <div className="flex items-center gap-3 mb-8">
            <CompassMark size={36} />
            <span className="font-serif text-2xl text-text-primary">Narrative Atlas</span>
          </div>
          
          <h1 className="font-serif text-5xl text-text-primary leading-tight mb-4">
            Where young changemakers connect, collaborate, and act.
          </h1>
          
          <p className="text-text-secondary text-lg mb-12">
            A global platform for students building bridges across borders.
          </p>
          
          {/* Floating quote cards */}
          <div className="space-y-4">
            {quotes.map((quote, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.5 + i * 0.2 }}
                className="bg-surface/80 border border-border rounded-xl p-4 backdrop-blur-sm"
              >
                <p className="text-sm text-text-primary mb-2">&ldquo;{quote.text}&rdquo;</p>
                <p className="text-xs text-text-secondary">— {quote.author}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </div>
      
      {/* Right panel */}
      <div className="flex-1 bg-surface flex flex-col items-center justify-center px-6 py-12">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4 }}
          className="w-full max-w-sm"
        >
          <div className="flex justify-center mb-8">
            <CompassMark size={48} />
          </div>
          
          <h2 className="font-serif text-3xl text-text-primary mb-2 text-center">Welcome back</h2>
          <p className="text-text-secondary text-sm text-center mb-8">Sign in to your account</p>
          
          <form className="space-y-5" onSubmit={(e) => e.preventDefault()}>
            <div>
              <label className="block text-xs text-text-secondary mb-2 uppercase tracking-wide">Email</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="you@example.com"
                className="w-full bg-transparent border-b border-border text-text-primary placeholder-text-secondary/50 py-2 text-sm focus:outline-none focus:border-accent transition-colors"
              />
            </div>
            
            <div>
              <label className="block text-xs text-text-secondary mb-2 uppercase tracking-wide">Password</label>
              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full bg-transparent border-b border-border text-text-primary placeholder-text-secondary/50 py-2 text-sm focus:outline-none focus:border-accent transition-colors pr-8"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-0 top-2 text-text-secondary hover:text-text-primary"
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>
            
            <Button variant="primary" size="lg" fullWidth>
              Sign in
            </Button>
          </form>
          
          <div className="flex items-center my-6">
            <div className="flex-1 h-px bg-border" />
            <span className="px-3 text-xs text-text-secondary">or</span>
            <div className="flex-1 h-px bg-border" />
          </div>
          
          <Button variant="ghost" size="lg" fullWidth>
            <svg className="w-4 h-4" viewBox="0 0 24 24">
              <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
          </Button>
          
          <p className="text-center text-sm text-text-secondary mt-6">
            New to Narrative Atlas?{' '}
            <a href="#" className="text-accent hover:text-accent-hover transition-colors">
              Request access
            </a>
          </p>
        </motion.div>
      </div>
    </div>
  )
}
