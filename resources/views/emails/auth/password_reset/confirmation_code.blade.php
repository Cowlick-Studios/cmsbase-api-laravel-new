<x-mail::message>
# Reset Password

Use the following code to reset your password.

## {{$verificationCode}}

<x-mail::button :url="$actionUrl">
Confirm
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
