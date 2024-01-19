<?php

// config for Envor/DatabaseManager
return [
    /***
     * The disk where the sqlite database files will be stored.
     */
    'sqlite_disk' => 'local',

    /***
     * Available drivers that can be managed.
     */
    'managers' => [
        'sqlite' => \Envor\DatabaseManager\SQLiteDatabaseManager::class,
        'mysql' => \Envor\DatabaseManager\MySQLDatabaseManager::class,
    ],
];
