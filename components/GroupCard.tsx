'use client'

import Link from 'next/link'
import { Group } from '@/lib/mockData'

interface GroupCardProps {
  group: Group
  compact?: boolean
}

const AVATAR_COLORS = ['#6B8F4E', '#4A7A9B', '#C2683D', '#8B6F47', '#5C7A9B', '#A0522D']

export default function GroupCard({ group, compact = false }: GroupCardProps) {
  const initials = group.name.split(' ').slice(0, 2).map(w => w[0]).join('')

  return (
    <Link href={`/groups/${group.id}`}>
      <div
        className="rounded-lg overflow-hidden cursor-pointer relative"
        style={{ height: compact ? 180 : 230, background: '#1C1C1A' }}
      >
        {/* Photo background using gradient as placeholder */}
        <div
          className="absolute inset-0"
          style={{ background: group.gradient, opacity: 0.9 }}
        />
        {/* Scrim */}
        <div
          className="absolute inset-0"
          style={{ background: 'linear-gradient(180deg, rgba(20,18,15,0.15) 0%, rgba(20,18,15,0.05) 40%, rgba(20,18,15,0.85) 100%)' }}
        />

        {/* Top row */}
        <div className="absolute top-3 left-4 right-4 flex items-center justify-between">
          <span
            style={{
              fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
              fontSize: '9px',
              letterSpacing: '0.05em',
              textTransform: 'uppercase',
              color: '#F4EFE6',
              background: 'rgba(20,18,15,0.45)',
              backdropFilter: 'blur(4px)',
              padding: '4px 9px',
              borderRadius: '3px',
            }}
          >
            {group.location}
          </span>
          <span
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '5px',
              fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
              fontSize: '9px',
              color: '#F4EFE6',
              background: 'rgba(20,18,15,0.45)',
              backdropFilter: 'blur(4px)',
              padding: '4px 9px',
              borderRadius: '3px',
            }}
          >
            <span style={{ width: 5, height: 5, borderRadius: '50%', background: '#7DC95E', display: 'inline-block' }} />
            Active
          </span>
        </div>

        {/* Bottom content */}
        <div className="absolute left-4 right-4 bottom-4">
          <div
            style={{
              fontSize: compact ? 16 : 20,
              fontWeight: 400,
              letterSpacing: '-0.01em',
              color: '#F8F4EC',
              marginBottom: 8,
              lineHeight: 1.2,
            }}
          >
            {group.name}
          </div>
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              {AVATAR_COLORS.slice(0, 3).map((bg, i) => (
                <div
                  key={i}
                  style={{
                    width: 24,
                    height: 24,
                    borderRadius: '50%',
                    border: '1.5px solid rgba(248,244,236,0.9)',
                    marginLeft: i === 0 ? 0 : -7,
                    background: bg,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
                    fontSize: 8,
                    color: '#fff',
                  }}
                >
                  {initials[i % initials.length]}
                </div>
              ))}
              <span
                style={{
                  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
                  fontSize: 10,
                  color: 'rgba(248,244,236,0.85)',
                  marginLeft: 9,
                }}
              >
                {group.memberCount}
              </span>
            </div>
            <span
              style={{
                fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
                fontSize: 9,
                color: 'rgba(248,244,236,0.7)',
              }}
            >
              {group.lastActive}
            </span>
          </div>
        </div>
      </div>
    </Link>
  )
}
