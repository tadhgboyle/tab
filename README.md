# tab

A lightweight, selfhosted web app that camps, resorts and schools can use as a Point Of Sale system.

## Features

- User control
    - Superusers can create as many roles as they want, and grant them specific permissions as well as hierarchy.
        - Some permissions include: 
            - `users_list`: This role can view the user list.
            - `users_manage`: This role can edit/create users (but only edit users which have a role lower than theirs in terms of hierarchy).
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
        - "Override Stock" -> Override the stock count for this item.
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
- Let products be purchasable by a parent only.
    - Will require a boolean attribute in users table "parent"
- Repeating activities. When they create activity, ask if repeated daily, weekly, monthly. Create more Activity rows for each day in the duration
    - If repeating activity, add a column (nullable), for root activity, the ID of the original activity it is duplicating
- Disable submit button when anything goes wrong *(Remaining: Stock, Categories)* 
    - Then on the backend, if they somehow bypass the disabled submit button: on errors during order, return back with their input + quantities
- Complete inventory features
    - "Set stock" in adjust page as well as add/subtract.
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
- Bulk change prices of items (Everything 10% *off* or everything 20% *more* etc)
- Staff Discount: check if purchaser is staff role and give % off (per item basis) 
- Tax-free products / users
- User tags (tax free tagged, limit of xyz tag, etc)
- Allow categories to be PST and/or GST exempt
- Move "ajax" routes to API controllers + paths
- Update services to use enums for result statuses
- Emails for a bunch of stuff:
    - Gift card given (would need to let gift cards optionally have an assigned user, but should prolly still be able to be used by anyone)
    - Admin emails: New user made, settings changed, etc
- Store credit
- Convert jquery ajax requests to use axios
- Let orders use multiple gift cards
- Create `Cart` model to represent an in-flight order, then use to implement cashier functionality with livewire and get rid of the fuckin item-sidebar.js
- Ability to mark products as final sale/cannot be returned
- Add returns to user timelines
- Dedicated returns page for orders where they can return n of each product at once or the whole thing
- Move Categories to Products subnav (and gift cards??)

## Issues/Bugs
- When cashier page refreshed with gift card, ensure it still has balance + update balance in table row
- Make seeders only create past entities nothing in the future
