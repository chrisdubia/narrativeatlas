'use client';
import { motion } from 'framer-motion';
import { Users, Clock } from 'lucide-react';
import Badge from './ui/Badge';
import Link from 'next/link';

interface Group {
  id: string; name: string; description: string; category: string; categoryColor: string;
  memberCount: number; activeToday: boolean; cover: string; icon: string;
  location: string; recentActivity: string;
}

export default function GroupCard({ group, index = 0 }: { group: Group; index?: number }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.07, duration: 0.4 }}
      whileHover={{ y: -4 }}
    >
      <Link href={`/groups/${group.id}`}>
        <div className="rounded-xl overflow-hidden border border-[#2A2A2A] bg-[#161616] hover:border-[#E8A838]/40 transition-all duration-300 cursor-pointer group">
          {/* Cover */}
          <div className="h-40 relative overflow-hidden" style={{ background: group.cover }}>
            <div className="absolute inset-0 bg-gradient-to-t from-[#161616] via-transparent to-transparent" />
            <div className="absolute top-3 left-3">
              <Badge label={group.category} color={group.categoryColor} />
            </div>
            {group.activeToday && (
              <div className="absolute top-3 right-3 flex items-center gap-1.5 bg-[#0D0D0D]/70 backdrop-blur-sm px-2 py-1 rounded-full">
                <span className="w-1.5 h-1.5 rounded-full bg-[#4CAF7D] animate-pulse" />
                <span className="text-xs text-[#4CAF7D] font-medium">Active</span>
              </div>
            )}
            <div className="absolute bottom-3 left-4 text-3xl">{group.icon}</div>
          </div>

          {/* Content */}
          <div className="p-4">
            <h3 className="font-display text-[#F0EDE8] text-lg leading-tight mb-1.5 group-hover:text-[#E8A838] transition-colors">
              {group.name}
            </h3>
            <p className="text-sm text-[#8A8580] leading-relaxed line-clamp-2 mb-3">{group.description}</p>

            <div className="flex items-center justify-between text-xs text-[#4A4845]">
              <div className="flex items-center gap-1.5">
                <Users size={12} />
                <span>{group.memberCount} members</span>
              </div>
              <div className="flex items-center gap-1.5">
                <Clock size={12} />
                <span>{group.recentActivity}</span>
              </div>
            </div>

            <div className="mt-2 text-xs text-[#4A4845]">📍 {group.location}</div>
          </div>
        </div>
      </Link>
    </motion.div>
  );
}
