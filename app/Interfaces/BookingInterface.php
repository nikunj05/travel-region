<?php

namespace App\Interfaces;

interface BookingInterface
{
    public function index($request);

    public function store($request);

    public function checkCoupon($request);
}
