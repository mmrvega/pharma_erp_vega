<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use QCod\AppSettings\SavesSettings;
use QCod\AppSettings\Setting\AppSettings;

class SettingController extends Controller
{
    use SavesSettings;

    /**
     * Save POS and general settings
     */
    public function savePOSSettings(Request $request, AppSettings $appSettings)
    {
        // The AppSettings->save() method expects a Request object and processes
        // all defined settings from config. However, for custom settings not in config,
        // we need to manually set them using the set() method.
        // Use the input value rather than has(), because a hidden field with value 0
        // will make has() return true even when the checkbox is unchecked.
        $value = $request->input('print_invoice_on_sale', 0) ? 1 : 0;
        $appSettings->set('print_invoice_on_sale', $value);
        
        return redirect()->route('settings')->with('success', 'Settings saved successfully');
    }
}

