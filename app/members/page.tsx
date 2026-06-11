'use client'

import { useState } from 'react'
import { motion } from 'framer-motion'
import { Search } from 'lucide-react'
import Link from 'next/link'
import CompassMark from '@/components/CompassMark'
import MemberCard from '@/components/MemberCard'
import { members } from '@/lib/mockData'

export default function MembersPage() {
  const [searchQuery, setSearchQuery] = useState('')

  const filtered = members.filter(m =>
    m.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    m.location.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="min-h-screen bg-background">
      <nav className="sticky top-0 z-50 bg-background/80 backdrop-blur-md border-b border-border">
        <div className="max-w-7xl mx-auto px-4 h-14 flex items-center gap-4">
          <Link href="/" className="flex items-center gap-2">
            <CompassMark size={28} />
            <span className="font-serif text-lg hidden sm:block">Narrative Atlas</span>
          </Link>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto px-4 py-8">
        <motion.div
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8"
        >
          <h1 className="font-serif text-4xl text-text-primary mb-2">Members</h1>
          <p className="text-text-secondary">{members.length} changemakers from around the world.</p>
        </motion.div>

        <div className="relative mb-6 max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-secondary" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search members…"
            className="bg-surface border border-border rounded-lg pl-9 pr-4 py-2 text-sm text-text-primary placeholder-text-secondary focus:outline-none focus:border-accent transition-colors w-full"
          />
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {filtered.map((member, i) => (
            <motion.div
              key={member.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: i * 0.05 }}
            >
              <MemberCard member={member} />
            </motion.div>
          ))}
        </div>

        {filtered.length === 0 && (
          <div className="text-center py-16">
            <p className="text-text-secondary">No members found.</p>
          </div>
        )}
      </div>
    </div>
  )
}
