<?php
require __DIR__ . '/../bootstrap.php';

use App\Controllers\NominationController;

(new NominationController())->finalSubmit();