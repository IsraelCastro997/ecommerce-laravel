@props(['name', 'size' => 20])
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('search')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/>@break
        @case('filter')<path d="M4 7h16M4 17h16"/><circle cx="8" cy="7" r="2" fill="currentColor"/><circle cx="16" cy="17" r="2" fill="currentColor"/>@break
        @case('grid')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
        @case('list')<path d="M9 5h12M9 12h12M9 19h12M3 5h1M3 12h1M3 19h1"/>@break
        @case('bag')<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>@break
        @case('arrow')<path d="M4 12h16m-6-6 6 6-6 6"/>@break
        @case('chevron')<path d="m8 4 8 8-8 8"/>@break
        @case('close')<path d="m6 6 12 12M6 18 18 6"/>@break
        @case('plus')<path d="M12 5v14M5 12h14"/>@break
        @case('mountain')<path d="m2 20 7-14 4 7 3-5 6 12H2Z"/><path d="m6 12 3 2 2-2"/>@break
        @case('check')<path d="m5 12 4 4L20 5"/>@break
        @case('user')<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
        @case('eye')<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>@break
    @endswitch
</svg>
