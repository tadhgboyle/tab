# tabReborn

A lightweight, selfhosted web app that camps, resorts and schools can use as a simple POS.

:raised_hands: Thank you to [DataTables](https://datatables.net) for the fast and interactive tables.

*Note: Migrations are not up to date. I am manually editing my local MySQL tables and will update migrations when it is more stable.*

Documentation (in the form of a printable user handbook) is being written. 

## Features (so far):

- User control
    - Three different access levels to choose from (Camper, Cashier and Administrator). Change each user's role anytime.
        - Cashiers can ring up orders
        - Administrators can create/edit/delete users, products, categories and settings. They can also view user history and see detailed information about every order.
- Parental control
    - Parents or guardians can set their children a certain amount of money to spend in each category.
    - These limits can be set per-category to either day or week.
- Product control and editing
    - Change the name and price anytime.
    - Optional PST charge on a per-item basis.
- Inventory/Stock management *(In progress)*
    - Depending on each product's settings (See next point), during an order, stock will be automatically removed as per quantity.
    - Each product has some settings:
        - "Stock" / "Unlimited Stock" -> Set stock count for this product, or set it to be unlimited.
        - "Box Size" -> When receiving, instead of counting/multiplying all of the boxes received, set this and receive using boxes.
            - Example: A box of Skittles might have 16 Skittle packs in it. If 4 boxes arrived, instead of adding 16*4 to the stock count, add 4 boxes.
        - "Override Stock" -> Override the stock count for this item.
    - Fast and easy to use stock adjustment page.
        - Uses AJAX to snappily load all available adjustments for the selected product.
- Live preview of price (with taxes) on sidebar during every purchase. If the user does not have enough money the submit button will be disabled (Backend check as well though!)
- Flexible return system
    - Either return a whole order, or:
    - Return an individual item from an order. A counter shows how many are left to be returned. Multiple backend checks to verify validity of return.
- Editable GST and PST percentages.
- Detailed user history and order list pages with fully interactive and searchable tables.
- Detailed statistics charts (Thanks to [Laravel Charts](https://charts.erik.cat/)!)
    - View # of orders and returns.
    - View all products and how many times they have sold.
    - More planned charts in roadmap.

## Screenshots:

#### First Cashier Page
![First Cashier Page](https://images.tadhgboyle.dev/scrn135552.png)

#### Second Cashier Page
![Second Cashier Page](https://images.tadhgboyle.dev/scrn135623.png)

#### User Information Page
![User Info Page](https://images.tadhgboyle.dev/scrn193710.png)

#### Order List Page
![Order List Page](https://images.tadhgboyle.dev/scrn172322.png)

#### Order Information Page
![Order Info Page](https://images.tadhgboyle.dev/scrn185343.png)

#### Chart.js Statistics Page
![Chart.js Statistics Page](https://i.imgur.com/5hCGf3i.png)

#### Stock Adjustment Page
![Stock Adjustment Page](https://i.imgur.com/L6cAMWo.png)

## Roadmap:
*High to Low priority sort*
- Fix everything in Issues/Bugs
- Complete inventory features
    - Remaining: 
        - "Set stock" in adjust page as well as add/subtract.
        - Item sidebar stock alerts? (Not looking forward to this)
- Change CSS to make readonly fields a tint of red to make it more clear they are not editable.
- Create user "pay out" page, which will be used to mark how much a user has paid off of their owing amount.
    - Track what payment method was used, as well as the transaction # + more details (depending on how they pay)
- Staff Discount: check if purchaser is staff role and give % off (per item basis) 
    - Serialize so if it gets returned they get the discount back
- Instead/as well as category limits, allow setting a hard limit per day/week.
    - Example: Make user with $35, and each day of the week they can spend max of $5
- Stats: Income by week, month, most popular products etc (In text form on different page)
    - (Todo) View all categories and how many times they have sold (Charts).
    - (Todo) View a user and their count of orders, returns, top products/categories (Charts).
- Add sales/discounts to item for period of time (automatic or button)
- Add auditing/tracking of everything
    - Role changes
    - New users
    - Price changes
    - Etc
- Bulk change prices of items (Everything 10% *off* or everything 20% *more* etc)
- Add Manager role.
    - All Cashier permissions + adding/editing products
- Add PDF printing of all users transactions
    - In settings page, allow to upload a custom logo to be on invoice
- Seperate orders, users and stats into weeks (Like Green Bay)
- Allow user to change light/dark mode
- Move to Material Design. Bootstrap is ugly

## Issues/Bugs:
- When the order page (re)loads, if a box is checked and the cashier unselects it, everything is thrown off.
    - Fix: On document load, loop thru form and set item sidebar 
- Disable submit button when anything goes wrong (balance, stock etc) 
    - Then on the backend, if they somehow bypass the disabled submit button: on errors during order, return back with their input + quantities
- Change how to select items and quantities. Right now it is somewhat not intuitive.
    - 1. Allow changing quantity after checking box
    - 2. Remove checkbox entirely, and instead consider all items with > 1 quantity as "selected"
    - 3. Similar to GBBC tab, click an item to add it to a list of all items. Click more times for more quantity
- Gracefully handle category deletion.