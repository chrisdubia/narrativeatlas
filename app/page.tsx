'use client'

import { Search, Lock, ArrowRight } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import GroupCard from '@/components/GroupCard'
import Avatar from '@/components/ui/Avatar'
import { groups, activities, events, members } from '@/lib/mockData'

const currentUser = members[0]

const mono: React.CSSProperties = {
  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
}

export default function Dashboard() {
  const myGroups = groups.filter(g => g.featured)
  const discoverGroups = groups.filter(g => !g.featured)

  return (
    <div style={{ background: '#F6F1E9', minHeight: '100vh' }}>
      {/* Nav */}
      <nav style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 40px', borderBottom: '1px solid #E6E0D6', background: '#F6F1E9', position: 'sticky', top: 0, zIndex: 50 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
          <CompassMark size={20} />
          <span style={{ ...mono, fontSize: 12, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase' }}>Narrative Atlas</span>
        </div>
        <div style={{ display: 'flex', gap: 28, ...mono, fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase', color: '#8A8880' }}>
          <Link href="/" style={{ color: '#1C1C1A', textDecoration: 'none' }}>Home</Link>
          <Link href="/groups" style={{ color: '#8A8880', textDecoration: 'none' }}>Communities</Link>
          <Link href="/members" style={{ color: '#8A8880', textDecoration: 'none' }}>Members</Link>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
          <Search size={16} style={{ color: '#8A8880' }} />
          <Avatar initials={currentUser.avatar} size="sm" online />
        </div>
      </nav>

      <div style={{ maxWidth: 1080, margin: '0 auto', padding: '30px 40px 70px' }}>

        {/* Hero photo banner */}
        <div style={{ position: 'relative', borderRadius: 8, overflow: 'hidden', height: 300, marginBottom: 14, background: '#2a1a0e' }}>
          <img
            src="https://images.unsplash.com/photo-1529390079861-591de354faf5?w=1200&q=80"
            alt=""
            style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block', opacity: 0.88 }}
          />
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(90deg, rgba(20,18,15,0.78) 0%, rgba(20,18,15,0.45) 42%, rgba(20,18,15,0.05) 75%)' }} />
          <div style={{ position: 'absolute', left: 0, top: 0, bottom: 0, display: 'flex', flexDirection: 'column', justifyContent: 'center', padding: '0 44px', maxWidth: 620 }}>
            <p style={{ ...mono, fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase', color: '#E7B89C', marginBottom: 14 }}>
              MapWorks Learning · Virtual Exchange
            </p>
            <h1 style={{ fontSize: 40, fontWeight: 300, letterSpacing: '-0.025em', lineHeight: 1.06, color: '#F8F4EC', marginBottom: 12 }}>
              Good morning,{' '}
              <em style={{ fontStyle: 'italic', color: '#F0A878' }}>{currentUser.name.split(' ')[0]}</em>.
            </h1>
            <p style={{ fontSize: 14, fontWeight: 300, lineHeight: 1.6, color: 'rgba(248,244,236,0.82)' }}>
              You belong to {myGroups.length} groups and {myGroups.length + 1} circles. Your circle has new posts since yesterday.
            </p>
          </div>
        </div>

        {/* Stats strip */}
        <div style={{ display: 'flex', gap: 10, marginBottom: 44 }}>
          {[
            { n: myGroups.length, l: 'Groups' },
            { n: myGroups.length + 1, l: 'Circles' },
            { n: activities.length, l: 'Activity' },
            { n: events.length, l: 'Upcoming' },
          ].map(({ n, l }) => (
            <div key={l} style={{ flex: 1, background: '#FBF7F0', border: '1px solid #E6E0D6', borderRadius: 6, padding: '16px 18px' }}>
              <div style={{ fontSize: 26, fontWeight: 300, letterSpacing: '-0.02em', color: '#1C1C1A' }}>{n}</div>
              <div style={{ ...mono, fontSize: 9, letterSpacing: '0.08em', textTransform: 'uppercase', color: '#A09A8E', marginTop: 3 }}>{l}</div>
            </div>
          ))}
        </div>

        {/* Group zones */}
        {myGroups.map(group => (
          <div key={group.id} style={{ marginBottom: 46 }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', paddingBottom: 14, borderBottom: '2px solid #1C1C1A' }}>
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 14 }}>
                <span style={{ fontSize: 21, fontWeight: 400, letterSpacing: '-0.01em' }}>{group.name}</span>
                <span style={{ ...mono, fontSize: 10, letterSpacing: '0.04em', color: '#A09A8E', textTransform: 'uppercase' }}>Group · {group.memberCount} people</span>
              </div>
              <Link href={`/groups/${group.id}`} style={{ display: 'flex', alignItems: 'center', gap: 6, ...mono, fontSize: 10, letterSpacing: '0.06em', textTransform: 'uppercase', color: '#C2683D', textDecoration: 'none' }}>
                Open group dialogue <ArrowRight size={12} />
              </Link>
            </div>

            <div style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase', color: '#A09A8E', margin: '22px 0 16px', display: 'flex', alignItems: 'center', gap: 8 }}>
              <Lock size={11} />
              Your circles in this group
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <GroupCard group={group} />
              <GroupCard group={groups[(groups.indexOf(group) + 2) % groups.length]} />
            </div>

            <p style={{ marginTop: 14, ...mono, fontSize: 9, letterSpacing: '0.03em', color: '#B8B0A2' }}>
              You only see circles you have been added to. Other circles in this group stay private to their members.
            </p>
          </div>
        ))}

        {/* Discover more */}
        <div style={{ marginTop: 10 }}>
          <p style={{ ...mono, fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase', color: '#C2683D', marginBottom: 12 }}>Discover</p>
          <h2 style={{ fontSize: 28, fontWeight: 300, letterSpacing: '-0.02em', marginBottom: 20 }}>More communities</h2>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 16 }}>
            {discoverGroups.slice(0, 3).map(group => (
              <GroupCard key={group.id} group={group} />
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
