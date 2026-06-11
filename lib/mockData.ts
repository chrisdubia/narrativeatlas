export interface Group {
  id: string
  name: string
  description: string
  memberCount: number
  location: string
  gradient: string
  borderColor: string
  tags: string[]
  lastActive: string
  organizer: string
  featured: boolean
}

export interface Member {
  id: string
  name: string
  avatar: string
  role: string
  location: string
  country: string
  countryFlag: string
  joinDate: string
  bio: string
  groups: string[]
}

export interface ActivityItem {
  id: string
  memberId: string
  memberName: string
  memberAvatar: string
  action: string
  target: string
  groupId: string
  groupName: string
  timestamp: string
  type: 'posted' | 'shared' | 'uploaded' | 'joined' | 'commented'
}

export interface DiscussionThread {
  id: string
  title: string
  authorId: string
  authorName: string
  authorAvatar: string
  groupId: string
  replyCount: number
  viewCount: number
  timestamp: string
  tags: string[]
}

export interface Event {
  id: string
  title: string
  description: string
  date: string
  time: string
  groupId: string
  groupName: string
  attendeeCount: number
  type: 'workshop' | 'meeting' | 'action' | 'showcase'
}

export interface MapRoute {
  id: string
  title: string
  from: string
  to: string
  groupId: string
  description: string
  createdBy: string
  timestamp: string
}

export const groups: Group[] = [
  { id: '1', name: 'Climate Futures: Nairobi–Copenhagen', description: 'Youth climate advocates from East Africa and Scandinavia co-designing solutions for a sustainable future.', memberCount: 47, location: 'Nairobi, Kenya · Copenhagen, Denmark', gradient: 'linear-gradient(135deg, #1a3a2a 0%, #0d2018 50%, #1a2a1a 100%)', borderColor: '#4CAF7D', tags: ['Climate', 'Environment', 'Cross-cultural'], lastActive: '2 hours ago', organizer: 'Amara Osei', featured: true },
  { id: '2', name: 'Youth Parliament: Lviv–Omaha', description: 'Students from Ukraine and Nebraska exploring democratic processes and civic engagement through simulation.', memberCount: 34, location: 'Lviv, Ukraine · Omaha, Nebraska', gradient: 'linear-gradient(135deg, #1a1a3a 0%, #0d0d28 50%, #1a1a3a 100%)', borderColor: '#5B8DEF', tags: ['Civics', 'Democracy', 'Exchange'], lastActive: '5 hours ago', organizer: 'Sofiya Petrenko', featured: true },
  { id: '3', name: 'Civic Tech Fellows', description: 'Building digital tools for community organizing and participatory governance.', memberCount: 28, location: 'Global · Remote', gradient: 'linear-gradient(135deg, #2a1a3a 0%, #180d28 50%, #2a1a3a 100%)', borderColor: '#9B59B6', tags: ['Technology', 'Civic Tech', 'Design'], lastActive: '1 day ago', organizer: 'Marcus Chen', featured: false },
  { id: '4', name: 'Ocean Stories', description: 'Coastal youth documenting marine ecosystems and advocating for ocean protection.', memberCount: 52, location: 'Pacific Rim · Atlantic', gradient: 'linear-gradient(135deg, #0d1a3a 0%, #060d28 50%, #0d1a3a 100%)', borderColor: '#2196F3', tags: ['Ocean', 'Environment', 'Storytelling'], lastActive: '3 hours ago', organizer: 'Leilani Kahale', featured: true },
  { id: '5', name: 'Future Cities Lab', description: 'Urban planning and design students reimagining cities for the next generation.', memberCount: 39, location: 'São Paulo · Seoul · Lagos', gradient: 'linear-gradient(135deg, #3a2a1a 0%, #281a0d 50%, #3a2a1a 100%)', borderColor: '#E8A838', tags: ['Urban', 'Design', 'Planning'], lastActive: '12 hours ago', organizer: 'Pedro Alves', featured: false },
  { id: '6', name: 'Arts Without Borders', description: 'Youth artists collaborating across cultures through visual art, music, and performance.', memberCount: 61, location: 'Global', gradient: 'linear-gradient(135deg, #3a1a1a 0%, #280d0d 50%, #3a1a1a 100%)', borderColor: '#E85555', tags: ['Arts', 'Culture', 'Performance'], lastActive: '30 minutes ago', organizer: 'Yuki Tanaka', featured: true },
]

