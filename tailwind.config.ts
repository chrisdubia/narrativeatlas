import type { Config } from 'tailwindcss'

const config: Config = {
  content: [
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        background: '#0D0D0D',
        surface: '#161616',
        'surface-elevated': '#1E1E1E',
        border: '#2A2A2A',
        'text-primary': '#F0EDE8',
        'text-secondary': '#8A8580',
        accent: '#E8A838',
        'accent-hover': '#F0B84A',
        'accent-subtle': 'rgba(232, 168, 56, 0.08)',
        success: '#4CAF7D',
        danger: '#E85555',
      },
      fontFamily: {
        serif: ['var(--font-instrument-serif)', 'Georgia', 'serif'],
        sans: ['var(--font-inter)', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
export default config
