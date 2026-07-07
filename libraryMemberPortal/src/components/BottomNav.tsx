import { BookCopy, BookOpen, CalendarCheck, Heart, User } from 'lucide-react'
import { NavLink } from 'react-router-dom'

const navItems = [
  { to: '/', icon: BookOpen, label: 'Kitaplar' },
  { to: '/favoriler', icon: Heart, label: 'Favoriler' },
  { to: '/rezervasyonlar', icon: CalendarCheck, label: 'Rezervasyon' },
  { to: '/oduncler', icon: BookCopy, label: 'Ödünçler' },
  { to: '/profil', icon: User, label: 'Profil' },
]

export function BottomNav() {
  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200/80 bg-white/95 backdrop-blur-lg safe-bottom md:hidden">
      <div className="mx-auto flex max-w-lg items-stretch justify-around px-2 pb-1 pt-1">
        {navItems.map(({ to, icon: Icon, label }) => (
          <NavLink
            key={to}
            to={to}
            end={to === '/'}
            className={({ isActive }) =>
              `flex flex-1 flex-col items-center gap-0.5 rounded-xl px-1 py-2 text-[9px] font-medium transition-colors sm:px-2 sm:text-[10px] ${
                isActive
                  ? 'text-brand-600'
                  : 'text-slate-400 hover:text-slate-600'
              }`
            }
          >
            {({ isActive }) => (
              <>
                <div
                  className={`flex h-8 w-8 items-center justify-center rounded-xl transition-colors ${
                    isActive ? 'bg-brand-50' : ''
                  }`}
                >
                  <Icon className={`h-5 w-5 ${isActive ? 'stroke-[2.5]' : ''}`} />
                </div>
                <span>{label}</span>
              </>
            )}
          </NavLink>
        ))}
      </div>
    </nav>
  )
}

export function DesktopNav() {
  return (
    <nav className="hidden md:flex md:items-center md:gap-1">
      {navItems.map(({ to, icon: Icon, label }) => (
        <NavLink
          key={to}
          to={to}
          end={to === '/'}
          className={({ isActive }) =>
            `flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-colors ${
              isActive
                ? 'bg-brand-600 text-white'
                : 'text-slate-600 hover:bg-slate-100'
            }`
          }
        >
          <Icon className="h-4 w-4" />
          {label}
        </NavLink>
      ))}
    </nav>
  )
}
