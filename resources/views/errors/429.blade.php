@extends('layouts.app')

@section('title', 'Too Many Requests')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-yellow-100">
                <svg class="h-12 w-12 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Too Many Requests
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                You've made too many requests in a short period of time.
            </p>
        </div>
        
        <div class="rounded-md bg-yellow-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Rate Limit Exceeded
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            @if(isset($retryAfter))
                                Please wait {{ ceil($retryAfter / 60) }} minute(s) before trying again.
                            @else
                                Please wait a moment before trying again.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="text-sm text-gray-500">
                <p class="font-medium">Why did this happen?</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>Too many login attempts</li>
                    <li>Too many form submissions</li>
                    <li>Too many API requests</li>
                    <li>Automated bot detection</li>
                </ul>
            </div>

            <div class="text-sm text-gray-500">
                <p class="font-medium">What can you do?</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>Wait for the specified time period</li>
                    <li>Reduce the frequency of your requests</li>
                    <li>Contact support if you believe this is an error</li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col space-y-3">
            <a href="{{ url('/dashboard') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Go to Dashboard
            </a>
            <a href="{{ url('/') }}" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Go to Homepage
            </a>
        </div>

        <div class="text-xs text-center text-gray-400 mt-4">
            Error Code: 429 - Too Many Requests
        </div>
    </div>
</div>
@endsection
