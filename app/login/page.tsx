'use client'

import { useState } from 'react'
import CompassMark from '@/components/CompassMark'

export default function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1.05fr 1fr', minHeight: '100vh', background: '#F2F0EB' }}>

      {/* Left panel */}
      <div
        style={{
          background: '#ECEAE4',
          padding: '56px 60px',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'space-between',
          borderRight: '1px solid #D8D5CE',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
          <CompassMark size={20} />
          <span style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 13, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase' as const }}>
            Narrative Atlas
          </span>
        </div>

        <div style={{ maxWidth: 380 }}>
          <p style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, letterSpacing: '0.18em', textTransform: 'uppercase' as const, color: '#A8A59E', marginBottom: 20 }}>
            A project of MapWorks Learning
          </p>
          <h1 style={{ fontSize: 42, fontWeight: 300, letterSpacing: '-0.025em', lineHeight: 1.08, marginBottom: 20 }}>
            Where young people<br />meet the{' '}
            <em style={{ fontStyle: 'italic', color: '#C2683D' }}>world</em>.
          </h1>
          <p style={{ fontSize: 14, fontWeight: 300, lineHeight: 1.75, color: '#5A5855' }}>
            Circles of students and teachers from different places, listening deeply, learning courageously, and building solutions together.
          </p>
          <div style={{ display: 'flex', gap: 18, alignItems: 'center', marginTop: 28 }}>
            <div style={{ width: 78, height: 112, border: '1.5px solid #1C1C1A', borderRadius: 2, position: 'relative' as const, background: '#F8F6F1', flexShrink: 0 }}>
              <div style={{ position: 'absolute' as const, top: 6, left: 6, right: 22, bottom: 6, border: '1px solid #C2683D', borderRadius: 1 }} />
              <div style={{ position: 'absolute' as const, top: '50%', right: 26, width: 4, height: 4, borderRadius: '50%', background: '#1C1C1A', transform: 'translateY(-50%)' }} />
            </div>
            <p style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, letterSpacing: '0.04em', lineHeight: 1.7, color: '#7A7870' }}>
              Open the door.<br /><strong style={{ color: '#1C1C1A', fontWeight: 400 }}>Pull up to the circle.</strong>
            </p>
          </div>
        </div>

        <p style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 9, letterSpacing: '0.08em', color: '#A8A59E', textTransform: 'uppercase' as const }}>
          Led by youth · Powered by purpose · Connected to the world
        </p>
      </div>

      {/* Right panel */}
      <div style={{ padding: '56px 60px', display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center' }}>
        <div style={{ width: '100%', maxWidth: 320 }}>
          <p style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase' as const, color: '#A8A59E', marginBottom: 12 }}>
            Welcome back
          </p>
          <h2 style={{ fontSize: 30, fontWeight: 300, letterSpacing: '-0.02em', marginBottom: 8 }}>Sign in</h2>
          <p style={{ fontSize: 13, fontWeight: 300, color: '#7A7870', marginBottom: 32, lineHeight: 1.6 }}>
            Continue to your circles and projects.
          </p>

          <div style={{ marginBottom: 14 }}>
            <label style={{ display: 'block', fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 9, letterSpacing: '0.1em', textTransform: 'uppercase' as const, color: '#A8A59E', marginBottom: 7 }}>
              Email or phone
            </label>
            <input
              type="text"
              value={email}
              onChange={e => setEmail(e.target.value)}
              placeholder="you@school.edu"
              style={{ width: '100%', padding: '12px 14px', border: '1px solid #D8D5CE', borderRadius: 3, background: '#FFFFFF', fontFamily: 'var(--font-epilogue), Epilogue, sans-serif', fontSize: 14, fontWeight: 300, color: '#1C1C1A', outline: 'none' }}
            />
          </div>
          <div style={{ marginBottom: 14 }}>
            <label style={{ display: 'block', fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 9, letterSpacing: '0.1em', textTransform: 'uppercase' as const, color: '#A8A59E', marginBottom: 7 }}>
              Password
            </label>
            <input
              type="password"
              value={password}
              onChange={e => setPassword(e.target.value)}
              placeholder="••••••••"
              style={{ width: '100%', padding: '12px 14px', border: '1px solid #D8D5CE', borderRadius: 3, background: '#FFFFFF', fontFamily: 'var(--font-epilogue), Epilogue, sans-serif', fontSize: 14, fontWeight: 300, color: '#1C1C1A', outline: 'none' }}
            />
          </div>

          <button style={{ width: '100%', padding: '13px 0', border: 'none', borderRadius: 3, background: '#1C1C1A', color: '#F2F0EB', fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase' as const, cursor: 'pointer', marginTop: 8 }}>
            Sign in
          </button>

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 16 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, color: '#7A7870' }}>
              <span style={{ width: 13, height: 13, border: '1px solid #B0ADA6', borderRadius: 2, display: 'inline-block' }} />
              Remember me
            </div>
            <a href="#" style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, color: '#4A7A9B', textDecoration: 'none' }}>
              Forgot password?
            </a>
          </div>

          <button style={{ width: '100%', padding: '11px 0', border: '1px solid #D8D5CE', borderRadius: 3, background: 'transparent', fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase' as const, color: '#5A5855', cursor: 'pointer', marginTop: 10 }}>
            Email me a one-time code
          </button>

          <div style={{ display: 'flex', alignItems: 'center', gap: 14, margin: '26px 0' }}>
            <div style={{ flex: 1, height: 1, background: '#E0DDD6' }} />
            <span style={{ fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#B0ADA6' }}>or</span>
            <div style={{ flex: 1, height: 1, background: '#E0DDD6' }} />
          </div>

          <button style={{ width: '100%', padding: '12px 0', border: '1px solid #D8D5CE', borderRadius: 3, background: '#FFFFFF', fontFamily: 'var(--font-dm-mono), DM Mono, monospace', fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase' as const, color: '#1C1C1A', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }}>
            <svg width="14" height="14" viewBox="0 0 18 18">
              <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.71-1.57 2.68-3.89 2.68-6.62z"/>
              <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.83.86-3.04.86-2.34 0-4.32-1.58-5.03-3.7H.96v2.33A9 9 0 0 0 9 18z"/>
              <path fill="#FBBC05" d="M3.97 10.72a5.4 5.4 0 0 1 0-3.44V4.95H.96a9 9 0 0 0 0 8.1l3.01-2.33z"/>
              <path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58A9 9 0 0 0 9 0 9 9 0 0 0 .96 4.95l3.01 2.33C4.68 5.16 6.66 3.58 9 3.58z"/>
            </svg>
            Continue with Google
          </button>
        </div>
      </div>
    </div>
  )
}