export const members: Member[] = [
  { id: '1', name: 'Amara Osei', avatar: 'AO', role: 'Organizer', location: 'Nairobi, Kenya', country: 'Kenya', countryFlag: '🇰🇪', joinDate: 'January 2024', bio: 'Environmental activist and youth leader working on climate solutions in East Africa.', groups: ['1', '4'] },
  { id: '2', name: 'Sofiya Petrenko', avatar: 'SP', role: 'Organizer', location: 'Lviv, Ukraine', country: 'Ukraine', countryFlag: '🇺🇦', joinDate: 'February 2024', bio: 'Student of political science passionate about democratic participation and civic education.', groups: ['2'] },
  { id: '3', name: 'Marcus Chen', avatar: 'MC', role: 'Organizer', location: 'San Francisco, USA', country: 'USA', countryFlag: '🇺🇸', joinDate: 'November 2023', bio: 'Software developer and civic technologist building tools for community organizing.', groups: ['3', '5'] },
  { id: '4', name: 'Leilani Kahale', avatar: 'LK', role: 'Organizer', location: 'Honolulu, Hawaii', country: 'USA', countryFlag: '🇺🇸', joinDate: 'March 2024', bio: 'Marine biologist and storyteller documenting Pacific Ocean ecosystems.', groups: ['4'] },
  { id: '5', name: 'Pedro Alves', avatar: 'PA', role: 'Organizer', location: 'São Paulo, Brazil', country: 'Brazil', countryFlag: '🇧🇷', joinDate: 'December 2023', bio: 'Urban designer and architect working on sustainable city planning.', groups: ['5'] },
  { id: '6', name: 'Yuki Tanaka', avatar: 'YT', role: 'Organizer', location: 'Tokyo, Japan', country: 'Japan', countryFlag: '🇯🇵', joinDate: 'October 2023', bio: 'Multimedia artist and cultural exchange advocate.', groups: ['6'] },
  { id: '7', name: 'Fatima Al-Hassan', avatar: 'FA', role: 'Member', location: 'Cairo, Egypt', country: 'Egypt', countryFlag: '🇪🇬', joinDate: 'April 2024', bio: 'Youth activist working on education access and gender equality.', groups: ['2', '6'] },
  { id: '8', name: 'Kieran Murphy', avatar: 'KM', role: 'Member', location: 'Dublin, Ireland', country: 'Ireland', countryFlag: '🇮🇪', joinDate: 'January 2024', bio: 'Environmental science student focused on renewable energy transitions.', groups: ['1', '3'] },
  { id: '9', name: 'Priya Sharma', avatar: 'PS', role: 'Member', location: 'Mumbai, India', country: 'India', countryFlag: '🇮🇳', joinDate: 'March 2024', bio: 'Tech entrepreneur building civic engagement platforms.', groups: ['3', '5'] },
  { id: '10', name: 'Kofi Mensah', avatar: 'KM', role: 'Moderator', location: 'Accra, Ghana', country: 'Ghana', countryFlag: '🇬🇭', joinDate: 'February 2024', bio: 'Community organizer and youth parliament delegate.', groups: ['2', '1'] },
  { id: '11', name: 'Isabella Rossi', avatar: 'IR', role: 'Member', location: 'Milan, Italy', country: 'Italy', countryFlag: '🇮🇹', joinDate: 'May 2024', bio: 'Fashion and art student exploring cultural exchange through creative work.', groups: ['6'] },
  { id: '12', name: 'Jin-ho Park', avatar: 'JP', role: 'Member', location: 'Seoul, South Korea', country: 'South Korea', countryFlag: '🇰🇷', joinDate: 'January 2024', bio: 'Urban planning student and smart city enthusiast.', groups: ['5'] },
]

