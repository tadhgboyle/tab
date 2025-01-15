@auth
<div class="bg-gray-50 border-y">
    @impersonating
    <div class="text-center text-sm py-2">
        <p>🕵️ You're impersonating {{ auth()->user()->full_name }}, 
            <a href="{{ route('impersonate.leave') }}" class="text-blue-600 hover:underline">click here to exit</a>
        </p>
    </div>
    @endImpersonating

    @if(auth()->user()->role->staff && auth()->user()->family)
    <div class="text-center text-sm py-2">
        <p>
            @if(\Str::contains(request()->url(), '/admin'))
                🏛 You're in an admin context, 
                <a href="{{ route('family_view', auth()->user()->family) }}" class="text-blue-600 hover:underline">click here to view your family</a>
            @else
                🧑‍💼️ You're in a family context
            @endif
        </p>
    </div>
    @endif
</div>

<nav class="bg-white border-b mb-5 px-52">
    <div class="container">
        <div class="flex justify-between items-center">
            <!-- Left Navigation Links -->
            <div class="flex space-x-3">
                @if(auth()->user()->family)
                    <x-nav-link :route="route('family_view', auth()->user()->family)" :active="request()->routeIs('family_view')">
                        🏠 Family
                    </x-nav-link>
                @endif

                @permission(\App\Helpers\Permission::DASHBOARD)
                    <x-nav-link :route="route('dashboard')" :active="request()->routeIs('dashboard')">
                        📊 Dashboard
                    </x-nav-link>
                @endpermission

                @permission(\App\Helpers\Permission::CASHIER_CREATE)
                    <x-nav-link :route="route('cashier')" :active="request()->routeIs('cashier')">
                        🛒 Cashier
                    </x-nav-link>
                @endpermission

                @permission(\App\Helpers\Permission::ORDERS)
                    <x-nav-link :route="route('orders_list')" :active="request()->routeIs('orders_list')">
                        📦 Orders
                    </x-nav-link>
                @endpermission

                @permission(\App\Helpers\Permission::PRODUCTS)
                    <x-nav-link :route="route('products_list')" :active="request()->routeIs('products_list')">
                        🏷  Products
                    </x-nav-link>
                @endpermission

                @permission(\App\Helpers\Permission::ACTIVITIES)
                    <x-nav-link :route="route('activities_calendar')" :active="request()->routeIs('activities_calendar')">
                        🗓 Activities
                    </x-nav-link>
                @endpermission

                @permission(\App\Helpers\Permission::USERS)
                    <x-nav-link :route="route('users_list')" :active="request()->routeIs('users_list')">
                        👥 Users
                    </x-nav-link>
                @endpermission

                @permission(\App\Helpers\Permission::FAMILIES)
                    <x-nav-link :route="route('families_list')" :active="request()->routeIs('families_list') || request()->routeIs('families_create')">
                        👪 Families
                    </x-nav-link>
                @endpermission
            </div>

            <!-- Right Navigation Links -->
            <div class="flex space-x-3">
                @permission(\App\Helpers\Permission::SETTINGS)
                    <x-nav-link :route="route('settings')" :active="request()->routeIs('settings')">
                        ⚙️ Settings
                    </x-nav-link>
                @endpermission
            </div>
        </div>
    </div>
</nav>
@endauth
