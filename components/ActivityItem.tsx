import Avatar from '@/components/ui/Avatar'
import type { ActivityItem as ActivityItemType } from '@/lib/mockData'

export default function ActivityItem({ activity }: { activity: ActivityItemType }) {
  return (
    <div className="flex gap-3 py-4 border-b last:border-0" style={{ borderColor: '#EAE3D8' }}>
      <Avatar initials={activity.memberAvatar} size={36} />
      <div className="flex-1 min-w-0">
        <p style={{ fontSize: 13, fontWeight: 300, color: '#5A5855', lineHeight: 1.5 }}>
          <span style={{ fontWeight: 400, color: '#1C1C1A' }}>{activity.memberName}</span>{' '}
          {activity.action}
          {activity.target && (
            <> <span style={{ color: '#1C1C1A', fontWeight: 400 }}>&ldquo;{activity.target}&rdquo;</span></>
          )}
        </p>
        <p
          style={{
            fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
            fontSize: 9,
            letterSpacing: '0.03em',
            color: '#A09A8E',
            marginTop: 5,
          }}
        >
          {activity.groupName} · {activity.timestamp}
        </p>
      </div>
    </div>
  )
}
