import type { Metadata } from 'next';
import { Inter, Instrument_Serif } from 'next/font/google';
import './globals.css';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
  variable: '--font-inter',
});

const instrumentSerif = Instrument_Serif({
  subsets: ['latin'],
  weight: ['400'],
  style: ['normal', 'italic'],
  display: 'swap',
  variable: '--font-instrument-serif',
});

export const metadata: Metadata = {
  title: 'Narrative Atlas',
  description: 'Where young changemakers connect, collaborate, and act.',
  keywords: ['youth', 'exchange', 'collaboration', 'civic', 'global'],
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html
      lang="en"
      className={`${inter.variable} ${instrumentSerif.variable}`}
      style={{ background: '#0D0D0D' }}
    >
      <body className="min-h-screen" style={{ background: '#0D0D0D', color: '#F0EDE8' }}>
        {children}
      </body>
    </html>
  );
}
