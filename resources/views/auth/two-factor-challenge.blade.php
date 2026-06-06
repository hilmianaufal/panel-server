<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Masukkan kode 6 digit dari Google Authenticator.
    </div>

    @if (session('error'))
        <div class="mb-4 text-sm text-red-600">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div>
            <x-input-label for="otp" value="Kode OTP" />

            <x-text-input id="otp"
                class="block mt-1 w-full text-center tracking-[0.4em]"
                type="text"
                name="otp"
                required
                autofocus
                autocomplete="one-time-code" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Verifikasi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>