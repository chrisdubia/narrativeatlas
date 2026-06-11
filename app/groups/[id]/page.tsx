'use client'

import { useState } from 'react'
import { motion } from 'framer-motion'
import { Users, MapPin, MessageSquare, Calendar, Map, FileText, ChevronLeft } from 'lucide-react'
import Link from 'next/link'
import { useParams } from 'next/navigation'
import CompassMark from '@/components/CompassMark'
import TabBar from '@/components/ui/TabBar'
import Avatar from '@/components/ui/Avatar'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/Button'
import MemberCard from '@/components/MemberCard'
import { groups, members, discussions, mapRoutes, activities } from '@/lib/mockData'

const tabs = [
  { id: 'discussions', label: 'Discussions', icon: <MessageSquare className="w-4 h-4" /> },
  { id: 'feed', label: 'Feed', icon: <FileText className="w-4 h-4" /> },
  { id: 'maps', label: 'Maps', icon: <Map className="w-4 h-4" /> },
  { id: 'members', label: 'Members', icon: <Users className="w-4 h-4" /> },
  { id: 'calendar', label: 'Calendar', icon: <Calendar className="w-4 h-4" /> },
]

export default function GroupDetailPage() {
  const params = useParams()
  const id = params.id as string
  const [activeTab, setActiveTab] = useState('discussions')

  const group = groups.find(g => g.id === id) || groups[0]
  const groupDiscussions = discussions.filter(d => d.groupId === group.id)
  const groupRoutes = mapRoutes.filter(r => r.groupId === group.id)
  const groupActivities = activities.filter(a => a.groupId === group.id)

  return (
    <div className="min-h-screen bg-background">
      <nav className="sticky top-0 z-50 bg-background/80 backdrop-blur-md border-b border-border">
        <div className="max-w-7xl mx-auto px-4 h-14 flex items-center gap-4">
          <Link href="/groups" className="flex items-center gap-1.5 text-text-secondary hover:text-text-primary">
            <ChevronLeft className="w-4 h-4" />
            <span className="text-sm">Communities</span>
          </Link>
          <div className="ml-auto">
            <Link href="/" className="flex items-center gap-2">
              <CompassMark size={24} />
            </Link>
          </div>
        </div>
      </nav>

      <div
        className="relative h-48 sm:h-64"
        style={{ background: group.gradient }}
      >
        <div className="absolute inset-0 bg-gradient-to-t from-background/80 to-transparent" />
        <div className="absolute bottom-0 left-0 right-0 p-6 max-w-7xl mx-auto">
          <div className="flex items-start justify-between gap-4">
            <div>
              <h1 className="font-serif text-3xl sm:text-4xl text-text-primary mb-2">{group.name}</h1>
              <div className="flex items-center gap-1 text-text-secondary text-sm">
                <MapPin className="w-3 h-3" />
                {group.location}
              </div>
            </div>
            <Button variant="primary" size="md">Join Group</Button>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4">
        <div className="py-4 flex flex-wrap items-center gap-4 border-b border-border">
          <div className="flex items-center gap-1.5 text-sm text-text-secondary">
            <Users className="w-4 h-4" />
            {group.memberCount} members
          </div>
          <div className="flex gap-1.5">
            {group.tags.map(tag => <Badge key={tag}>{tag}</Badge>)}
          </div>
          <div className="ml-auto text-xs text-text-secondary">Active {group.lastActive}</div>
        </div>

        <p className="py-4 text-text-secondary">{group.description}</p>

        <TabBar tabs={tabs} activeTab={activeTab} onChange={setActiveTab} />

        <div className="py-6">
          {activeTab === 'discussions' && (
            <div className="space-y-4">
              <div className="bg-surface border border-border rounded-xl p-4">
                <textarea
                  placeholder="Start a new discussion…"
                  className="w-full bg-transparent text-text-primary placeholder-text-secondary text-sm resize-none focus:outline-none"
                  rows={3}
                />
                <div className="flex justify-end mt-2">
                  <Button variant="primary" size="sm">Post</Button>
                </div>
              </div>
              {groupDiscussions.length > 0 ? groupDiscussions.map(d => (
                <motion.div
                  key={d.id}
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  className="bg-surface border border-border rounded-xl p-4 hover:border-accent/30 transition-colors cursor-pointer"
                >
                  <h3 className="font-medium text-text-primary mb-2">{d.title}</h3>
                  <div className="flex items-center gap-3 text-xs text-text-secondary">
                    <Avatar initials={d.authorAvatar} size="sm" />
                    <span>{d.authorName}</span>
                    <span>·</span>
                    <span>{d.replyCount} replies</span>
                    <span>·</span>
                    <span>{d.viewCount} views</span>
                    <span>·</span>
                    <span>{d.timestamp}</span>
                  </div>
                  <div className="flex gap-1.5 mt-2">
                    {d.tags.map(t => <Badge key={t}>{t}</Badge>)}
                  </div>
                </motion.div>
              )) : (
                <p className="text-text-secondary text-center py-8">No discussions yet. Start the conversation!</p>
              )}
            </div>
          )}

          {activeTab === 'feed' && (
            <div className="bg-surface border border-border rounded-xl divide-y divide-border">
              {groupActivities.length > 0 ? groupActivities.map(item => (
                <div key={item.id} className="px-4">
                  <div className="py-3 flex gap-3">
                    <Avatar initials={item.memberAvatar} size="sm" />
                    <div>
                      <p className="text-sm text-text-primary">
                        <span className="font-medium">{item.memberName}</span>{' '}
                        <span className="text-text-secondary">{item.action}</span>
                        {item.target && <> &ldquo;{item.target}&rdquo;</>}
                      </p>
                      <p className="text-xs text-text-secondary mt-0.5">{item.timestamp}</p>
                    </div>
                  </div>
                </div>
              )) : (
                <p className="text-text-secondary text-center py-8 px-4">No activity yet in this group.</p>
              )}
            </div>
          )}

          {activeTab === 'maps' && (
            <div className="space-y-4">
              <div className="bg-surface border border-border rounded-xl h-64 flex items-center justify-center">
                <div className="text-center">
                  <CompassMark size={48} className="mx-auto mb-3 opacity-50" />
                  <p className="text-text-secondary text-sm">Interactive map coming soon</p>
                </div>
              </div>
              {groupRoutes.map(route => (
                <div key={route.id} className="bg-surface border border-border rounded-xl p-4">
                  <h3 className="font-medium text-text-primary mb-1">{route.title}</h3>
                  <p className="text-sm text-text-secondary mb-2">{route.from} → {route.to}</p>
                  <p className="text-sm text-text-secondary">{route.description}</p>
                  <div className="flex items-center gap-2 mt-3 text-xs text-text-secondary">
                    <span>By {route.createdBy}</span>
                    <span>·</span>
                    <span>{route.timestamp}</span>
                  </div>
                </div>
              ))}
            </div>
          )}

          {activeTab === 'members' && (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {members.filter(m => m.groups.includes(group.id)).map(member => (
                <MemberCard key={member.id} member={member} />
              ))}
            </div>
          )}

          {activeTab === 'calendar' && (
            <div className="text-center py-16">
              <Calendar className="w-12 h-12 text-text-secondary mx-auto mb-3" />
              <p className="text-text-secondary">Calendar view coming soon</p>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
