'use client';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import CompassMark from './CompassMark';
import Avatar from './ui/Avatar';
import { Bell, MessageSquare, Search } from 'lucide-react';
import { members } from '@/lib/mockData';

const currentUser = members[0];

export default function TopNav() {
  const pathname = usePathname();

  const navLinks = [
    { href: '/', label: 'Home' },
    { href: '/groups', label: 'Groups' },
    { href: '/members', label: 'Members' },
  ];

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 h-14 bg-[#0D0D0D]/90 backdrop-blur-md border-b border-[#1E1E1E] flex items-center px-6 gap-6">
      <Link href="/" className="flex items-center gap-2.5 shrink-0">
        <CompassMark size={28} animate />
        <span className="font-display text-[#F0EDE8] text-base italic hidden sm:block">Narrative Atlas</span>
      </Link>

      {/* Nav links */}
      <div className="hidden md:flex items-center gap-1 ml-2">
        {navLinks.map(link => (
          <Link
            key={link.href}
            href={link.href}
            className={`px-3 py-1.5 rounded-lg text-sm transition-all ${
              pathname === link.href
                ? 'text-[#E8A838] bg-[#E8A838]/10'
                : 'text-[#8A8580] hover:text-[#F0EDE8] hover:bg-[#1E1E1E]'
            }`}
          >
            {link.label}
          </Link>
        ))}
      </div>

      {/* Search */}
      <div className="flex-1 max-w-sm mx-auto hidden sm:block">
        <div className="flex items-center gap-2 bg-[#161616] border border-[#2A2A2A] rounded-lg px-3 py-2 text-sm text-[#4A4845] hover:border-[#3A3A3A] transition-colors cursor-text">
          <Search size={14} />
          <span>Search groups, members, docs…</span>
          <kbd className="ml-auto text-xs bg-[#2A2A2A] px-1.5 py-0.5 rounded font-mono">⌘K</kbd>
        </div>
      </div>

      {/* Right icons */}
      <div className="ml-auto flex items-center gap-3">
        <button className="relative p-2 text-[#8A8580] hover:text-[#F0EDE8] transition-colors rounded-lg hover:bg-[#1E1E1E]">
          <MessageSquare size={18} />
          <span className="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-[#E8A838] rounded-full" />
        </button>
        <button className="relative p-2 text-[#8A8580] hover:text-[#F0EDE8] transition-colors rounded-lg hover:bg-[#1E1E1E]">
          <Bell size={18} />
          <span className="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-[#E85555] rounded-full" />
        </button>
        <Link href={`/members/${currentUser.id}`}>
          <Avatar initials={currentUser.avatar} color="#E8A838" size={30} online />
        </Link>
      </div>
    </nav>
  );
}
