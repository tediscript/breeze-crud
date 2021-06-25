<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('__PNAME__ Table') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div
                class="py-3 px-5 mb-4 bg-white overflow-hidden shadow-sm sm:rounded-lg border-b border-gray-200 text-gray-700 text-sm font-medium">
                <ul class="flex">
                    <li><a href="{{ route('dashboard') }}" class="underline">{{ __('Dashboard') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li>{{ __('__PNAME__') }}</li>
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

                    <div class="flex items-center justify-end">
                        <a href="{{ route('__LPNAME__.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Create') }}
                        </a>
                    </div>

                    <!-- table start -->
                    <div class="flex items-center justify-end mt-3">
                        <table class="w-full border border-gray-200">
                            <thead>
                                <tr class="bg-gray-200 text-gray-600 text-sm leading-normal">
                                    <th class="p-3 text-right">{{ __('ID') }}</th>
                                    <th class="p-3 text-left">{{ __('Title') }}</th>
                                    <th class="p-3 text-left">{{ __('Description') }}</th>
                                    <th class="p-3 text-right">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @foreach ($__LPNAME__ as $__CNAME__)
                                    <tr class="border-b border-gray-200">
                                        <td class="p-3 text-right">{{ $__CNAME__->id }}</td>
                                        <td class="p-3">{{ $__CNAME__->title }}</td>
                                        <td class="p-3">{{ $__CNAME__->description }}</td>
                                        <td class="p-3 text-right">
                                            <form method="post"
                                                action="{{ route('__LPNAME__.destroy', $__CNAME__->id) }}">
                                                @method('delete')
                                                @csrf
                                                <a href="{{ route('__LPNAME__.show', $__CNAME__->id) }}"
                                                    class="mb-1 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    {{ __('Show') }}
                                                </a>
                                                <a href="{{ route('__LPNAME__.edit', $__CNAME__->id) }}"
                                                    class="mb-1 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    {{ __('Edit') }}
                                                </a>
                                                <button type="submit"
                                                    onclick="return confirm('{{ __('Are you sure?') }}')"
                                                    class="mb-1 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- table end -->

                    <div class="mt-3">
                        {{ $__LPNAME__->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
