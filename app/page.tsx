'use client';

import { motion } from 'framer-motion';
import { Bell, MessageSquare, Search, ChevronRight, Video, BookOpen, AlertCircle, Users } from 'lucide-react';
import Link from 'next/link';
import CompassMark from '@/components/CompassMark';
import GroupCard from '@/components/GroupCard';
import ActivityItem from '@/components/ActivityItem';
import Avatar from '@/components/ui/Avatar';
import { groups, activities, events, members } from '@/lib/mockData';

const currentUser = members[0];

function EventIcon({ type }: { type: string }) {
  if (type === 'video-call') return <Video size={14} style={{ color: '#38BDF8' }} />;
  if (type === 'workshop') return <BookOpen size={14} style={{ color: '#A78BFA' }} />;
  if (type === 'deadline') return <AlertCircle size={14} style={{ color: '#E85555' }} />;
  return <Users size={14} style={{ color: '#4CAF7D' }} />;
}

export default function Dashboard() {
  const myGroups = groups.filter((g) => g.featured);
  const discoverGroups = groups.filter((g) => !g.featured);

  return (
    <div className="min-h-screen" style={{ backgroundColor: '#0D0D0D' }}>
      {/* Nav */}
      <nav className="sticky top-0 z-50 backdrop-blur-md" style={{ backgroundColor: 'rgba(13,13,13,0.8)', borderBottom: '1px solid #2A2A2A' }}>
        <div className="max-w-7xl mx-auto px-4 h-14 flex items-center gap-4">
          <Link href="/" className="flex items-center gap-2 shrink-0">
            <CompassMark size={28} />
            <span className="hidden sm:block text-lg" style={{ fontFamily: 'var(--font-instrument-serif), Georgia, serif', color: '#F0EDE8' }}>Narrative Atlas</span>
          </Link>

          {/* Search */}
          <div className="flex-1 max-w-md mx-auto">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2" size={15} style={{ color: '#8A8580' }} />
              <input
                type="search"
                placeholder="Search groups, members, documents..."
                className="w-full rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none transition-colors"
                style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A', color: '#F0EDE8' }}
              />
            </div>
          </div>

          {/* Right icons */}
          <div className="flex items-center gap-2 shrink-0">
            <button className="relative p-2 rounded-lg transition-colors" style={{ color: '#8A8580' }}>
              <Bell size={18} />
              <span className="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full" style={{ backgroundColor: '#E8A838' }} />
            </button>
            <button className="p-2 rounded-lg transition-colors" style={{ color: '#8A8580' }}>
              <MessageSquare size={18} />
            </button>
            <Avatar initials={currentUser.avatar} size="sm" online />
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main content */}
          <div className="lg:col-span-2 space-y-8">
            {/* Hero */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="relative rounded-2xl overflow-hidden"
              style={{
                background: 'linear-gradient(135deg, #1a2a1a 0%, #1a1a2a 50%, #2a1a1a 100%)',
                border: '1px solid #2A2A2A',
              }}
            >
              <div
                className="absolute inset-0 opacity-30"
                style={{
                  backgroundImage: 'radial-gradient(circle at 30% 50%, rgba(232,168,56,0.12) 0%, transparent 60%), radial-gradient(circle at 70% 50%, rgba(76,175,125,0.06) 0%, transparent 60%)',
                }}
              />
              <div className="relative z-10 p-8">
                <p className="text-sm mb-1" style={{ color: '#8A8580' }}>Good morning,</p>
                <h1 style={{ fontFamily: 'var(--font-instrument-serif), Georgia, serif', fontSize: '2.5rem', color: '#F0EDE8' }}>{currentUser.name.split(' ')[0]}.</h1>
                <p className="mt-2 text-sm" style={{ color: '#8A8580' }}>
                  You have{' '}
                  <span className="font-medium" style={{ color: '#E8A838' }}>3 new messages</span> and{' '}
                  <span className="font-medium" style={{ color: '#E8A838' }}>1 upcoming event</span> today.
                </p>
              </div>
            </motion.div>

            {/* My Groups */}
            <section>
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-lg font-medium" style={{ color: '#F0EDE8' }}>My Groups</h2>
                <Link href="/groups" className="text-sm flex items-center gap-1 transition-colors" style={{ color: '#E8A838' }}>
                  View all <ChevronRight size={14} />
                </Link>
              </div>
              <div className="flex gap-4 overflow-x-auto pb-2 no-scrollbar">
                {myGroups.map((group, i) => (
                  <motion.div
                    key={group.id}
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.1 }}
                    className="shrink-0 w-64"
                  >
                    <GroupCard group={group} compact />
                  </motion.div>
                ))}
              </div>
            </section>

            {/* Activity Feed */}
            <section>
              <h2 className="text-lg font-medium mb-4" style={{ color: '#F0EDE8' }}>Global Activity</h2>
              <div className="rounded-xl" style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}>
                {activities.slice(0, 10).map((activity) => (
                  <div key={activity.id} className="px-4">
                    <ActivityItem activity={activity} />
                  </div>
                ))}
                <div className="px-4 py-3">
                  <button className="text-sm flex items-center gap-1 transition-colors" style={{ color: '#E8A838' }}>
                    Load more activity <ChevronRight size={14} />
                  </button>
                </div>
              </div>
            </section>

            {/* Discover */}
            <section>
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-lg font-medium" style={{ color: '#F0EDE8' }}>Discover Groups</h2>
                <Link href="/groups" className="text-sm flex items-center gap-1 transition-colors" style={{ color: '#E8A838' }}>
                  Browse all <ChevronRight size={14} />
                </Link>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {discoverGroups.map((group, i) => (
                  <motion.div
                    key={group.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: i * 0.1 }}
                  >
                    <GroupCard group={group} />
                  </motion.div>
                ))}
              </div>
            </section>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Upcoming Events */}
            <div className="rounded-xl p-5" style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}>
              <h3 className="font-medium mb-4" style={{ color: '#F0EDE8' }}>Upcoming Events</h3>
              <div className="space-y-3">
                {events.map((event) => (
                  <motion.div
                    key={event.id}
                    whileHover={{ x: 2 }}
                    className="flex gap-3 p-3 rounded-lg transition-colors cursor-pointer"
                  >
                    <div className="mt-0.5">
                      <EventIcon type={event.type} />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm leading-snug line-clamp-2" style={{ color: '#F0EDE8' }}>{event.title}</p>
                      <p className="text-xs mt-1" style={{ color: '#8A8580' }}>{event.date}</p>
                      <p className="text-xs mt-0.5" style={{ color: '#E8A838' }}>{event.time}</p>
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>

            {/* Members online */}
            <div className="rounded-xl p-5" style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}>
              <h3 className="font-medium mb-4" style={{ color: '#F0EDE8' }}>Members Online</h3>
              <div className="space-y-3">
                {members.slice(0, 5).map((member) => (
                  <Link key={member.id} href={`/members/${member.id}`}>
                    <div className="flex items-center gap-3 py-1 transition-opacity hover:opacity-80">
                      <Avatar initials={member.avatar} size="sm" online />
                      <div className="min-w-0">
                        <p className="text-sm truncate" style={{ color: '#F0EDE8' }}>{member.name}</p>
                        <p className="text-xs truncate" style={{ color: '#8A8580' }}>{member.location}</p>
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