export const activities: ActivityItem[] = [
  { id: '1', memberId: '1', memberName: 'Amara Osei', memberAvatar: 'AO', action: 'posted a new discussion', target: 'Carbon budgets and youth accountability', groupId: '1', groupName: 'Climate Futures', timestamp: '2 hours ago', type: 'posted' },
  { id: '2', memberId: '6', memberName: 'Yuki Tanaka', memberAvatar: 'YT', action: 'uploaded artwork', target: 'Migration Series – Panel 3', groupId: '6', groupName: 'Arts Without Borders', timestamp: '3 hours ago', type: 'uploaded' },
  { id: '3', memberId: '4', memberName: 'Leilani Kahale', memberAvatar: 'LK', action: 'shared a map route', target: 'Pacific Plastic Gyre Awareness Trail', groupId: '4', groupName: 'Ocean Stories', timestamp: '5 hours ago', type: 'shared' },
  { id: '4', memberId: '7', memberName: 'Fatima Al-Hassan', memberAvatar: 'FA', action: 'joined the group', target: '', groupId: '2', groupName: 'Youth Parliament', timestamp: '6 hours ago', type: 'joined' },
  { id: '5', memberId: '3', memberName: 'Marcus Chen', memberAvatar: 'MC', action: 'posted a new discussion', target: 'Open-source tools for participatory budgeting', groupId: '3', groupName: 'Civic Tech Fellows', timestamp: '8 hours ago', type: 'posted' },
  { id: '6', memberId: '8', memberName: 'Kieran Murphy', memberAvatar: 'KM', action: 'commented on', target: 'Wind energy transition in coastal communities', groupId: '1', groupName: 'Climate Futures', timestamp: '10 hours ago', type: 'commented' },
  { id: '7', memberId: '2', memberName: 'Sofiya Petrenko', memberAvatar: 'SP', action: 'uploaded a document', target: 'Youth Parliament Session Notes – May', groupId: '2', groupName: 'Youth Parliament', timestamp: '1 day ago', type: 'uploaded' },
  { id: '8', memberId: '9', memberName: 'Priya Sharma', memberAvatar: 'PS', action: 'shared a resource', target: 'Civic tech toolkit for local elections', groupId: '3', groupName: 'Civic Tech Fellows', timestamp: '1 day ago', type: 'shared' },
  { id: '9', memberId: '5', memberName: 'Pedro Alves', memberAvatar: 'PA', action: 'posted a new discussion', target: 'Green corridors in high-density cities', groupId: '5', groupName: 'Future Cities Lab', timestamp: '1 day ago', type: 'posted' },
  { id: '10', memberId: '10', memberName: 'Kofi Mensah', memberAvatar: 'KM', action: 'joined the group', target: '', groupId: '1', groupName: 'Climate Futures', timestamp: '2 days ago', type: 'joined' },
  { id: '11', memberId: '11', memberName: 'Isabella Rossi', memberAvatar: 'IR', action: 'uploaded artwork', target: 'Cross-cultural textile collaboration', groupId: '6', groupName: 'Arts Without Borders', timestamp: '2 days ago', type: 'uploaded' },
  { id: '12', memberId: '12', memberName: 'Jin-ho Park', memberAvatar: 'JP', action: 'posted a new discussion', target: 'Smart city data and privacy rights', groupId: '5', groupName: 'Future Cities Lab', timestamp: '2 days ago', type: 'posted' },
]

