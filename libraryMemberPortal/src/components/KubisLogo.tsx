import { APP_NAME, APP_TAGLINE } from '../constants/branding'

const sizeClass = {
  sm: 'h-9 w-9',
  md: 'h-11 w-11',
  lg: 'h-16 w-16',
  header: 'h-9 w-9 md:h-12 md:w-12 lg:h-14 lg:w-14',
}

interface KubisLogoProps {
  size?: keyof typeof sizeClass
  className?: string
}

export function KubisLogo({ size = 'md', className = '' }: KubisLogoProps) {
  return (
    <svg
      viewBox="0 0 48 48"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={`shrink-0 ${sizeClass[size]} ${className}`}
      aria-hidden
    >
      <rect width="48" height="48" rx="12" fill="#1e3a5f" />
      <path
        d="M24 36C24 36 10 31 8 28V14.5C8 12.8 9.4 12 10.8 12.6L24 18.5V36Z"
        fill="#f1f5f9"
      />
      <path
        d="M24 36C24 36 38 31 40 28V14.5C40 12.8 38.6 12 37.2 12.6L24 18.5V36Z"
        fill="#ffffff"
      />
      <path d="M24 18.5V36" stroke="#f59e0b" strokeWidth="1.5" strokeLinecap="round" />
      <path
        d="M13 17.5L20 19.5M13 21.5L19 23M13 25.5L18 27"
        stroke="#1e3a5f"
        strokeWidth="1"
        strokeLinecap="round"
        opacity="0.18"
      />
      <path
        d="M28 19.5L35 17.5M29 23L35 21M30 27L35 25.5"
        stroke="#1e3a5f"
        strokeWidth="1"
        strokeLinecap="round"
        opacity="0.18"
      />
      <path
        d="M24 12V18.5"
        stroke="#f59e0b"
        strokeWidth="2.5"
        strokeLinecap="round"
      />
      <circle cx="24" cy="11" r="2" fill="#f59e0b" />
    </svg>
  )
}

interface KubisBrandProps {
  size?: 'sm' | 'md' | 'lg' | 'header'
  variant?: 'light' | 'dark'
  showTagline?: boolean
  subtitle?: string
  layout?: 'horizontal' | 'vertical'
  className?: string
}

const titleSizeClass = {
  sm: 'text-sm',
  md: 'text-base md:text-lg',
  lg: 'text-2xl',
  header: 'text-sm md:text-lg lg:text-xl',
}

const taglineSizeClass = {
  sm: 'text-[10px] md:text-xs',
  md: 'text-[10px] md:text-xs',
  lg: 'text-sm',
  header: 'text-[10px] md:text-xs lg:text-sm',
}

export function KubisBrand({
  size = 'md',
  variant = 'dark',
  showTagline = true,
  subtitle,
  layout = 'horizontal',
  className = '',
}: KubisBrandProps) {
  const isLight = variant === 'light'
  const isVertical = layout === 'vertical'
  const logoSize = size === 'header' ? 'header' : size

  return (
    <div
      className={`flex items-center gap-2.5 md:gap-3 ${isVertical ? 'flex-col text-center' : ''} ${className}`}
    >
      <KubisLogo size={logoSize} className="shadow-sm" />
      <div className={isVertical ? 'flex flex-col items-center' : 'min-w-0'}>
        <p
          className={`font-bold leading-tight tracking-tight ${titleSizeClass[size]} ${
            isLight ? 'text-white' : 'text-brand-700'
          }`}
        >
          {APP_NAME}
        </p>
        {showTagline && (
          <p
            className={`mt-0.5 leading-snug ${taglineSizeClass[size]} ${
              isLight ? 'text-brand-200' : 'text-slate-500'
            }`}
          >
            {APP_TAGLINE}
          </p>
        )}
        {subtitle && (
          <p className="mt-0.5 text-[11px] text-slate-500 md:text-xs lg:text-sm">{subtitle}</p>
        )}
      </div>
    </div>
  )
}
