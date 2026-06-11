'use client';
import { motion } from 'framer-motion';
import Link from 'next/link';
import TopNav from '@/components/TopNav';
import Avatar from '@/components/ui/Avatar';
import Badge from '@/components/ui/Badge';
import { members } from '@/lib/mockData';

export default function MembersPage() {
  return (
    <div className="min-h-screen" style={{ background: '#0D0D0D' }}>
      <TopNav />
      <div className="pt-14">
        <div className="px-6 py-12 border-b border-[#1E1E1E]" style={{ background: '#111111' }}>
          <div className="max-w-6xl mx-auto">
            <p className="text-xs text-[#E8A838] uppercase tracking-widest mb-2 font-medium">Members</p>
            <h1 className="font-display text-5xl text-[#F0EDE8]">The people making it happen.</h1>
            <p className="text-[#8A8580] text-lg mt-3">{members.length} members · 24 countries</p>
          </div>
        </div>
        <div className="max-w-6xl mx-auto px-6 py-8">
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {members.map((member, i) => (
              <motion.div
                key={member.id}
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.05 }}
                whileHover={{ y: -4 }}
              >
                <Link href={`/members/${member.id}`}>
                  <div className="p-5 rounded-xl border border-[#2A2A2A] bg-[#161616] hover:border-[#E8A838]/40 transition-all cursor-pointer text-center">
                    <div className="flex justify-center mb-3">
                      <Avatar initials={member.avatar} color={member.color} size={52} online={i < 4} />
                    </div>
                    <p className="font-medium text-[#F0EDE8]">{member.name}</p>
                    <p className="text-xs text-[#4A4845] mt-0.5">{member.flag} {member.location}</p>
                    <div className="mt-2 flex justify-center">
                      <Badge
                        label={member.role}
                        color={member.role === 'Organizer' ? '#E8A838' : member.role === 'Teacher' ? '#7B68EE' : '#8A8580'}
                      />
                    </div>
                    <p className="text-xs text-[#4A4845] mt-2">{member.groups} group{member.groups !== 1 ? 's' : ''}</p>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
