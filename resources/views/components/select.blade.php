@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:focus:ring-indigo-600 dark:focus:border-indigo-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300']) !!}>
    {{ $slot }}
</select>
