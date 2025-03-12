<x-mail::message>
# {{ $subject }}

@if ($body)
{{ $body }}
@endif
</x-mail::message>
