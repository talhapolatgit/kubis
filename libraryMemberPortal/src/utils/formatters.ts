export function coverUrl(path: string | null | undefined): string | null {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/')) {
    const base =
      import.meta.env.VITE_API_BASE_URL?.replace(/\/api\/mobile\/?$/, '') ||
      'http://localhost:85'
    return `${base}${path}`
  }
  return path
}

export function formatDateShort(dateStr: string | null | undefined): string {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleDateString('tr-TR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

export function formatDate(dateStr: string | null | undefined): string {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleDateString('tr-TR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

export function formatDateTime(dateStr: string | null | undefined): string {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleString('tr-TR', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return dateStr
  }
}

export function statusColor(durum: string): string {
  switch (durum) {
    case 'Rafta':
      return 'bg-emerald-100 text-emerald-700'
    case 'Ödünç':
      return 'bg-amber-100 text-amber-700'
    case 'Rezerve':
      return 'bg-blue-100 text-blue-700'
    default:
      return 'bg-slate-100 text-slate-600'
  }
}

export function calculateAge(birthDate: string): number {
  const birth = new Date(birthDate)
  const today = new Date()
  let age = today.getFullYear() - birth.getFullYear()
  const m = today.getMonth() - birth.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--
  return age
}

export function isMinor(birthDate: string): boolean {
  return calculateAge(birthDate) < 18
}
