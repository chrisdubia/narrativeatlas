import type { Metadata } from 'next'
import { Epilogue, DM_Mono } from 'next/font/google'
import './globals.css'

const epilogue = Epilogue({
  subsets: ['latin'],
  weight: ['300', '400', '500'],
  style: ['normal', 'italic'],
  variable: '--font-epilogue',
})

const dmMono = DM_Mono({
  subsets: ['latin'],
  weight: ['300', '400'],
  variable: '--font-dm-mono',
})

export const metadata: Metadata = {
  title: 'Narrative Atlas',
  description: 'Where young people meet the world.',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <body className={`${epilogue.variable} ${dmMono.variable} antialiased`}>
        {children}
      </body>
    </html>
  )
}
