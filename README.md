# tabReborn

A lightweight, selfhosted web app that camps, resorts and schools can use as a simple POS.

#### Credits

- :page_with_curl: [DataTables](https://datatables.net) for the fast and interactive tables.
- :bar_chart: [ChartJS](https://www.chartjs.org) for the beautiful statistics charts, and [ConsoleTVs/Charts](https://github.com/ConsoleTVs/Charts) for the Laravel package.
- :art: [Bulma](https://bulma.io) for their lightweight, easy to use and great-looking CSS framework.
- :white_check_mark::x: [Switchery](https://github.com/abpetkov/switchery) for the simple to use & nice looking Switches replacement for ugly HTML checkboxes.
- :calendar: [FullCalendar](https://fullcalendar.io) for their simple and responsive calendar library.

*Note: Some migrations are not up to date. I am manually editing my local MySQL tables and will update migrations when it is more stable.*

Documentation (in the form of a printable user handbook) is being written. 

## Features (so far):

- User control
    - Superusers can create as many roles as they want, and grant them specific permissions as well as hierarchy.
        - Some permissions include: 
            - `users_list`: This role can view the user list.
            - `users_manage`: This role can edit/create users (but only edit users which have a role lower than theirs in terms of hierarchy).
            - `products_adjust`: This role can adjust product stock, but they will need `products_list` and `products_manage` in order to list or create products.
            - `settings_categories_manage`: This role can create/edit product categories in the Settings page.
            - And many more! I try to add as much customizability as possible for permissions.
    - Users can be edited anytime, with an easy to use interface.
    - Users can be (soft) deleted anytime.
- Parental control
    - Parents or guardians can set their children a certain amount of money to spend in each category.
    - These limits can be set per-category to either day or week.
- Product control and editing
    - Change the name and price anytime.
    - Optional PST charge on a per-item basis.
- Inventory/Stock management
    - Depending on each product's settings (See next point), during an order, stock will be automatically removed as per quantity.
    - Each product has some settings:
        - "Stock" / "Unlimited Stock" -> Set stock count for this product, or set it to be unlimited.
        - "Box Size" -> When receiving, instead of counting/multiplying all of the boxes received, set this and receive using boxes.
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

## Installation:

*Todo*

## Screenshots:

#### First Cashier Page
![First Cashier Page](https://i.imgur.com/8DQ9LN2.png)

#### Second Cashier Page
![Second Cashier Page](https://i.imgur.com/e8upEMU.png)

#### User Information Page
![User Info Page](https://i.imgur.com/7qMsy2E.png)

#### Order List Page
![Order List Page](https://i.imgur.com/UIDVpjB.png)

#### Order Information Page
![Order Info Page](https://i.imgur.com/SZkOOmn.png)

#### Chart.js Statistics Page
![Chart.js Statistics Page](https://i.imgur.com/Qqhmj5z.png)

#### Stock Adjustment Page
![Stock Adjustment Page](https://i.imgur.com/inUoVcl.png)

#### Settings Page
![Settings Page](https://i.imgur.com/nY7jMg9.png)

#### Role Editing Page
![Role Editing Page](https://i.imgur.com/n6H2hWQ.png)

## Roadmap:

*High to Low priority sort*
- Fix everything in Issues/Bugs
- Let categories be for just products or just activities or both
- Let products be purchasable by a parent only.
    - Will require a boolean attribute in users table "parent"
- Repeating activities. When they create activity, ask if repeated daily, weekly, monthly. Create more Activity rows for each day in the duration
    - If repeating activity, add a column (nullable), for root activity, the ID of the original activity it is duplicating
- Disable submit button when anything goes wrong *(Remaining: Stock, Categories)* 
    - Then on the backend, if they somehow bypass the disabled submit button: on errors during order, return back with their input + quantities
- Rework how to select items and quantities. Right now it is somewhat not intuitive. New system:
    - **Similar to GBBC tab, click an item to add it to a list of all items. Click more times for more quantity**
- Complete inventory features
    - Remaining: 
        - "Set stock" in adjust page as well as add/subtract.
        - Add product option if stock should be added back upon return or not.
- Change user list to display deleted users (with toggle) & add an undelete function. (All for products as well).
- Create user "pay out" page, which will be used to mark how much a user has paid off of their owing amount.
    - Track what payment method was used, as well as the transaction # + more details (depending on how they pay)
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
- Seperate orders, users and stats into weeks (Like Green Bay)
- Allow user to change light/dark mode
- Staff Discount: check if purchaser is staff role and give % off (per item basis) 
    - Serialize so if it gets returned they get the discount back

## Issues/Bugs:
- Fix js in Role form page, staff and superuser checkboxes return undefined
- Categories should use an ID, and be serialized into each order product incase they are deleted.
- Dont import classes in views, use controllers to return views instead.
- (Intermittent) When an error happens on order screen, it returns back with selection, but when you unselect a box it all gets NaN

## Contributors:
- @nUKEmAN4
- Locus