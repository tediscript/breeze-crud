<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create __NAME__') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div
                class="py-3 px-5 mb-4 bg-white overflow-hidden shadow-sm sm:rounded-lg border-b border-gray-200 text-gray-700 text-sm font-medium">
                <ul class="flex">
                    <li><a href="{{ route('dashboard') }}" class="underline">{{ __('Dashboard') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('__LPNAME__.index') }}" class="underline">{{ __('__PNAME__') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>{{ __('Create') }}</li>
                </ul>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-b border-gray-200">

                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600">
                                {{ __('Whoops! Something went wrong.') }}
                            </div>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('__LPNAME__.store') }}">
                        @csrf

                        <div class="mt-4">
                            <label class="block font-medium text-sm text-gray-700" for="title">
                                {{ __('Title') }}
                            </label>
                            <input
                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full"
                                id="title" type="text" name="title" value="{{ old('title') }}" required />
                        </div>

                        <div class="mt-4">
                            <label class="block font-medium text-sm text-gray-700" for="description">
                                {{ __('Description') }}
                            </label>
                            <textarea
                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full"
                                id="description" name="description" cols="100"
                                rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
