'use client';
import { useState } from 'react';
import { motion } from 'framer-motion';
import { useParams } from 'next/navigation';
import TopNav from '@/components/TopNav';
import Avatar from '@/components/ui/Avatar';
import Badge from '@/components/ui/Badge';
import Button from '@/components/ui/Button';
import { groups, members, discussions, routes, events } from '@/lib/mockData';
import { MessageSquare, Map, Users, Calendar, FileText, Folder, Bell, Plus, Reply, Clock } from 'lucide-react';

const tabs = [
  { id: 'discussions', label: 'Discussions', icon: <MessageSquare size={14} /> },
  { id: 'maps', label: 'Maps', icon: <Map size={14} /> },
  { id: 'members', label: 'Members', icon: <Users size={14} /> },
  { id: 'calendar', label: 'Calendar', icon: <Calendar size={14} /> },
  { id: 'docs', label: 'Docs', icon: <FileText size={14} /> },
  { id: 'files', label: 'Files', icon: <Folder size={14} /> },
];

export default function GroupPage() {
  const { id } = useParams();
  const group = groups.find(g => g.id === id) || groups[0];
  const [activeTab, setActiveTab] = useState('discussions');
  const [composerOpen, setComposerOpen] = useState(false);

  return (
    <div className="min-h-screen" style={{ background: '#0D0D0D' }}>
      <TopNav />
      <div className="pt-14">
        {/* Hero */}
        <div
          className="relative h-52 md:h-64 flex items-end"
          style={{ background: group.cover }}
        >
          <div className="absolute inset-0 bg-gradient-to-t from-[#0D0D0D] via-[#0D0D0D]/40 to-transparent" />
          <div className="relative z-10 w-full max-w-6xl mx-auto px-6 pb-6 flex items-end gap-5">
            <div className="text-5xl">{group.icon}</div>
            <div className="flex-1 min-w-0">
              <Badge label={group.category} color={group.categoryColor} />
              <h1 className="font-display text-3xl md:text-4xl text-[#F0EDE8] mt-1 leading-tight">{group.name}</h1>
              <p className="text-sm text-[#8A8580] mt-1">📍 {group.location}</p>
            </div>
            <div className="flex items-center gap-2 shrink-0">
              <Button variant="ghost" size="sm">
                <Bell size={14} /> Follow
              </Button>
              <Button variant="primary" size="sm">
                ✓ Organizer
              </Button>
            </div>
          </div>
        </div>

        {/* Group meta */}
        <div className="border-b border-[#1E1E1E]" style={{ background: '#111111' }}>
          <div className="max-w-6xl mx-auto px-6 py-4">
            <div className="flex flex-col sm:flex-row sm:items-center gap-4">
              <p className="text-sm text-[#8A8580] flex-1 max-w-xl">
                {group.description}
              </p>
              <div className="flex items-center gap-3">
                <div className="flex -space-x-2">
                  {members.slice(0, 5).map(m => (
                    <Avatar key={m.id} initials={m.avatar} color={m.color} size={26} />
                  ))}
                </div>
                <span className="text-sm text-[#8A8580]">{group.memberCount} members</span>
              </div>
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div className="border-b border-[#1E1E1E] sticky top-14 z-40" style={{ background: '#0D0D0D' }}>
          <div className="max-w-6xl mx-auto px-6">
            <div className="flex gap-0 overflow-x-auto">
              {tabs.map(tab => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex items-center gap-1.5 px-4 py-3.5 text-sm whitespace-nowrap border-b-2 transition-all ${
                    activeTab === tab.id
                      ? 'border-[#E8A838] text-[#E8A838]'
                      : 'border-transparent text-[#8A8580] hover:text-[#F0EDE8]'
                  }`}
                >
                  {tab.icon}
                  {tab.label}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Tab content */}
        <div className="max-w-6xl mx-auto px-6 py-8">
          {activeTab === 'discussions' && (
            <div className="max-w-3xl">
              {/* Composer */}
              <div className="mb-6">
                {!composerOpen ? (
                  <button
                    onClick={() => setComposerOpen(true)}
                    className="w-full flex items-center gap-3 p-4 rounded-xl border border-[#2A2A2A] bg-[#161616] text-[#4A4845] hover:border-[#E8A838]/40 hover:text-[#8A8580] transition-all text-sm"
                  >
                    <Avatar initials="MC" color="#E8A838" size={28} />
                    Start a discussion...
                    <span className="ml-auto text-xs">⌘N</span>
                  </button>
                ) : (
                  <motion.div
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="rounded-xl border border-[#E8A838]/40 bg-[#161616] p-5"
                  >
                    <input
                      autoFocus
                      placeholder="Discussion title"
                      className="w-full bg-transparent text-lg font-display text-[#F0EDE8] placeholder-[#3A3A3A] mb-4 border-b border-[#2A2A2A] pb-3"
                    />
                    <textarea
                      rows={4}
                      placeholder="Share your thoughts, ask a question, start a conversation..."
                      className="w-full bg-transparent text-sm text-[#8A8580] placeholder-[#3A3A3A] resize-none"
                    />
                    <div className="flex items-center justify-between mt-4 pt-4 border-t border-[#2A2A2A]">
                      <div className="flex gap-3 text-[#4A4845]">
                        {['📎', '🖼️', '🎬', '😊'].map(e => (
                          <button key={e} className="text-lg hover:opacity-70">{e}</button>
                        ))}
                      </div>
                      <div className="flex gap-2">
                        <Button variant="subtle" size="sm" onClick={() => setComposerOpen(false)}>Cancel</Button>
                        <Button variant="primary" size="sm">Post</Button>
                      </div>
                    </div>
                  </motion.div>
                )}
              </div>

              {/* Discussion list */}
              <div className="space-y-2">
                {discussions.map((d, i) => (
                  <motion.div
                    key={d.id}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: i * 0.05 }}
                    className={`p-4 rounded-xl border bg-[#161616] hover:border-[#E8A838]/30 transition-all cursor-pointer group ${
                      d.pinned ? 'border-[#E8A838]/20' : 'border-[#2A2A2A]'
                    }`}
                  >
                    {d.pinned && (
                      <div className="flex items-center gap-1.5 text-xs text-[#E8A838] mb-2">
                        <span>📌</span> Pinned
                      </div>
                    )}
                    <div className="flex items-start gap-3">
                      <Avatar initials={d.author.avatar} color={d.author.color} size={32} />
                      <div className="flex-1 min-w-0">
                        <h3 className="font-medium text-[#F0EDE8] group-hover:text-[#E8A838] transition-colors leading-snug">
                          {d.title}
                        </h3>
                        <div className="flex items-center gap-3 mt-2 text-xs text-[#4A4845]">
                          <span>{d.author.name}</span>
                          <span className="flex items-center gap-1"><Reply size={10} /> {d.replies} replies</span>
                          <span className="flex items-center gap-1"><Users size={10} /> {d.members} members</span>
                          <span className="flex items-center gap-1"><Clock size={10} /> {d.lastActivity}</span>
                        </div>
                        <div className="flex gap-1.5 mt-2">
                          {d.tags.map(tag => (
                            <Badge key={tag} label={tag} color="#8A8580" />
                          ))}
                        </div>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          )}

          {activeTab === 'maps' && (
            <div>
              <div className="flex items-center justify-between mb-6">
                <h2 className="font-display text-2xl text-[#F0EDE8]">Group Routes</h2>
                <Button variant="primary" size="sm"><Plus size={14} /> Add Route</Button>
              </div>
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Map placeholder */}
                <div className="lg:col-span-2 rounded-xl overflow-hidden border border-[#2A2A2A] relative" style={{ height: 400 }}>
                  <div
                    className="absolute inset-0 map-grid flex items-center justify-center"
                    style={{ background: '#0a0f1a' }}
                  >
                    <div className="text-center">
                      <div className="text-6xl mb-3">🗺️</div>
                      <p className="text-[#8A8580] text-sm">Interactive map coming soon</p>
                      <p className="text-[#4A4845] text-xs mt-1">Powered by Mapbox</p>
                    </div>
                  </div>
                  {/* Fake route overlay */}
                  <div className="absolute bottom-4 left-4 right-4 p-3 rounded-lg bg-[#0D0D0D]/80 backdrop-blur-sm border border-[#2A2A2A] text-xs text-[#8A8580]">
                    {routes.length} routes · Click a route to view on map
                  </div>
                </div>

                {/* Route list */}
                <div className="space-y-3">
                  {routes.map((route, i) => (
                    <motion.div
                      key={route.id}
                      initial={{ opacity: 0, x: 20 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: i * 0.07 }}
                      className="p-4 rounded-xl border border-[#2A2A2A] bg-[#161616] hover:border-[#4CAF7D]/40 transition-all cursor-pointer"
                    >
                      <div className="flex items-start gap-3">
                        <div className="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style={{ background: '#4CAF7D20' }}>
                          <Map size={14} style={{ color: '#4CAF7D' }} />
                        </div>
                        <div className="min-w-0">
                          <p className="text-sm font-medium text-[#F0EDE8] leading-snug">{route.title}</p>
                          <div className="flex items-center gap-2 mt-1 text-xs text-[#4A4845]">
                            <span>{route.distance}</span>
                            <span>·</span>
                            <span>{route.author.name}</span>
                          </div>
                        </div>
                      </div>
                    </motion.div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {activeTab === 'members' && (
            <div>
              <div className="flex items-center justify-between mb-6">
                <h2 className="font-display text-2xl text-[#F0EDE8]">{group.memberCount} Members</h2>
                <Button variant="ghost" size="sm">Invite</Button>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {members.map((member, i) => (
                  <motion.div
                    key={member.id}
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ delay: i * 0.04 }}
                    className="p-4 rounded-xl border border-[#2A2A2A] bg-[#161616] hover:border-[#E8A838]/30 transition-all cursor-pointer text-center"
                  >
                    <div className="flex justify-center mb-3">
                      <Avatar initials={member.avatar} color={member.color} size={44} online={i < 3} />
                    </div>
                    <p className="font-medium text-[#F0EDE8] text-sm">{member.name}</p>
                    <p className="text-xs text-[#4A4845] mt-0.5">{member.flag} {member.location}</p>
                    <div className="mt-2">
                      <Badge
                        label={member.role}
                        color={member.role === 'Organizer' ? '#E8A838' : member.role === 'Teacher' ? '#7B68EE' : '#8A8580'}
                      />
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          )}

          {activeTab === 'calendar' && (
            <div className="max-w-2xl">
              <div className="flex items-center justify-between mb-6">
                <h2 className="font-display text-2xl text-[#F0EDE8]">Events</h2>
                <Button variant="primary" size="sm"><Plus size={14} /> New Event</Button>
              </div>
              <div className="space-y-4">
                {events.map((event, i) => (
                  <motion.div
                    key={event.id}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: i * 0.08 }}
                    className="flex gap-5 p-5 rounded-xl border border-[#2A2A2A] bg-[#161616] hover:border-[#E8A838]/40 transition-all cursor-pointer"
                  >
                    <div className="text-center w-14 shrink-0 border-r border-[#2A2A2A] pr-5">
                      <div className="text-xs text-[#E8A838] font-medium uppercase tracking-wide">{event.date.split(' ')[0]}</div>
                      <div className="text-3xl font-bold text-[#F0EDE8] leading-none mt-0.5">{event.date.split(' ')[1]}</div>
                    </div>
                    <div>
                      <h3 className="font-medium text-[#F0EDE8] leading-snug">{event.title}</h3>
                      <p className="text-sm text-[#8A8580] mt-1">{event.time}</p>
                      <div className="flex items-center gap-2 mt-2 text-xs text-[#4A4845]">
                        <Users size={11} /> {event.attendees} attending
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          )}

          {(activeTab === 'docs' || activeTab === 'files') && (
            <div className="max-w-2xl">
              <div className="flex items-center justify-between mb-6">
                <h2 className="font-display text-2xl text-[#F0EDE8]">
                  {activeTab === 'docs' ? 'Collaborative Docs' : 'Files'}
                </h2>
                <Button variant="primary" size="sm"><Plus size={14} /> New</Button>
              </div>
              <div className="rounded-xl border border-[#2A2A2A] overflow-hidden">
                {['Project Charter', 'Meeting Notes — June 2025', 'Research Findings', 'Action Plan Draft'].map((doc, i) => (
                  <div key={i} className="flex items-center gap-3 px-5 py-4 border-b border-[#1E1E1E] last:border-0 hover:bg-[#161616] transition-colors cursor-pointer">
                    <div className="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style={{ background: '#E8A83815' }}>
                      <FileText size={14} style={{ color: '#E8A838' }} />
                    </div>
                    <div className="flex-1">
                      <p className="text-sm text-[#F0EDE8]">{doc}</p>
                      <p className="text-xs text-[#4A4845] mt-0.5">Edited {i + 1} day{i !== 0 ? 's' : ''} ago</p>
                    </div>
                    <Badge label="Shared" color="#4CAF7D" />
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
