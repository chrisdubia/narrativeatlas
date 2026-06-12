'use client'

interface Tab {
  id: string
  label: string
  icon?: React.ReactNode
}

interface TabBarProps {
  tabs: Tab[]
  activeTab: string
  onChange: (id: string) => void
}

export default function TabBar({ tabs, activeTab, onChange }: TabBarProps) {
  return (
    <div className="flex gap-7 border-b overflow-x-auto" style={{ borderColor: '#E6E0D6' }}>
      {tabs.map((tab) => (
        <button
          key={tab.id}
          onClick={() => onChange(tab.id)}
          className="flex items-center gap-1.5 pb-4 pt-1 whitespace-nowrap relative transition-colors"
          style={{
            fontFamily: 'var(--font-dm-mono), DM Mono, monospace',
            fontSize: '11px',
            letterSpacing: '0.06em',
            textTransform: 'uppercase',
            color: activeTab === tab.id ? '#1C1C1A' : '#8A8880',
            borderBottom: activeTab === tab.id ? '2px solid #C2683D' : '2px solid transparent',
            marginBottom: '-1px',
          }}
        >
          {tab.icon}
          {tab.label}
        </button>
      ))}
    </div>
  )
}
