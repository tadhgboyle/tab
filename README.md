# tabReborn

A lightweight, selfhosted web app that camps, resorts and schools can use as a simple POS.

#### Credits

- :page_with_curl: [DataTables](https://datatables.net) for the fast and interactive tables.
- :bar_chart: [ChartJS](https://www.chartjs.org) for the beautiful statistics charts, and [ConsoleTVs/Charts](https://github.com/ConsoleTVs/Charts) for the Laravel package.
- :art: [Bulma](https://bulma.io) for their lightweight, easy to use and great-looking CSS framework.

*Note: Some migrations are not up to date. I am manually editing my local MySQL tables and will update migrations when it is more stable.*

Documentation (in the form of a printable user handbook) is being written. 

## Features (so far):

- User control
    - Three different access levels to choose from (Camper, Cashier and Administrator). Change each user's role anytime.
        - Cashiers can ring up orders
        - Administrators can create/edit/delete users, products, categories and settings. They can also view user history and see detailed information about every order.
        - Setting to control if Cashiers can ring up themselves or not.
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
![First Cashier Page](https://images.tadhgboyle.dev/scrn135552.png)

#### Second Cashier Page
![Second Cashier Page](https://i.imgur.com/D373Qwt.png)

#### User Information Page
![User Info Page](https://i.imgur.com/iOHUU4U.png)

#### Order List Page
![Order List Page](https://i.imgur.com/rLcKxDx.png)

#### Order Information Page
![Order Info Page](https://i.imgur.com/rrlUtO4.png)

#### Chart.js Statistics Page
![Chart.js Statistics Page](https://i.imgur.com/E4IG9Hr.png)

#### Stock Adjustment Page
![Stock Adjustment Page](https://i.imgur.com/kkwQgHy.png)

## Roadmap:

*High to Low priority sort*
- Fix everything in Issues/Bugs
- Order status between "Normal" and "Returned" for when some items (but not all) are returned.
- Disable submit button when anything goes wrong *(Remaining: Stock, Categories)* 
    - Then on the backend, if they somehow bypass the disabled submit button: on errors during order, return back with their input + quantities
- Change how to select items and quantities. Right now it is somewhat not intuitive. Options:
    - 1. Allow changing quantity after checking box
    - 2. Remove checkbox entirely, and instead consider all items with >= 1 quantity as "selected"
    - 3. Similar to GBBC tab, click an item to add it to a list of all items. Click more times for more quantity
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
- Add Manager role (Or let them make all their own roles with permissions?).
    - All Cashier permissions + adding/editing products
- Add PDF printing of all users transactions
    - In settings page, allow to upload a custom logo to be on invoice
- Seperate orders, users and stats into weeks (Like Green Bay)
- Allow user to change light/dark mode
- Staff Discount: check if purchaser is staff role and give % off (per item basis) 
    - Serialize so if it gets returned they get the discount back

## Issues/Bugs:
- Categories should use an ID, and be serialized into each order product incase they are deleted.
- When an error happens on order screen, it returns back with selection, but when you unselect a box it all gets NaN
- Fix alerts close button + auto fading.
- Use `route()` helper incase we need to easily change routes.
- Dont import classes in views, use controllers to return views instead.

## Contributors:
- nuKeMan4
- Locus