<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Show __NAME__') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div
                class="py-3 px-6 mb-4 bg-white overflow-hidden shadow-sm sm:rounded-lg border-b border-gray-200 text-gray-700 text-sm font-medium">
                <ul class="flex">
                    <li><a href="{{ route('dashboard') }}" class="underline">{{ __('Dashboard') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('__LPNAME__.index') }}" class="underline">{{ __('__PNAME__') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>{{ __('Show') }}</li>
                </ul>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-b border-gray-200">

                <div class="p-6">

                    @if (session()->has('flash.message'))
                        <div class="my-5 font-medium text-green-600">
                            {{ session('flash.message') }}
                            <button class="w-4" type="button" data-dismiss="alert" aria-label="Close"
                                onclick="this.parentElement.remove();">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path strokelinecap="round" strokelinejoin="round" strokewidth="{2}"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    {{ __('Title') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-700 sm:mt-0 sm:col-span-2">
                                    {{ $__CNAME__->title }}
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    {{ __('Description') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-700 sm:mt-0 sm:col-span-2">
                                    {{ $__CNAME__->description }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('__LPNAME__.edit', $__CNAME__->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Edit') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
