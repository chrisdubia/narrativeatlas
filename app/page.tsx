'use client';
import { motion } from 'framer-motion';
import Link from 'next/link';
import TopNav from '@/components/TopNav';
import GroupCard from '@/components/GroupCard';
import ActivityItem from '@/components/ActivityItem';
import Avatar from '@/components/ui/Avatar';
import { groups, members, activities, events } from '@/lib/mockData';
import { Calendar, ChevronRight, Globe, Users, MessageSquare } from 'lucide-react';

const currentUser = members[0];

export default function Dashboard() {
  const myGroups = groups.slice(0, 3);
  const discoverGroups = groups.slice(3, 5);

  return (
    <div className="min-h-screen" style={{ background: '#0D0D0D' }}>
      <TopNav />

      {/* Hero */}
      <div className="pt-14">
        <div className="aurora-bg px-6 py-12 md:py-16 relative overflow-hidden">
          <div className="max-w-6xl mx-auto relative z-10">
            <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }}>
              <div className="flex items-center gap-3 mb-3">
                <Avatar initials={currentUser.avatar} color={currentUser.color} size={40} online />
                <div>
                  <p className="text-xs text-[#8A8580] uppercase tracking-wider">Good morning</p>
                  <h1 className="font-display text-2xl text-[#F0EDE8]">{currentUser.name}</h1>
                </div>
              </div>

              <div className="flex flex-wrap gap-4 mt-6">
                {[
                  { icon: <Users size={14} />, label: '3 active groups' },
                  { icon: <MessageSquare size={14} />, label: '2 unread messages' },
                  { icon: <Calendar size={14} />, label: '1 upcoming event' },
                  { icon: <Globe size={14} />, label: `${currentUser.flag} ${currentUser.location}` },
                ].map((stat, i) => (
                  <div key={i} className="flex items-center gap-2 text-sm text-[#8A8580]">
                    <span style={{ color: '#E8A838' }}>{stat.icon}</span>
                    {stat.label}
                  </div>
                ))}
              </div>
            </motion.div>
          </div>
        </div>
      </div>

      <div className="max-w-6xl mx-auto px-6 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main content */}
          <div className="lg:col-span-2 space-y-10">

            {/* My Groups */}
            <section>
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-display text-xl text-[#F0EDE8]">My Groups</h2>
                <Link href="/groups" className="flex items-center gap-1 text-sm text-[#E8A838] hover:text-[#F0B84A] transition-colors">
                  View all <ChevronRight size={14} />
                </Link>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {myGroups.map((group, i) => (
                  <GroupCard key={group.id} group={group} index={i} />
                ))}
              </div>
            </section>

            {/* Activity Feed */}
            <section>
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-display text-xl text-[#F0EDE8]">What&apos;s happening</h2>
              </div>
              <div className="rounded-xl border border-[#2A2A2A] bg-[#161616] overflow-hidden">
                {activities.map(activity => (
                  <div key={activity.id} className="px-4">
                    <ActivityItem activity={activity} />
                  </div>
                ))}
              </div>
            </section>

            {/* Discover */}
            <section>
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-display text-xl text-[#F0EDE8]">Discover communities</h2>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {discoverGroups.map((group, i) => (
                  <GroupCard key={group.id} group={group} index={i} />
                ))}
              </div>
            </section>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Upcoming Events */}
            <section>
              <h2 className="font-display text-xl text-[#F0EDE8] mb-4">Upcoming Events</h2>
              <div className="space-y-3">
                {events.map((event, i) => (
                  <motion.div
                    key={event.id}
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.1 }}
                    className="p-4 rounded-xl border border-[#2A2A2A] bg-[#161616] hover:border-[#E8A838]/40 transition-colors cursor-pointer"
                  >
                    <div className="flex items-start gap-3">
                      <div className="text-center shrink-0 w-10">
                        <div className="text-xs text-[#E8A838] font-medium uppercase">{event.date.split(' ')[0]}</div>
                        <div className="text-lg font-bold text-[#F0EDE8] leading-none">{event.date.split(' ')[1]}</div>
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-medium text-[#F0EDE8] leading-snug">{event.title}</p>
                        <p className="text-xs text-[#8A8580] mt-1">{event.time}</p>
                        <p className="text-xs text-[#4A4845] mt-1">{event.group.icon} {event.group.name}</p>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            </section>

            {/* Stats */}
            <section className="p-5 rounded-xl border border-[#2A2A2A] bg-[#161616]">
              <h3 className="text-sm font-medium text-[#8A8580] uppercase tracking-wider mb-4">Platform</h3>
              <div className="space-y-3">
                {[
                  { label: 'Countries represented', value: '24' },
                  { label: 'Active members', value: '281' },
                  { label: 'Discussions this month', value: '47' },
                  { label: 'Routes mapped', value: '138' },
                ].map(stat => (
                  <div key={stat.label} className="flex justify-between items-center">
                    <span className="text-sm text-[#8A8580]">{stat.label}</span>
                    <span className="text-sm font-semibold text-[#E8A838]">{stat.value}</span>
                  </div>
                ))}
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}
