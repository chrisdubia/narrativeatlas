'use client'

import { motion } from 'framer-motion'
import { Users, Clock, ArrowRight } from 'lucide-react'
import Link from 'next/link'
import { Group } from '@/lib/mockData'
import Badge from '@/components/ui/Badge'

interface GroupCardProps {
  group: Group
  compact?: boolean
}

export default function GroupCard({ group, compact = false }: GroupCardProps) {
  return (
    <Link href={`/groups/${group.id}`}>
      <motion.div
        whileHover={{ y: -4, boxShadow: `0 8px 32px ${group.borderColor}20` }}
        transition={{ duration: 0.2 }}
        className={`relative rounded-xl overflow-hidden cursor-pointer group ${compact ? 'min-w-[260px]' : ''}`}
        style={{
          backgroundColor: '#161616',
          border: `1px solid #2A2A2A`,
          borderLeft: `3px solid ${group.borderColor}`,
        }}
      >
        {/* Cover gradient */}
        <div
          className="h-24 w-full"
          style={{ background: group.gradient }}
        />

        {/* Content */}
        <div className="p-4">
          <h3 className="text-base leading-tight mb-1 group-hover:text-[#E8A838] transition-colors" style={{ fontFamily: 'var(--font-instrument-serif), Georgia, serif', color: '#F0EDE8' }}>
            {group.name}
          </h3>
          {!compact && (
            <p className="text-sm mb-3 line-clamp-2" style={{ color: '#8A8580' }}>
              {group.description}
            </p>
          )}

          <div className="flex flex-wrap gap-1.5 mb-3">
            {group.tags.slice(0, compact ? 2 : 3).map(tag => (
              <Badge key={tag}>{tag}</Badge>
            ))}
          </div>

          <div className="flex items-center justify-between text-xs" style={{ color: '#8A8580' }}>
            <div className="flex items-center gap-1">
              <Users className="w-3 h-3" />
              <span>{group.memberCount} members</span>
            </div>
            <div className="flex items-center gap-1">
              <Clock className="w-3 h-3" />
              <span>{group.lastActive}</span>
            </div>
          </div>
        </div>

        <div className="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
          <ArrowRight className="w-4 h-4" style={{ color: '#E8A838' }} />
        </div>
      </motion.div>
    </Link>
  )
}
