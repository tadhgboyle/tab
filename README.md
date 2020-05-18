# tabReborn

A lightweight, selfhosted web app that camps, resorts and schools can use as a simple POS.

:raised_hands: Thank you to [DataTables](https://datatables.net) for the fast and interactive tables.

*Note: Migrations are not up to date. I am manually editing my local MySQL tables and will update migrations when it is more stable.*

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
- Live preview of price (with taxes) on sidebar during every purchase. If the user does not have enough money the submit button will be disabled (Backend check as well though!).
- Flexible return system
    - Either return a whole order, or:
    - Return an individual item from an order. A counter shows how many are left to be returned. Multiple backend checks to verify validity of return.
- Editable GST and PST percentages per product.
- Detailed user history and order list pages with fully interactive and searchable tables.

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

## Roadmap:
*High to Low priority sort*
- Staff Discount: check if purchaser is staff role and give % off (per item basis) 
    - Serialize so if it gets returned they get the discount back
- Add sales/discounts to item for period of time (automatic or button)
- Add auditing/tracking of everything
    - Role changes
    - New users
    - Price changes
    - Etc
- Stats: Income by week, month, most popular products etc
- Add stock/inventory features
- Bulk change prices of items (Everything 10% *off* or everything 20% *more* etc)
- Add Manager role.
    - All Cashier permissions + adding/editing products
- Merge index and orders page with ajax for better preformance
- Add PDF printing of all users transactions
- For parents to pay off balance possibly integrate with Square API
- Seperate orders, users and stats into weeks (Like Green Bay)
- Allow user to change light/dark mode
- Move to Material Design. Bootstrap is ugly

## Issues/Bugs:
- Inconsistent miscalculations during returns and limit checking. Sometimes it happens and sometimes it doesn't.
- When new categories are created, everyone defaults to `0.00` limit. Change this to `-1`.
- Gracefully handle user/product/category/order deletion.