'use client'

import Link from 'next/link'
import { MapPin } from 'lucide-react'
import Avatar from '@/components/ui/Avatar'
import { Member } from '@/lib/mockData'

interface MemberCardProps {
  member: Member
}

const ROLE_STYLES: Record<string, React.CSSProperties> = {
  Organizer: { color: '#8B6F47', border: '1px solid #D9C4A8' },
  Moderator: { color: '#4A7A9B', border: '1px solid #B3CDD9' },
  Teacher:   { color: '#5C7A3E', border: '1px solid #C5D6B3' },
  Member:    { color: '#7A7870', border: '1px solid #E6E0D6' },
}

export default function MemberCard({ member }: MemberCardProps) {
  const roleStyle = ROLE_STYLES[member.role] ?? ROLE_STYLES.Member

  return (
    <Link href={`/members/${member.id}`}>
      <div
        className="rounded-lg p-4 cursor-pointer transition-colors hover:bg-[#F3EEE6]"
        style={{ backgroundColor: '#FBF7F0', border: '1px solid #E6E0D6' }}
      >
        <div className="flex items-start gap-3">
          <Avatar initials={member.avatar} size="md" />
          <div className="flex-1 min-w-0">
            <div className="flex items-start justify-between gap-2 mb-1">
              <span style={{ fontSize: 14, fontWeight: 400, color: '#1C1C1A' }}>{member.name}</span>
              <span
                style={{
                  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
                  fontSize: 9,
                  letterSpacing: '0.08em',
                  textTransform: 'uppercase' as const,
                  padding: '3px 8px',
                  borderRadius: 3,
                  whiteSpace: 'nowrap' as const,
                  ...roleStyle,
                }}
              >
                {member.role}
              </span>
            </div>
            <div
              style={{
                fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
                fontSize: 10,
                letterSpacing: '0.04em',
                color: '#A09A8E',
                textTransform: 'uppercase' as const,
              }}
            >
              {member.location}
            </div>
          </div>
        </div>
        {member.bio && (
          <p
            className="mt-3 line-clamp-2"
            style={{ fontSize: 13, fontWeight: 300, color: '#5A5855', lineHeight: 1.55 }}
          >
            {member.bio}
          </p>
        )}
        <div className="mt-3 flex flex-wrap gap-1.5">
          {member.groups.slice(0, 3).map((g, i) => (
            <span
              key={i}
              style={{
                fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
                fontSize: 9,
                letterSpacing: '0.03em',
                color: '#7A7870',
                border: '1px solid #E6E0D6',
                borderRadius: 3,
                padding: '3px 8px',
              }}
            >
              {g}
            </span>
          ))}
        </div>
      </div>
    </Link>
  )
}
