<?php

use Illuminate\Support\Facades\Config;

Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite.database', ':memory:');
