<?php
require 'bootstrap/app.php';

$password = 'password';
echo \Illuminate\Support\Facades\Hash::make($password);
