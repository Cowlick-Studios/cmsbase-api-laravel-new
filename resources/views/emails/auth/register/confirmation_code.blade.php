<x-mail::message>
# Confirm Account

Use the following code to verify your account.

## {{$verificationCode}}

<x-mail::button :url="$actionUrl">
Verify
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
