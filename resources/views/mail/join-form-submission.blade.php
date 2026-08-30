<x-mail::message>
# 

**Name:** {{ $submission->name }}

**Email:** {{ $submission->email }}

**Organization:** {{ $submission->organization ?: '—' }}

**Phone:** {{ $submission->phone ?: '—' }}

**Role:** {{ $submission->participationPathway?->title ?: '—' }}

**Message:**

{{ $submission->message }}

<x-mail::button :url="$url">
Open in the admin panel
</x-mail::button>

Submitted {{ $submission->created_at?->format('d M Y H:i') }}
</x-mail::message>
