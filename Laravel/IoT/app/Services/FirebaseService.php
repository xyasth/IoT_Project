<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase-service-account.json'));
        $this->database = $factory->createDatabase();
    }

    public function getLogs()
    {
        return $this->database->getReference('sensor_data')->getValue();
    }
}
