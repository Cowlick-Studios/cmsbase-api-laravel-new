<x-mail::message>
# Email Confirmation

Please confirm your email address.

**Code:** {{$code}}

<x-mail::button :url="$url">
Confirm
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
