'use client'

import { motion } from 'framer-motion'
import { useState } from 'react'
import { useParams } from 'next/navigation'
import { ArrowLeft, MapPin, Calendar } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import Avatar from '@/components/ui/Avatar'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/Button'
import TabBar from '@/components/ui/TabBar'
import ActivityItem from '@/components/ActivityItem'
import GroupCard from '@/components/GroupCard'
import { members, activities, groups } from '@/lib/mockData'

const tabs = [
  { id: 'activity', label: 'Activity' },
  { id: 'groups', label: 'Groups' },
]

export default function MemberProfilePage() {
  const params = useParams()
  const id = params.id as string
  const [activeTab, setActiveTab] = useState('activity')

  const member = members.find((m) => m.id === id) || members[0]
  const memberActivity = activities.filter((a) => a.memberId === member.id)
  const memberGroups = groups.filter((g) => member.groups.includes(g.id))

  const roleVariant: Record<string, 'warning' | 'info' | 'default'> = {
    Organizer: 'warning',
    Moderator: 'info',
    Member: 'default',
  }
  const variant = roleVariant[member.role] || 'default'

  return (
    <div className="min-h-screen" style={{ backgroundColor: '#0D0D0D' }}>
      {/* Nav */}
      <nav className="sticky top-0 z-50 backdrop-blur-md" style={{ backgroundColor: 'rgba(13,13,13,0.8)', borderBottom: '1px solid #2A2A2A' }}>
        <div className="max-w-4xl mx-auto px-4 h-14 flex items-center gap-4">
          <Link href="/" className="flex items-center gap-2 transition-colors" style={{ color: '#8A8580' }}>
            <ArrowLeft size={18} />
            <span className="text-sm">Back</span>
          </Link>
          <div className="flex-1" />
          <Link href="/"><CompassMark size={28} /></Link>
        </div>
      </nav>

      <main className="max-w-4xl mx-auto px-4 py-8">
        {/* Profile header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4 }}
          className="rounded-2xl p-6 md:p-8"
          style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}
        >
          <div className="flex flex-col sm:flex-row gap-6 items-start">
            <Avatar initials={member.avatar} size="xl" online />

            <div className="flex-1">
              <div className="flex items-start justify-between gap-4 flex-wrap">
                <div>
                  <div className="flex items-center gap-3 flex-wrap">
                    <h1 style={{ fontFamily: 'var(--font-instrument-serif), Georgia, serif', fontSize: '1.875rem', color: '#F0EDE8' }}>{member.name}</h1>
                    <span className="text-2xl">{member.countryFlag}</span>
                    <Badge variant={variant}>{member.role}</Badge>
                  </div>
                  <div className="flex items-center gap-4 mt-2 text-sm flex-wrap" style={{ color: '#8A8580' }}>
                    <span className="flex items-center gap-1"><MapPin size={14} />{member.location}</span>
                    <span className="flex items-center gap-1"><Calendar size={14} />Joined {member.joinDate}</span>
                  </div>
                </div>
                <Button variant="ghost" size="sm">Connect</Button>
              </div>

              <p className="mt-4 text-sm leading-relaxed" style={{ color: '#8A8580' }}>{member.bio}</p>

              {/* Stats */}
              <div className="mt-5 flex gap-6">
                <div className="text-center">
                  <div className="text-xl font-semibold" style={{ color: '#F0EDE8' }}>{member.groups.length}</div>
                  <div className="text-xs mt-0.5" style={{ color: '#8A8580' }}>Groups</div>
                </div>
                <div className="w-px" style={{ backgroundColor: '#2A2A2A' }} />
                <div className="text-center">
                  <div className="text-xl font-semibold" style={{ color: '#F0EDE8' }}>{memberActivity.length}</div>
                  <div className="text-xs mt-0.5" style={{ color: '#8A8580' }}>Activities</div>
                </div>
              </div>
            </div>
          </div>
        </motion.div>

        {/* Tabs */}
        <div className="mt-6">
          <TabBar tabs={tabs} activeTab={activeTab} onChange={setActiveTab} />
        </div>

        {/* Tab content */}
        <div className="mt-6">
          {activeTab === 'activity' && (
            <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}>
              <div className="rounded-xl" style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}>
                {(memberActivity.length > 0 ? memberActivity : activities.slice(0, 6)).map((activity) => (
                  <div key={activity.id} className="px-4">
                    <ActivityItem activity={activity} />
                  </div>
                ))}
              </div>
            </motion.div>
          )}

          {activeTab === 'groups' && (
            <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              {(memberGroups.length > 0 ? memberGroups : groups.slice(0, 2)).map((group) => (
                <GroupCard key={group.id} group={group} />
              ))}
            </motion.div>
          )}
        </div>
      </main>
    </div>
  )
}
