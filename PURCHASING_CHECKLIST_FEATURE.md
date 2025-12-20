# Purchasing Checklist Feature

## Overview
This document describes the checklist system added to the procurement system for tracking item completion during the purchasing phase.

## Features Added

### 1. Database Schema
- Added `is_checked` (boolean) field to `procurement_items` table
- Added `checked_at` (timestamp) field to track when an item was checked
- Added `checked_by` (foreign key to users) field to track who checked the item

### 2. Seeder: PurchasingPhaseSeeder
Located at: `database/seeders/PurchasingPhaseSeeder.php`

This seeder creates **10 procurement transactions** in the purchasing phase (`in_purchasing` status), where:
- Each transaction has **10 items** (total of 100 items)
- Items include a variety of medical and office equipment
- Each transaction has complete approval history from all approval levels
- Items are initially unchecked

#### Running the Seeder
```bash
php artisan db:seed --class=PurchasingPhaseSeeder
```

### 3. Checklist System UI

#### Viewing Checklist
The checklist is visible on the procurement detail page when:
- The procurement request status is `in_purchasing`
- The logged-in user has the `purchasing` role

#### Features:
1. **Checklist Counter Badge**: Shows "X / Y Checked" at the top of the items table
2. **Check/Uncheck Button**: Each item has a button to toggle its checked status
3. **Visual Indicators**:
   - Checked items have a green background (`table-success`)
   - Checked items show who checked them and when
   - Button changes from outline to solid green when checked
4. **Real-time Updates**: The counter updates immediately when items are checked/unchecked

#### User Experience:
- Only users with the `purchasing` role can check/uncheck items
- Items can only be checked when the procurement is in `in_purchasing` status
- Checking an item records the timestamp and user who checked it
- Unchecking an item removes the check information

### 4. API Endpoint
**Route**: `POST /procurement/items/{item}/toggle-check`

**Controller Method**: `ProcurementController@toggleItemCheck`

**Authorization**: Only users with `purchasing` role

**Response**:
```json
{
    "success": true,
    "is_checked": true,
    "checked_at": "20 Dec 2025 20:07",
    "checked_by": "Purchasing Team"
}
```

## Testing the Feature

### 1. Login as Purchasing User
- Email: `purchasing@phj.com`
- Password: `password`

### 2. View Purchasing Requests
Navigate to the procurement requests page and filter by status `in_purchasing`

### 3. Open a Request
Click on any request in the purchasing phase to view details

### 4. Test Checklist
- Click the check button next to any item
- Observe the visual changes (green background, check icon)
- See the checker's name and timestamp appear
- Click again to uncheck
- Watch the counter update in real-time

## Database Migration

The migration file: `2025_12_20_120725_add_checklist_to_procurement_items_table.php`

To run:
```bash
php artisan migrate
```

To rollback:
```bash
php artisan migrate:rollback
```

## Model Updates

### ProcurementItem Model
Added:
- `is_checked`, `checked_at`, `checked_by` to fillable array
- `is_checked` and `checked_at` to casts array
- `checkedBy()` relationship method to User model

## Code Files Modified/Created

1. **Migration**: `database/migrations/2025_12_20_120725_add_checklist_to_procurement_items_table.php`
2. **Seeder**: `database/seeders/PurchasingPhaseSeeder.php`
3. **Model**: `app/Models/ProcurementItem.php`
4. **Controller**: `app/Http/Controllers/ProcurementController.php`
   - Added `toggleItemCheck()` method
5. **Route**: `routes/web.php`
   - Added POST route for toggling item check
6. **View**: `resources/views/procurement/show.blade.php`
   - Enhanced items table with checklist UI
   - Added JavaScript for AJAX toggle functionality

## Future Enhancements

Possible improvements:
1. Add bulk check/uncheck functionality
2. Export checklist report
3. Add notifications when all items are checked
4. Add item-level notes for the purchasing team
5. Track item delivery status separately from checklist
