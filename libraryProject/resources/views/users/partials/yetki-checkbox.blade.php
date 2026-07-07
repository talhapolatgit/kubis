@php
    $assignment = $assigned->get($perm->id);
    $checked = (bool) $assignment;
@endphp
<label class="perm {{ $checked ? 'is-checked' : '' }}">
    <input type="checkbox" name="permissions[]" value="{{ $perm->legacy_no }}" {{ $checked ? 'checked' : '' }} />
    <div>
        <!-- <div class="perm-title">
            <span class="perm-no">{{ $perm->legacy_no }}</span>
        </div> -->
        <div class="perm-desc">{{ $perm->label }}</div>
        @if($checked)
            <div class="perm-meta">
                @if($assignment->pivot->granted_by)
                    <div><strong>Tanımlayan:</strong> {{ $granters[$assignment->pivot->granted_by] ?? '—' }}</div>
                @else
                    <div><strong>Tanımlayan:</strong> Sistem (eski kayıt)</div>
                @endif
                @if($assignment->pivot->created_at)
                    <div><strong>Tarih:</strong> {{ \Carbon\Carbon::parse($assignment->pivot->created_at)->format('d.m.Y H:i') }}</div>
                @endif
            </div>
        @endif
    </div>
</label>
