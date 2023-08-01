<?php namespace Dilexus\Octobase\Models;

//
// Copyright 2023 Chatura Dilan Perera. All rights reserved.
// Use of this source code is governed by license that can be
//  found in the LICENSE file.
// Website: https://www.dilan.me
//

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'octobase_settings';
    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
}
