<?php
require_once __DIR__.'/bootstrap.php';
function require_login(): void { require_user(); }
function require_role(string ...$roles): void { require_user(...$roles); }
