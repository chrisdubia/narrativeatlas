'use client'

import { useState } from 'react'
import { useParams } from 'next/navigation'
import { ArrowLeft, MessageSquare, FileText, Map, Users, Calendar, Image, Video, Upload } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import Avatar from '@/components/ui/Avatar'
import { groups, members, discussions, mapRoutes, activities } from '@/lib/mockData'

const mono: React.CSSProperties = {
  fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
}

const tabs = [
  { id: 'discussions', label: 'Discussions', icon: <MessageSquare size={13} /> },
  { id: 'feed', label: 'Feed', icon: <FileText size={13} /> },
  { id: 'maps', label: 'Maps', icon: <Map size={13} /> },
  { id: 'members', label: 'Members', icon: <Users size={13} /> },
  { id: 'calendar', label: 'Calendar', icon: <Calendar size={13} /> },
]

const AVATAR_COLORS = ['#6B8F4E', '#4A7A9B', '#C2683D', '#8B6F47', '#5C7A9B']

export default function GroupDetailPage() {
  const params = useParams()
  const id = params.id as string
  const [activeTab, setActiveTab] = useState('discussions')

  const group = groups.find(g => g.id === id) || groups[0]
  const groupDiscussions = discussions.filter(d => d.groupId === group.id)
  const groupRoutes = mapRoutes.filter(r => r.groupId === group.id)
  const groupActivities = activities.filter(a => a.groupId === group.id)
  const groupMembers = members.filter(m => m.groups.includes(group.id))

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
        <Avatar initials={members[0].avatar} size="sm" />
      </nav>

      <div style={{ maxWidth: 1040, margin: '0 auto', padding: '24px 40px 70px' }}>
        <div style={{ ...mono, fontSize: 10, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: '#A09A8E', display: 'flex', alignItems: 'center', gap: 8, marginBottom: 18 }}>
          <Link href="/groups" style={{ color: '#A09A8E', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
            <ArrowLeft size={12} /> Communities
          </Link>
          <span>·</span>
          <span>Circle</span>
        </div>

        {/* Cover */}
        <div style={{ position: 'relative' as const, borderRadius: 8, overflow: 'hidden', height: 240, marginBottom: 24, background: '#1C1C1A' }}>
          <div style={{ position: 'absolute' as const, inset: 0, background: group.gradient, opacity: 0.9 }} />
          <div style={{ position: 'absolute' as const, inset: 0, background: 'linear-gradient(180deg, rgba(20,18,15,0.1) 0%, rgba(20,18,15,0.05) 40%, rgba(20,18,15,0.85) 100%)' }} />
          <div style={{ position: 'absolute' as const, left: 0, right: 0, bottom: 0, padding: '28px 32px', display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between' }}>
            <div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 9, marginBottom: 12 }}>
                <span style={{ width: 26, height: 4, background: '#7DC95E', borderRadius: 1, display: 'inline-block' }} />
                <span style={{ ...mono, fontSize: 10, letterSpacing: '0.06em', textTransform: 'uppercase' as const, color: 'rgba(248,244,236,0.9)' }}>{group.location}</span>
              </div>
              <h1 style={{ fontSize: 38, fontWeight: 300, letterSpacing: '-0.025em', color: '#F8F4EC', lineHeight: 1.05 }}>{group.name}</h1>
            </div>
            <button style={{ padding: '12px 26px', background: '#F6F1E9', color: '#1C1C1A', border: 'none', borderRadius: 4, ...mono, fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase' as const, cursor: 'pointer', whiteSpace: 'nowrap' as const }}>
              Join the circle
            </button>
          </div>
        </div>

        {/* Subrow */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', paddingBottom: 22, borderBottom: '1px solid #E6E0D6', marginBottom: 4 }}>
          <p style={{ fontSize: 14, fontWeight: 300, lineHeight: 1.65, color: '#5A5855', maxWidth: 520 }}>{group.description}</p>
          <div style={{ display: 'flex', alignItems: 'center' }}>
            {AVATAR_COLORS.slice(0, Math.min(4, groupMembers.length || 4)).map((bg, i) => (
              <div key={i} style={{ width: 30, height: 30, borderRadius: '50%', border: '2px solid #F6F1E9', marginLeft: i === 0 ? 0 : -9, background: bg, display: 'flex', alignItems: 'center', justifyContent: 'center', ...mono, fontSize: 9, color: '#fff' }}>
                {(groupMembers[i]?.avatar ?? 'MC').slice(0, 2)}
              </div>
            ))}
            <span style={{ ...mono, fontSize: 11, color: '#8A8880', marginLeft: 10 }}>{group.memberCount} members</span>
          </div>
        </div>

        {/* Tabs */}
        <div style={{ display: 'flex', gap: 28, borderBottom: '1px solid #E6E0D6', marginTop: 24 }}>
          {tabs.map(tab => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              style={{
                ...mono,
                fontSize: 11,
                letterSpacing: '0.06em',
                textTransform: 'uppercase' as const,
                color: activeTab === tab.id ? '#1C1C1A' : '#8A8880',
                padding: '0 0 15px',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                gap: 7,
                background: 'none',
                border: 'none',
                borderBottom: `2px solid ${activeTab === tab.id ? '#C2683D' : 'transparent'}`,
                marginBottom: -1,
              }}
            >
              {tab.icon}
              {tab.label}
            </button>
          ))}
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 270px', gap: 40, marginTop: 30 }}>
          <div>
            {activeTab === 'discussions' && (
              <>
                <div style={{ background: '#FBF7F0', border: '1px solid #E6E0D6', borderRadius: 8, padding: '16px 18px', marginBottom: 26, display: 'flex', alignItems: 'center', gap: 14 }}>
                  <Avatar initials={members[0].avatar} size={38} />
                  <span style={{ fontSize: 14, fontWeight: 300, color: '#A09A8E', flex: 1 }}>Share something with the circle…</span>
                  <div style={{ display: 'flex', gap: 12, color: '#C2683D' }}>
                    <Image size={17} /><Video size={17} /><Upload size={17} />
                  </div>
                </div>
                {groupDiscussions.length > 0 ? groupDiscussions.map(d => (
                  <div key={d.id} style={{ paddingBottom: 24, borderBottom: '1px solid #EAE3D8', marginBottom: 24 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 11, marginBottom: 12 }}>
                      <Avatar initials={d.authorAvatar} size={38} />
                      <div>
                        <p style={{ fontSize: 14, fontWeight: 400 }}>{d.authorName}</p>
                        <p style={{ ...mono, fontSize: 9, color: '#A09A8E', marginTop: 2 }}>{d.timestamp}</p>
                      </div>
                    </div>
                    <h3 style={{ fontSize: 15, fontWeight: 400, marginBottom: 8 }}>{d.title}</h3>
                    <div style={{ display: 'flex', gap: 22, ...mono, fontSize: 9, letterSpacing: '0.04em', textTransform: 'uppercase' as const, color: '#A09A8E' }}>
                      <span>{d.replyCount} replies</span><span>{d.viewCount} views</span>
                    </div>
                  </div>
                )) : (
                  <p style={{ fontSize: 14, fontWeight: 300, color: '#A09A8E', textAlign: 'center' as const, padding: '48px 0' }}>No discussions yet. Start the conversation!</p>
                )}
              </>
            )}

            {activeTab === 'feed' && (
              <div>
                {(groupActivities.length > 0 ? groupActivities : activities.slice(0, 5)).map(item => (
                  <div key={item.id} style={{ paddingBottom: 24, borderBottom: '1px solid #EAE3D8', marginBottom: 24 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 11, marginBottom: 12 }}>
                      <Avatar initials={item.memberAvatar} size={38} />
                      <div>
                        <p style={{ fontSize: 14, fontWeight: 400 }}>{item.memberName}</p>
                        <p style={{ ...mono, fontSize: 9, color: '#A09A8E', marginTop: 2 }}>{item.timestamp}</p>
                      </div>
                    </div>
                    <p style={{ fontSize: 14, fontWeight: 300, lineHeight: 1.7, color: '#3A3833' }}>
                      {item.action}{item.target && ` "${item.target}"`}
                    </p>
                  </div>
                ))}
              </div>
            )}

            {activeTab === 'maps' && (
              <div>
                <div style={{ background: '#FBF7F0', border: '1px solid #E6E0D6', borderRadius: 8, height: 200, display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 20 }}>
                  <p style={{ ...mono, fontSize: 10, color: '#A09A8E' }}>Interactive map coming soon</p>
                </div>
                {groupRoutes.map(route => (
                  <div key={route.id} style={{ paddingBottom: 20, borderBottom: '1px solid #EAE3D8', marginBottom: 20 }}>
                    <h3 style={{ fontSize: 15, fontWeight: 400, marginBottom: 4 }}>{route.title}</h3>
                    <p style={{ fontSize: 13, fontWeight: 300, color: '#5A5855', marginBottom: 4 }}>{route.from} → {route.to}</p>
                    <p style={{ fontSize: 13, fontWeight: 300, color: '#5A5855' }}>{route.description}</p>
                    <p style={{ ...mono, fontSize: 9, color: '#A09A8E', marginTop: 8 }}>By {route.createdBy} · {route.timestamp}</p>
                  </div>
                ))}
              </div>
            )}

            {activeTab === 'members' && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
                {(groupMembers.length > 0 ? groupMembers : members.slice(0, 4)).map(member => (
                  <Link key={member.id} href={`/members/${member.id}`} style={{ textDecoration: 'none' }}>
                    <div style={{ background: '#FBF7F0', border: '1px solid #E6E0D6', borderRadius: 8, padding: 16, display: 'flex', gap: 12 }}>
                      <Avatar initials={member.avatar} size={40} />
                      <div>
                        <p style={{ fontSize: 14, fontWeight: 400, color: '#1C1C1A' }}>{member.name}</p>
                        <p style={{ ...mono, fontSize: 9, color: '#A09A8E', letterSpacing: '0.04em', textTransform: 'uppercase' as const, marginTop: 3 }}>{member.location}</p>
                        <p style={{ ...mono, fontSize: 9, color: '#C2683D', marginTop: 4, letterSpacing: '0.04em', textTransform: 'uppercase' as const }}>{member.role}</p>
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            )}

            {activeTab === 'calendar' && (
              <div style={{ textAlign: 'center' as const, padding: '64px 0' }}>
                <p style={{ ...mono, fontSize: 10, color: '#A09A8E', letterSpacing: '0.06em' }}>Calendar view coming soon</p>
              </div>
            )}
          </div>

          {/* Sidebar */}
          <div>
            <div style={{ marginBottom: 30 }}>
              <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 16, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>Members online</p>
              {members.slice(0, 4).map(member => (
                <div key={member.id} style={{ display: 'flex', alignItems: 'center', gap: 11, marginBottom: 13 }}>
                  <Avatar initials={member.avatar} size={32} online />
                  <div>
                    <p style={{ fontSize: 13, fontWeight: 400 }}>{member.name}</p>
                    <p style={{ ...mono, fontSize: 9, color: '#A09A8E' }}>{member.location}</p>
                  </div>
                </div>
              ))}
            </div>
            <div>
              <p style={{ ...mono, fontSize: 9, letterSpacing: '0.14em', textTransform: 'uppercase' as const, color: '#A09A8E', marginBottom: 16, paddingBottom: 10, borderBottom: '1px solid #E6E0D6' }}>Upcoming</p>
              {[
                { title: 'Climate Action Workshop', date: 'Jun 14 · 15:00 UTC' },
                { title: 'Data Storytelling Lab', date: 'Jun 20 · 18:00 UTC' },
              ].map(event => (
                <div key={event.title} style={{ paddingBottom: 13, borderBottom: '1px solid #EAE3D8', marginBottom: 13 }}>
                  <p style={{ fontSize: 13, fontWeight: 400, marginBottom: 4 }}>{event.title}</p>
                  <p style={{ ...mono, fontSize: 9, color: '#A09A8E', letterSpacing: '0.04em' }}>{event.date}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
