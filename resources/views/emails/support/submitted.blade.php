<x-mail::message>
# Support Request: {{ $requestSubject }}

**From:** {{ $userName }} ({{ $userEmail }})

**Tenant:** {{ $tenantDomain }} (ID: {{ $tenantId }})

---

**Message:**

{!! nl2br(e($requestMessage)) !!}

---

@if (count($uploadedFiles) > 0)
**Attachments:** {{ count($uploadedFiles) }} file(s) attached
@endif

This is an automated message from {{ config('app.name') }}.
</x-mail::message>
