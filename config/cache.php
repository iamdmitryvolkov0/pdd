<?php

return ['default' => env('CACHE_STORE', 'database'), 'stores' => ['database' => ['driver' => 'database', 'connection' => null, 'table' => 'cache', 'lock_connection' => null, 'lock_table' => 'cache_locks']], 'prefix' => 'vector_cache'];
