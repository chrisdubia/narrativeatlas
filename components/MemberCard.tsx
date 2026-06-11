'use client'

import { motion } from 'framer-motion'
import Link from 'next/link'
import { MapPin } from 'lucide-react'
import Avatar from '@/components/ui/Avatar'
import Badge from '@/components/ui/Badge'
import { Member } from '@/lib/mockData'

interface MemberCardProps {
  member: Member
}

const roleVariant: Record<string, 'warning' | 'info' | 'default'> = {
  Organizer: 'warning',
  Moderator: 'info',
  Member: 'default',
}

export default function MemberCard({ member }: MemberCardProps) {
  return (
    <Link href={`/members/${member.id}`}>
      <motion.div
        whileHover={{ y: -2 }}
        className="rounded-xl p-4 cursor-pointer"
        style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}
      >
        <div className="flex items-start gap-3">
          <Avatar initials={member.avatar} size="md" />
          <div className="flex-1 min-w-0">
            <div className="flex items-start justify-between gap-2">
              <h3 className="font-medium text-sm leading-tight" style={{ color: '#F0EDE8' }}>{member.name}</h3>
              <Badge variant={roleVariant[member.role] || 'default'}>{member.role}</Badge>
            </div>
            <div className="flex items-center gap-1 mt-1">
              <span className="text-sm">{member.countryFlag}</span>
              <span className="text-xs truncate" style={{ color: '#8A8580' }}>{member.location}</span>
            </div>
            <div className="flex items-center gap-1 mt-0.5">
              <MapPin className="w-3 h-3" style={{ color: '#8A8580' }} />
              <span className="text-xs" style={{ color: '#8A8580' }}>Joined {member.joinDate}</span>
            </div>
          </div>
        </div>
        {member.bio && (
          <p className="text-xs mt-3 line-clamp-2" style={{ color: '#8A8580' }}>{member.bio}</p>
        )}
        <div className="mt-2 text-xs" style={{ color: '#8A8580' }}>
          {member.groups.length} {member.groups.length === 1 ? 'group' : 'groups'}
        </div>
      </motion.div>
    </Link>
  )
}
