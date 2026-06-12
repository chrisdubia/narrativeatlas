'use client'

import { useState } from 'react'
import { Search } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import MemberCard from '@/components/MemberCard'
import Avatar from '@/components/ui/Avatar'
import { members } from '@/lib/mockData'

const mono: React.CSSProperties = {
  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
}

export default function MembersPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const currentUser = members[0]

  const filtered = members.filter(m =>
    m.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    m.location.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div style={{ background: '#F6F1E9', minHeight: '100vh' }}>
      <nav style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 40px', borderBottom: '1px solid #E6E0D6', background: '#F6F1E9', position: 'sticky', top: 0, zIndex: 50 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
          <CompassMark size={20} />
          <span style={{ ...mono, fontSize: 12, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase' as const }}>Narrative Atlas</span>
        </div>
        <div style={{ display: 'flex', gap: 28, ...mono, fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase' as const, color: '#8A8880' }}>
          <Link href="/" style={{ color: '#8A8880', textDecoration: 'none' }}>Home</Link>
          <Link href="/groups" style={{ color: '#8A8880', textDecoration: 'none' }}>Communities</Link>
          <Link href="/members" style={{ color: '#1C1C1A', textDecoration: 'none' }}>Members</Link>
        </div>
        <Avatar initials={currentUser.avatar} size="sm" />
      </nav>

      <main style={{ maxWidth: 1080, margin: '0 auto', padding: '40px 40px 70px' }}>
        <p style={{ ...mono, fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase' as const, color: '#C2683D', marginBottom: 12 }}>Directory</p>
        <h1 style={{ fontSize: 48, fontWeight: 300, letterSpacing: '-0.025em', marginBottom: 10 }}>Members</h1>
        <p style={{ fontSize: 15, fontWeight: 300, color: '#5A5855', marginBottom: 36 }}>
          {members.length} changemakers from around the world.
        </p>

        <div style={{ position: 'relative' as const, maxWidth: 320, marginBottom: 32 }}>
          <Search size={14} style={{ position: 'absolute' as const, left: 12, top: '50%', transform: 'translateY(-50%)', color: '#A09A8E' }} />
          <input
            type="text"
            value={searchQuery}
            onChange={e => setSearchQuery(e.target.value)}
            placeholder="Search members…"
            style={{ width: '100%', padding: '9px 14px 9px 34px', border: '1px solid #D8D5CE', borderRadius: 4, background: '#FBF7F0', ...mono, fontSize: 11, color: '#1C1C1A', outline: 'none' }}
          />
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {filtered.map(member => (
            <MemberCard key={member.id} member={member} />
          ))}
        </div>

        {filtered.length === 0 && (
          <div style={{ textAlign: 'center' as const, padding: '96px 0' }}>
            <p style={{ ...mono, fontSize: 11, color: '#A09A8E' }}>No members found.</p>
          </div>
        )}
      </main>
    </div>
  )
}
