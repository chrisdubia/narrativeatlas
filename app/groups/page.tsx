'use client';
import { useState } from 'react';
import { motion } from 'framer-motion';
import TopNav from '@/components/TopNav';
import GroupCard from '@/components/GroupCard';
import { groups } from '@/lib/mockData';
import { Search } from 'lucide-react';

const filters = ['All', 'My Groups', 'Climate Action', 'Youth Exchange', 'Civic Tech', 'Arts & Culture'];

export default function GroupsPage() {
  const [activeFilter, setActiveFilter] = useState('All');
  const [query, setQuery] = useState('');

  const filtered = groups.filter(g => {
    const matchesQuery = g.name.toLowerCase().includes(query.toLowerCase()) ||
      g.description.toLowerCase().includes(query.toLowerCase());
    const matchesFilter = activeFilter === 'All' || activeFilter === 'My Groups' ||
      g.category === activeFilter;
    return matchesQuery && matchesFilter;
  });

  return (
    <div className="min-h-screen" style={{ background: '#0D0D0D' }}>
      <TopNav />
      <div className="pt-14">
        {/* Header */}
        <div className="px-6 py-12 border-b border-[#1E1E1E]" style={{ background: '#111111' }}>
          <div className="max-w-6xl mx-auto">
            <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
              <p className="text-xs text-[#E8A838] uppercase tracking-widest mb-2 font-medium">Communities</p>
              <h1 className="font-display text-5xl text-[#F0EDE8] mb-3">Find your people.<br />Join the mission.</h1>
              <p className="text-[#8A8580] text-lg max-w-xl">
                {groups.length} communities · {groups.reduce((a, g) => a + g.memberCount, 0)} members · 24 countries
              </p>
            </motion.div>
          </div>
        </div>

        <div className="max-w-6xl mx-auto px-6 py-8">
          {/* Filter bar */}
          <div className="flex flex-col sm:flex-row gap-4 mb-8">
            <div className="flex items-center gap-2 overflow-x-auto pb-1 flex-1">
              {filters.map(f => (
                <button
                  key={f}
                  onClick={() => setActiveFilter(f)}
                  className={`shrink-0 px-4 py-2 rounded-full text-sm transition-all border ${
                    activeFilter === f
                      ? 'bg-[#E8A838] text-[#0D0D0D] border-[#E8A838] font-medium'
                      : 'border-[#2A2A2A] text-[#8A8580] hover:border-[#3A3A3A] hover:text-[#F0EDE8]'
                  }`}
                >
                  {f}
                </button>
              ))}
            </div>
            <div className="flex items-center gap-2 bg-[#161616] border border-[#2A2A2A] rounded-lg px-3 py-2 w-full sm:w-64">
              <Search size={14} className="text-[#4A4845] shrink-0" />
              <input
                value={query}
                onChange={e => setQuery(e.target.value)}
                placeholder="Search groups..."
                className="bg-transparent text-sm text-[#F0EDE8] placeholder-[#3A3A3A] flex-1"
              />
            </div>
          </div>

          {/* Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {filtered.map((group, i) => (
              <GroupCard key={group.id} group={group} index={i} />
            ))}
            {filtered.length === 0 && (
              <div className="col-span-3 text-center py-16 text-[#4A4845]">
                No communities match your search.
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
