'use client';
import { motion } from 'framer-motion';
import { useParams } from 'next/navigation';
import TopNav from '@/components/TopNav';
import Avatar from '@/components/ui/Avatar';
import Badge from '@/components/ui/Badge';
import ActivityItem from '@/components/ActivityItem';
import { members, activities, groups } from '@/lib/mockData';
import { MapPin, Calendar, Users, MessageSquare, Map } from 'lucide-react';
import Button from '@/components/ui/Button';

export default function MemberPage() {
  const { id } = useParams();
  const member = members.find(m => m.id === id) || members[0];
  const memberActivity = activities.filter(a => a.member.id === member.id).slice(0, 5);

  return (
    <div className="min-h-screen" style={{ background: '#0D0D0D' }}>
      <TopNav />
      <div className="pt-14">
        {/* Header */}
        <div className="px-6 py-12 border-b border-[#1E1E1E]" style={{ background: '#111111' }}>
          <div className="max-w-4xl mx-auto">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="flex flex-col sm:flex-row items-start sm:items-center gap-6"
            >
              <Avatar initials={member.avatar} color={member.color} size={72} online />
              <div className="flex-1">
                <h1 className="font-display text-4xl text-[#F0EDE8]">{member.name}</h1>
                <div className="flex flex-wrap items-center gap-3 mt-2">
                  <Badge
                    label={member.role}
                    color={member.role === 'Organizer' ? '#E8A838' : member.role === 'Teacher' ? '#7B68EE' : '#8A8580'}
                  />
                  <span className="flex items-center gap-1.5 text-sm text-[#8A8580]">
                    <MapPin size={13} /> {member.flag} {member.location}
                  </span>
                  <span className="flex items-center gap-1.5 text-sm text-[#8A8580]">
                    <Calendar size={13} /> Joined {member.joined}
                  </span>
                </div>
                <div className="flex gap-6 mt-4">
                  {[
                    { icon: <Users size={14} />, value: member.groups, label: 'groups' },
                    { icon: <MessageSquare size={14} />, value: 12, label: 'discussions' },
                    { icon: <Map size={14} />, value: 4, label: 'routes' },
                  ].map(stat => (
                    <div key={stat.label} className="flex items-center gap-1.5 text-sm">
                      <span style={{ color: '#E8A838' }}>{stat.icon}</span>
                      <span className="font-semibold text-[#F0EDE8]">{stat.value}</span>
                      <span className="text-[#4A4845]">{stat.label}</span>
                    </div>
                  ))}
                </div>
              </div>
              <Button variant="ghost" size="sm">Message</Button>
            </motion.div>
          </div>
        </div>

        <div className="max-w-4xl mx-auto px-6 py-8">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2">
              <h2 className="font-display text-xl text-[#F0EDE8] mb-4">Recent Activity</h2>
              {memberActivity.length > 0 ? (
                <div className="rounded-xl border border-[#2A2A2A] bg-[#161616] overflow-hidden">
                  {memberActivity.map(a => (
                    <div key={a.id} className="px-4">
                      <ActivityItem activity={a} />
                    </div>
                  ))}
                </div>
              ) : (
                <div className="rounded-xl border border-[#2A2A2A] bg-[#161616] p-8 text-center text-[#4A4845] text-sm">
                  No recent activity
                </div>
              )}
            </div>

            <div>
              <h2 className="font-display text-xl text-[#F0EDE8] mb-4">Groups</h2>
              <div className="space-y-3">
                {groups.slice(0, member.groups).map((group, i) => (
                  <motion.div
                    key={group.id}
                    initial={{ opacity: 0, x: 16 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.08 }}
                    className="p-3 rounded-xl border border-[#2A2A2A] bg-[#161616] hover:border-[#E8A838]/30 transition-colors cursor-pointer flex items-center gap-3"
                  >
                    <div className="text-2xl">{group.icon}</div>
                    <div className="min-w-0">
                      <p className="text-sm font-medium text-[#F0EDE8] leading-snug">{group.name}</p>
                      <Badge label={group.category} color={group.categoryColor} />
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
