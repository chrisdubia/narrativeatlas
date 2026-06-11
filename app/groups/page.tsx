'use client';

import { motion } from 'framer-motion';
import { useState } from 'react';
import { Search } from 'lucide-react';
import Link from 'next/link';
import CompassMark from '@/components/CompassMark';
import GroupCard from '@/components/GroupCard';
import Avatar from '@/components/ui/Avatar';
import { groups, members } from '@/lib/mockData';

const filters = ['All', 'My Groups', 'Featured', 'Recently Active'];

export default function GroupsPage() {
  const [activeFilter, setActiveFilter] = useState('All');
  const [search, setSearch] = useState('');

  const filtered = groups.filter((g) => {
    const matchSearch = g.name.toLowerCase().includes(search.toLowerCase()) || g.description.toLowerCase().includes(search.toLowerCase());
    if (!matchSearch) return false;
    if (activeFilter === 'My Groups') return false; // no isMember in Group type
    if (activeFilter === 'Featured') return g.featured;
    if (activeFilter === 'Recently Active') return ['1 hour ago', '2 hours ago', '3 hours ago', '4 hours ago'].includes(g.lastActive);
    return true;
  });

  const currentUser = members[0];

  return (
    <div className="min-h-screen" style={{ backgroundColor: '#0D0D0D' }}>
      {/* Nav */}
      <nav className="sticky top-0 z-50 backdrop-blur-md" style={{ backgroundColor: 'rgba(13,13,13,0.8)', borderBottom: '1px solid #2A2A2A' }}>
        <div className="max-w-7xl mx-auto px-4 h-14 flex items-center gap-4">
          <Link href="/" className="flex items-center gap-2 shrink-0">
            <CompassMark size={28} />
            <span className="hidden sm:block text-lg" style={{ fontFamily: 'var(--font-instrument-serif), Georgia, serif', color: '#F0EDE8' }}>Narrative Atlas</span>
          </Link>
          <div className="flex-1" />
          <Avatar initials={currentUser.avatar} size="sm" />
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 py-10">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4 }}
        >
          <h1 style={{ fontFamily: 'var(--font-instrument-serif), Georgia, serif', fontSize: '3rem', color: '#F0EDE8' }}>Communities</h1>
          <p className="mt-2" style={{ color: '#8A8580' }}>Explore groups of young changemakers working across borders.</p>
        </motion.div>

        {/* Filter bar */}
        <div className="mt-8 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div className="flex gap-1 rounded-lg p-1" style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A' }}>
            {filters.map((f) => (
              <button
                key={f}
                onClick={() => setActiveFilter(f)}
                className="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                style={{
                  backgroundColor: activeFilter === f ? '#1E1E1E' : 'transparent',
                  color: activeFilter === f ? '#F0EDE8' : '#8A8580',
                }}
              >
                {f}
              </button>
            ))}
          </div>

          <div className="relative flex-1 max-w-xs">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2" size={15} style={{ color: '#8A8580' }} />
            <input
              type="search"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search communities..."
              className="w-full rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none transition-colors"
              style={{ backgroundColor: '#161616', border: '1px solid #2A2A2A', color: '#F0EDE8' }}
            />
          </div>

          <p className="text-sm ml-auto" style={{ color: '#8A8580' }}>{filtered.length} communities</p>
        </div>

        {/* Group grid */}
        <div className="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {filtered.map((group, i) => (
            <motion.div
              key={group.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: i * 0.07 }}
            >
              <GroupCard group={group} />
            </motion.div>
          ))}
        </div>

        {filtered.length === 0 && (
          <div className="text-center py-24">
            <p style={{ color: '#8A8580' }}>No communities found.</p>
          </div>
        )}
      </main>
    </div>
  );
}
