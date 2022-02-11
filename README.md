# tab

A lightweight, selfhosted web app that camps, resorts and schools can use as a Point Of Sale system.

#### Credits

- :page_with_curl: [DataTables](https://datatables.net) for the fast and interactive tables.
- :bar_chart: [ChartJS](https://www.chartjs.org) for the beautiful statistics charts, and [ConsoleTVs/Charts](https://github.com/ConsoleTVs/Charts) for the Laravel package.
- :art: [Bulma](https://bulma.io) for their lightweight, easy to use and great-looking CSS framework.
- :white_check_mark::x: [Switchery](https://github.com/abpetkov/switchery) for the simple to use & nice looking Switches replacement for ugly HTML checkboxes.
- :calendar: [FullCalendar](https://fullcalendar.io) for their simple and responsive calendar library.

Documentation (in the form of a printable user handbook) is being written. 

## Features

- User control
    - Superusers can create as many roles as they want, and grant them specific permissions as well as hierarchy.
        - Some permissions include: 
            - `users_list`: This role can view the user list.
            - `users_manage`: This role can edit/create users (but only edit users which have a role lower than theirs in terms of hierarchy).
            - `products_adjust`: This role can adjust product stock, but they will need `products_list` and `products_manage` in order to list or create products.
            - `settings_categories_manage`: This role can create/edit product categories in the Settings page.
            - And many more! I try to add as much customizability as possible for permissions.
    - Users can be edited anytime, with an easy-to-use interface.
    - Users can be (soft) deleted anytime.
- "Category" organization theory
    - Categories are used to organize products and activities.
    - Each Category can be set to be strictly for Products, or Activities or both.
    - When making or editing a Product or Activity, only applicable Categories will be shown.
    - See "Parental control" to see how Categories are used further.
- Parental control
    - Parents or guardians can set their children a certain amount of money to spend in each "category".
    - These limits can be set per-category to either day or week.
- Product control and editing
    - Change the name and price anytime.
    - Optional PST charge on a per-item basis.
- Activity scheduling
    - Create Activities which last up to many days.
    - Add price, location, and extra info if you want.
    - Users can register for activities.
- Inventory/Stock management
    - Depending on each product's settings (See next point), during an order, stock will be automatically removed as per quantity.
    - Each product has some settings:
        - "Stock" / "Unlimited Stock" -> Set stock count for this product, or set it to be unlimited.
        - "Box Size" -> When receiving, instead of counting/multiplying all the boxes received, set this and receive using boxes.
            - Example: A box of Skittles might have 16 Skittle packs in it. If 4 boxes arrived, instead of calculating 16*4, add 4 boxes.
        - "Override Stock" -> Override the stock count for this item.
    - Fast and easy to use stock adjustment page.
        - Uses AJAX to snappily load all available adjustments for the selected product.
- Live preview of price (with taxes, quantities, etc) on sidebar during every purchase. If the user does not have enough money the submit button will be disabled (Backend check as well though!)
- Flexible return system
    - Either return a whole order, or:
    - Return an individual item from an order. A counter shows how many are left to be returned. Multiple backend checks to verify validity of return.
- Editable GST and PST percentages.
- Detailed user history and order list pages with fully interactive and searchable tables.
- Detailed statistics charts
    - View # of orders and returns.
    - View all products and how many times they have sold.
    - More planned charts in the [Roadmap](#roadmap).

## Deployment

#### :whale: Docker
- Clone the repository to your server.
- Edit the `docker-compose.yml` file to configure it for your specific needs (should work by default).
- Edit the `.env` file to set your database credentials.
- Run `docker-compose up` to start the containers.
- Run `docker-compose exec app php artisan key:generate` to generate a new key.
- Run `docker-compose exec app php artisan migrate:fresh --seed` to create the database tables needed for tab and insert the admin account.
- Browse to `http://ip:8080`, and login with `admin` and `123456`!


## Screenshots

#### First Cashier Page
![First Cashier Page](https://i.imgur.com/6K6JtQK.png)

#### Second Cashier Page
![Second Cashier Page](https://i.imgur.com/LFTAmZu.png)

#### User Information Page
![User Info Page](https://i.imgur.com/8fFn5E3.png)

#### Order List Page
![Order List Page](https://i.imgur.com/rh7UG1m.png)

#### Order Information Page
![Order Info Page](https://i.imgur.com/hSspquS.png)

#### Activity List Page
![Activity List Page](https://i.imgur.com/15DXyW5.png)

#### Activity Information Page
![Activity Information Page](https://i.imgur.com/QopdJEz.png)

#### Statistics Page
![Statistics Page](https://i.imgur.com/o4FJcdI.png)

#### Stock Adjustment Page
![Stock Adjustment Page](https://i.imgur.com/VDWtJ6O.png)

#### Settings Page
![Settings Page](https://i.imgur.com/0jKtI5F.png)

#### Role Editing Page
![Role Editing Page](https://i.imgur.com/1OutDlo.png)

## Roadmap

*High to low sort*
- Fix everything in Issues/Bugs
- Add "rotation" option to user limits (along with day, week, etc)
- Update codebase to not store periods in DB for money (store as int, and / or * by 10).
- Let products be purchasable by a parent only.
    - Will require a boolean attribute in users table "parent"
- Repeating activities. When they create activity, ask if repeated daily, weekly, monthly. Create more Activity rows for each day in the duration
    - If repeating activity, add a column (nullable), for root activity, the ID of the original activity it is duplicating
- Disable submit button when anything goes wrong *(Remaining: Stock, Categories)* 
    - Then on the backend, if they somehow bypass the disabled submit button: on errors during order, return back with their input + quantities
- Rework how to select items and quantities. Right now it is somewhat not intuitive. New system:
    - **Similar to GBBC tab, click an item to add it to a list of all items. Click more times for more quantity**
    - Use livewire
- Complete inventory features
    - "Set stock" in adjust page as well as add/subtract.
    - Add product option if stock should be added back upon return or not.
- Change user list to display deleted users (with toggle) & implement un-deleting (for products as well).
    - Livewire?
- Instead/as well as category limits, allow setting a hard limit per day/week.
    - Example: Make user with $35, and each day of the week they can spend max of $5
- Stats: Income by week, month, most popular products etc (In text form on different page)
    - (Todo) View all categories and how many times they have sold (Charts).
    - (Todo) View a user and their count of orders, returns, top products/categories (Charts).
    - (Todo) Staff sales tracking (Charts).
    - (Todo) User's favorite items (Charts).
- Add sales/discounts to item for period of time (automatic or button)
- Add auditing/tracking of everything
    - Role changes
    - New users
    - Price changes
    - Etc
- Bulk change prices of items (Everything 10% *off* or everything 20% *more* etc)
- Add PDF printing of all users transactions
    - In settings page, allow to upload a custom logo to be on invoice
- Allow user to change light/dark mode
- Staff Discount: check if purchaser is staff role and give % off (per item basis) 
- Lazy loading of users/products (especially in cashier view).
    - Use this https://github.com/yajra/laravel-datatables
    - They should type a query first, or use some ajax to fetch data. or else it could take forever to load

## Issues/Bugs
- Rotation end selector is broken in edit/create page
- When an error happens on order screen, it returns back with selection, but when you unselect a box it all gets NaN
- Rotation selection dropdown not working on Stats page + general clean up of it
