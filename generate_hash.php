<?php
require 'bootstrap/app.php';

$password = 'admin123';
echo \Illuminate\Support\Facades\Hash::make($password);