export const discussions: DiscussionThread[] = [
  { id: '1', title: 'Carbon budgets and youth accountability', authorId: '1', authorName: 'Amara Osei', authorAvatar: 'AO', groupId: '1', replyCount: 12, viewCount: 87, timestamp: '2 hours ago', tags: ['Climate', 'Policy'] },
  { id: '2', title: 'How do we make climate data accessible to all?', authorId: '8', authorName: 'Kieran Murphy', authorAvatar: 'KM', groupId: '1', replyCount: 8, viewCount: 54, timestamp: '1 day ago', tags: ['Data', 'Accessibility'] },
  { id: '3', title: 'Youth Parliament simulation – feedback thread', authorId: '2', authorName: 'Sofiya Petrenko', authorAvatar: 'SP', groupId: '2', replyCount: 23, viewCount: 134, timestamp: '5 hours ago', tags: ['Parliament', 'Feedback'] },
  { id: '4', title: 'Open-source tools for participatory budgeting', authorId: '3', authorName: 'Marcus Chen', authorAvatar: 'MC', groupId: '3', replyCount: 15, viewCount: 92, timestamp: '8 hours ago', tags: ['Tech', 'Budgeting'] },
  { id: '5', title: 'Green corridors in high-density cities', authorId: '5', authorName: 'Pedro Alves', authorAvatar: 'PA', groupId: '5', replyCount: 7, viewCount: 41, timestamp: '1 day ago', tags: ['Urban', 'Green'] },
  { id: '6', title: 'Documenting coral bleaching through art', authorId: '4', authorName: 'Leilani Kahale', authorAvatar: 'LK', groupId: '4', replyCount: 19, viewCount: 108, timestamp: '3 hours ago', tags: ['Ocean', 'Art'] },
  { id: '7', title: 'Cross-cultural collaboration challenges', authorId: '7', authorName: 'Fatima Al-Hassan', authorAvatar: 'FA', groupId: '6', replyCount: 31, viewCount: 201, timestamp: '12 hours ago', tags: ['Culture', 'Collaboration'] },
  { id: '8', title: 'Smart city data and privacy rights', authorId: '12', authorName: 'Jin-ho Park', authorAvatar: 'JP', groupId: '5', replyCount: 11, viewCount: 67, timestamp: '2 days ago', tags: ['Privacy', 'Tech'] },
]

export const events: Event[] = [
  { id: '1', title: 'Climate Action Workshop', description: 'Co-design session for youth-led climate pledges', date: '2026-06-14', time: '15:00 UTC', groupId: '1', groupName: 'Climate Futures', attendeeCount: 28, type: 'workshop' },
  { id: '2', title: 'Youth Parliament Simulation', description: 'Live debate on digital rights legislation', date: '2026-06-17', time: '14:00 UTC', groupId: '2', groupName: 'Youth Parliament', attendeeCount: 34, type: 'action' },
  { id: '3', title: 'Civic Tech Demo Day', description: 'Teams present their community tools', date: '2026-06-20', time: '18:00 UTC', groupId: '3', groupName: 'Civic Tech Fellows', attendeeCount: 52, type: 'showcase' },
  { id: '4', title: 'Ocean Stories Showcase', description: 'Multimedia exhibition of marine ecosystem documentation', date: '2026-06-25', time: '16:00 UTC', groupId: '4', groupName: 'Ocean Stories', attendeeCount: 67, type: 'showcase' },
]

export const mapRoutes: MapRoute[] = [
  { id: '1', title: 'Nairobi to Copenhagen Climate Exchange', from: 'Nairobi, Kenya', to: 'Copenhagen, Denmark', groupId: '1', description: 'Tracing the journey of climate data between two youth organizations', createdBy: 'Amara Osei', timestamp: '1 week ago' },
  { id: '2', title: 'Pacific Plastic Gyre Awareness Trail', from: 'Honolulu, Hawaii', to: 'Manila, Philippines', groupId: '4', description: 'Documenting ocean plastic pollution hotspots', createdBy: 'Leilani Kahale', timestamp: '3 days ago' },
  { id: '3', title: 'Lviv to Omaha Democracy Bridge', from: 'Lviv, Ukraine', to: 'Omaha, Nebraska', groupId: '2', description: 'Cultural exchange route between partner schools', createdBy: 'Sofiya Petrenko', timestamp: '2 weeks ago' },
  { id: '4', title: 'Future Cities Network', from: 'São Paulo, Brazil', to: 'Seoul, South Korea', groupId: '5', description: 'Urban innovation exchange between megacities', createdBy: 'Pedro Alves', timestamp: '5 days ago' },
  { id: '5', title: 'Arts Without Borders Tour', from: 'Tokyo, Japan', to: 'Milan, Italy', groupId: '6', description: 'Cultural art exchange route highlighting partner institutions', createdBy: 'Yuki Tanaka', timestamp: '1 week ago' },
  { id: '6', title: 'West African Climate Corridor', from: 'Accra, Ghana', to: 'Lagos, Nigeria', groupId: '1', description: 'Regional climate vulnerability mapping', createdBy: 'Kofi Mensah', timestamp: '4 days ago' },
]
