@auth
<aside class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0">
    <div class="h-full flex flex-col px-3 py-4 overflow-y-auto bg-gray-50 border-r border-gray-200">
        <!-- Navigation -->
        <div class="flex-1">
            <div class="flex items-center ps-2.5 mb-5">
                <span class="self-center text-xl font-semibold whitespace-nowrap">tab</span>
            </div>

            <ul class="space-y-2 font-medium text-md">
                @if(auth()->user()->family)
                    <x-nav-link :routes="['family_view']" :icon="'🏠'" :name="'Family'" :url="route('family_view', auth()->user()->family)" />
                @endif

                @permission(\App\Helpers\Permission::DASHBOARD)
                    <x-nav-link :routes="['dashboard']" :icon="'📊'" :name="'Dashboard'" :url="route('dashboard')" />
                @endpermission

                @permission(\App\Helpers\Permission::CASHIER_CREATE)
                    <x-nav-link :routes="['cashier']" :icon="'🛒'" :name="'Cashier'" :url="route('cashier')" />
                @endpermission

                @permission(\App\Helpers\Permission::ORDERS)
                    <x-nav-link :routes="['orders_list']" :icon="'📦'" :name="'Orders'" :url="route('orders_list')" />
                @endpermission

                @permission(\App\Helpers\Permission::PRODUCTS)
                    <x-nav-link :routes="['products_list']" :icon="'🏷'" :name="'Products'" :url="route('products_list')" :sublinks="[
                        ['route' => 'products_ledger', 'name' => 'Ledger'],
                    ]" />
                @endpermission

                @permission(\App\Helpers\Permission::PRODUCTS)
                    <x-nav-link :routes="['activities_calendar']" :icon="'📅'" :name="'Activities'" :url="route('activities_calendar')" :sublinks="[
                        ['route' => 'activities_list', 'name' => 'List'],
                    ]" />
                @endpermission

                @permission(\App\Helpers\Permission::USERS)
                    <x-nav-link :routes="['users_list']" :icon="'👥'" :name="'Users'" :url="route('users_list')"/>
                @endpermission

                @permission(\App\Helpers\Permission::FAMILIES)
                    <x-nav-link :routes="['families_list']" :icon="'👪'" :name="'Families'" :url="route('families_list')"/>
                @endpermission
            </ul>
        </div>

        <!-- Settings Section -->
        @permission(\App\Helpers\Permission::SETTINGS)
            <div class="mt-4 border-t border-gray-200 pt-4">
                <ul class="space-y-2 font-medium text-md">
                    <x-nav-link :routes="['settings']" :icon="'⚙️'" :name="'Settings'" :url="route('settings')"/>
                </ul>
            </div>
        @endpermission

        <!-- User Info -->
        <div class="border-t border-gray-200 pt-4 mt-4">
            <div class="flex items-center">
                <div class="ms-3">
                    <p class="text-sm text-gray-900 font-medium">{{ auth()->user()->full_name }}</p>
                    <p class="text-xs text-gray-600">{{ auth()->user()->role->name }}</p>
                </div>
            </div>
            <a href="{{ route('logout') }}" class="flex items-center mt-3 py-2 text-red-600 rounded-lg hover:bg-gray-100 group">
                <span class="ms-3 text-sm">Logout</span>
            </a>
        </div>
    </div>
</aside>
@endauth
