# Bug Fix: BadMethodCallException in SettingController

## Issue
**Error:** `BadMethodCallException: Method App\Http\Controllers\Admin\SettingController::settings does not exist.`

## Root Cause
The `SavesSettings` trait from QCod/AppSettings doesn't provide a `settings()` method. The correct way to save settings is to:
1. Inject `AppSettings` instance via dependency injection
2. Call the `save()` method on that instance with an array of settings

## Solution Applied

### Changed File: `app/Http/Controllers/Admin/SettingController.php`

**Before:**
```php
public function savePOSSettings(Request $request)
{
    $this->settings()->save('print_invoice_on_sale', 
        $request->has('print_invoice_on_sale') ? 1 : 0);
    
    return redirect()->route('settings')->with('success', 'Settings saved successfully');
}
```

**After:**
```php
use QCod\AppSettings\Setting\AppSettings;

public function savePOSSettings(Request $request, AppSettings $appSettings)
{
    $appSettings->save([
        'print_invoice_on_sale' => $request->has('print_invoice_on_sale') ? 1 : 0
    ]);
    
    return redirect()->route('settings')->with('success', 'Settings saved successfully');
}
```

## Key Changes
1. Added `use QCod\AppSettings\Setting\AppSettings;` to imports
2. Changed method signature to inject `AppSettings $appSettings`
3. Changed from `$this->settings()->save()` to `$appSettings->save([])`
4. Passed settings as array: `['key' => 'value']`

## How to Access Settings in Views/Controllers

### In Blade Templates (Views):
```php
{{ settings('print_invoice_on_sale', 0) }}
```

### In Controllers (with AppSettings injection):
```php
public function someMethod(AppSettings $appSettings)
{
    $value = $appSettings->get('print_invoice_on_sale', 0);
}
```

## Related Files Using This Pattern
- `resources/views/admin/settings.blade.php` - Form to save settings
- `resources/views/admin/dashboard.blade.php` - Reads setting to show/hide button text
- `routes/web.php` - Route definition for `settings.save`

## Testing
All syntax checks passed:
```
✓ No PHP syntax errors in SettingController
✓ Route properly defined in routes/web.php
✓ Blade template correctly uses settings() helper
✓ Cache cleared successfully
```

## Verification Steps
1. Navigate to Settings page: `/admin/settings`
2. Find "POS Settings" card
3. Check "Print Invoice on Sale" checkbox
4. Click "Save Settings"
5. Verify checkbox state persists after page reload
6. Go to Dashboard and verify button text changes based on setting
