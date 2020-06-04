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
- Inventory/Stock management *(In progress)*
    - Each product has some settings:
        - "Stock" / "Unlimited Stock" -> Set stock count for this product, or set it to be unlimited.
        - "Box Size" -> When receiving, instead of counting/multiplying all of the boxes received, set this and receive using boxes.
            - Example: A box of Skittles might have 16 Skittle packs in it. If 4 boxes arrived, instead of adding 16*4 to the stock count, add 4 boxes.
        - "Override Stock" -> Override the stock count for this item.
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
![Chart.js Statistics Page](https://i.imgur.com/AsHG1iD.png)

## Roadmap:
*High to Low priority sort*
- Fix everything in Issues/Bugs
- Complete inventory features
- Simplify user creation/editing into 1 view
- When editing/creating a product, dynamically hide fields which are irrelevent
    - Example: Don't show stock field when unlimited stock is selected
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
- For parents to pay off balance possibly integrate with Square/Paypal API
- Seperate orders, users and stats into weeks (Like Green Bay)
- Allow user to change light/dark mode
- Move to Material Design. Bootstrap is ugly

## Issues/Bugs:
- Gracefully handle category deletion.