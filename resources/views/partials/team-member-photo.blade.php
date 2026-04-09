@php
    use App\Support\TeamDirectory;
    /** @var array<string, mixed> $member */
    $variant = $variant ?? 'card';
    $useAvatar = TeamDirectory::imageIsGenericPlaceholder((string) ($member['image'] ?? ''));
    $initials = TeamDirectory::initialsFromMemberName((string) ($member['name'] ?? ''));
    $imageUrl = $useAvatar ? '' : TeamDirectory::memberPortraitImageUrl((string) ($member['image'] ?? ''));
@endphp
@if($useAvatar)
    <div class="team-portrait-avatar team-portrait-avatar--{{ $variant }}" role="img" aria-label="{{ $member['name'] }}">{{ $initials }}</div>
@else
    @php
        $extraImgClass = trim((string) ($imgClass ?? ''));
        $imgClasses = trim($variant === 'card' ? 'avatar-img '.$extraImgClass : $extraImgClass);
    @endphp
    <img
        @if($imgClasses !== '') class="{{ $imgClasses }}" @endif
        src="{{ $imageUrl }}"
        alt="{{ $member['name'] }} portrait"
        @if($variant === 'card') loading="lazy" @endif
    >
@endif
