import Avatar from './ui/Avatar';
import { MessageSquare, Map, FileText, UserPlus } from 'lucide-react';

const icons: Record<string, { icon: React.ReactNode; color: string }> = {
  discussion: { icon: <MessageSquare size={12} />, color: '#E8A838' },
  route: { icon: <Map size={12} />, color: '#4CAF7D' },
  document: { icon: <FileText size={12} />, color: '#38B2E8' },
  join: { icon: <UserPlus size={12} />, color: '#7B68EE' },
};

export default function ActivityItem({ activity }: { activity: any }) {
  const typeStyle = icons[activity.type] || icons.discussion;
  return (
    <div className="flex items-start gap-3 py-3 border-b border-[#1E1E1E] last:border-0">
      <Avatar initials={activity.member.avatar} color={activity.member.color} size={32} />
      <div className="flex-1 min-w-0">
        <p className="text-sm text-[#F0EDE8]">
          <span className="font-medium">{activity.member.name}</span>
          <span className="text-[#8A8580]"> {activity.action} </span>
          {activity.target && <span className="text-[#E8A838]">{activity.target}</span>}
        </p>
        <div className="flex items-center gap-2 mt-1">
          <span
            className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs"
            style={{ color: typeStyle.color, background: `${typeStyle.color}15` }}
          >
            {typeStyle.icon}
            {activity.group.name}
          </span>
          <span className="text-xs text-[#4A4845]">{activity.time}</span>
        </div>
      </div>
    </div>
  );
}
