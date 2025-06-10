<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/sihp-logo.png') }}"
                     alt="SiHP Logo"
                     class="w-auto drop-shadow-lg" style="height: 150px;" />
            </div>
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-6 bg-white p-8 rounded-lg shadow-lg">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" class="text-lg font-semibold text-gray-700" />
                <x-input id="email" class="block mt-2 w-full border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                         type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" class="text-lg font-semibold text-gray-700" />
                <x-input id="password" class="block mt-2 w-full border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                         type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" class="text-blue-600" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </div>

                @if (Route::has('password.request'))
                    <a class="text-sm text-blue-500 hover:text-blue-700 underline" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div class="flex items-center justify-center mt-6">
                <x-button class="w-full py-3 px-6 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition duration-300">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
