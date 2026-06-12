'use client'

import { useState } from 'react'
import { useParams } from 'next/navigation'
import { ArrowLeft, MapPin, Calendar } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import Avatar from '@/components/ui/Avatar'
import ActivityItem from '@/components/ActivityItem'
import GroupCard from '@/components/GroupCard'
import { members, activities, groups } from '@/lib/mockData'

const mono: React.CSSProperties = {
  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
}

const ROLE_STYLES: Record<string, React.CSSProperties> = {
  Organizer: { color: '#8B6F47', border: '1px solid #D9C4A8' },
  Moderator: { color: '#4A7A9B', border: '1px solid #B3CDD9' },
  Teacher:   { color: '#5C7A3E', border: '1px solid #C5D6B3' },
  Member:    { color: '#7A7870', border: '1px solid #E6E0D6' },
  Student:   { color: '#5C7A3E', border: '1px solid #C5D6B3' },
}

const INTEREST_COLORS = ['#6B8F4E', '#4A7A9B', '#C2683D', '#8B6F47', '#5C7A9B', '#A0522D']

export default function MemberProfilePage() {
  const params = useParams()
  const id = params.id as string
  const [activeTab, setActiveTab] = useState('activity')

  const member = members.find(m => m.id === id) || members[0]
  const memberActivity = activities.filter(a => a.memberId === member.id)
  const memberGroups = groups.filter(g => member.groups.includes(g.id))
  const roleStyle = ROLE_STYLES[member.role] ?? ROLE_STYLES.Member

  const interests = ['Climate', 'Data viz', 'Photography', 'Public health', 'Cross-cultural', 'Urban design']

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
        <Avatar initials={members[0].avatar} size="sm" />
      </nav>

      <div style={{ maxWidth: 1000, margin: '0 auto', padding: '24px 40px 70px' }}>
        {/* Breadcrumb */}
        <div style={{ ...mono, fontSize: 10, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: '#A09A8E', display: 'flex', alignItems: 'center', gap: 8, marginBottom: 18 }}>
          <Link href="/members" style={{ color: '#A09A8E', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
            <ArrowLeft size={12} /> Members
          </Link>
        </div>

        {/* Cover photo */}
        <div style={{ position: 'relative' as const, borderRadius: 8, overflow: 'hidden', height: 180, marginBottom: -50, background: '#ECEAE4' }}>
          <div style={{ position: 'absolute' as const, inset: 0, background: 'linear-gradient(135deg, #D4C9B8 0%, #C8BC9A 100%)' }} />
          <div style={{ position: 'absolute' as const, inset: 0, background: 'linear-gradient(180deg, rgba(20,18,15,0.05), rgba(20,18,15,0.35))' }} />
        </div>

        {/* Profile header */}
        <div style={{ display: 'flex', alignItems: 'flex-end', gap: 24, padding: '0 16px 28px', borderBottom: '1px solid #E6E0D6', position: 'relative' as const }}>
          <div style={{ position: 'relative' as const, zIndex: 1 }}>
            <Avatar initials={member.avatar} size={120} />
          </div>
          <div style={{ flex: 1, paddingBottom: 6 }}>
            <h1 style={{ fontSize: 34, fontWeight: 300, letterSpacing: '-0.025em', marginBottom: 7 }}>{member.name}</h1>
            <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 12, flexWrap: 'wrap' as const }}>
              <span style={{ ...mono, fontSize: 11, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: '#A09A8E', display: 'flex', alignItems: 'center', gap: 6 }}>
                <MapPin size={12} /> {member.location}
              </span>
              <span style={{ ...mono, fontSize: 11, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: '#A09A8E', display: 'flex', alignItems: 'center', gap: 6 }}>
                <Calendar size={12} /> Joined {member.joinDate}
              </span>
            </div>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, ...mono, fontSize: 9, letterSpacing: '0.08em', textTransform: 'uppercase' as const, padding: '4px 10px', borderRadius: 3, ...roleStyle }}>
              {member.role}
            </span>
          </div>
          <button style={{ padding: '11px 24px', background: 'transparent', border: '1px solid #1C1C1A', borderRadius: 4, ...mono, fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase' as const, cursor: 'pointer', marginBottom: 6 }}>
            Message
          </button>
        </div>

        {/* Bio */}
        <p style={{ fontSize: 15, fontWeight: 300, lineHeight: 1.75, color: '#5A5855', maxWidth: 560, margin: '26px 16px 0' }}>{member.bio}</p>

        {/* Body */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 270px', gap: 44, margin: '34px 16px 0' }}>
          <div>
            {/* Work gallery placeholder */}
            <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 18, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>Work & Projects</p>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14, marginBottom: 38 }}>
              {INTEREST_COLORS.slice(0, 4).map((bg, i) => (
                <div key={i} style={{ borderRadius: 8, overflow: 'hidden', position: 'relative' as const, height: 180, cursor: 'pointer', background: bg }}>
                  <div style={{ position: 'absolute' as const, inset: 0, background: 'linear-gradient(180deg, rgba(20,18,15,0.05) 40%, rgba(20,18,15,0.8) 100%)' }} />
                  <div style={{ position: 'absolute' as const, left: 14, right: 14, bottom: 12 }}>
                    <p style={{ fontSize: 13, fontWeight: 400, color: '#F8F4EC', marginBottom: 3, lineHeight: 1.3 }}>Project {i + 1}</p>
                    <p style={{ ...mono, fontSize: 9, letterSpacing: '0.04em', textTransform: 'uppercase' as const, color: 'rgba(248,244,236,0.8)' }}>Research</p>
                  </div>
                </div>
              ))}
            </div>

            {/* Activity */}
            <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 18, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>Recent activity</p>
            {(memberActivity.length > 0 ? memberActivity : activities.slice(0, 5)).map(activity => (
              <ActivityItem key={activity.id} activity={activity} />
            ))}
          </div>

          {/* Sidebar */}
          <div>
            {/* Groups tab toggle */}
            <div style={{ marginBottom: 32 }}>
              <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 16, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>In common with you</p>
              {(memberGroups.length > 0 ? memberGroups.slice(0, 2) : groups.slice(0, 2)).map(group => (
                <Link key={group.id} href={`/groups/${group.id}`} style={{ textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 11, marginBottom: 13 }}>
                  <div style={{ width: 38, height: 38, borderRadius: 6, background: group.gradient, flexShrink: 0 }} />
                  <div>
                    <p style={{ fontSize: 13, fontWeight: 400, color: '#1C1C1A', lineHeight: 1.2 }}>{group.name}</p>
                    <p style={{ ...mono, fontSize: 9, color: '#A09A8E' }}>{group.memberCount} members</p>
                  </div>
                </Link>
              ))}
            </div>

            <div style={{ marginBottom: 32 }}>
              <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 16, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>Interests</p>
              <div style={{ display: 'flex', flexWrap: 'wrap' as const, gap: 7 }}>
                {interests.map(interest => (
                  <span key={interest} style={{ ...mono, fontSize: 9, letterSpacing: '0.03em', color: '#7A7870', border: '1px solid #E6E0D6', borderRadius: 3, padding: '4px 9px' }}>
                    {interest}
                  </span>
                ))}
              </div>
            </div>

            <div>
              <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 16, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>On the platform</p>
              <div style={{ display: 'flex', gap: 28 }}>
                <div>
                  <div style={{ fontSize: 26, fontWeight: 300, letterSpacing: '-0.02em', color: '#1C1C1A' }}>{member.groups.length}</div>
                  <div style={{ ...mono, fontSize: 9, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: '#A09A8E', marginTop: 2 }}>Circles</div>
                </div>
                <div>
                  <div style={{ fontSize: 26, fontWeight: 300, letterSpacing: '-0.02em', color: '#1C1C1A' }}>{memberActivity.length || 12}</div>
                  <div style={{ ...mono, fontSize: 9, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: '#A09A8E', marginTop: 2 }}>Activities</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
