<?php namespace Dilexus\Octobase\Models;

use Model;

class Settings extends Model {
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'octobase_settings';
    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

}
