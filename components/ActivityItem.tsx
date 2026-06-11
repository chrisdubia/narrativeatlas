import React from 'react'
import { FileText, Share2, Upload, UserPlus, MessageSquare } from 'lucide-react'
import Avatar from '@/components/ui/Avatar'
import type { ActivityItem as ActivityItemType } from '@/lib/mockData'

const TYPE_CONFIG: Record<string, { Icon: React.ComponentType<{ size: number }>; color: string }> = {
  posted: { Icon: FileText, color: '#E8A838' },
  shared: { Icon: Share2, color: '#38BDF8' },
  uploaded: { Icon: Upload, color: '#A78BFA' },
  joined: { Icon: UserPlus, color: '#4CAF7D' },
  commented: { Icon: MessageSquare, color: '#F97316' },
}

export default function ActivityItem({ activity }: { activity: ActivityItemType }) {
  const config = TYPE_CONFIG[activity.type] ?? TYPE_CONFIG.posted
  const { Icon, color } = config

  return (
    <div className="flex gap-3 py-3 border-b last:border-0" style={{ borderColor: '#1E1E1E' }}>
      <div className="relative shrink-0">
        <Avatar initials={activity.memberAvatar} size={32} />
        <div
          className="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center"
          style={{ backgroundColor: '#1E1E1E', border: '1px solid #2A2A2A', color }}
        >
          <Icon size={9} />
        </div>
      </div>

      <div className="flex-1 min-w-0">
        <p className="text-sm" style={{ color: '#F0EDE8' }}>
          <span className="font-medium">{activity.memberName}</span>{' '}
          <span style={{ color: '#8A8580' }}>{activity.action}</span>
        </p>
        {activity.target && (
          <p className="text-sm mt-0.5 truncate" style={{ color: '#E8A838' }}>
            &ldquo;{activity.target}&rdquo;
          </p>
        )}
        <div className="flex items-center gap-2 mt-1">
          <span className="text-xs" style={{ color: '#8A8580' }}>{activity.groupName}</span>
          <span className="text-xs" style={{ color: '#2A2A2A' }}>·</span>
          <span className="text-xs" style={{ color: '#8A8580' }}>{activity.timestamp}</span>
        </div>
      </div>
    </div>
  )
}
