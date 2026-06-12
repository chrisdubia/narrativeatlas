'use client'

import { useState } from 'react'
import { Search } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import GroupCard from '@/components/GroupCard'
import Avatar from '@/components/ui/Avatar'
import { groups, members } from '@/lib/mockData'

const filters = ['All', 'Featured', 'Recently Active']

const mono: React.CSSProperties = {
  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
}

export default function GroupsPage() {
  const [activeFilter, setActiveFilter] = useState('All')
  const [search, setSearch] = useState('')
  const currentUser = members[0]

  const filtered = groups.filter(g => {
    const matchSearch = g.name.toLowerCase().includes(search.toLowerCase()) || g.description.toLowerCase().includes(search.toLowerCase())
    if (!matchSearch) return false
    if (activeFilter === 'Featured') return g.featured
    if (activeFilter === 'Recently Active') return ['1 hour ago', '2 hours ago', '3 hours ago', '4 hours ago'].includes(g.lastActive)
    return true
  })

  return (
    <div style={{ background: '#F6F1E9', minHeight: '100vh' }}>
      <nav style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 40px', borderBottom: '1px solid #E6E0D6', background: '#F6F1E9', position: 'sticky', top: 0, zIndex: 50 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
          <CompassMark size={20} />
          <span style={{ ...mono, fontSize: 12, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase' as const }}>Narrative Atlas</span>
        </div>
        <div style={{ display: 'flex', gap: 28, ...mono, fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase' as const, color: '#8A8880' }}>
          <Link href="/" style={{ color: '#8A8880', textDecoration: 'none' }}>Home</Link>
          <Link href="/groups" style={{ color: '#1C1C1A', textDecoration: 'none' }}>Communities</Link>
          <Link href="/members" style={{ color: '#8A8880', textDecoration: 'none' }}>Members</Link>
        </div>
        <Avatar initials={currentUser.avatar} size="sm" />
      </nav>

      <main style={{ maxWidth: 1080, margin: '0 auto', padding: '40px 40px 70px' }}>
        <p style={{ ...mono, fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase' as const, color: '#C2683D', marginBottom: 12 }}>Explore</p>
        <h1 style={{ fontSize: 48, fontWeight: 300, letterSpacing: '-0.025em', marginBottom: 10 }}>Circles</h1>
        <p style={{ fontSize: 15, fontWeight: 300, color: '#5A5855', marginBottom: 36 }}>
          Groups of young changemakers working across borders.
        </p>

        <div style={{ display: 'flex', flexWrap: 'wrap' as const, gap: 12, alignItems: 'center', marginBottom: 32 }}>
          <div style={{ display: 'flex', gap: 4 }}>
            {filters.map(f => (
              <button
                key={f}
                onClick={() => setActiveFilter(f)}
                style={{
                  ...mono,
                  fontSize: 10,
                  letterSpacing: '0.08em',
                  textTransform: 'uppercase' as const,
                  padding: '8px 16px',
                  borderRadius: 4,
                  border: '1px solid',
                  cursor: 'pointer',
                  background: activeFilter === f ? '#1C1C1A' : 'transparent',
                  color: activeFilter === f ? '#F2F0EB' : '#5A5855',
                  borderColor: activeFilter === f ? '#1C1C1A' : '#D8D5CE',
                }}
              >
                {f}
              </button>
            ))}
          </div>
          <div style={{ position: 'relative' as const, flex: 1, maxWidth: 280 }}>
            <Search size={14} style={{ position: 'absolute' as const, left: 12, top: '50%', transform: 'translateY(-50%)', color: '#A09A8E' }} />
            <input
              type="search"
              value={search}
              onChange={e => setSearch(e.target.value)}
              placeholder="Search communities..."
              style={{ width: '100%', padding: '9px 14px 9px 34px', border: '1px solid #D8D5CE', borderRadius: 4, background: '#FBF7F0', ...mono, fontSize: 11, color: '#1C1C1A', outline: 'none' }}
            />
          </div>
          <span style={{ ...mono, fontSize: 10, letterSpacing: '0.04em', color: '#A09A8E', marginLeft: 'auto' }}>
            {filtered.length} communities
          </span>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16 }}>
          {filtered.map(group => (
            <GroupCard key={group.id} group={group} />
          ))}
        </div>

        {filtered.length === 0 && (
          <div style={{ textAlign: 'center' as const, padding: '96px 0' }}>
            <p style={{ ...mono, fontSize: 11, color: '#A09A8E' }}>No communities found.</p>
          </div>
        )}
      </main>
    </div>
  )
}
